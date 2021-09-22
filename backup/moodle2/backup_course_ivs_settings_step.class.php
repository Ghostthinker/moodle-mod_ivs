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
 * This class is used to backup settings
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define the complete ivs structure for backup, with file and id annotations
 *
 * @package   mod_ivs
 * @category  backup
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */
class backup_course_ivs_settings_step extends backup_activity_structure_step {

    /**
     * Defines the backup structure of the module
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        // Get know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Get anonymize setting.
        $anonymize = $this->get_setting_value('anonymize');

        // Backup Player Settings.
        $coursesettings = new backup_nested_element('settings');

        $coursesetting = new backup_nested_element('setting', array('id'), array(
                'target_id',
                'target_type',
                'name',
                'value',
                'locked'
        ));

        $coursesettings->add_child($coursesetting);

        // Player settings activity.
        $coursesetting->set_source_sql('
            SELECT *
              FROM {ivs_settings}
             WHERE target_id = ? AND target_type = \'course\'',
                array(backup::VAR_COURSEID));

        return $this->prepare_activity_structure($coursesettings);
    }
}
