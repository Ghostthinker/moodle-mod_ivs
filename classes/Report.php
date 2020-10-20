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

namespace mod_ivs;


use DateTime;

defined('MOODLE_INTERNAL') || die();

class Report {

    const ROTATION_DAY = "daily";
    const ROTATION_WEEK = "weekly";
    const ROTATION_MONTH = "monthly";

    private $id = null;
    private $rotation;
    private $userid;
    private $courseid;
    private $filter;
    private $startdate;
    private $timecreated;
    private $timemodified;

    function __construct($dbrecord = false) {
        if (is_object($dbrecord)) {
            $dbrecord->filter = unserialize($dbrecord->filter);
            $this->set_record($dbrecord);
        }

    }

    /**
     * reporting constructor.
     *
     * @param $id
     * @param $rotation
     * @param $user_id
     * @param $course_id
     * @param $filter
     * @param $start_date
     * @param $timecreated
     * @param $timemodified
     */
    public function set_record($dbrecord) {
        $this->id = $dbrecord->id;
        $this->rotation = $dbrecord->rotation;
        $this->userid = $dbrecord->user_id;
        $this->courseid = $dbrecord->course_id;
        $this->filter = $dbrecord->filter;
        $this->startdate = $dbrecord->start_date;
        $this->timecreated = $dbrecord->timecreated;
        $this->timemodified = $dbrecord->timemodified;
    }

    /**
     * Get the database value fields from this class
     *
     * @return array
     */
    public function get_record() {
        return array(
                'id' => $this->id,
                'rotation' => $this->rotation,
                'user_id' => $this->userid,
                'course_id' => $this->courseid,
                'filter' => $this->filter,
                'start_date' => $this->startdate,
                'timecreated' => (int) $this->timecreated,
                'timemodified' => (int) $this->timemodified,
        );
    }

    /**
     * @return mixed
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function set_id($id) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function get_rotation() {
        return $this->rotation;
    }

    /**
     * @param mixed $rotation
     */
    public function set_rotation($rotation) {
        $this->rotation = $rotation;
    }

    /**
     * @return mixed
     */
    public function get_userid() {
        return $this->userid;
    }

    /**
     * @param mixed $userid
     */
    public function set_userid($userid) {
        $this->userid = $userid;
    }

    /**
     * @return mixed
     */
    public function get_courseid() {
        return $this->courseid;
    }

    /**
     * @param mixed $courseid
     */
    public function set_courseid($courseid) {
        $this->courseid = $courseid;
    }

    /**
     * @return mixed
     */
    public function get_filter() {
        return $this->filter;
    }

    /**
     * @param mixed $filter
     */
    public function set_filter($filter) {
        $this->filter = $filter;
    }

    /**
     * @return mixed
     */
    public function get_startdate() {
        return $this->startdate;
    }

    /**
     * @param mixed $startdate
     */
    public function set_startdate($startdate) {
        $this->startdate = $startdate;
    }

    /**
     * @return mixed
     */
    public function get_timecreated() {
        return $this->timecreated;
    }

    /**
     * @param mixed $timecreated
     */
    public function set_timecreated($timecreated) {
        $this->timecreated = $timecreated;
    }

    /**
     * @return mixed
     */
    public function get_timemodified() {
        return $this->timemodified;
    }

    /**
     * @param mixed $timemodified
     */
    public function set_timemodified($timemodified) {
        $this->timemodified = $timemodified;
    }


}
