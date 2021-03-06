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

class SettingsService {

    public static function get_settings_definitions() {

        $settings[] = new SettingsDefinition(
                SettingsDefinition::SETTING_MATCH_QUESTION_ENABLED,
                get_string('ivs_setting_match_question', 'ivs'),
                'ivs_setting_match_question',
                'checkbox',
                0,
                true,
                true);

        $settings[] = new SettingsDefinition(
                SettingsDefinition::SETTING_PLAYER_PLAYBACKRATE,
                get_string('ivs_setting_playbackrate_enabled', 'ivs'),
                'ivs_setting_playbackrate_enabled',
                'checkbox',
                0,
                true,
                true);

        $settings[] = new SettingsDefinition(
                SettingsDefinition::SETTING_PLAYBACKCOMMANDS_ENABLED,
                get_string('ivs_setting_playbackcommands', 'ivs'),
                'ivs_setting_playbackcommands',
                'checkbox',
                0,
                true,
                true);

        $settings[] = new SettingsDefinition(
                SettingsDefinition::SETTING_BUTTONS_HOVER_ENABLED,
                get_string('ivs_setting_annotation_buttons', 'ivs'),
                'ivs_setting_annotation_buttons',
                'checkbox',
                0,
                true,
                true);

        $settings[] = new SettingsDefinition(
                SettingsDefinition::SETTING_ANNOTATION_READMORE_ENABLED,
                get_string('ivs_setting_annotation_readmore', 'ivs'),
                'ivs_setting_annotation_readmore',
                'checkbox',
                0,
                true,
                true);

        $settings[] = new SettingsDefinition(
                SettingsDefinition::SETTING_ANNOTATION_REALM_DEFAULT_ENABLED,
                get_string('ivs_setting_annotation_realm_default', 'ivs'),
                'ivs_setting_annotation_realm_default',
                'checkbox',
                0,
                true,
                true);

        $settings[] = new SettingsDefinition(
                SettingsDefinition::SETTING_MATCH_SINGLE_CHOICE_QUESTION_RANDOM_DEFAULT,
                get_string('ivs_setting_single_choice_question_random_default', 'ivs'),
                'ivs_setting_single_choice_question_random_default',
                'checkbox',
                0,
                true,
                true);

        $settings[] = new SettingsDefinition(
                SettingsDefinition::SETTING_PLAYER_AUTOHIDE_CONTROLBAR,
                get_string('ivs_setting_autohide_controlbar', 'ivs'),
                'ivs_setting_autohide_controlbar',
                'checkbox',
                0,
                true,
                true);

        $settings[] = new SettingsDefinition(
                SettingsDefinition::SETTING_PLAYER_ACCESSIBILITY,
                get_string('ivs_setting_accessibility', 'ivs'),
                'ivs_setting_accessibility',
                'checkbox',
                0,
                true,
                true);

        return $settings;
    }

    public function load_setting($targetid, $targettype, $settingname) {
        global $DB;

        $record = $DB->get_record('ivs_settings', array(
                'target_id' => $targetid,
                'target_type' => $targettype,
                'name' => $settingname,
        ));

        if (!empty($record)) {
            return Setting::from_db_record($record);
        }
    }

    public function load_settings($targetid, $targettype) {
        global $DB;

        $records = $DB->get_records('ivs_settings', array(
                'target_id' => $targetid,
                'target_type' => $targettype
        ));

        $settings = [];

        foreach ($records as $record) {
            if (!empty($record)) {
                $setting = Setting::from_db_record($record);
                $settings[$setting->name] = $setting;
            }
        }

        return $settings;

    }

    public function save_setting(Setting $setting) {
        global $DB;

        $db_data = (object) [
                'target_id' => $setting->targetid,
                'target_type' => $setting->targettype,
                'name' => $setting->name,
                'value' => $setting->value,
                'locked' => $setting->locked
        ];

        $existingsetting = $this->load_setting($setting->targetid, $setting->targettype, $setting->name);

        if ($existingsetting) {
            $db_data->id = $existingsetting->id;
        }

        if (isset($db_data->id)) {
            $DB->update_record('ivs_settings', $db_data);
        } else {
            $DB->insert_record('ivs_settings', $db_data, true, true);
        }

    }

    public function get_settings_global() {
        $defs = self::get_settings_definitions();
        $settings = [];
        $conf = get_config('mod_ivs');

        foreach ($defs as $def) {
            $setting = new Setting();
            $setting->name = $def->name;
            if (isset($conf->{$def->name})) {
                $setting->value = $conf->{$def->name};
                $setting->locked = $conf->{$def->name . '_locked'};
            }
            $settings[$setting->name] = $setting;
        }

        return $settings;

    }

    public function get_rarent_settings_for_activity($courseid = null) {
        $settingsglobal = $this->get_settings_global();

        $settingscourse = $this->load_settings($courseid, 'course');

        $settingsfinal = [];

        foreach ($settingsglobal as $name => $setting) {

            $settingsfinal[$name] = $setting;

            if (!$setting->locked) {
                if (!empty($settingscourse[$name])) {
                    $settingsfinal[$name] = $settingscourse[$name];
                    // Activity.
                    if (!$settingsfinal[$name]->locked) {
                        if (!empty($settings_activity[$name])) {
                            $settingsfinal[$name] = $settings_activity[$name];
                        }
                    }
                }
            }
        }

        return $settingsfinal;

    }

    public function get_settings_for_activity($activityid, $courseid = null) {
        $settingsglobal = $this->get_settings_global();

        $settingscourse = $this->load_settings($courseid, 'course');

        $settingsactivity = $this->load_settings($activityid, 'activity');

        $settingsfinal = [];

        foreach ($settingsglobal as $name => $setting) {

            $settingsfinal[$name] = $setting;

            if (!$setting->locked) {
                if (!empty($settingscourse[$name])) {
                    $settingsfinal[$name] = $settingscourse[$name];
                    // Activity.
                    if (!$settingsfinal[$name]->locked) {
                        if (!empty($settingsactivity[$name])) {
                            $settingsfinal[$name] = $settingsactivity[$name];
                        }
                    }
                } else {
                    // Activity.
                    if (!$settingsfinal[$name]->locked) {
                        if (!empty($settingsactivity[$name])) {
                            $settingsfinal[$name] = $settingsactivity[$name];
                        }
                    }
                }
            }
        }

        return $settingsfinal;
    }

    public static function add_vis_setting_to_form($globalsettings, SettingsDefinition $settingdefinition, $mform, $addlocked) {
        $attributes = array('class' => 'text-muted');
        if ($globalsettings[$settingdefinition->name]->locked) {
            $attributes['disabled'] = 'disabled';
        }

        if ($globalsettings[$settingdefinition->name]->value) {
            $defaultinfo = get_string('checkboxyes', 'admin');
        } else {
            $defaultinfo = get_string('checkboxno', 'admin');
        }

        $defaultinfo = get_string('defaultsettinginfo', 'admin', $defaultinfo);

        $availablefromgroup = array();
        $availablefromgroup[] = &$mform->createElement('checkbox', 'value', '', $defaultinfo, $attributes);

        $attributes = array('class' => 'ivs-setting-locked-checkbox');
        if ($globalsettings[$settingdefinition->name]->locked) {
            $attributes['disabled'] = 'disabled';
        }

        if ($settingdefinition->lockedsite && $addlocked) {
            $availablefromgroup[] = &
                    $mform->createElement('checkbox', 'locked', '', get_string('ivs_player_settings_locked', 'ivs'), $attributes);
        }

        $availablefromgroup[] = $mform->createElement('hidden', 'parent_value', $globalsettings[$settingdefinition->name]->value);

        $mform->addGroup($availablefromgroup, $settingdefinition->name, $settingdefinition->title, ' ', true);
        $mform->addHelpButton($settingdefinition->name, $settingdefinition->description, 'ivs');

        $mform->setType($settingdefinition->name . "[parent_value]", PARAM_INT);
    }

    public function process_activity_settings_form($ivs) {

        $parentsettings = $this->get_rarent_settings_for_activity($ivs->course);

        foreach ((array) $ivs as $key => $vals) {

            if (!array_key_exists($key, $parentsettings)) {
                continue;
            }

            if (!$parentsettings[$key]->locked) {

                if (!is_array($vals)) {
                    $val = $vals;
                } else {
                    $val = isset($vals['value']) ? $vals['value'] : 0;
                }

                $setting = new \mod_ivs\settings\Setting();
                $setting->name = $key;
                $setting->targettype = 'activity';
                $setting->targetid = $ivs->id;
                $setting->value = $val;
                $setting->locked = 0;

                $this->save_setting($setting);
            }
        }
    }

}
