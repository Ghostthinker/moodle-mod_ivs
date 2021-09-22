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
        $mform->addElement('header', 'mod_ivs/playersettings', get_string('ivs_player_settings', 'ivs'));

        $courseid = $this->_customdata['course_id'];

        $settingsdefinitions = \mod_ivs\settings\SettingsService::get_settings_definitions();
        $settingscontroller = new SettingsService();
        $globalsettings = $settingscontroller->get_settings_global();
        $coursesettings = $settingscontroller->load_settings($courseid, 'course');

        $lockreadaccessoptions = SettingsService::get_ivs_read_access_options();

        /** @var \mod_ivs\settings\SettingsDefinition $settingsdefinition */
        foreach ($settingsdefinitions as $settingsdefinition) {

            $settingscontroller::add_vis_setting_to_form($settingsdefinition->type, $globalsettings, $settingsdefinition, $mform,
                    true, $lockreadaccessoptions);

            if (isset($coursesettings[$settingsdefinition->name])) {
                if (!$globalsettings[$settingsdefinition->name]->locked) {
                    $mform->setDefault($settingsdefinition->name . "[value]",
                            $coursesettings[$settingsdefinition->name]->value);
                    $mform->setDefault($settingsdefinition->name . "[locked]",
                            $coursesettings[$settingsdefinition->name]->locked);
                } else {
                    $mform->setDefault($settingsdefinition->name . "[value]",
                            $globalsettings[$settingsdefinition->name]->value);
                    $mform->setDefault($settingsdefinition->name . "[locked]",
                            $globalsettings[$settingsdefinition->name]->locked);
                }
            } else {
                $mform->setDefault($settingsdefinition->name . "[value]",
                        $globalsettings[$settingsdefinition->name]->value);
                $mform->setDefault($settingsdefinition->name . "[locked]",
                        $globalsettings[$settingsdefinition->name]->locked);
            }

        }

        $mform->addElement('submit', 'submitbutton', get_string('savechanges'));

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
