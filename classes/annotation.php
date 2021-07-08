<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs;

//use \mod_ivs\exception;

use html_writer;
use \mod_ivs\service;
use mod_ivs\settings\SettingsService;
use user_picture;

global $CFG;

defined('MOODLE_INTERNAL') || die();

define('MOD_ivs', 'ivs');
define('MOD_ivs_COMMENT', 'ivs_videocomment');
define('MOD_ivs_VC_ACCESS', 'ivs_vc_access');

class annotation {

    private $id;
    private $body;
    private $videoid;
    private $timestamp;
    private $duration;
    private $thumbnail;
    private $userid;
    private $additionaldata;
    private $timemodified;
    private $timecreated;
    private $accessview;
    private $courseservice;
    private $parentid;
    protected $replies;

    function __construct($annotation = false) {

        if (is_object($annotation)) {
            $annotation->additional_data = unserialize($annotation->additional_data);
            $annotation->access_view = unserialize($annotation->access_view);
            $this->set_record($annotation);
        } else if (is_numeric($annotation)) {
            // Load from db.
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

        $annotationids = array();
        /** @var \mod_ivs\annotation $annotation */
        foreach ($annotations as $annotation) {
            $annotationids[] = $annotation->get_id();
        }

        if (empty($annotationids)) {
            return;
        }

        list($insql, $inparams) = $DB->get_in_or_equal($annotationids);
        $params = $annotationids;

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

        $dbrecord = $this->get_record();

        $dbrecord['additional_data'] = serialize($dbrecord['additional_data']);
        $dbrecord['access_view'] = serialize($dbrecord['access_view']);


        $save = false;
        if (isset($this->id)) {
            $save = $DB->update_record('ivs_videocomment', $dbrecord);
            if ($save) {
                $save = $this->write_annotation_access();
            }
        } else {
            if ($id = $DB->insert_record('ivs_videocomment', $dbrecord)) {
                $this->id = $id;
                $save = $this->write_annotation_access();
            }
        }

        return $save;
    }

    public function delete_from_db($annotation = null) {
        global $DB;

        // Delete recomments access.
        $recomments = $DB->get_records('ivs_videocomment', array('parent_id' => $annotation->id));
        foreach ($recomments as $recomment) {
            $DB->delete_records('ivs_vc_access', array('annotation_id' => $recomment->id));
        }

        // Delete comments access.
        $DB->delete_records('ivs_vc_access', array('annotation_id' => $annotation->id));

        // Delete recomments.
        $DB->delete_records('ivs_videocomment', array('parent_id' => $annotation->id));

        // Delete comments.
        $DB->delete_records('ivs_videocomment', array("id" => $this->id));

    }

    /**
     * Load an annotation from the database
     *
     * @param $annotationid
     * @param bool $loadreplies
     * @return \mod_ivs\annotation
     */
    public static function retrieve_from_db($annotationid, $loadreplies = false) {
        global $DB;

        $annotationdata = $DB->get_record('ivs_videocomment', array('id' => $annotationid));

        if (is_object($annotationdata)) {
            $annotation = new annotation($annotationdata);

            if ($loadreplies) {
                $annotations[$annotation->get_id()] = $annotation;
                self::load_replies($annotations);
            }
            return $annotation;
        }
        return null;
    }

    public static function retrieve_from_db_by_video($videonid, $grants = null, $offset = 0, $limit = 0, $countonly = false) {
        global $DB, $USER;

        $annotations = array();

        $coursemodule = get_coursemodule_from_instance('ivs', $videonid, 0, false, MUST_EXIST);
        $courseid = $coursemodule->course;
        $activity = \context_module::instance($coursemodule->id);

        // Build the  base query.
        $query = "SELECT * FROM {ivs_videocomment} vc WHERE video_id = ? AND parent_id IS NULL";
        $parameters = array($videonid);

        if (!self::has_capability_view_any_comment($activity)) {

            if ($grants === null) {
                $grants = self::get_user_grants($USER->id, $courseid);
            }

            list($accessquery, $accessparameters) =
                    self::get_user_grants_query($grants['user'], $grants['course'], $grants['group'], $grants['role']);

            if (!empty($accessquery)) {
                $query .= " AND " . $accessquery;
            }
            $parameters = array_merge($parameters, $accessparameters);

        }

        // Add order options.

        $query .= " Order by time_stamp, timecreated";

        if ($countonly) {
            $query = "SELECT DISTINCT COUNT(vc.id) as total FROM {ivs_videocomment} vc WHERE video_id = ? AND parent_id IS NULL";
            return $DB->get_record_sql($query, [$videonid]);
        }

        $data = $DB->get_records_sql($query, $parameters, (int) $offset, (int) $limit);

        foreach ($data as $record) {
            $annotations[$record->id] = new annotation($record);
        }

        if (!empty($annotations)) {
            self::load_replies($annotations);
        }

        return $annotations;
    }

    public function write_annotation_access(\stdClass $accessview = null) {
        global $DB;

        if (!isset($accessview)) {
            $accessview = $this->accessview;
        }

        if (!empty($this->parentid)) {
            return;
        }

        // Delete all ralms prior to insert.

        $DB->delete_records(MOD_ivs_VC_ACCESS, array("annotation_id" => $this->id));

        $record = array();
        $record[] = array("annotation_id" => $this->id, "realm" => 'author', 'rid' => $this->userid);
        if ($accessview->realm == 'course') {

            $coursemodule = get_coursemodule_from_instance('ivs', $this->videoid, 0, false, MUST_EXIST);
            $courseid = $coursemodule->course;
            $record[] = array("annotation_id" => $this->id, "realm" => 'course', 'rid' => $courseid);

        } else if ($accessview->realm == 'private') {
            $record[] = array("annotation_id" => $this->id, "realm" => 'private', 'rid' => $this->userid);
        } else {
            if (isset($accessview->gids)) {
                foreach ($accessview->gids as $rid) {
                    $record[] = array(
                            "annotation_id" => $this->id,
                            "realm" => $accessview->realm,
                            "rid" => $rid
                    );
                }
            } else {
                $record[] = array(
                        "annotation_id" => $this->id,
                        "realm" => $accessview->realm
                );
            }
        }

        $save = true;

        foreach ($record as $rec) {
            $save = $DB->insert_record(MOD_ivs_VC_ACCESS, $rec, false, true);
        }

        return $save;
    }

    public static function get_user_grants($moodleuserid, $courseid) {
        $grants = array();
        $grants['user'] = $moodleuserid;
        $grants['course'] = $courseid;

        $groups = CourseService::get_user_course_groups($courseid, $moodleuserid);

        $arr = array();
        foreach ($groups as $group) {
            $arr[] = $group->id;
        }
        $grants['group'] = $arr;

        $roles = CourseService::get_user_course_role_assignments($courseid, $moodleuserid);

        $arr = array();
        foreach ($roles as $role) {
            $arr[] = $role->roleid;
        }
        $grants['role'] = $arr;

        return $grants;
    }

    public function get_user_grants_as_index($moodleuser, $courseid) {
        $grants = array();
        $grants[0] = array('user_id' => $moodleuser->id);
        $grants[1] = array('course' => $courseid);
        $grants[2] = array('author' => $this->userid);

        $groups = $this->get_courseservice()->get_user_course_groups($courseid, $moodleuser->id);
        foreach ($groups as $group) {
            $grants[] = array('group' => $group->id);
        }
        $roles = $this->get_courseservice()->get_user_course_role_assignments($courseid, $moodleuser->id);
        foreach ($roles as $role) {
            $grants[] = array('role' => $role->roleid);
        }
        return $grants;
    }

    /**
     * @return mixed
     */
    public function get_parentid() {
        return $this->parentid;
    }

    /**
     * @param mixed $parentid
     */
    public function set_parentid($parentid) {
        $this->parentid = $parentid;
    }

    /**
     * Create moodle events so observers can react
     *
     * @param $op
     * @param null $additionaldata
     * @throws \coding_exception
     */
    protected function message_api($op, $additionaldata = null) {

       $coursemodule = get_coursemodule_from_instance('ivs', $this->videoid, 0, false, MUST_EXIST);
        $courseid = $coursemodule->course;
        $activity = \context_module::instance($coursemodule->id);

        $params = array(
                'objectid' => $this->id,
                'contextid' => $activity->id,
                'courseid' => $courseid
        );

        switch ($op) {
            case 'created':
                $event = \mod_ivs\event\annotation_created::create($params);
                $event->add_record_snapshot('ivs_videocomment', (object) $this->get_record());
                break;
            case 'updated':
                $event = \mod_ivs\event\annotation_updated::create($params);
                $event->add_record_snapshot('ivs_videocomment', (object) $this->get_record());
                break;
            case 'deleted':
                $event = \mod_ivs\event\annotation_deleted::create($params);
                $event->add_record_snapshot('ivs_videocomment', (object) $this->get_record());
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
    public function get_reply_users($excludeauthor = true) {
        global $DB;

        $parameters = array(
                $this->get_id()
        );

        if ($excludeauthor) {
            $excludesql = " AND user_id != ?";
            $parameters[] = $this->get_userid();
        }

        $sql = "SELECT DISTINCT u.*
            FROM {user} u
            JOIN {ivs_videocomment} vc ON vc.user_id = u.id
            WHERE parent_id = ? $excludesql";

        $uids = $DB->get_records_sql($sql, $parameters);

        return $uids;

    }

    /**
     * @return array
     */
    public function get_replies() {
        return $this->replies;
    }

    private function get_user_params($userid, $courseid, $groupid, $roleid) {
        return array(
                'course_id' => $courseid,
                'video_id' => $this->videoid,
                'user_id' => $userid,
                'group_id' => $groupid,
                'role_id' => $roleid
        );
    }

    /**
     * Build a query and parameters for the access check of video comments that can be added to a where group.
     *
     * @param $userid
     * @param $courseid
     * @param $groupids
     * @param $roleids
     * @return array
     */
    public static function get_user_grants_query($userid, $courseid, $groupids, $roleids) {
        $accessparameters = array(
                $courseid,
                $userid,
                $userid
        );

        // Build the base query for access.
        $sql = ' EXISTS(
              SELECT ac.id AS acid FROM {ivs_vc_access} ac WHERE 
              ac.annotation_id = vc.id AND (
              (ac.rid = ? AND ac.realm = \'course\') OR
              (ac.rid = ? AND ac.realm = \'member\') OR (ac.rid = ? AND ac.realm = \'author\')';

        // Add group realms if needed.
        $groupquery = '';
        if (!empty($groupids)) {
            foreach ($groupids as $gid) {
                $groupquery .= ' OR (ac.rid = ' . $gid . ' AND ac.realm = \'group\')';
                $accessparameters[] = $gid;
            }
        }

        // Add the  role realms if needed.
        $rolequery = '';
        if (!empty($roleids)) {
            foreach ($roleids as $rid) {
                $rolequery .= ' OR (ac.rid = ' . $rid . ' AND ac.realm = \'role\')';
                $accessparameters[] = $rid;
            }
        }

        // Build the  complete access query.
        $sql = $sql . $groupquery . $rolequery . '))';

        return array($sql, $accessparameters);
    }

    public function from_request_body($requestbody, $parentid = null) {
        global $USER;

        $this->body = $requestbody->body;
        $this->timestamp = $requestbody->timestamp;

        if (!empty($requestbody->drawing_data)) {
            $this->additionaldata['drawing_data'] = $requestbody->drawing_data;
        }

        if (isset($requestbody->rating)) {
            $this->additionaldata['rating'] = $requestbody->rating;
        }

        if ($requestbody->access_settings) {
            $this->additionaldata['access'] = $requestbody->access_settings;
            $this->accessview = $requestbody->access_settings;
        }

        $ACTION = "created";

        // Pin mode.
        if (!empty($requestbody->pinmode)) {
            $this->additionaldata['pinmode'] = $requestbody->pinmode;
        }

        if (isset($requestbody->pinmode_pause_seconds)) {
            $this->additionaldata['pinmode_pause_seconds'] = $requestbody->pinmode_pause_seconds;
        }

        // New annotation.
        if ($this->id == null) {
            // Set values that can not be changed on updates.
            $this->timecreated = time();
            $this->userid = $USER->id;
            $this->parentid = $parentid;

            if (empty($parentid)) {
                $this->videoid = $requestbody->video_nid;

            } else {
                $parentannotation = annotation::retrieve_from_db($parentid);
                $this->videoid = $parentannotation->get_videoid();

            }
        } else {
            // Edit annotation.
            $ACTION = "updated";
        }

        $this->timemodified = time();

        $this->save_to_db();

        if ($requestbody->preview) {
            $this->save_preview_image($requestbody->preview);
        }

        // Fire event Api (Generate Message).
        $this->message_api($ACTION, $requestbody);
    }

    /**
     * Save Preview Image as base 64
     *
     * @param $imagebase64
     * @param $annotation_id
     * @return \stored_file
     * @throws \file_exception
     * @throws \stored_file_creation_exception
     */
    public function save_preview_image($imagebase64) {

        if (empty($this->id)) {
            return;
        }

        $coursemodule = get_coursemodule_from_instance('ivs', $this->videoid, 0, false, MUST_EXIST);
        $context = \context_module::instance($coursemodule->id);

        $imagedata = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imagebase64));

        // Reload from db to get the proper timestamp value.
        $annotationdb = self::retrieve_from_db($this->get_id());

        $itemid = $annotationdb->get_preview_id();

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_ivs', 'preview', $itemid, 'itemid', false);

        $existingfile = $value = end($files);

        $fileinfo = array(
                'contextid' => $context->id,
                'component' => 'mod_ivs',
                'filearea' => 'preview',
                'itemid' => $itemid,
                'filepath' => '/',
                'filename' => 'preview.jpg');

        if ($existingfile) {
            $existingfile->delete();

        }
        $file = $fs->create_file_from_string($fileinfo, $imagedata);

        return $file;
    }

    /**
     * Get the normalized timecode id for the preview image
     *
     * @return int
     */
    function get_preview_id() {
        return (int) ($this->timestamp * 1000);
    }

    /**
     * Get Preview Image
     *
     * @param $annotation_id
     * @throws \coding_exception
     */
    public function get_preview_url() {

        $itemid = $this->get_preview_id();

        $coursemodule = get_coursemodule_from_instance('ivs', $this->videoid, 0, false, MUST_EXIST);
        $context = \context_module::instance($coursemodule->id);

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
     * @param $accesssettings
     */
    public function lock_access($accesssettings) {

        $this->additionaldata['access'] = $accesssettings;
        $this->accessview = $accesssettings;
        $this->additionaldata['access_locked'] = true;

        $this->timemodified = time();

        $this->save_to_db();


    }

    public function get_player_user_data() {
        return IvsHelper::get_user_data_for_player($this->userid);
    }

    public function get_player_permissions() {
        return array(
                'update' => $this->access('edit'),
                'delete' => $this->access('delete'),
                'reply' =>  $this->access('create'),
                'edit_access' => $this->access("lock_access")
        );
    }

    /**
     * Get the database value fields from this class
     *
     * @return array
     */
    public function get_record() {
        return array(
                'id' => $this->id,
                'body' => $this->body,
                'video_id' => $this->videoid,
                'time_stamp' => $this->timestamp,
                'duration' => (int) $this->duration,
                'thumbnail' => $this->thumbnail,
                'user_id' => $this->userid,
                'timecreated' => (int) $this->timecreated,
                'timemodified' => (int) $this->timemodified,
                'additional_data' => $this->additionaldata,
                'access_view' => $this->accessview,
                'parent_id' => $this->parentid,
        );
    }

    public function to_player_comment() {
        global $PAGE;
        $object = (object) $this->get_record();

        // Additional_data.
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

        // Pinmode.
        if (isset($object->additional_data['pinmode'])) {
            $object->pinmode = $object->additional_data['pinmode'];
        }

        if (isset($object->additional_data['pinmode_pause_seconds'])) {
            $object->pinmode_pause_seconds = $object->additional_data['pinmode_pause_seconds'];
        }

        unset($object->additional_data);

        // Timestamp rename.
        $object->timestamp = $object->time_stamp;
        unset($object->time_stamp);

        // Add user_data.
        $object->userdata = $this->get_player_user_data();
        $object->perms = $this->get_player_permissions();

        $object->nid = $this->id;
        unset($object->id);

        foreach ($this->replies as $reply) {
            $object->replies[] = $reply->to_player_comment();
        }

        return $object;
    }

    /**
     * Populate an object we can store in the database
     *
     * @param $dbrecord
     */
    private function set_record($dbrecord) {
        $this->id = $dbrecord->id;
        $this->body = $dbrecord->body;
        $this->videoid = $dbrecord->video_id;
        $this->timestamp = $dbrecord->time_stamp;
        $this->duration = $dbrecord->duration;
        $this->thumbnail = $dbrecord->thumbnail;
        $this->userid = $dbrecord->user_id;
        $this->timecreated = $dbrecord->timecreated;
        $this->timemodified = $dbrecord->timemodified;
        $this->additionaldata = $dbrecord->additional_data;
        $this->accessview = $dbrecord->access_view;
        $this->parentid = $dbrecord->parent_id;

    }

    public function access($op) {
        global $USER;
        $coursemodule = get_coursemodule_from_instance('ivs', $this->videoid, 0, false, MUST_EXIST);
        $context = \context_module::instance($coursemodule->id);

        switch ($op) {
            case 'create':
                if (is_siteadmin()) {
                    return true;
                }
                //instance permission
                if (has_capability('mod/ivs:create_comment', $context)) {
                    return true;
                }
                break;
            case 'view':
                if (is_siteadmin()) {
                    return true;
                }
                // We are creator.
                if (!empty($this->userid) && $this->userid == $USER->id) {
                    return true;
                }
                // Instance permission.
                if (has_capability('mod/ivs:view_any_comment', $context)) {
                    return true;
                }
                break;
            case 'edit':
            case 'delete':
                if (is_siteadmin()) {
                    return true;
                }
                // We are creator.
                if (!empty($this->userid) && $this->userid == $USER->id) {
                    return true;
                }
                // Instance permission.
                if (has_capability('mod/ivs:edit_any_comment', $context)) {
                    return true;
                }
                break;
            case "lock_access":
                if (is_siteadmin()) {
                    return true;
                }
                // We are creator.
                if (!empty($this->userid) && $this->userid == $USER->id) {
                    if (isset($this->additionaldata['access_locked'])) {
                        return !$this->additionaldata['access_locked'];
                    }
                    return true;
                }
                // Instance permission.
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

        $timeformatted = str_pad($minutes, 2, '0', STR_PAD_LEFT) . ':' . str_pad($seconds, 2, '0', STR_PAD_LEFT);

        if ($millisecs) {

            $dec = floor($timecode - (1000 * $seconds));
            $milli = str_pad($dec, 3, '0', STR_PAD_LEFT);

            $timeformatted .= ":" . $milli;
        }
        return $timeformatted;
    }

    public function get_timecode($millisecs = false) {
        return annotation::format_timecode($this->timestamp, $millisecs);
    }

    public static function has_capability_view_any_comment($context) {
        if (is_siteadmin()) {
            return true;
        }
        // Instance permission.
        if (has_capability('mod/ivs:view_any_comment', $context)) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public
    function get_id() {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public
    function set_id($id) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public
    function get_body() {
        return $this->body;
    }
    /**
     * @return mixed
     */
    public
    function get_rendered_body() {
        $this->body = str_replace('\[','$$',$this->body);
        $this->body = str_replace('\]','$$',$this->body);
        $this->body = str_replace('\(','$',$this->body);
        $this->body = str_replace('\)','$',$this->body);
        return format_text($this->body, FORMAT_MARKDOWN);
    }

    /**
     * @param mixed $body
     */
    public
    function set_body($body) {
        $this->body = $body;
    }

    /**
     * @return mixed
     */
    public
    function get_videoid() {
        return $this->videoid;
    }

    /**
     * @param mixed $videoid
     */
    public
    function set_videoid($videoid) {
        $this->videoid = $videoid;
    }

    /**
     * @return mixed
     */
    public
    function get_timestamp() {
        return $this->timestamp;
    }

    /**
     * @param mixed $timestamp
     */
    public
    function set_timestamp($timestamp) {
        $this->timestamp = $timestamp;
    }

    /**
     * @return mixed
     */
    public
    function get_duration() {
        return $this->duration;
    }

    /**
     * @param mixed $duration
     */
    public
    function set_duration($duration) {
        $this->duration = $duration;
    }

    /**
     * @return mixed
     */
    public
    function get_thumbnail() {
        return $this->thumbnail;
    }

    /**
     * @param mixed $thumbnail
     */
    public
    function set_thumbnail($thumbnail) {
        $this->thumbnail = $thumbnail;
    }

    /**
     * @return mixed
     */
    public
    function get_userid() {
        return $this->userid;
    }

    /**
     * @param mixed $userid
     */
    public
    function set_userid($userid) {
        $this->userid = $userid;
    }

    /**
     * @return mixed
     */
    public
    function get_additionaldata() {
        return $this->additionaldata;
    }

    /**
     * @param mixed $additionaldata
     */
    public
    function set_additionaldata($additionaldata) {
        $this->additionaldata = $additionaldata;
    }

    /**
     * @return mixed
     */
    public
    function get_timemodified() {
        return $this->timemodified;
    }

    /**
     * @param mixed $timemodified
     */
    public
    function set_timemodified($timemodified) {
        $this->timemodified = $timemodified;
    }

    /**
     * @return mixed
     */
    public
    function get_timecreated() {
        return $this->timecreated;
    }

    /**
     * @param mixed $timecreated
     */
    public
    function set_timecreated($timecreated) {
        $this->timecreated = $timecreated;
    }

    /**
     * @return mixed
     */
    public function get_accessview() {
        return $this->accessview;
    }

    /**
     * @param $accessview
     */
    public function set_accessview($accessview) {
        $this->accessview = $accessview;
    }

    /**
     * @return mixed
     */
    public function get_courseservice() {
        if ($this->courseservice === null) {
            $this->courseservice = new CourseService();
        }
        return $this->courseservice;
    }

    /**
     * @param mixed $courseservice
     */
    public function set_courseservice($courseservice) {
        $this->courseservice = $courseservice;
    }

    /**
     * @return \moodle_url
     */
    public function get_annotation_player_url() {
        $id = $this->get_id();
        if (!empty($this->get_parentid())) {
            $id = $this->get_parentid();
        }

        $coursemodule = get_coursemodule_from_instance('ivs', $this->get_videoid(), 0, false, MUST_EXIST);
        $activity = \context_module::instance($coursemodule->id);
        $activityid = $activity->instanceid;

        return (new \moodle_url('/mod/ivs/view.php', array('id' => $activityid, 'cid' => $id)));
    }

    /**
     * @return \moodle_url
     */
    public function get_annotation_overview_url() {

        $id = $this->get_id();

        return new \moodle_url('/mod/ivs/annotation_overview.php', array('id' => $this->get_videoid()), 'comment-' . $id);
    }

}
