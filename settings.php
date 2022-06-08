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
 * File for the settings
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

use mod_ivs\admin_setting_configtext_ivs_custom;
use mod_ivs\admin_setting_configtext_ivs_custom_with_lock;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/adminlib.php');
require_login(null, false);

$ADMIN->add('modsettings', new admin_category('interactive_video_suite_settings', get_string('modulecategory', 'ivs'),
        $module->is_enabled() === false));

$settings = new admin_settingpage($section, get_string('settings', 'ivs'), 'moodle/site:config', $module->is_enabled() === false);

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/mod/ivs/lib.php');

    // IVS-Settings.
    $settings->add(new admin_setting_heading('mod_ivs/ivssettings', get_string('ivs_settings', 'ivs'), ''));

    $settings->add(new admin_setting_configcheckbox('mod_ivs/ivs_opencast_external_files_enabled',
            get_string('ivs_setting_opencast_external_files_title', 'ivs'),
            get_string('ivs_setting_opencast_external_files_help', 'ivs'), 1));

    $settings->add(new admin_setting_configcheckbox('mod_ivs/ivs_panopto_external_files_enabled',
      get_string('ivs_setting_panopto_external_files_title', 'ivs'),
      get_string('ivs_setting_panopto_external_files_help', 'ivs'), 1));

    $settings->add(new admin_setting_configcheckbox('mod_ivs/ivs_opencast_internal_files_enabled',
            get_string('ivs_setting_opencast_internal_files_title', 'ivs'),
            get_string('ivs_setting_opencast_internal_files_help', 'ivs'), 1));

    // Player-Settings.

    $settings->add(new admin_setting_heading('mod_ivs/playersettings', get_string('ivs_player_settings', 'ivs'), ''));

    $ivssettings = \mod_ivs\settings\SettingsService::get_settings_definitions();

    foreach ($ivssettings as $playersetting) {

        switch ($playersetting->type) {
            case 'checkbox':
                if ($playersetting->lockedsite) {
                    $settings->add(new admin_setting_configcheckbox_with_lock("mod_ivs/" . $playersetting->name,
                            $playersetting->title, get_string($playersetting->description . '_help', 'ivs'),
                            ['value' => $playersetting->default]));
                } else {
                    $settings->add(new admin_setting_configcheckbox($playersetting->name, $playersetting->title,
                            $playersetting->description, ['value' => $playersetting->default]));
                }
                break;
            case 'select':
                if ($playersetting->lockedsite) {
                    $settings->add(new admin_setting_configselect_with_lock("mod_ivs/" . $playersetting->name,
                      $playersetting->title,
                            get_string($playersetting->description . '_help', 'ivs'), ['value' => $playersetting->default, 'locked' => 0], $playersetting->options));

                } else {
                    $settings->add(new admin_setting_configselect("mod_ivs/" . $playersetting->name, $playersetting->name,
                            get_string($playersetting->description . '_help', 'ivs'), $playersetting->default, $playersetting->options));

                }
                break;
            case 'text':
                if ($playersetting->lockedsite) {
                    $settings->add(new admin_setting_configtext_ivs_custom_with_lock("mod_ivs/" . $playersetting->name,
                            $playersetting->title,
                            get_string($playersetting->description . '_help', 'ivs'), ['value' => $playersetting->default], PARAM_INT));

                } else {
                    $settings->add(new admin_setting_configtext_ivs_custom("mod_ivs/" . $playersetting->name, $playersetting->title,
                            get_string($playersetting->description . '_helpcd', 'ivs'), $playersetting->default), PARAM_INT);
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


