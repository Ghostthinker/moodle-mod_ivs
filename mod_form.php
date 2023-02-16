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
 * All form elements to create or edit an Interactive video suite
 *
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

defined('MOODLE_INTERNAL') || die();

use core_grades\component_gradeitems;
use mod_ivs\gradebook\GradebookService;
use \mod_ivs\ivs_match\AssessmentConfig;
use mod_ivs\settings\SettingsService;
use mod_ivs\upload\ExternalSourceVideoHost;
use \tool_opencast\local\api;

global $CFG;
require_once($CFG->dirroot . '/course/moodleform_mod.php');

if (file_exists($CFG->dirroot . '/blocks/panopto/lib/block_panopto_lib.php')) {
    require_once($CFG->dirroot . '/blocks/panopto/lib/block_panopto_lib.php');
}

/**
 * Module instance settings form
 *
 * @package    mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */
class mod_ivs_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $PAGE, $course, $USER;

        $PAGE->requires->css(new moodle_url($CFG->httpswwwroot . '/mod/ivs/templates/settings_course.css'));

        $panoptoblocksenabled = file_exists($CFG->dirroot . '/blocks/panopto/lib/block_panopto_lib.php');

        $panoptodata = '';
        if ($panoptoblocksenabled) {

            $configuredserverarray = panopto_get_configured_panopto_servers();

            if (file_exists(dirname(__FILE__) . '/../../blocks/panopto/lib/panopto_data.php')) {
                require_once(dirname(__FILE__) . '/../../blocks/panopto/lib/panopto_data.php');
                $panoptodata = new \panopto_data($course->id);
                if (!empty($panoptodata->servername) && !empty($panoptodata->applicationkey)) {
                    $panoptodata->sync_external_user($USER->id);
                }
            }

            $panoptodata->buttonname = get_string('ivs_setting_panopto_menu_button', 'ivs');
            $panoptodata->tooltip = get_string('ivs_setting_panopto_menu_tooltip', 'ivs');
        }

        $PAGE->requires->js_call_amd('mod_ivs/ivs_activity_settings_page', 'init', ['panopto_data' => $panoptodata]);

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('ivsname', 'ivs'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'ivsname', 'ivs');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }
        $panoptoexternalfilesenabled = get_config('mod_ivs', 'ivs_panopto_external_files_enabled');
        if ((int) $panoptoexternalfilesenabled && $panoptoblocksenabled) {
            $mform->addElement('hidden', 'panopto_video_json_field', get_string('ivs_setting_panopto_menu_title', 'ivs'),
                    ['id' => 'id_panopto_video_json_field']);
            $mform->addElement('text', 'panopto_video', get_string('ivs_setting_panopto_menu_title', 'ivs'),
                    ['readonly' => true, 'size' => '64']);
            if (!empty($CFG->formatstringstriptags)) {
                $mform->setType('panopto_video_json_field', PARAM_TEXT);
                $mform->setType('panopto_video', PARAM_TEXT);
            } else {
                $mform->setType('panopto_video_json_field', PARAM_CLEANHTML);
                $mform->setType('panopto_video', PARAM_CLEANHTML);
            }
        }

        $opencastexternalfilesenabled = get_config('mod_ivs', 'ivs_opencast_external_files_enabled');
        if ((int) $opencastexternalfilesenabled) {

            try {
                $opencastvideos = $this->get_opencast_videos_for_select();
                if ($opencastvideos && count($opencastvideos) > 0) {
                    $select =
                            $mform->addElement('select', 'opencast_video', get_string('ivs_setting_opencast_menu_title', 'ivs'),
                                    $opencastvideos);
                }

            } catch (Exception $e) {
                \core\notification::error($e->getMessage());
            }
        }

        $opencastinternalfilesenabled = get_config('mod_ivs', 'ivs_opencast_internal_files_enabled');
        if ((int) $opencastinternalfilesenabled) {
            $mform->addElement('filepicker', 'video_file', get_string('file'), null,
                    array(
                            'subdirs' => 0,
                            'maxbytes' => 0,
                            'areamaxbytes' => 10485760,
                            'maxfiles' => 1,
                            'accepted_types' => array('.mp4'),
                            'return_types' => FILE_INTERNAL,
                    ));
        }

        $kalturafilesenabled = get_config('mod_ivs', 'ivs_kaltura_external_files_enabled');
        if ((int) $kalturafilesenabled) {
            require_once($CFG->dirroot . '/mod/ivs/classes/KalturaService.php');
            try {
                $kalturavideos = $this->get_kaltura_videos_for_select();
                if ($kalturavideos && count($kalturavideos) > 0) {
                    $select =
                            $mform->addElement('select', 'kaltura_video', get_string('ivs_setting_kaltura_menu_title', 'ivs'),
                                    $kalturavideos);
                }

            } catch (Exception $e) {
                \core\notification::error($e->getMessage());
            }
        }

        $externalsourcesenabled = get_config('mod_ivs', 'ivs_external_sources_enabled');
        if ((int) $externalsourcesenabled) {
            $mform->addElement('text', 'external_video_source', get_string('ivs_setting_external_source_menu_title', 'ivs'),
                    ['size' => '64']);
            if (!empty($CFG->formatstringstriptags)) {
                $mform->setType('external_video_source', PARAM_TEXT);
            } else {
                $mform->setType('external_video_source', PARAM_CLEANHTML);
            }
        }



        // Grade settings.
        $this->standard_grading_coursemodule_elements();
        $mform->removeElement('grade');
        $mform->removeElement('gradecat');



        $settingscontroller = new SettingsService();
        $parentsettings = $settingscontroller->get_parent_settings_for_activity($this->_course->id);
        $activitysettings = [];
        if (!empty($this->_instance)) {
            $activitysettings = $settingscontroller->load_settings($this->_instance, 'activity');
        }

        $moodlematchcontroller = new \mod_ivs\MoodleMatchController();
        \mod_ivs\settings\SettingsService::ivs_add_new_activity_settings_heading('mod_ivs/advanced',get_string('ivs_player_settings_main', 'ivs'),$mform);
        \mod_ivs\settings\SettingsService::ivs_add_new_activity_settings_heading('mod_ivs/playerfeatures',get_string('ivs_player_settings_features', 'ivs'),$mform);
        $ivsplayersettings = \mod_ivs\settings\SettingsService::ivs_get_player_settings();
        SettingsService::ivs_render_activity_settings($ivsplayersettings,$activitysettings,$mform,$parentsettings, [\mod_ivs\settings\SettingsDefinition::SETTING_MATCH_QUESTION_ENABLED => $moodlematchcontroller->get_assessment_type_options()]);

        \mod_ivs\settings\SettingsService::ivs_add_new_activity_settings_heading('mod_ivs/notification',get_string('ivs_player_settings_notification', 'ivs'),$mform);
        $ivsplayernotificationsettings = \mod_ivs\settings\SettingsService::ivs_get_player_notification_settings();
        SettingsService::ivs_render_activity_settings($ivsplayernotificationsettings,$activitysettings,$mform,$parentsettings);

        \mod_ivs\settings\SettingsService::ivs_add_new_activity_settings_heading('mod_ivs/controls',get_string('ivs_player_settings_controls', 'ivs'),$mform);
        $ivsplayercontrolssettings = \mod_ivs\settings\SettingsService::ivs_get_player_control_settings();
        SettingsService::ivs_render_activity_settings($ivsplayercontrolssettings,$activitysettings,$mform,$parentsettings);

        \mod_ivs\settings\SettingsService::ivs_add_new_activity_settings_heading('mod_ivs/advanced',get_string('ivs_player_settings_advanced', 'ivs'),$mform);
        \mod_ivs\settings\SettingsService::ivs_add_new_activity_settings_heading('mod_ivs/advanced_comments',get_string('ivs_player_settings_advanced_comments', 'ivs'),$mform);
        $ivsplayeradvancedcommentssettings = \mod_ivs\settings\SettingsService::ivs_get_player_advanced_comments_settings();
        SettingsService::ivs_render_activity_settings($ivsplayeradvancedcommentssettings,$activitysettings,$mform,$parentsettings,[\mod_ivs\settings\SettingsDefinition::SETTING_PLAYER_LOCK_REALM => SettingsService::get_ivs_read_access_options()]);


        \mod_ivs\settings\SettingsService::ivs_add_new_activity_settings_heading('mod_ivs/advanced_match',get_string('ivs_player_settings_advanced_match', 'ivs'),$mform);
        $ivsplayeradvancedmatchsettings = \mod_ivs\settings\SettingsService::ivs_get_player_advanced_match_settings();
        SettingsService::ivs_render_activity_settings($ivsplayeradvancedmatchsettings,$activitysettings,$mform,$parentsettings);

        $gradebookservice = new GradebookService();
        \mod_ivs\settings\SettingsService::ivs_add_new_activity_settings_heading('mod_ivs/grades',get_string('ivs_grade', 'ivs'),$mform);
        $ivsplayergradesettings = \mod_ivs\settings\SettingsService::ivs_get_player_grade_settings();
        SettingsService::ivs_render_activity_settings($ivsplayergradesettings,$activitysettings,$mform,$parentsettings,[\mod_ivs\settings\SettingsDefinition::SETTING_PLAYER_VIDEOTEST_ATTEMPTS => $gradebookservice->ivs_get_attempt_options(), \mod_ivs\settings\SettingsDefinition::SETTING_PLAYER_VIDEOTEST_GRADE_METHOD => $gradebookservice->ivs_get_grade_method_options()]);





        $mform->addElement('header', 'mod_ivs/misc', get_string('ivs_player_settings_misc', 'ivs'));

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();

    }

    public function validation($data, $files) {
        $errors = [];
        if (!is_numeric($data['annotation_audio_max_duration']['value'])) {
            $errors['numeric'] = get_string('ivs_setting_annotation_audio_max_duration_validation', 'mod_ivs');
          \core\notification::error(get_string('ivs_setting_annotation_audio_max_duration_validation', 'mod_ivs'));
        }

        if ($data['annotation_audio_max_duration']['value'] > IVS_SETTING_PLAYER_ANNOTATION_AUDIO_MAX_DURATION || $data['annotation_audio_max_duration']['value'] < 0) {
            $errors['range'] = get_string('ivs_setting_annotation_audio_max_duration_validation', 'mod_ivs');
          \core\notification::error(get_string('ivs_setting_annotation_audio_max_duration_validation', 'mod_ivs'));
        }

        $unsupportedvideotype = ExternalSourceVideoHost::parseExternalVideoSourceUrl($data['external_video_source']);

        if (!empty($data['external_video_source'])){
          if ( $unsupportedvideotype['type'] == ExternalSourceVideoHost::TYPE_UNSUPPORTED){
            $errors['unsupported_video'] = get_string('ivs_setting_external_video_source_validation', 'mod_ivs');
            \core\notification::error(get_string('ivs_setting_external_video_source_validation', 'mod_ivs'));
          }
        }


        return $errors;
    }

    /**
     * Process default values
     *
     * @param array $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues) {
        if ($this->current->instance) {
            $options = array(
                    'subdirs' => false,
                    'maxbytes' => 0,
                    'maxfiles' => -1
            );
            $draftitemid = file_get_submitted_draft_itemid('video_file');

            file_prepare_draft_area($draftitemid,
                    $this->context->id,
                    'mod_ivs',
                    'videos',
                    0,
                    $options);
            $defaultvalues['video_file'] = $draftitemid;
        }
        if (!empty($defaultvalues['videourl'])) {
            $parts = explode("://", $defaultvalues['videourl']);

            if ($parts[0] == "OpenCastFileVideoHost" || $parts[0] == "SwitchCastFileVideoHost") {
                $defaultvalues['opencast_video'] = $parts[1];
            } else if ($parts[0] == "PanoptoFileVideoHost") {
                $defaultvalues['panopto_video_json_field'] = $parts[1];
                $decodedvalues = json_decode($parts[1]);
                if (!empty($decodedvalues)) {
                    $defaultvalues['panopto_video'] = $decodedvalues->videoname[0];
                }
            } else if ($parts[0] == "KalturaFileVideoHost") {
                $defaultvalues['kaltura_video'] = $parts[1];

            } else if  ($parts[0] == "ExternalSourceVideoHost") {
                $externalsourceinfo = json_decode($parts[1]);
                $defaultvalues['external_video_source'] = $externalsourceinfo->originalstring;
            }
        }

    }

    /**
     * Get all videos from opencast
     *
     * @return array|void
     */
    public function get_opencast_videos_for_select() {

        global $COURSE;
        $publishedvideos = array();

        if (!class_exists('\\tool_opencast\\seriesmapping') || !class_exists('\\tool_opencast\\local\\api')) {
            return array(get_string('ivs_opencast_video_chooser', 'ivs'));
        }
        $mapping = \tool_opencast\seriesmapping::get_record(array('courseid' => $COURSE->id));
        if (!is_object($mapping)) {
            return;
        }
        $seriesid = $mapping->get('series');
        $seriesfilter = "series:" . $seriesid;

        $query = '/api/events?sign=1&withmetadata=1&withpublications=1&filter=' . urlencode($seriesfilter);

        $api = new api();
        $videos = $api->oc_get($query);
        $videos = json_decode($videos);

        $publishedvideos = array(get_string('ivs_opencast_video_chooser', 'ivs'));

        if (empty($videos)) {
            return $publishedvideos;
        }

        foreach ($videos as $video) {
            if (is_array($video->publication_status)) {
                foreach ($video->publication_status as $entry) {
                    if (strpos($entry, 'api') > -1) {
                        $publishedvideos[$video->identifier] = $video->title;
                    }
                }
            }
        }

        return $publishedvideos;
    }

    /**
     * Get all videos from opencast
     *
     * @return array|void
     */
    public function get_kaltura_videos_for_select() {
        global $COURSE, $CFG;

        $publishedvideos = array();

        if (!file_exists($CFG->dirroot . '/local/kaltura/API/KalturaClient.php')) {
            return $publishedvideos;
        }

        $kalturaservice = new KalturaService();
        $results = current($kalturaservice->getMediaList($COURSE->id));

        if (!empty($results)) {
            foreach ($results as $entry) {
                $publishedvideos[$entry->rootEntryId] = $entry->name;
            }
        }

        return $publishedvideos;
    }
}
