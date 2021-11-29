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
 * This class is used to backup a ivs activity
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/ivs/backup/moodle2/backup_ivs_stepslib.php');
require_once($CFG->dirroot . '/mod/ivs/backup/moodle2/backup_course_ivs_settings_step.class.php');

/**
 * Class backup_ivs_activity_task
 */
class backup_ivs_activity_task extends backup_activity_task {

    /**
     * No specific settings for this activity
     */
    protected function define_my_settings() {

    }

    /**
     * Defines a backup step to store the instance data in the ivs.xml file
     */
    protected function define_my_steps() {
        $this->add_step(new backup_ivs_activity_structure_step('ivs_structure', 'ivs.xml'));
    }

    /**
     * Encodes URLs to the index.php and view.php scripts
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string the content with the URLs encoded
     */
    public static function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, '/');

        // Link to the list of ivss.
        $search = '/(' . $base . '\/mod\/ivs\/index.php\?id\=)([0-9]+)/';
        $content = preg_replace($search, '$@IVSINDEX*$2@$', $content);

        // Link to ivs view by moduleid.
        $search = '/(' . $base . '\/mod\/ivs\/view.php\?id\=)([0-9]+)/';
        $content = preg_replace($search, '$@IVSVIEWBYID*$2@$', $content);

        return $content;
    }
}
