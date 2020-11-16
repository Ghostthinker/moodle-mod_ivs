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

defined('MOODLE_INTERNAL') || die();

use \mod_ivs\ivs_match\AssessmentConfig;
use mod_ivs\settings\SettingsService;
use \tool_opencast\local\api;


global $CFG;
require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Module instance settings form
 *
 * @package    mod_ivs
 * @copyright 2017 Ghostthinker GmbH <info@ghostthinker.de>
 * @license   All Rights Reserved.
 */
class mod_ivs_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        global $PAGE;
        $PAGE->requires->js_call_amd('mod_ivs/ivs_activity_settings_page', 'init', []);

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

        if ((int) $CFG->ivs_switchcast_external_files_enabled) {

            try {
                $switchcast_videos = $this->get_videos_for_select();
                if ($switchcast_videos && count($switchcast_videos) > 0) {
                    $select =
                            $mform->addElement('select', 'switchcast_video', get_string('ivs_setting_switchcast_menu_title', 'ivs'),
                                    $switchcast_videos);
                }

            } catch (Exception $e) {
            }
        }

        if ((int) $CFG->ivs_switchcast_internal_files_enabled) {
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

        // Grade settings.
        $this->standard_grading_coursemodule_elements();

        $mform->addElement('header', 'mod_ivs/playersettings', get_string('ivs_player_settings', 'ivs'));
        $settingsdefinitions = \mod_ivs\settings\SettingsService::get_settings_definitions();

        $settingsController = new SettingsService();
        $parentsettings = $settingsController->get_rarent_settings_for_activity($this->_course->id);

        if (!empty($this->_instance)) {
            $activiysettings = $settingsController->load_settings($this->_instance, 'activity');
        }

        /** @var \mod_ivs\settings\SettingsDefinition $settingsdefinition */
        foreach ($settingsdefinitions as $settingsdefinition) {
            switch ($settingsdefinition->type) {
                case 'checkbox':
                    $settingsController::add_vis_setting_to_form($parentsettings, $settingsdefinition, $mform, false);

                    if (isset($activiysettings[$settingsdefinition->name])) {
                        if (!$parentsettings[$settingsdefinition->name]->locked) {
                            $mform->setDefault($settingsdefinition->name . "[value]",
                                    $activiysettings[$settingsdefinition->name]->value);
                            $mform->setDefault($settingsdefinition->name . "[locked]",
                                    $activiysettings[$settingsdefinition->name]->locked);
                        } else {
                            $mform->setDefault($settingsdefinition->name . "[value]",
                                    $parentsettings[$settingsdefinition->name]->value);
                            $mform->setDefault($settingsdefinition->name . "[locked]",
                                    $parentsettings[$settingsdefinition->name]->locked);
                        }
                    } else {
                        $mform->setDefault($settingsdefinition->name . "[value]",
                                $parentsettings[$settingsdefinition->name]->value);
                        $mform->setDefault($settingsdefinition->name . "[locked]",
                                $parentsettings[$settingsdefinition->name]->locked);
                    }
            }
        }

        $mform->addElement('header', 'mod_ivs/match_config_video_test', get_string('ivs_match_config_video_test', 'ivs'));

        // Assessment Mode.
        $attemptoptions = array(
                AssessmentConfig::ASSESSMENT_TYPE_FORMATIVE => get_string('ivs_match_config_assessment_mode_formative', 'ivs'));

        $mform->addElement('select', 'match_config_assessment_mode', get_string('ivs_match_config_mode', 'ivs'),
                $attemptoptions);

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();

    }

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
            if ($parts[0] == "SwitchCastFileVideoHost") {

                $defaultvalues['switchcast_video'] = $parts[1];
            }
        }

    }

    function get_videos_for_select() {

        global $COURSE;
        $publishedvideos = array();

        if (!class_exists('\\tool_opencast\\seriesmapping') || !class_exists('\\tool_opencast\\local\\api')) {
            return array(get_string('ivs_switchcast_video_chooser', 'ivs'));
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

        $publishedvideos = array(get_string('ivs_switchcast_video_chooser', 'ivs'));

        if (empty($videos)) {
            return $publishedvideos;
        }

        foreach ($videos as $video) {


            if (in_array('switchcast-api', $video->publication_status)) {
                $publishedvideos[$video->identifier] = $video->title;
            }
        }

        return $publishedvideos;
    }
}
