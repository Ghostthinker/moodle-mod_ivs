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
 * Lib.php
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

use mod_ivs\settings\SettingsService;
use mod_ivs\upload\ExternalSourceVideoHost;

defined('MOODLE_INTERNAL') || die();

/**
 * Example constant, you probably want to remove this :-)
 */
define('IVS_SETTING_PLAYER_ANNOTATION_AUDIO_MAX_DURATION', 300);
define('IVS_MAX_ATTEMPT_OPTION', 10);

/* Moodle core API */

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@see plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function ivs_supports($feature) {

    $featurePurpose = null;
    if(defined('FEATURE_MOD_PURPOSE')) {
        // catch moodle 3 undefined warning
        $featurePurpose = FEATURE_MOD_PURPOSE;
    }

    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case $featurePurpose:
            return MOD_PURPOSE_COLLABORATION;
        default:
            return null;
    }
}


/**
 * Saves a new instance of the ivs into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $ivs Submitted data from the form in mod_form.php
 * @param mod_ivs_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted ivs record
 */
function ivs_add_instance(stdClass $ivs, mod_ivs_mod_form $mform = null) {
    global $DB;

    $ivs->timecreated = time();

    $ivs->timemodified = time();

    $ivs->id = $DB->insert_record('ivs', $ivs);

    $DB->set_field('course_modules', 'instance', $ivs->id, array('id' => $ivs->coursemodule));
    if (!empty($ivs->opencast_video)) {
        $ivs->videourl = "OpenCastFileVideoHost://" . $ivs->opencast_video;
    } else if (!empty($ivs->panopto_video_json_field) && !empty($ivs->panopto_video)) {
        $ivs->videourl = "PanoptoFileVideoHost://" . $ivs->panopto_video_json_field;
    } else if (!empty($ivs->kaltura_video)) {
        $ivs->videourl = "KalturaFileVideoHost://" . $ivs->kaltura_video;
    } else if (!empty($ivs->external_video_source)) {
        $sourceinfo = ExternalSourceVideoHost::parseExternalVideoSourceUrl($ivs->external_video_source);
        if ($sourceinfo['type'] != ExternalSourceVideoHost::TYPE_UNSUPPORTED) {
            $ivs->videourl = $sourceinfo['idstring'];
        }
    } else if (!empty($ivs->sample_video)) {
        $ivs->videourl = 'TestingFileVideoHost://' . $ivs->id;
    } else {
        $ivs->videourl = 'MoodleFileVideoHost://' . $ivs->id;
    }

    $DB->update_record('ivs', $ivs);

    $videohost = \mod_ivs\upload\VideoHostFactory::create(null, $ivs);

    $videohost->save_video(null);

    // Save settings.
    $settingscontroller = new \mod_ivs\settings\SettingsService();
    $settingscontroller->process_activity_settings_form($ivs);

    return $ivs->id;
}

/**
 * Updates an instance of the ivs in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $ivs An object from the form in mod_form.php
 * @param mod_ivs_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function ivs_update_instance(stdClass $ivs, mod_ivs_mod_form $mform = null) {
    global $DB, $CFG;

    $ivs->timemodified = time();
    $ivs->id = $ivs->instance;

    if (!empty($ivs->opencast_video)) {
        $ivs->videourl = "OpenCastFileVideoHost://" . $ivs->opencast_video;
    } else if (!empty($ivs->panopto_video_json_field) && !empty($ivs->panopto_video)) {
        $ivs->videourl = "PanoptoFileVideoHost://" . $ivs->panopto_video_json_field;
    } else if (!empty($ivs->kaltura_video)) {
        $ivs->videourl = "KalturaFileVideoHost://" . $ivs->kaltura_video;
    } else if (!empty($ivs->external_video_source)) {
        $sourceinfo = ExternalSourceVideoHost::parseExternalVideoSourceUrl($ivs->external_video_source);
        if ($sourceinfo['type'] != ExternalSourceVideoHost::TYPE_UNSUPPORTED) {
            $ivs->videourl = $sourceinfo['idstring'];
        }
    } # Was macht diese Abfrage? Leider nicht verständlich
    else if (substr($mform->get_current()->videourl, 0, strlen('TestingFileVideoHost')) &&
            substr($mform->get_current()->videourl, 0, strlen('TestingFileVideoHost')) != 'PanoptoFileVideoHost') {
        $ivs->videourl = $mform->get_current()->videourl;
    } else {
        $ivs->videourl = 'MoodleFileVideoHost://' . $ivs->id;
    }

    $result = $DB->update_record('ivs', $ivs);

    $videohost = \mod_ivs\upload\VideoHostFactory::create(null, $ivs);

    $videohost->save_video(null);

    // Save settings.

    $settingscontroller = new \mod_ivs\settings\SettingsService();
    $settingscontroller->process_activity_settings_form($ivs);

    return $result;
}

/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every ivs event in the site is checked, else
 * only ivs events belonging to the course specified are checked.
 * This is only required if the module is generating calendar events.
 *
 * @param int $courseid Course ID
 * @return bool
 */
function ivs_refresh_events($courseid = 0) {
    global $DB;

    if ($courseid == 0) {
        if (!$ivss = $DB->get_records('ivs')) {
            return true;
        }
    } else {
        if (!$ivss = $DB->get_records('ivs', array('course' => $courseid))) {
            return true;
        }
    }

    return true;
}

/**
 * Removes an instance of the ivs from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function ivs_delete_instance($id) {
    global $DB;

    $ivs = $DB->get_record('ivs', array('id' => $id), '*', MUST_EXIST);

    if (!$ivs) {
        return false;
    }

    // Delete related video annotations access.
    $annotations = $DB->get_records('ivs_videocomment', array('video_id' => $id));

    foreach ($annotations as $annotation) {
        $DB->delete_records('ivs_vc_access', array('annotation_id' => $annotation->id));
    }

    // Delete related video annotations.
    $DB->delete_records('ivs_videocomment', array('video_id' => $id));

    // Delete related edubreak match questions.
    $matchquestions = $DB->get_records('ivs_matchquestion', array('video_id' => $id));

    $matchcontroller = new \mod_ivs\MoodleMatchController();

    foreach ($matchquestions as $matchqquestion) {
        $matchcontroller->match_question_delete_db($matchqquestion->id);
    }

    // Delete ivs settings.
    $DB->delete_records('ivs_settings', array('target_id' => $id, 'target_type' => 'activity'));

    // Delete related takes.
    $DB->delete_records('ivs_matchtake', array('video_id' => $id));

    // Delete any dependent records here.
    $DB->delete_records('ivs', array('id' => $id));

    return true;
}

/**
 * ivs_backend_comments
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param stdClass $course The course record
 * @param stdClass $user The user record
 * @param cm_info|stdClass $mod The course module info object or record
 * @param stdClass $ivs The ivs instance record
 * @return stdClass|null
 */
function ivs_user_outline($course, $user, $mod, $ivs) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * It is supposed to echo directly without returning a value.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $ivs the module instance record
 */
function ivs_user_complete($course, $user, $mod, $ivs) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in ivs activities and print it out.
 *
 * @param stdClass $course The course record
 * @param bool $viewfullnames Should we display full names
 * @param int $timestart Print activity since this timestamp
 * @return boolean True if anything was printed, otherwise false
 */
function ivs_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@see ivs_print_recent_mod_activity()}.
 *
 * Returns void, it adds items into $activities and increases $index.
 *
 * @param array $activities sequentially indexed array of objects with added 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 */
function ivs_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0, $groupid = 0) {
}

/**
 * Prints single activity item prepared by {@see ivs_get_recent_mod_activity()}
 *
 * @param stdClass $activity activity record with added 'cmid' property
 * @param int $courseid the id of the course we produce the report for
 * @param bool $detail print detailed report
 * @param array $modnames as returned by {@see get_module_types_names()}
 * @param bool $viewfullnames display users' full names
 */
function ivs_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 *
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * Note that this has been deprecated in favour of scheduled task API.
 *
 * @return boolean
 */
function ivs_cron() {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * For example, this could be array('moodle/site:accessallgroups') if the
 * module uses that capability.
 *
 * @return array
 */
function ivs_get_extra_capabilities() {
    return array();
}

/* File API */

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@see file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function ivs_get_file_areas($course, $cm, $context) {
    return array(
            'video' => get_string('filearea_videos', 'ivs'),
    );
}

/**
 * File browsing support for ivs file areas
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 * @package mod_ivs
 * @category files
 *
 */
function ivs_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the ivs file areas
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the ivs's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @category files
 *
 * @package mod_ivs
 */
function ivs_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options = array()) {
    global $DB, $CFG;

    require_login($course, true, $cm);

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = rtrim('/' . $context->id . '/mod_ivs/' . $filearea . '/' .
            $relativepath, '/');
    $file = $fs->get_file_by_hash(sha1($fullpath));
    if (!$file || $file->is_directory()) {
        return false;
    }

    // Default cache lifetime is 86400s.
    send_stored_file($file); // Possible options for the file are 86400, 0, $forcedownload, $options);.
    return true;
}

/* Navigation API */

/**
 * Extends the global navigation tree by adding ivs nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the ivs module instance
 * @param stdClass $course current course record
 * @param stdClass $module current ivs instance record
 * @param cm_info $cm course module information
 */
function ivs_extend_navigation(navigation_node $navref, stdClass $course, stdClass $module, cm_info $cm) {
    // TODO Delete this function and its docblock, or implement it.
}

/**
 * Extends the settings navigation with the ivs settings
 *
 * This function is called when the context for the page is a ivs module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav complete settings navigation tree
 * @param navigation_node $ivsnode ivs administration node
 */
function ivs_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $ivsnode = null) {
    // TODO Delete this function and its docblock, or implement it.
}

/**
 * Get the saved ivs activity
 *
 * @param string $ivs
 *
 * @return string
 */
function ivs_get_file_dir($ivs) {
    global $CFG;

    $path = $CFG->dataroot . '/ivs/' . $ivs->id;

    check_dir_exists($path);

    return $path;
}

/**
 * Message sending by creating an annotation
 *
 * @param \mod_ivs\event\annotation_created $event
 */
function ivs_annotation_event_created(\mod_ivs\event\annotation_created $event) {
    ivs_annotation_event_message_send($event);
}

/**
 * Message sending by updating an annotation (not used)
 *
 * @param \mod_ivs\event\annotation_updated $event
 */
function ivs_annotation_event_updated(\mod_ivs\event\annotation_updated $event) {
}

/**
 * Message sending by deleting an annotation (not used)
 *
 * @param \mod_ivs\event\annotation_deleted $event
 */
function ivs_annotation_event_deleted(\mod_ivs\event\annotation_deleted $event) {

}

/**
 * Generate message realm
 *
 * @param \stdClass $event
 * @throws \coding_exception
 */
function ivs_annotation_event_message_send($event) {

    global $DB, $USER;
    $courseservice = new \mod_ivs\CourseService();

    /** @var \mod_ivs\annotation $annotation */
    $annotation = \mod_ivs\annotation::retrieve_from_db($event->objectid, true);
    $cm = get_coursemodule_from_instance('ivs', $annotation->get_videoid(), 0, false, MUST_EXIST);
    $coursecontext = context_course::instance($cm->course);
    $ivs = $DB->get_record('ivs', array('id' => $cm->instance), '*', MUST_EXIST);
    $access = json_decode(json_encode($annotation->get_accessview()), true);
    $course = $DB->get_record('course', array('id' => $ivs->course), '*', MUST_EXIST);

    // No notifacions by private realms.
    if ($access['realm'] == 'private') {
        return;
    }

    // Send Message by creating an annotation (not reply).
    if (empty($annotation->get_parentid())) {
        switch ($access['realm']) {

            case 'member':

                // Send message to users by memberselection.
                $provider = 'ivs_annotation_direct_mention';
                if (!empty($access['gids'])) {
                    foreach ($access['gids'] as $uid) {
                        $receivers[] = $DB->get_record('user', array('id' => $uid));
                    }
                }
                break;

            case 'course':

                // Send message to users in course.
                $provider = 'ivs_annotation_indirect_mention';
                $receivers = get_enrolled_users($coursecontext, '', 0, 'u.*');

                break;

            case 'role':

                // Send message to users by roleselection.
                $provider = 'ivs_annotation_indirect_mention';
                if (!empty($access['gids'])) {
                    foreach ($access['gids'] as $rid) {
                        $roleusers = $courseservice->get_course_membersby_role($course->id, $rid);
                        foreach ($roleusers as $roleuser) {
                            $receivers[$roleuser->id] = $roleuser;
                        }
                    }
                }

                break;
            case 'group':

                // Send message to users by groupselection.
                $provider = 'ivs_annotation_indirect_mention';
                if (!empty($access['gids'])) {
                    foreach ($access['gids'] as $gid) {
                        $groupusers = get_enrolled_users($coursecontext, '', $gid, 'u.*');
                        foreach ($groupusers as $groupuser) {
                            $receivers[$groupuser->id] = $groupuser;
                        }
                    }
                }
                break;
        }

        ivs_annotation_event_process_message_send($provider, $receivers, $course, $annotation);

    } else {

        // Send Messages by creating a reply.
        /** @var \mod_ivs\annotation $parentannotation */
        $parentannotation = \mod_ivs\annotation::retrieve_from_db($annotation->get_parentid(), false);

        // Reply.
        // Send notification to parent annotation author.
        $provider = 'ivs_annotation_reply';
        $receivers = array(
                $DB->get_record('user', array('id' => $parentannotation->get_userid()))
        );

        ivs_annotation_event_process_message_send($provider, $receivers, $course, $annotation);

        // Conversation.
        // Send notification to everyone, who replied to the parent annotation.
        $provider = 'ivs_annotation_conversation';
        $receivers = $parentannotation->get_reply_users();

        ivs_annotation_event_process_message_send($provider, $receivers, $course, $annotation);
    }
}

/**
 * Set and send message content
 *
 * @param string $provider
 * @param string $receivers
 * @param \stdClass $course
 * @param \mod_ivs\annotation $annotation
 * @throws \coding_exception
 */
function ivs_annotation_event_process_message_send($provider, $receivers, $course, \mod_ivs\annotation $annotation) {

    global $USER;

    $settingscontroller = new SettingsService();
    $activitysettings = $settingscontroller->get_settings_for_activity($annotation->get_videoid(), $course->id);

    if (!$activitysettings['user_notification_settings']->value) {
        return;
    }

    if (!empty($receivers)) {

        foreach ($receivers as $account) {

            // Never send a message to the acting user.
            if ($account->id == $USER->id) {
                continue;
            }

            // Message details.
            switch ($provider) {
                case 'ivs_annotation_direct_mention':
                case 'ivs_annotation_indirect_mention':
                    $annotationsubject = 'annotation_direct_mention_subject';
                    $annotationfullmessage = 'annotation_direct_mention_fullmessage';
                    $annotationsmallmessage = 'annotation_direct_mention_smallmessage';
                    break;
                case 'ivs_annotation_reply':
                    $annotationsubject = 'annotation_reply_subject';
                    $annotationfullmessage = 'annotation_reply_fullmessage';
                    $annotationsmallmessage = 'annotation_reply_smallmessage';
                    break;
                case 'ivs_annotation_conversation':
                    $annotationsubject = 'annotation_conversation_subject';
                    $annotationfullmessage = 'annotation_conversation_fullmessage';
                    $annotationsmallmessage = 'annotation_conversation_smallmessage';
                    break;
            }

            $url = $annotation->get_annotation_player_url()->out(false);
            $subject = get_string($annotationsubject, 'mod_ivs',
                    ['fullname' => fullname($USER)]);
            $fullmessage = get_string($annotationfullmessage, 'mod_ivs',
                    ['fullname' => fullname($account),
                            'fullname' => fullname($USER),
                            'annotation' => $annotation->get_rendered_body(),
                      'course_name' => $course->fullname, 'annotation_url' => $url]);
            $smallmessage = get_string($annotationsmallmessage, 'mod_ivs');

            $message = new \core\message\message();
            $message->component = 'mod_ivs';
            $message->name = $provider;
            $message->userfrom = $USER;
            $message->userto = $account;
            $message->subject = $subject;
            $message->fullmessage = $fullmessage;
            $message->fullmessageformat = FORMAT_HTML;
            $message->fullmessagehtml = '';
            $message->smallmessage = $smallmessage;
            $message->notification = '1';
            $message->contexturl = $url;
            $message->contexturlname = get_string('annotation_context_url_name', 'mod_ivs');
            $message->courseid = $course->id;

            message_send($message);
        }
    }
}

/**
 * Navigation for the Interactive video suite activity
 * @param string $navigation
 * @param \stdClass $course
 * @param \stdClass $context
 */
function ivs_extend_navigation_course($navigation, $course, $context) {

    // IVS Annotations.
    if (has_capability('mod/ivs:access_reports', $context)) {

        $urlannotations = new moodle_url('/mod/ivs/cockpit.php', array('id' => $course->id));

        $navigation->add(get_string('annotation_overview_menu_item', 'mod_ivs'), $urlannotations,
                navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }

    // IVS Settings.
    if (has_capability('mod/ivs:access_course_settings', $context)) {

        $urlsettings = new moodle_url('/mod/ivs/settings_course.php', array('id' => $course->id));

        $navigation->add(get_string('ivs_settings_title', 'mod_ivs'), $urlsettings,
                navigation_node::TYPE_SETTING, null, null, new pix_icon('i/settings', ''));
    }

}

/**
 * Returns the MoodleLicenseController
 *
 * @return \mod_ivs\license\ILicenseController
 */
function ivs_get_license_controller() {
    /** @var \mod_ivs\license\ILicenseController $lc */
    static $lc;
    if (empty($lc)) {
        $lc = new \mod_ivs\license\MoodleLicenseController();
    }

    return $lc;
}

function ivs_update_grades($ivs, $take, $nullifnone=true){
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    $gradebookservice = new \mod_ivs\gradebook\GradebookService();
    $userid = $take->userid;
    if (!$gradebookservice->ivs_gradebook_enabled($ivs)){
        return;
    }

    $moodlematchcontroller = new \mod_ivs\MoodleMatchController();

    $takes = $moodlematchcontroller->match_takes_get_by_user_and_video_db($take->userid, $take->videoid, $take->videoid);
    if ($takes) {
        $scoreinfo = $gradebookservice->ivs_gradebook_get_score_info_by_takes($takes, $ivs);

        $grade = new stdClass();
        $grade->userid = $take->userid;
        $grade->rawgrade = $scoreinfo['score'];

        ivs_grade_item_update($ivs, $grade);
    }
}


/**
 * Updates grade item for ivs activity
 * @param $ivs
 * @param null $grade
 * @return int|null
 */
function ivs_grade_item_update($ivs, $grades = NULL)
{

    global $CFG;
    $gradebookservice = new \mod_ivs\gradebook\GradebookService();
    $access =  $gradebookservice->ivs_gradebook_enabled($ivs);

    if (!$access){
        return NULL;
    }


    if (!function_exists('grade_update')) { //workaround for buggy PHP versions
        require_once($CFG->libdir . '/gradelib.php');
    }

    $params = array('itemname' => $ivs->name, 'idnumber' => $ivs->id);

    if (!empty($ivs->grade)){
        if ($ivs->grade > 0) {
            $params['gradetype'] = GRADE_TYPE_VALUE;
            $params['grademax']  = $ivs->grade;
            $params['grademin']  = 0;

        } else {
            $params['gradetype'] = GRADE_TYPE_NONE;
        }
    }

    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = NULL;
    }

    return grade_update('mod/ivs', $ivs->course, 'mod', 'ivs', $ivs->id, 0, $grades, $params);
}
