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
 * annotation_created.php
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\event;
defined('MOODLE_INTERNAL') || die();

/**
 * Class annotation_created
 *
 */
class annotation_created extends \mod_ivs\event\annotation_base {

    /**
     * Return the name of the event.
     *
     * @return string
     */
    public static function get_name(): string  {
        return get_string('eventannotationcreated', 'mod_ivs');
    }

    /**
     * Return a description of the event.
     *
     * @return string
     */
    public function get_description(): string {
        return "The user with id {$this->userid} created an annotation with id {$this->objectid}.";
    }
}
