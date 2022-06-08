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
 * This class manage all the settings
 *
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\settings;

use lang_string;

/**
 * Class SettingsService
 */
class SettingsService {

    /**
     * Get the read access options
     *
     * @return array
     */
    public static function get_ivs_read_access_options() {
        // Prepare system roles.
        // See users.php.
        $lockreadaccessoptions = array();
        $defaultteacherid = null;
        $lockreadaccessoptions['none'] = (string) (new lang_string('ivs_setting_read_access_none', 'ivs'));
        $lockreadaccessoptions['private'] = (string) (new lang_string('ivs_setting_read_access_private', 'ivs'));
        $lockreadaccessoptions['course'] = (string) (new lang_string('ivs_setting_read_access_course', 'ivs'));

        $roles = role_fix_names(get_all_roles(), null, ROLENAME_ORIGINALANDSHORT);
        foreach ($roles as $role) {
            $rolename = $role->localname;
            switch ($role->archetype) {
                case 'editingteacher':
                    $defaultteacherid = isset($defaultteacherid) ? $defaultteacherid : $role->id;
                    $lockreadaccessoptions['role_' . $role->id] = $rolename;
                    break;
            }
        }

        return $lockreadaccessoptions;
    }

    /**
     * Get all settings
     *
     * @return array
     */
    public static function get_settings_definitions() {

        $lockreadaccessoptions = self::get_ivs_read_access_options();

        $settings[] = new SettingsDefinition(
                SettingsDefinition::SETTING_ANNOTATIONS_ENABLED,
                get_string('ivs_setting_annotations_enabled', 'ivs'),
                'ivs_setting_annotations_enabled',
                'checkbox',
                1,
                true,
                true);

        $settings[] = new SettingsDefinition(
                SettingsDefinition::SETTING_USER_NOTIFICATION_SETTINGS,
                get_string('ivs_setting_user_notification_settings', 'ivs'),
                'ivs_setting_user_notification_settings',
                'checkbox',
                1,
                true,
                true
        );

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
                SettingsDefinition::SETTING_PLAYER_LOCK_REALM,
                get_string('ivs_setting_read_access_lock', 'ivs'),
                'ivs_setting_read_access_lock',
                'select',
                'none',
                true,
                true,
                $lockreadaccessoptions
        );

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

        $settings[] = new SettingsDefinition(
                SettingsDefinition::SETTING_PLAYER_ANNOTATION_AUDIO,
                get_string('ivs_setting_annotation_audio', 'ivs'),
                'ivs_setting_annotation_audio',
                'checkbox',
                0,
                true,
                true);

        $settings[] = new SettingsDefinition(
                SettingsDefinition::SETTING_PLAYER_ANNOTATION_AUDIO_MAX_DURATION,
                get_string('ivs_setting_annotation_audio_max_duration', 'ivs'),
                'ivs_setting_annotation_audio_max_duration',
                'text',
                120,
                true,
                true
                );

        $settings[] = new SettingsDefinition(
                SettingsDefinition::SETTING_PLAYER_ANNOTATION_COMMENT_PREVIEW_OFFSET,
                get_string('ivs_setting_annotation_comment_preview_offset', 'ivs'),
                'ivs_setting_annotation_comment_preview_offset',
                'text',
                0,
                true,
                true
        );

        return $settings;
    }

    /**
     * Load a specific setting
     *
     * @param int $targetid
     * @param string $targettype
     * @param string $settingname
     *
     * @return \mod_ivs\settings\Setting
     */
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

    /**
     * Load all settings
     *
     * @param int $targetid
     * @param string $targettype
     *
     * @return array
     */
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

    /**
     * Save the setting
     *
     * @param \mod_ivs\settings\Setting $setting
     */
    public function save_setting(Setting $setting) {
        global $DB;

        $dbdata = (object) [
                'target_id' => $setting->targetid,
                'target_type' => $setting->targettype,
                'name' => $setting->name,
                'value' => $setting->value,
                'locked' => $setting->locked
        ];

        $existingsetting = $this->load_setting($setting->targetid, $setting->targettype, $setting->name);

        if ($existingsetting) {
            $dbdata->id = $existingsetting->id;
        }

        if (isset($dbdata->id)) {
            $DB->update_record('ivs_settings', $dbdata);
        } else {
            $DB->insert_record('ivs_settings', $dbdata, true, true);
        }

    }

    /**
     * Get the global setting for an activity
     *
     * @return array
     */
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

    /**
     * Get the course setting for an activity
     *
     * @param null|int $courseid
     *
     * @return array
     */
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
                        if (!empty($settingsactivity[$name])) {
                            $settingsfinal[$name] = $settingsactivity[$name];
                        }
                    }
                }
            }
        }

        return $settingsfinal;

    }

    /**
     * Get the activity setting
     *
     * @param int $activityid
     * @param null|int $courseid
     *
     * @return array
     */
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

    /**
     * Visibility setting for the form
     *
     * @param string $type
     * @param array $globalsettings
     * @param SettingsDefinition $settingdefinition
     * @param \mod_ivs\settings\SettingsCourseForm $mform
     * @param bool $addlocked
     * @param null|array $options
     */
    public static function add_vis_setting_to_form($type, $globalsettings, SettingsDefinition $settingdefinition, $mform,
            $addlocked, $options = null) {
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

        $defaultsuffix = " ";

        $availablefromgroup = array();
        switch ($type) {
            case "checkbox":
                $availablefromgroup[] = &$mform->createElement('checkbox', 'value', '', $defaultinfo, $attributes);
                $mform->setType($settingdefinition->name . "[parent_value]", PARAM_INT);
                break;
            case "select":
                $availablefromgroup[] = &$mform->createElement('select', 'value', '', $options, $attributes);
                $mform->setType($settingdefinition->name . "[parent_value]", PARAM_TEXT);
                $defaultsuffix = get_string('defaultsettinginfo', 'admin',
                        $settingdefinition->options[$globalsettings[$settingdefinition->name]->value]);
                $availablefromgroup[] = $mform->createElement('html', '<label class="text-muted">' . $defaultsuffix . '</label>');
                break;
            case "text":
                if ($globalsettings[$settingdefinition->name]->locked) {
                    $attributes['disabled'] = 'disabled';
                }
                $availablefromgroup[] = &$mform->createElement('text', 'value', '', $attributes);
                $mform->setType($settingdefinition->name . "[parent_value]", PARAM_INT);
                $mform->setType($settingdefinition->name . "[value]", PARAM_INT);
                $defaultsuffix = get_string('defaultsettinginfo', 'admin',
                        $settingdefinition->default);
                $availablefromgroup[] = $mform->createElement('html', '<label class="text-muted">' . $defaultsuffix . '</label>');

        }

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
    }

    /**
     * Process the settings form for the activity
     *
     * @param mixed $ivs
     */
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


