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

defined('MOODLE_INTERNAL') || die();

use mod_ivs\MoodleLicenseController;

/**
 * Execute ivs upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_ivs_upgrade($oldversion) {
    global $DB;

  if ($oldversion < 2020050524) {
    $switchcastexternalfilesenabled =
      get_config('mod_ivs', 'ivs_switchcast_external_files_enabled');
    $ivsswitchcastinternalfilesenabled =
      get_config('mod_ivs', 'ivs_switchcast_internal_files_enabled');

    set_config('ivs_switchcast_external_files_enabled',
      $switchcastexternalfilesenabled, 'mod_ivs');
    set_config('ivs_switchcast_internal_files_enabled',
      $ivsswitchcastinternalfilesenabled, 'mod_ivs');

    upgrade_mod_savepoint(TRUE, 2020050524, 'ivs');
  }

    if($oldversion < 2020050524){
        $enablecommentsbyroleconfigexists = get_config('mod_ivs', 'enable_comments_by_role');
        if(!$enablecommentsbyroleconfigexists){
            $all_roles = get_all_roles();
            foreach($all_roles as $role){
                $roles[] = $role->id;
            }
            $roles = implode(',',$roles);
            set_config('enable_comments_by_role', $roles, 'mod_ivs');
            set_config('enable_match_results_by_role', $roles, 'mod_ivs');
        }
        upgrade_mod_savepoint(true, 2020050524, 'ivs');
    }
    return true;
}

