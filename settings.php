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
 * @package   mod_forum
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/adminlib.php');

$ADMIN->add('modsettings', new admin_category('interactive_video_suite_settings', get_string('modulecategory', 'ivs'),
        $module->is_enabled() === false));

$settings = new admin_settingpage($section, get_string('settings', 'ivs'), 'moodle/site:config', $module->is_enabled() === false);

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/mod/ivs/lib.php');

    //IVS-Settings
    $settings->add(new admin_setting_heading('mod_ivs/ivssettings', get_string('ivs_settings', 'ivs'), ''));

    $settings->add(new admin_setting_configcheckbox('ivs_switchcast_external_files_enabled',
            get_string('ivs_setting_switchcast_external_files_title', 'ivs'),
            get_string('ivs_setting_switchcast_external_files_help', 'ivs'), 1));

    $settings->add(new admin_setting_configcheckbox('ivs_switchcast_internal_files_enabled',
            get_string('ivs_setting_switchcast_internal_files_title', 'ivs'),
            get_string('ivs_setting_switchcast_internal_files_help', 'ivs'), 1));

    //Player-Settings

    $settings->add(new admin_setting_heading('mod_ivs/playersettings', get_string('ivs_player_settings', 'ivs'), ''));

    $ivs_settings = \mod_ivs\settings\SettingsService::getSettingsDefinitions();

    foreach ($ivs_settings as $player_setting) {

        switch ($player_setting->type) {
            case 'checkbox':
                if ($player_setting->locked_site) {
                    $settings->add(new admin_setting_configcheckbox_with_lock("mod_ivs/" . $player_setting->name,
                            $player_setting->title, get_string($player_setting->description . '_help', 'ivs'),
                            $player_setting->default));
                } else {
                    $settings->add(new admin_setting_configcheckbox($player_setting->name, $player_setting->title,
                            $player_setting->description, $player_setting->default));
                }
                break;
        }
    }
}

$ADMIN->add('interactive_video_suite_settings', $settings);
// Tell core we already added the settings structure.
$settings = null;

$ADMIN->add('interactive_video_suite_settings', new admin_externalpage('admin_settings_license', get_string('ivs_license', 'ivs'),
        $CFG->wwwroot . '/mod/ivs/admin/admin_settings_license.php',
        'moodle/site:config'));
