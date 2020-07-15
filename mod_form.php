<?php
/*************************************************************************
 *
 * GHOSTTHINKER CONFIDENTIAL
 * __________________
 *
 *  2006 - 2017 Ghostthinker GmbH
 *  All Rights Reserved.
 *
 * NOTICE:  All information contained herein is, and remains
 * the property of Ghostthinker GmbH and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Ghostthinker GmbH
 * and its suppliers and may be covered by German and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Ghostthinker GmbH.
 */

/**
 * The main ivs configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_ivs
 * @copyright 2017 Ghostthinker GmbH <info@ghostthinker.de>
 * @license   All Rights Reserved.
 */

defined('MOODLE_INTERNAL') || die();

use \mod_ivs\ivs_match\AssessmentConfig;
use mod_ivs\settings\SettingsService;
use \tool_opencast\local\api;

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

        //Grade settings
        $this->standard_grading_coursemodule_elements();

        $mform->addElement('header', 'mod_ivs/playersettings', get_string('ivs_player_settings', 'ivs'));
        $settings_definitions = \mod_ivs\settings\SettingsService::getSettingsDefinitions();

        $settingsController = new SettingsService();
        $parent_settings = $settingsController->getParentSettingsForActivity($this->_course->id);

        if (!empty($this->_instance)) {
            $activiy_settings = $settingsController->loadSettings($this->_instance, 'activity');
        }

        /** @var \mod_ivs\settings\SettingsDefinition $settings_definition */
        foreach ($settings_definitions as $settings_definition) {

            switch ($settings_definition->type) {
                case 'checkbox':
                    $settingsController::addVisSettingToForm($parent_settings, $settings_definition, $mform, false);

                    if (isset($activiy_settings[$settings_definition->name])) {
                        if (!$parent_settings[$settings_definition->name]->locked) {
                            $mform->setDefault($settings_definition->name . "[value]",
                                    $activiy_settings[$settings_definition->name]->value);
                            $mform->setDefault($settings_definition->name . "[locked]",
                                    $activiy_settings[$settings_definition->name]->locked);
                        } else {
                            $mform->setDefault($settings_definition->name . "[value]",
                                    $parent_settings[$settings_definition->name]->value);
                            $mform->setDefault($settings_definition->name . "[locked]",
                                    $parent_settings[$settings_definition->name]->locked);
                        }
                    } else {
                        $mform->setDefault($settings_definition->name . "[value]",
                                $parent_settings[$settings_definition->name]->value);
                        $mform->setDefault($settings_definition->name . "[locked]",
                                $parent_settings[$settings_definition->name]->locked);
                    }
            }
        }

        $mform->addElement('header', 'mod_ivs/playersettings', get_string('ivs_match_config_video_test', 'ivs'));

        ////Assessment Mode
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
