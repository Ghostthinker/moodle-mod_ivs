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
 * File to upgrade the ivs plugin
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

defined('MOODLE_INTERNAL') || die();

use mod_ivs\MoodleLicenseController;

/**
 * Execute ivs upgrade from the given old version
 *
 * @param int $oldversion
 *
 * @return bool
 */
function xmldb_ivs_upgrade($oldversion) {
    global $DB,$CFG;

    $dbman = $DB->get_manager();
    $updateservice = new \mod_ivs\UpdateService();
    if ($oldversion < 2020050524) {
        $switchcastexternalfilesenabled =
          get_config('mod_ivs', 'ivs_switchcast_external_files_enabled');
        $ivsswitchcastinternalfilesenabled =
          get_config('mod_ivs', 'ivs_switchcast_internal_files_enabled');

        set_config('ivs_switchcast_external_files_enabled',
          $switchcastexternalfilesenabled, 'mod_ivs');
        set_config('ivs_switchcast_internal_files_enabled',
          $ivsswitchcastinternalfilesenabled, 'mod_ivs');

        upgrade_mod_savepoint(true, 2020050524, 'ivs');
    }

    if ($oldversion < 2020050524) {
        $enablecommentsbyroleconfigexists = get_config('mod_ivs',
          'enable_comments_by_role');
        if (!$enablecommentsbyroleconfigexists) {
            $allroles = get_all_roles();
            foreach ($allroles as $role) {
                $roles[] = $role->id;
            }
            $roles = implode(',', $roles);
            set_config('enable_comments_by_role', $roles, 'mod_ivs');
            set_config('enable_match_results_by_role', $roles, 'mod_ivs');
        }
        upgrade_mod_savepoint(true, 2020050524, 'ivs');
    }

    // Change setting value type from int to varchar.
    if ($oldversion < 2021080604) {
        $table = new xmldb_table('ivs_settings');
        $field = new xmldb_field('value', XMLDB_TYPE_CHAR, null, null, null, null, 1, null);
        $dbman->change_field_type($table, $field);

        upgrade_mod_savepoint(true, 2021080604, 'ivs');
    }

    // Change setting value type from int to varchar.
    if ($oldversion < 2022030100) {
        //Get the current values from the config
        $ivs_opencast_internal_files_enabled = $CFG->ivs_opencast_internal_files_enabled;
        $ivs_opencast_external_files_enabled = $CFG->ivs_opencast_external_files_enabled;
        $ivs_panopto_external_files_enabled = $CFG->ivs_panopto_external_files_enabled;

        set_config('ivs_opencast_internal_files_enabled', $ivs_opencast_internal_files_enabled, 'mod_ivs');
        set_config('ivs_opencast_external_files_enabled', $ivs_opencast_external_files_enabled, 'mod_ivs');
        set_config('ivs_panopto_external_files_enabled', $ivs_panopto_external_files_enabled, 'mod_ivs');

        upgrade_mod_savepoint(true, 2022030100, 'ivs');
    }

    // Add new roles to export annotation capability
    if ($oldversion < 2022053103) {

        assign_capability('mod/ivs:download_annotations', CAP_ALLOW, 1, context_system::instance()->id);
        assign_capability('mod/ivs:download_annotations', CAP_ALLOW, 4, context_system::instance()->id);
        assign_capability('mod/ivs:download_annotations', CAP_ALLOW, 5, context_system::instance()->id);

        upgrade_mod_savepoint(true, 2022053103, 'ivs');
    }


    if($oldversion < 2022072902){

        $updateservice->settingInvertUpdate();
        upgrade_mod_savepoint(true, 2022072902, 'ivs');
    }

    if($oldversion < 2022110800){
        $updateservice->alterVideocommentTableForCommentType();
        upgrade_mod_savepoint(true, 2022110800, 'ivs');
    }

    return true;
}

