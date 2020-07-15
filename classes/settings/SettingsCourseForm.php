<?php
/**
 * Created by PhpStorm.
 * User: Ghostthinker
 * Date: 20.11.2018
 * Time: 13:29
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

        $course_id = $this->_customdata['course_id'];

        $settings_definitions = \mod_ivs\settings\SettingsService::getSettingsDefinitions();
        $settingsController = new SettingsService();
        $global_settings = $settingsController->getSettingsGlobal();
        $coursesettings = $settingsController->loadSettings($course_id, 'course');

        /** @var \mod_ivs\settings\SettingsDefinition $settings_definition */
        foreach ($settings_definitions as $settings_definition) {

            switch ($settings_definition->type) {
                case 'checkbox':

                    $settingsController::addVisSettingToForm($global_settings, $settings_definition, $mform, true);

                    if (isset($coursesettings[$settings_definition->name])) {
                        if (!$global_settings[$settings_definition->name]->locked) {
                            $mform->setDefault($settings_definition->name . "[value]",
                                    $coursesettings[$settings_definition->name]->value);
                            $mform->setDefault($settings_definition->name . "[locked]",
                                    $coursesettings[$settings_definition->name]->locked);
                        } else {
                            $mform->setDefault($settings_definition->name . "[value]",
                                    $global_settings[$settings_definition->name]->value);
                            $mform->setDefault($settings_definition->name . "[locked]",
                                    $global_settings[$settings_definition->name]->locked);
                        }
                    }
                    break;
            }
        }

        $mform->addElement('submit', 'submitbutton', get_string('savechanges'));

    }

    //Custom validation should be added here
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
