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

namespace mod_ivs\settings;

global $CFG;
require_once("$CFG->libdir/formslib.php");

use moodleform;

class SettingsCourseForm extends moodleform {

    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $mform->addElement('header', 'mod_ivs/playersettings', get_string('ivs_player_settings', 'ivs'));

        $courseid = $this->_customdata['course_id'];

        $settingsdefinitions = \mod_ivs\settings\SettingsService::get_settings_definitions();
        $settingsController = new SettingsService();
        $globalsettings = $settingsController->get_settings_global();
        $coursesettings = $settingsController->load_settings($courseid, 'course');

        /** @var \mod_ivs\settings\SettingsDefinition $settings_definition */
        foreach ($settingsdefinitions as $settings_definition) {

            switch ($settings_definition->type) {
                case 'checkbox':

                    $settingsController::add_vis_setting_to_form($globalsettings, $settings_definition, $mform, true);

                    if (isset($coursesettings[$settings_definition->name])) {
                        if (!$globalsettings[$settings_definition->name]->locked) {
                            $mform->setDefault($settings_definition->name . "[value]",
                                    $coursesettings[$settings_definition->name]->value);
                            $mform->setDefault($settings_definition->name . "[locked]",
                                    $coursesettings[$settings_definition->name]->locked);
                        } else {
                            $mform->setDefault($settings_definition->name . "[value]",
                                    $globalsettings[$settings_definition->name]->value);
                            $mform->setDefault($settings_definition->name . "[locked]",
                                    $globalsettings[$settings_definition->name]->locked);
                        }
                    }
                    break;
            }
        }

        $mform->addElement('submit', 'submitbutton', get_string('savechanges'));

    }

    // Custom validation should be added here.
    function validation($data, $files) {
        return array();
    }

    /**
     * @param $global_settings
     * @param $player_setting
     * @param $mform
     * @throws \coding_exception
     */

}
