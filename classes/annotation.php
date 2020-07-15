<?php

namespace mod_ivs;

//use \mod_ivs\exception;

use html_writer;
use \mod_ivs\service;
use user_picture;

global $CFG;

defined('MOODLE_INTERNAL') || die();

define('MOD_ivs', 'ivs');
define('MOD_ivs_COMMENT', 'ivs_videocomment');
define('MOD_ivs_VC_ACCESS', 'ivs_vc_access');

class annotation {

    private $id;
    private $body;
    private $video_id;
    private $time_stamp;
    private $duration;
    private $thumbnail;
    private $user_id;
    private $additional_data;
    private $timemodified;
    private $timecreated;
    private $access_view;
    private $courseService;
    private $parent_id;
    protected $replies;

    function __construct($annotation = false) {

        if (is_object($annotation)) {
            $annotation->additional_data = unserialize($annotation->additional_data);
            $annotation->access_view = unserialize($annotation->access_view);
            $this->setRecord($annotation);
        } else if (is_numeric($annotation)) {
            //load from db
        } else {

        }
        $this->replies = array();

    }

    /**
     * @param $annotations
     * @param $annotation_ids
     */
    public static function load_replies(&$annotations) {

        global $DB;

        $annotation_ids = array();
        /** @var \mod_ivs\annotation $annotation */
        foreach ($annotations as $annotation) {
            $annotation_ids[] = $annotation->getId();
        }

        if (empty($annotation_ids)) {
            return;
        }

        list($insql, $inparams) = $DB->get_in_or_equal($annotation_ids);
        $params = $annotation_ids;

        $query = 'SELECT * FROM {ivs_videocomment} WHERE parent_id ' . $insql . ' ORDER BY time_stamp ';

        $replies = $DB->get_records_sql($query, $params);

        foreach ($replies as $reply) {
            $annotations[$reply->parent_id]->replies[] = new annotation($reply);
        }
    }

    /**
     * Create of update a record in the database
     *
     * @return bool
     */
    public function save_to_db() {
        global $DB;
        $this->time_modified = time();

        $db_record = $this->getRecord();

        $db_record['additional_data'] = serialize($db_record['additional_data']);
        $db_record['access_view'] = serialize($db_record['access_view']);

        $save = false;
        if (isset($this->id)) {
            $save = $DB->update_record('ivs_videocomment', $db_record);
            if ($save) {
                $save = $this->write_annotation_access();
            }
        } else {
            if ($id = $DB->insert_record('ivs_videocomment', $db_record)) {
                $this->id = $id;
                $save = $this->write_annotation_access();
            }
        }

        return $save;
    }

    public function delete_from_db($annotation = null) {
        global $DB;

        //Delete recomments access
        $recomments = $DB->get_records('ivs_videocomment', array('parent_id' => $annotation->id));
        foreach ($recomments as $recomment) {
            $DB->delete_records('ivs_vc_access', array('annotation_id' => $recomment->id));
        }

        //Delete comments access
        $DB->delete_records('ivs_vc_access', array('annotation_id' => $annotation->id));

        //Delete recomments
        $DB->delete_records('ivs_videocomment', array('parent_id' => $annotation->id));

        //Delete comments
        $DB->delete_records('ivs_videocomment', array("id" => $this->id));

        //TODO - check if there are other annotations in this video with the same timestamp. if not, delete the  file
        //$DB->delete_records('files', array("itemid" => $this->getPreviewId()));

    }

    /**
     * Load an annotation from the database
     *
     * @param $annotation_id
     * @param bool $load_replies
     * @return \mod_ivs\annotation
     */
    public static function retrieve_from_db($annotation_id, $load_replies = false) {
        global $DB;

        $annotation_data = $DB->get_record('ivs_videocomment', array('id' => $annotation_id));

        if (is_object($annotation_data)) {
            $annotation = new annotation($annotation_data);

            if ($load_replies) {
                $annotations[$annotation->getId()] = $annotation;
                self::load_replies($annotations);
            }
            return $annotation;
        }
        return null;
    }

    public static function retrieve_from_db_by_video($video_nid, $grants = null, $offset = 0, $limit = 0, $count_only = false) {
        global $DB, $USER;

        $annotations = array();

        //$activity_context = \context_module::instance($id);
        // $activity = \context_module::instance($video_nid);
        //  $course_module         = get_coursemodule_from_id('ivs', $video_nid, 0, false, MUST_EXIST);
        $course_module = get_coursemodule_from_instance('ivs', $video_nid, 0, false, MUST_EXIST);
        $course_id = $course_module->course;
        $activity = \context_module::instance($course_module->id);

        //build the  base query
        $query = "SELECT * FROM {ivs_videocomment} vc WHERE video_id = ? AND parent_id IS NULL";
        $parameters = array($video_nid);

        if (!self::hasCapabilityViewAnyComment($activity)) {

            if ($grants === null) {
                $grants = self::get_user_grants($USER->id, $course_id);
            }

            list($access_query, $access_parameters) =
                    self::get_user_grants_query($grants['user'], $grants['course'], $grants['group'], $grants['role']);

            if (!empty($access_query)) {
                $query .= " AND " . $access_query;
            }
            $parameters = array_merge($parameters, $access_parameters);

        }

        //add order options

        $query .= " Order by time_stamp, timecreated";

        if ($count_only) {
            $query = "SELECT DISTINCT COUNT(vc.id) as total FROM {ivs_videocomment} vc WHERE video_id = ? AND parent_id IS NULL";
            return $DB->get_record_sql($query, [$video_nid]);
        }

        $data = $DB->get_records_sql($query, $parameters, (int) $offset, (int) $limit);

        foreach ($data as $record) {
            $annotations[$record->id] = new annotation($record);
        }

        if (!empty($annotations)) {
            self::load_replies($annotations);
        }

        //        $data = $DB->get_records_sql('SELECT * FROM {ivs_videocomment} WHERE video_id = ? AND parent_id IS NULL ORDER BY time_stamp', array($video_nid));

        return $annotations;
    }

    public function write_annotation_access(\stdClass $access_view = null) {
        global $DB;

        if (!isset($access_view)) {
            $access_view = $this->access_view;
        }

        if (!empty($this->parent_id)) {
            return;
        }

        //delete all ralms prior to insert

        $DB->delete_records(MOD_ivs_VC_ACCESS, array("annotation_id" => $this->id));

        $record = array();
        $record[] = array("annotation_id" => $this->id, "realm" => 'author', 'rid' => $this->user_id);
        if ($access_view->realm == 'course') {

            $course_module = get_coursemodule_from_instance('ivs', $this->video_id, 0, false, MUST_EXIST);
            $course_id = $course_module->course;
            $record[] = array("annotation_id" => $this->id, "realm" => 'course', 'rid' => $course_id);

        } else if ($access_view->realm == 'private') {
            $record[] = array("annotation_id" => $this->id, "realm" => 'private', 'rid' => $this->user_id);
        } else {
            if (isset($access_view->gids)) {
                foreach ($access_view->gids as $rid) {
                    $record[] = array(
                            "annotation_id" => $this->id,
                            "realm" => $access_view->realm,
                            "rid" => $rid
                    );
                }
            } else {
                $record[] = array(
                        "annotation_id" => $this->id,
                        "realm" => $access_view->realm
                );
            }
        }

        $save = true;

        foreach ($record as $rec) {
            $save = $DB->insert_record(MOD_ivs_VC_ACCESS, $rec, false, true);
        }

        return $save;
    }

    public static function get_user_grants($moodle_user_id, $course_id) {
        $grants = array();
        $grants['user'] = $moodle_user_id;
        $grants['course'] = $course_id;

        //$grants['author'] = $this->user_id;

        $groups = CourseService::getUserCourseGroups($course_id, $moodle_user_id);

        $arr = array();
        foreach ($groups as $group) {
            //$arr[] = array('group' => $group->id);
            $arr[] = $group->id;
        }
        $grants['group'] = $arr;

        $roles = CourseService::getUserCourseRoleAssignments($course_id, $moodle_user_id);

        $arr = array();
        foreach ($roles as $role) {
            //$arr[] = array('role' => $role->roleid);
            $arr[] = $role->roleid;
        }
        $grants['role'] = $arr;

        return $grants;
    }

    public function get_user_grants_as_index($moodle_user, $course_id) {
        $grants = array();
        $grants[0] = array('user_id' => $moodle_user->id);
        $grants[1] = array('course' => $course_id);
        $grants[2] = array('author' => $this->user_id);

        $groups = $this->getCourseService()->getUserCourseGroups($course_id, $moodle_user->id);
        foreach ($groups as $group) {
            $grants[] = array('group' => $group->id);
        }
        $roles = $this->getCourseService()->getUserCourseRoleAssignments($course_id, $moodle_user->id);
        foreach ($roles as $role) {
            $grants[] = array('role' => $role->roleid);
        }
        return $grants;
    }

    /**
     * @return mixed
     */
    public function getParentId() {
        return $this->parent_id;
    }

    /**
     * @param mixed $parent_id
     */
    public function setParentId($parent_id) {
        $this->parent_id = $parent_id;
    }

    /**
     * Create moodle events so observers can react
     *
     * @param $op
     * @param null $additional_data
     * @throws \coding_exception
     */
    protected function message_api($op, $additional_data = null) {

        /* $course_module = get_coursemodule_from_instance('ivs', $this->video_id, 0, FALSE, MUST_EXIST);
         $course_id = $course_module->course;
         $activity_context = \context_module::instance($this->video_id);*/
        $course_module = get_coursemodule_from_instance('ivs', $this->video_id, 0, false, MUST_EXIST);
        $course_id = $course_module->course;
        $activity = \context_module::instance($course_module->id);

        $params = array(
                'objectid' => $this->id,
                'contextid' => $activity->id,
                'courseid' => $course_id
        );

        switch ($op) {
            case 'created':
                $event = \mod_ivs\event\annotation_created::create($params);
                $event->add_record_snapshot('ivs_videocomment', (object) $this->getRecord());
                break;
            case 'updated':
                $event = \mod_ivs\event\annotation_updated::create($params);
                $event->add_record_snapshot('ivs_videocomment', (object) $this->getRecord());
                break;
            case 'deleted':
                $event = \mod_ivs\event\annotation_deleted::create($params);
                $event->add_record_snapshot('ivs_videocomment', (object) $this->getRecord());
                break;
        }

        $event->trigger();
    }

    /**
     * Get the ids of all users that replied to this comment
     *
     * @param bool $exlude_author
     * @return array
     */
    public function getReplyUsers($exclude_author = true) {
        global $DB;

        $parameters = array(
                $this->getId()
        );

        if ($exclude_author) {
            $exclude_sql = " AND user_id != ?";
            $parameters[] = $this->getUserId();
        }

        $sql = "SELECT DISTINCT u.*
            FROM {user} u
            JOIN {ivs_videocomment} vc ON vc.user_id = u.id
            WHERE parent_id = ? $exclude_sql";

        $uids = $DB->get_records_sql($sql, $parameters);

        return $uids;

    }

    /**
     * @return array
     */
    public function getReplies() {
        return $this->replies;
    }

    private function get_user_params($user_id, $course_id, $group_id, $role_id) {
        return array(
                'course_id' => $course_id,
                'video_id' => $this->video_id,
                'user_id' => $user_id,
                'group_id' => $group_id,
                'role_id' => $role_id
        );
    }

    /**
     * Build a query and parameters for the access check of video comments that can be added to a where group.
     *
     * @param $user_id
     * @param $course_id
     * @param $group_ids
     * @param $role_ids
     * @return array
     */
    public static function get_user_grants_query($user_id, $course_id, $group_ids, $role_ids) {
        $access_parameters = array(
                $course_id,
                $user_id,
                $user_id
        );

        //build the base query for access
        $sql = ' EXISTS(
              SELECT ac.id AS acid FROM {ivs_vc_access} ac WHERE 
              ac.annotation_id = vc.id AND (
              (ac.rid = ? AND ac.realm = \'course\') OR
              (ac.rid = ? AND ac.realm = \'member\') OR (ac.rid = ? AND ac.realm = \'author\')';

        //add group realms if needed
        $group_query = '';
        if (!empty($group_ids)) {
            foreach ($group_ids as $gid) {
                $group_query .= ' OR (ac.rid = ' . $gid . ' AND ac.realm = \'group\')';
                $access_parameters[] = $gid;
            }
        }

        //add the  role realms if needed
        $role_query = '';
        if (!empty($role_ids)) {
            foreach ($role_ids as $rid) {
                $role_query .= ' OR (ac.rid = ' . $rid . ' AND ac.realm = \'role\')';
                $access_parameters[] = $rid;
            }
        }

        //build the  complete access query
        $sql = $sql . $group_query . $role_query . '))';

        return array($sql, $access_parameters);
    }

    public function from_request_body($request_body, $parent_id = null) {
        global $USER;

        $this->body = $request_body->body;
        $this->time_stamp = $request_body->timestamp;

        if (!empty($request_body->drawing_data)) {
            $this->additional_data['drawing_data'] = $request_body->drawing_data;
        }

        if (isset($request_body->rating)) {
            $this->additional_data['rating'] = $request_body->rating;
        }

        if ($request_body->access_settings) {
            $this->additional_data['access'] = $request_body->access_settings;
            $this->access_view = $request_body->access_settings;
        }

        $ACTION = "created";

        //pin mode
        if (!empty($request_body->pinmode)) {
            $this->additional_data['pinmode'] = $request_body->pinmode;
        }

        if (isset($request_body->pinmode_pause_seconds)) {
            $this->additional_data['pinmode_pause_seconds'] = $request_body->pinmode_pause_seconds;
        }

        //new annotation
        if ($this->id == null) {
            //set values that can not be changed on updates
            $this->timecreated = time();
            $this->user_id = $USER->id;
            $this->parent_id = $parent_id;

            if (empty($parent_id)) {
                $this->video_id = $request_body->video_nid;

            } else {
                $parent_annotation = annotation::retrieve_from_db($parent_id);
                $this->video_id = $parent_annotation->getVideoId();

            }
        } else {
            //edit annotation
            $ACTION = "updated";
        }

        $this->timemodified = time();

        $this->save_to_db();

        if ($request_body->preview) {
            $this->savePreviewImage($request_body->preview);
        }

        //Fire event Api (Generate Message)
        $this->message_api($ACTION, $request_body);
    }

    /**
     * Save Preview Image as base 64
     *
     * @param $image_base64
     * @param $annotation_id
     * @return \stored_file
     * @throws \file_exception
     * @throws \stored_file_creation_exception
     */
    public function savePreviewImage($image_base64) {

        if (empty($this->id)) {
            return;
        }

        $course_module = get_coursemodule_from_instance('ivs', $this->video_id, 0, false, MUST_EXIST);
        $context = \context_module::instance($course_module->id);

        $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image_base64));

        //reload from db to get the proper timestamp value
        $annotation_db = self::retrieve_from_db($this->getId());

        $itemid = $annotation_db->getPreviewId();

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_ivs', 'preview', $itemid, 'itemid', false);

        $existing_file = $value = end($files);

        $fileinfo = array(
                'contextid' => $context->id,
                'component' => 'mod_ivs',
                'filearea' => 'preview',
                'itemid' => $itemid,
                'filepath' => '/',
                'filename' => 'preview.jpg');

        if ($existing_file) {
            $existing_file->delete();

        }
        $file = $fs->create_file_from_string($fileinfo, $image_data);

        return $file;
    }

    /**
     * Get the normalized timecode id for the preview image
     *
     * @return int
     */
    function getPreviewId() {
        return (int) ($this->time_stamp * 1000);
    }

    /**
     * Get Preview Image
     *
     * @param $annotation_id
     * @throws \coding_exception
     */
    public function getPreviewURL() {

        $itemid = $this->getPreviewId();

        $course_module = get_coursemodule_from_instance('ivs', $this->video_id, 0, false, MUST_EXIST);
        $context = \context_module::instance($course_module->id);

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_ivs', 'preview', $itemid, 'itemid', false);

        $file = $value = end($files);
        if (!empty($file)) {
            $url = \moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename(),
                    false);

            return $url;
        }

        return null;
    }

    /**
     * Lock the access field so only privilleged users can edit the access
     *
     * @param $access_settings
     */
    public function lockAccess($access_settings) {

        $this->additional_data['access'] = $access_settings;
        $this->access_view = $access_settings;
        $this->additional_data['access_locked'] = true;

        $this->timemodified = time();

        $this->save_to_db();

        //Fire event Api (Generate Message)
        //$this->message_api("update",$access_settings);

    }

    public function getPlayerUserData() {
        return IvsHelper::getUserDataForPlayer($this->user_id);
    }

    public function getPlayerPermissions() {

        return array(
                'update' => $this->access('edit'),
                'delete' => $this->access('delete'),
                'reply' => true,
                'edit_access' => $this->access("lock_access")
        );
    }

    /**
     * Get the database value fields from this class
     *
     * @return array
     */
    public function getRecord() {
        return array(
                'id' => $this->id,
                'body' => $this->body,
                'video_id' => $this->video_id,
                'time_stamp' => $this->time_stamp,
                'duration' => (int) $this->duration,
                'thumbnail' => $this->thumbnail,
                'user_id' => $this->user_id,
                'timecreated' => (int) $this->timecreated,
                'timemodified' => (int) $this->timemodified,
                'additional_data' => $this->additional_data,
                'access_view' => $this->access_view,
                'parent_id' => $this->parent_id,
        );
    }

    public function toPlayerComment() {
        global $PAGE;
        $object = (object) $this->getRecord();

        //additional_data
        if (isset($object->additional_data['drawing_data'])) {
            $object->drawing_data = $object->additional_data['drawing_data'];
        }
        if (isset($object->additional_data['rating'])) {
            $object->rating = $object->additional_data['rating'];
        }
        if (isset($object->additional_data['access'])) {
            $object->access_settings = $object->additional_data['access'];
        } else {
            $object->access_settings = array();
        }
        if ($object->body == null) {
            $object->body = "";
        }

        //pinmode
        if (isset($object->additional_data['pinmode'])) {
            $object->pinmode = $object->additional_data['pinmode'];
        }

        if (isset($object->additional_data['pinmode_pause_seconds'])) {
            $object->pinmode_pause_seconds = $object->additional_data['pinmode_pause_seconds'];
        }

        unset($object->additional_data);

        //timestamp rename
        $object->timestamp = $object->time_stamp;
        unset($object->time_stamp);

        //add user_data
        $object->userdata = $this->getPlayerUserData();
        $object->perms = $this->getPlayerPermissions();

        $object->nid = $this->id;
        unset($object->id);

        foreach ($this->replies as $reply) {
            $object->replies[] = $reply->toPlayerComment();
        }

        return $object;
    }

    /**
     * Populate an object we can store in the database
     *
     * @param $db_record
     */
    private function setRecord($db_record) {
        $this->id = $db_record->id;
        $this->body = $db_record->body;
        $this->video_id = $db_record->video_id;
        $this->time_stamp = $db_record->time_stamp;
        $this->duration = $db_record->duration;
        $this->thumbnail = $db_record->thumbnail;
        $this->user_id = $db_record->user_id;
        $this->timecreated = $db_record->timecreated;
        $this->timemodified = $db_record->timemodified;
        $this->additional_data = $db_record->additional_data;
        $this->access_view = $db_record->access_view;
        $this->parent_id = $db_record->parent_id;

    }

    public function access($op) {

        $course_module = get_coursemodule_from_instance('ivs', $this->video_id, 0, false, MUST_EXIST);
        $context = \context_module::instance($course_module->id);

        global $USER;
        //require_capability('mod/quiz:view', $context);

        switch ($op) {
            case 'view':
                if (is_siteadmin()) {
                    return true;
                }
                //we are creator
                if (!empty($this->user_id) && $this->user_id == $USER->id) {
                    return true;
                }
                //instance permission
                if (has_capability('mod/ivs:view_any_comment', $context)) {
                    return true;
                }
                break;
            case 'edit':
            case 'delete':
                if (is_siteadmin()) {
                    return true;
                }
                //we are creator
                if (!empty($this->user_id) && $this->user_id == $USER->id) {
                    return true;
                }
                //instance permission
                if (has_capability('mod/ivs:edit_any_comment', $context)) {
                    return true;
                }
                break;
            case "lock_access":
                if (is_siteadmin()) {
                    return true;
                }
                //we are creator
                if (!empty($this->user_id) && $this->user_id == $USER->id) {
                    if (isset($this->additional_data['access_locked'])) {
                        return !$this->additional_data['access_locked'];
                    }
                    return true;
                }
                //instance permission
                if (has_capability('mod/ivs:lock_annotation_access', $context)) {
                    return true;
                }
                break;

        }
        return false;

    }

    /**
     * format a timecode
     *
     * @param $timecode
     * @param bool $millisecs
     * @return string
     */
    public static function format_timecode($timecode, $millisecs = false) {

        $time = $timecode / 1000;

        $minutes = floor($time / 60);
        $seconds = floor($time) - (60 * $minutes);

        $time_formatted = str_pad($minutes, 2, '0', STR_PAD_LEFT) . ':' . str_pad($seconds, 2, '0', STR_PAD_LEFT);

        if ($millisecs) {

            $dec = floor($timecode - (1000 * $seconds));
            $milli = str_pad($dec, 3, '0', STR_PAD_LEFT);

            $time_formatted .= ":" . $milli;
        }
        return $time_formatted;
    }

    public function getTimecode($millisecs = false) {
        return annotation::format_timecode($this->time_stamp, $millisecs);
    }

    public static function hasCapabilityViewAnyComment($context) {
        if (is_siteadmin()) {
            return true;
        }
        //instance permission
        if (has_capability('mod/ivs:view_any_comment', $context)) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public
    function getId() {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public
    function setId($id) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public
    function getBody() {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public
    function setBody($body) {
        $this->body = $body;
    }

    /**
     * @return mixed
     */
    public
    function getVideoId() {
        return $this->video_id;
    }

    /**
     * @param mixed $video_id
     */
    public
    function setVideoId($video_id) {
        $this->video_id = $video_id;
    }

    /**
     * @return mixed
     */
    public
    function getTimestamp() {
        return $this->time_stamp;
    }

    /**
     * @param mixed $time_stamp
     */
    public
    function setTimestamp($time_stamp) {
        $this->time_stamp = $time_stamp;
    }

    /**
     * @return mixed
     */
    public
    function getDuration() {
        return $this->duration;
    }

    /**
     * @param mixed $duration
     */
    public
    function setDuration($duration) {
        $this->duration = $duration;
    }

    /**
     * @return mixed
     */
    public
    function getThumbnail() {
        return $this->thumbnail;
    }

    /**
     * @param mixed $thumbnail
     */
    public
    function setThumbnail($thumbnail) {
        $this->thumbnail = $thumbnail;
    }

    /**
     * @return mixed
     */
    public
    function getUserId() {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public
    function setUserId($user_id) {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public
    function getAdditionalData() {
        return $this->additional_data;
    }

    /**
     * @param mixed $additional_data
     */
    public
    function setAdditionalData($additional_data) {
        $this->additional_data = $additional_data;
    }

    /**
     * @return mixed
     */
    public
    function getTimemodified() {
        return $this->timemodified;
    }

    /**
     * @param mixed $timemodified
     */
    public
    function setTimemodified($timemodified) {
        $this->timemodified = $timemodified;
    }

    /**
     * @return mixed
     */
    public
    function getTimecreated() {
        return $this->timecreated;
    }

    /**
     * @param mixed $timecreated
     */
    public
    function setTimecreated($timecreated) {
        $this->timecreated = $timecreated;
    }

    /**
     * @return mixed
     */
    public function getAccessView() {
        return $this->access_view;
    }

    /**
     * @param $access_view
     */
    public function setAccessView($access_view) {
        $this->access_view = $access_view;
    }

    /**
     * @return mixed
     */
    public function getCourseService() {
        if ($this->courseService === null) {
            $this->courseService = new CourseService();
        }
        return $this->courseService;
    }

    /**
     * @param mixed $courseService
     */
    public function setCourseService($courseService) {
        $this->courseService = $courseService;
    }

    /**
     * @return \moodle_url
     */
    public function getAnnotationPlayerUrl() {
        $id = $this->getId();
        if (!empty($this->getParentId())) {
            $id = $this->getParentId();
        }

        $course_module = get_coursemodule_from_instance('ivs', $this->getVideoId(), 0, false, MUST_EXIST);
        $activity = \context_module::instance($course_module->id);
        $activity_id = $activity->instanceid;

        return (new \moodle_url('/mod/ivs/view.php', array('id' => $activity_id, 'cid' => $id)));
    }

    /**
     * @return \moodle_url
     */
    public function getAnnotationOverviewUrl() {

        $id = $this->getId();
        /*
        if(!empty($this->getParentId())){
          $id = $this->getParentId();
        }
        */
        return new \moodle_url('/mod/ivs/annotation_overview.php', array('id' => $this->getVideoId()), 'comment-' . $id);
    }

}
