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
 * annotation_base.php
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\event;
defined('MOODLE_INTERNAL') || die();

/**
 * Class annotation_base
 *
 */
#[\AllowDynamicProperties]
abstract class annotation_base extends \core\event\base {

    /**
     * Init function
     */
    protected function init() {
        $this->data['crud'] = 'c'; // Different crud operations which are possible 1. c(reate), r(ead), u(pdate), d(elete).
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'ivs_videocomment';
    }

    /**
     * Return the name of the event.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('eventannotationcreated', 'mod_ivs');
    }

    /**
     * Return a description of the event.
     *
     * @return string
     */
    public function get_description(): string {
        return "The user with id '{$this->userid}' created an annotation with id '{$this->objectid}' in the activity with course module id '{$this->contextinstanceid}'.";
    }


    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/ivs/view.php', array('id' => $this->contextinstanceid));
    }

    /**
     * Get legacy logdata
     * @return array
     */
    public function get_legacy_logdata() {
        // Override if you are migrating an add_to_log() call.
        return array($this->courseid, 'ivs', 'LOGACTION',
                '...........',
                $this->objectid, $this->contextinstanceid);
    }

    /**
     * Return legacy log data (if applicable).
     *
     * @return array
     */
    protected function get_legacy_eventdata(): array  {
        // Override if you migrating events_trigger() call.
        $data = new \stdClass();
        $data->id = $this->objectid;
        $data->userid = $this->relateduserid;
        return $data;
    }
}
