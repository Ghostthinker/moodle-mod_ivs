<?php

namespace mod_ivs\settings;

class SettingsService {

    public static function getSettingsDefinitions() {

        $settings[] = new SettingsDefinition(
                SettingsDefinition::SETTING_MATCH_QUESTION_ENABLED,
                get_string('ivs_setting_match_question', 'ivs'),
                'ivs_setting_match_question',
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

        return $settings;
    }

    public function loadSetting($target_id, $target_type, $setting_name) {
        global $DB;

        $record = $DB->get_record('ivs_settings', array(
                'target_id' => $target_id,
                'target_type' => $target_type,
                'name' => $setting_name,
        ));

        if (!empty($record)) {
            return Setting::fromDBRecord($record);
        }
    }

    public function loadSettings($target_id, $target_type) {
        global $DB;

        $records = $DB->get_records('ivs_settings', array(
                'target_id' => $target_id,
                'target_type' => $target_type
        ));

        $settings = [];

        foreach ($records as $record) {
            if (!empty($record)) {
                $setting = Setting::fromDBRecord($record);
                $settings[$setting->name] = $setting;
            }
        }

        return $settings;

    }

    public function saveSetting(Setting $setting) {
        global $DB;

        $db_data = (object) [
                'target_id' => $setting->target_id,
                'target_type' => $setting->target_type,
                'name' => $setting->name,
                'value' => $setting->value,
                'locked' => $setting->locked
        ];

        $existing_setting = $this->loadSetting($setting->target_id, $setting->target_type, $setting->name);

        if ($existing_setting) {
            $db_data->id = $existing_setting->id;
        }

        if (isset($db_data->id)) {
            $DB->update_record('ivs_settings', $db_data);
        } else {
            $DB->insert_record('ivs_settings', $db_data, true, true);
        }

        //$a = 1;
    }

    public function getSettingsGlobal() {
        $defs = self::getSettingsDefinitions();
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

    public function getParentSettingsForActivity($course_id = null) {
        $settings_global = $this->getSettingsGlobal();

        $settings_course = $this->loadSettings($course_id, 'course');

        $settings_final = [];

        foreach ($settings_global as $name => $setting) {

            $settings_final[$name] = $setting;

            if (!$setting->locked) {
                if (!empty($settings_course[$name])) {
                    $settings_final[$name] = $settings_course[$name];
                    //activity
                    if (!$settings_final[$name]->locked) {
                        if (!empty($settings_activity[$name])) {
                            $settings_final[$name] = $settings_activity[$name];
                        }
                    }
                }
            }
        }

        return $settings_final;

    }

    public function getSettingsForActivity($activity_id, $course_id = null) {
        $settings_global = $this->getSettingsGlobal();

        $settings_course = $this->loadSettings($course_id, 'course');

        $settings_activity = $this->loadSettings($activity_id, 'activity');

        $settings_final = [];

        foreach ($settings_global as $name => $setting) {

            $settings_final[$name] = $setting;

            if (!$setting->locked) {
                if (!empty($settings_course[$name])) {
                    $settings_final[$name] = $settings_course[$name];
                    //activity
                    if (!$settings_final[$name]->locked) {
                        if (!empty($settings_activity[$name])) {
                            $settings_final[$name] = $settings_activity[$name];
                        }
                    }
                } else {
                    //activity
                    if (!$settings_final[$name]->locked) {
                        if (!empty($settings_activity[$name])) {
                            $settings_final[$name] = $settings_activity[$name];
                        }
                    }
                }
            }
        }

        //$settings_course = $this->loadSetting($activity_id, 'activity');

        return $settings_final;
    }

    public static function addVisSettingToForm($global_settings, SettingsDefinition $setting_definition, $mform, $add_locked) {
        $attributes = array('class' => 'text-muted');
        if ($global_settings[$setting_definition->name]->locked) {
            $attributes['disabled'] = 'disabled';
        }

        if ($global_settings[$setting_definition->name]->value) {
            $defaultinfo = get_string('checkboxyes', 'admin');
        } else {
            $defaultinfo = get_string('checkboxno', 'admin');
        }

        $defaultinfo = get_string('defaultsettinginfo', 'admin', $defaultinfo);

        $availablefromgroup = array();
        $availablefromgroup[] = &$mform->createElement('checkbox', 'value', '', $defaultinfo, $attributes);

        $attributes = array('class' => 'setting-locked-checkbox');
        if ($global_settings[$setting_definition->name]->locked) {
            $attributes['disabled'] = 'disabled';
        }

        if ($setting_definition->locked_site && $add_locked) {
            $availablefromgroup[] = &
                    $mform->createElement('checkbox', 'locked', '', get_string('ivs_player_settings_locked', 'ivs'), $attributes);
        }

        $availablefromgroup[] = $mform->createElement('hidden', 'parent_value', $global_settings[$setting_definition->name]->value);

        $mform->addGroup($availablefromgroup, $setting_definition->name, $setting_definition->title, ' ', true);
        //$mform->addElement('static', 'description', '', $setting_definition->description);
        $mform->addHelpButton($setting_definition->name, $setting_definition->description, 'ivs');

        $mform->setType($setting_definition->name . "[parent_value]", PARAM_INT);
    }

    public function processActivitySettingsForm($ivs) {

        $parent_settings = $this->getParentSettingsForActivity($ivs->course);

        foreach ((array) $ivs as $key => $vals) {

            if (!array_key_exists($key, $parent_settings)) {
                continue;
            }

            if (!$parent_settings[$key]->locked) {

                if (!is_array($vals)) {
                    $val = $vals;
                } else {
                    $val = isset($vals['value']) ? $vals['value'] : 0;
                }

                $setting = new \mod_ivs\settings\Setting();
                $setting->name = $key;
                $setting->target_type = 'activity';
                $setting->target_id = $ivs->id;
                $setting->value = $val;
                $setting->locked = 0;

                $this->saveSetting($setting);
            }
        }
    }

}
