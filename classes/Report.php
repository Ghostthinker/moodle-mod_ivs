<?php

namespace mod_ivs;

//use \mod_ivs\exception;

use DateTime;

defined('MOODLE_INTERNAL') || die();

class Report {

    const ROTATION_DAY = "daily";
    const ROTATION_WEEK = "weekly";
    const ROTATION_MONTH = "monthly";

    private $id = null;
    private $rotation;
    private $user_id;
    private $course_id;
    private $filter;
    private $start_date;
    private $timecreated;
    private $timemodified;

    function __construct($db_record = false) {
        if (is_object($db_record)) {
            $db_record->filter = unserialize($db_record->filter);
            $this->setRecord($db_record);
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
    public function setRecord($db_record) {
        $this->id = $db_record->id;
        $this->rotation = $db_record->rotation;
        $this->user_id = $db_record->user_id;
        $this->course_id = $db_record->course_id;
        $this->filter = $db_record->filter;
        $this->start_date = $db_record->start_date;
        $this->timecreated = $db_record->timecreated;
        $this->timemodified = $db_record->timemodified;
    }

    /**
     * Get the database value fields from this class
     *
     * @return array
     */
    public function getRecord() {
        return array(
                'id' => $this->id,
                'rotation' => $this->rotation,
                'user_id' => $this->user_id,
                'course_id' => $this->course_id,
                'filter' => $this->filter,
                'start_date' => $this->start_date,
                'timecreated' => (int) $this->timecreated,
                'timemodified' => (int) $this->timemodified,
        );
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getRotation() {
        return $this->rotation;
    }

    /**
     * @param mixed $rotation
     */
    public function setRotation($rotation) {
        $this->rotation = $rotation;
    }

    /**
     * @return mixed
     */
    public function getUserId() {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id) {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getCourseId() {
        return $this->course_id;
    }

    /**
     * @param mixed $course_id
     */
    public function setCourseId($course_id) {
        $this->course_id = $course_id;
    }

    /**
     * @return mixed
     */
    public function getFilter() {
        return $this->filter;
    }

    /**
     * @param mixed $filter
     */
    public function setFilter($filter) {
        $this->filter = $filter;
    }

    /**
     * @return mixed
     */
    public function getStartDate() {
        return $this->start_date;
    }

    /**
     * @param mixed $start_date
     */
    public function setStartDate($start_date) {
        $this->start_date = $start_date;
    }

    /**
     * @return mixed
     */
    public function getTimecreated() {
        return $this->timecreated;
    }

    /**
     * @param mixed $timecreated
     */
    public function setTimecreated($timecreated) {
        $this->timecreated = $timecreated;
    }

    /**
     * @return mixed
     */
    public function getTimemodified() {
        return $this->timemodified;
    }

    /**
     * @param mixed $timemodified
     */
    public function setTimemodified($timemodified) {
        $this->timemodified = $timemodified;
    }
    /*
      public function getTimeStartedFormatted() {

      }
    */

}
