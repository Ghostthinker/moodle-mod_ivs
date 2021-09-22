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
 * Class to create the reports
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs;


use DateTime;

defined('MOODLE_INTERNAL') || die();

/**
 * Class Report
 */
class Report {

    /**
     * @var string
     */
    const ROTATION_DAY = "daily";
    /**
     * @var string
     */
    const ROTATION_WEEK = "weekly";

    /**
     * @var string
     */
    const ROTATION_MONTH = "monthly";

    /**
     * @var null|int
     */
    private $id = null;

    /**
     * @var int
     */
    private $rotation;

    /**
     * @var int
     */
    private $userid;

    /**
     * @var int
     */
    private $courseid;

    /**
     * @var array
     */
    private $filter;

    /**
     * @var int
     */
    private $startdate;

    /**
     * @var int
     */
    private $timecreated;

    /**
     * @var int
     */
    private $timemodified;

    /**
     * Report constructor.
     *
     * @param false $dbrecord
     */
    public function __construct($dbrecord = false) {
        if (is_object($dbrecord)) {
            $dbrecord->filter = unserialize($dbrecord->filter);
            $this->set_record($dbrecord);
        }

    }

    /**
     * Set the record
     * @param \stdClass $dbrecord
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
     * Get the report id
     * @return mixed
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Set the report id
     * @param mixed $id
     */
    public function set_id($id) {
        $this->id = $id;
    }

    /**
     * Get the rotation
     * @return mixed
     */
    public function get_rotation() {
        return $this->rotation;
    }

    /**
     * Set the rotation
     * @param mixed $rotation
     */
    public function set_rotation($rotation) {
        $this->rotation = $rotation;
    }

    /**
     * Get the user id
     * @return mixed
     */
    public function get_userid() {
        return $this->userid;
    }

    /**
     * Set the user id
     * @param mixed $userid
     */
    public function set_userid($userid) {
        $this->userid = $userid;
    }

    /**
     * Get the course id
     * @return mixed
     */
    public function get_courseid() {
        return $this->courseid;
    }

    /**
     * Set the course id
     * @param mixed $courseid
     */
    public function set_courseid($courseid) {
        $this->courseid = $courseid;
    }

    /**
     * Get the filter
     * @return mixed
     */
    public function get_filter() {
        return $this->filter;
    }

    /**
     * Set the filter
     * @param mixed $filter
     */
    public function set_filter($filter) {
        $this->filter = $filter;
    }

    /**
     * Get the startdate
     * @return mixed
     */
    public function get_startdate() {
        return $this->startdate;
    }

    /**
     * Set the startdate
     * @param mixed $startdate
     */
    public function set_startdate($startdate) {
        $this->startdate = $startdate;
    }

    /**
     * Get the created date
     * @return mixed
     */
    public function get_timecreated() {
        return $this->timecreated;
    }

    /**
     * Set the created date
     * @param mixed $timecreated
     */
    public function set_timecreated($timecreated) {
        $this->timecreated = $timecreated;
    }

    /**
     * Get the edit date
     * @return mixed
     */
    public function get_timemodified() {
        return $this->timemodified;
    }

    /**
     * Set the edit date
     * @param mixed $timemodified
     */
    public function set_timemodified($timemodified) {
        $this->timemodified = $timemodified;
    }


}
