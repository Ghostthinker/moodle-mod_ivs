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

namespace mod_ivs;

use admin_setting_configtext;
use admin_setting_flag;

class admin_setting_configtext_ivs_custom_with_lock extends admin_setting_configtext {
    /**
     * Constructor
     *
     * @param string $name unique ascii name, either 'mysetting' for settings that in config,
     *     or 'myplugin/mysetting' for ones in config_plugins.
     * @param string $visiblename localised
     * @param string $description long localised info
     * @param array $defaultsetting ('value'=>string, 'locked'=>bool)
     * @param array $choices array of $value=>$label for each selection
     */
    public function __construct($name, $visiblename, $description, $defaultsetting, $yes = '1', $no = '0') {
        parent::__construct($name, $visiblename, $description, $defaultsetting['value'], $yes, $no);
        $this->set_locked_flag_options(admin_setting_flag::ENABLED, !empty($defaultsetting['locked']));
    }

    public function validate($data) {
        if (!is_numeric($data)) {
            return get_string('ivs_setting_annotation_audio_max_duration_validation', 'mod_ivs');
        }

        if ($data > IVS_SETTING_PLAYER_ANNOTATION_AUDIO_MAX_DURATION || $data < 0) {
            return get_string('ivs_setting_annotation_audio_max_duration_validation', 'mod_ivs');
        }
        return true;
    }
}