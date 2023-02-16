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
 * Render the ivs settings in the course form
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\settings;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("$CFG->libdir/formslib.php");

use mod_ivs\gradebook\GradebookService;
use mod_ivs\MoodleMatchController;
use moodleform;

/**
 * Class SettingsCourseForm
 */
class SettingsCourseForm extends moodleform {

    /**
     * Define the form
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $courseid = $this->_customdata['course_id'];

        $settingscontroller = new SettingsService();
        $globalsettings = $settingscontroller->get_settings_global();
        $coursesettings = $settingscontroller->load_settings($courseid, 'course');

        $moodlematchcontroller = new \mod_ivs\MoodleMatchController();
        \mod_ivs\settings\SettingsService::ivs_add_new_activity_settings_heading('mod_ivs/playerfeatures',
                get_string('ivs_player_settings_features', 'ivs'), $mform);
        $ivsplayersettings = \mod_ivs\settings\SettingsService::ivs_get_player_settings();
        SettingsService::ivs_render_activity_settings($ivsplayersettings, $coursesettings, $mform, $globalsettings,[\mod_ivs\settings\SettingsDefinition::SETTING_MATCH_QUESTION_ENABLED => $moodlematchcontroller->get_assessment_type_options()]);

        \mod_ivs\settings\SettingsService::ivs_add_new_activity_settings_heading('mod_ivs/notification',
                get_string('ivs_player_settings_notification', 'ivs'), $mform);
        $ivsplayernotificationsettings = \mod_ivs\settings\SettingsService::ivs_get_player_notification_settings();
        SettingsService::ivs_render_activity_settings($ivsplayernotificationsettings, $coursesettings, $mform, $globalsettings);

        \mod_ivs\settings\SettingsService::ivs_add_new_activity_settings_heading('mod_ivs/controls',
                get_string('ivs_player_settings_controls', 'ivs'), $mform);
        $ivsplayercontrolssettings = \mod_ivs\settings\SettingsService::ivs_get_player_control_settings();
        SettingsService::ivs_render_activity_settings($ivsplayercontrolssettings, $coursesettings, $mform, $globalsettings);
        \mod_ivs\settings\SettingsService::ivs_add_new_activity_settings_heading('mod_ivs/advanced',
                get_string('ivs_player_settings_advanced', 'ivs'), $mform);
        \mod_ivs\settings\SettingsService::ivs_add_new_activity_settings_heading('mod_ivs/advanced_comments',
                get_string('ivs_player_settings_advanced_comments', 'ivs'), $mform);
        $ivsplayeradvancedcommentssettings = \mod_ivs\settings\SettingsService::ivs_get_player_advanced_comments_settings();
        SettingsService::ivs_render_activity_settings($ivsplayeradvancedcommentssettings, $coursesettings, $mform, $globalsettings,
                [SettingsDefinition::SETTING_PLAYER_LOCK_REALM => SettingsService::get_ivs_read_access_options()]);

        $moodlematchcontroller = new MoodleMatchController();
        \mod_ivs\settings\SettingsService::ivs_add_new_activity_settings_heading('mod_ivs/advanced_match',
                get_string('ivs_player_settings_advanced_match', 'ivs'), $mform);
        $ivsplayeradvancedmatchsettings = \mod_ivs\settings\SettingsService::ivs_get_player_advanced_match_settings();
        SettingsService::ivs_render_activity_settings($ivsplayeradvancedmatchsettings, $coursesettings, $mform, $globalsettings);

        $gradebookservice = new GradebookService();
        \mod_ivs\settings\SettingsService::ivs_add_new_activity_settings_heading('mod_ivs/grades',
            get_string('ivs_grade', 'ivs'),$mform);
        $ivsplayergradesettings = \mod_ivs\settings\SettingsService::ivs_get_player_grade_settings();
        SettingsService::ivs_render_activity_settings($ivsplayergradesettings,$coursesettings,$mform,$globalsettings,[SettingsDefinition::SETTING_PLAYER_VIDEOTEST_ATTEMPTS => $gradebookservice->ivs_get_attempt_options(), SettingsDefinition::SETTING_PLAYER_VIDEOTEST_GRADE_METHOD => $gradebookservice->ivs_get_grade_method_options()]);

        $mform->closeHeaderBefore('ivssubmitbutton');
        $mform->addElement('submit', 'ivssubmitbutton', get_string('savechanges'));
    }

    /**
     * Validation function
     * @param array $data
     * @param mixed $files
     *
     * @return array
     */
    public function validation($data, $files) {
        return array();
    }
}
