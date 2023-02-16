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

$ADMIN->add('modsettings', new admin_category('interactive_video_suite_settings', get_string('modulecategory', 'ivs'),
        $module->is_enabled() === false));

$settings = new admin_settingpage($section, get_string('settings', 'ivs'), 'moodle/site:config', $module->is_enabled() === false);

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/mod/ivs/lib.php');

    \mod_ivs\settings\SettingsService::ivs_add_new_settings_heading('mod_ivs/playersettings',get_string('ivs_player_settings', 'ivs'),$settings);
    $ivsplayersettings = \mod_ivs\settings\SettingsService::ivs_get_player_settings();
    \mod_ivs\settings\SettingsService::ivs_add_settings($ivsplayersettings,$settings);

    \mod_ivs\settings\SettingsService::ivs_add_new_settings_heading('mod_ivs/notification',get_string('ivs_player_settings_notification', 'ivs'),$settings);
    $ivsplayernotificationsettings = \mod_ivs\settings\SettingsService::ivs_get_player_notification_settings();
    \mod_ivs\settings\SettingsService::ivs_add_settings($ivsplayernotificationsettings,$settings);

    \mod_ivs\settings\SettingsService::ivs_add_new_settings_heading('mod_ivs/controls',get_string('ivs_player_settings_controls', 'ivs'),$settings);
    $ivsplayercontrolssettings = \mod_ivs\settings\SettingsService::ivs_get_player_control_settings();
    \mod_ivs\settings\SettingsService::ivs_add_settings($ivsplayercontrolssettings,$settings);

    \mod_ivs\settings\SettingsService::ivs_add_new_settings_heading('mod_ivs/advanced',get_string('ivs_player_settings_advanced', 'ivs'),$settings);
    \mod_ivs\settings\SettingsService::ivs_add_new_settings_heading('mod_ivs/advanced_comments',get_string('ivs_player_settings_advanced_comments', 'ivs'),$settings);
    $ivsplayeradvancedcommentssettings = \mod_ivs\settings\SettingsService::ivs_get_player_advanced_comments_settings();
    \mod_ivs\settings\SettingsService::ivs_add_settings($ivsplayeradvancedcommentssettings,$settings);

    \mod_ivs\settings\SettingsService::ivs_add_new_settings_heading('mod_ivs/advanced_match',get_string('ivs_player_settings_advanced_match', 'ivs'),$settings);
    $ivsplayeradvancedmatchsettings = \mod_ivs\settings\SettingsService::ivs_get_player_advanced_match_settings();
    \mod_ivs\settings\SettingsService::ivs_add_settings($ivsplayeradvancedmatchsettings,$settings);

    \mod_ivs\settings\SettingsService::ivs_add_new_settings_heading('mod_ivs/grades',get_string('ivs_grade', 'ivs'),$settings);
    $ivsplayergradesettings = \mod_ivs\settings\SettingsService::ivs_get_player_grade_settings();
    \mod_ivs\settings\SettingsService::ivs_add_settings($ivsplayergradesettings,$settings);

    \mod_ivs\settings\SettingsService::ivs_add_new_settings_heading('mod_ivs/advanced_video_source',get_string('ivs_player_settings_advanced_video_source', 'ivs'),$settings);
    \mod_ivs\settings\SettingsService::ivs_get_player_advanced_video_source_settings($settings);

    \mod_ivs\settings\SettingsService::ivs_add_new_settings_heading('mod_ivs/statistics',
            get_string('ivs_player_settings_statistics', 'ivs'), $settings);
    \mod_ivs\settings\SettingsService::ivs_usage_statistic_settings($settings);

}

$ADMIN->add('interactive_video_suite_settings', $settings);
// Tell core we already added the settings structure.
$settings = null;

$ADMIN->add('interactive_video_suite_settings', new admin_externalpage('admin_settings_license', get_string('ivs_license', 'ivs'),
        $CFG->wwwroot . '/mod/ivs/admin/admin_settings_license.php',
        'moodle/site:config'));

$ADMIN->add('interactive_video_suite_settings', new admin_externalpage('statistics', get_string('ivs_statistics', 'ivs'),
        $CFG->wwwroot . '/mod/ivs/admin/statistics.php',
        'moodle/site:config'));


