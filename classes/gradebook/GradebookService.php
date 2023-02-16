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
 * This class manage all the functions for gradebook
 *
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\gradebook;

use core_course\analytics\target\course_gradetopass;
use enrol_self\self_test;
use Helper\MoodleHelper;
use mod_ivs\ivs_match\AssessmentConfig;
use mod_ivs\MoodleMatchController;
use mod_ivs\settings\SettingsDefinition;
use mod_ivs\settings\SettingsService;
use stdClass;

/**
 * Class GradebookService
 */
class GradebookService {

    /**
     * @var integer
     */

    const GRADE_METHOD_BEST_ATTEMPT = 0;

    /**
     * @var integer
     */

    const GRADE_METHOD_AVERAGE = 1;

    /**
     * @var integer
     */

    const GRADE_METHOD_FIRST_ATTEMPT = 2;

    /**
     * @var integer
     */

    const GRADE_METHOD_LAST_ATTEMPT = 3;

    private $moodlematchcontroller;

    /**
     * GradebookService constructor
     */
    public function __construct() {
        $this->moodlematchcontroller = new MoodleMatchController();
    }

    /**
     * Get grade method options
     * @return array |
     * @throws \coding_exception
     */
    public function ivs_get_grade_method_options() {
        return [

            self::GRADE_METHOD_BEST_ATTEMPT => get_string('ivs_grademethod_best_attempt', 'ivs'),
            self::GRADE_METHOD_AVERAGE => get_string('ivs_grademethod_average', 'ivs'),
            self::GRADE_METHOD_FIRST_ATTEMPT => get_string('ivs_grademethod_first_attempt', 'ivs'),
            self::GRADE_METHOD_LAST_ATTEMPT => get_string('ivs_grademethod_last_attempt', 'ivs'),
        ];
    }

    /*
     * Get attempt options
     * @return array
     * @throws \coding_exception
     */
    public function ivs_get_attempt_options() {
        $attemptoptions = array('0' => get_string('unlimited'));
        for ($i = 1; $i <= IVS_MAX_ATTEMPT_OPTION; $i++) {
            $attemptoptions[$i] = $i;
        }

        return $attemptoptions;
    }

    /**
     * Returns best attempt score
     * @param $takes
     * @return mixed|null
     */
    private function get_grade_item_best_attempt($takes) {

        $score = null;

        foreach ($takes as $take) {
            if (isset($take->score) && ($score === null || $take->score > $score)) {
                $score = $take->score;
            }
        }

        return $score;
    }

    /**
     * returns the average of all attempted scores by an ivs activity
     * @param $takes
     * @return float|int
     */
    private function get_grade_item_average($takes) {

        $total = 0;
        $count = 0;

        foreach ($takes as $take) {
            if (isset($take->score)) {
                $total += $take->score;
                $count++;
            }
        }

        $score = $total / $count;

        return $score;
    }

    /**
     * returns score of first attempt of an ivs activity
     * @param $takes
     * @return int
     */
    private function get_grade_item_first_attempt($takes) {
        return $takes[0]->score ?? 0;
    }

    /**
     * returns score of last attempt of an ivs activity
     * @param $takes
     * @return int
     */
    private function get_grade_item_last_attempt($takes) {
        $take = end($takes);
        return $take->score ?? 0;
    }

    /**
     * Update grade to pass for all course ivs activities
     * @param $courseid
     * @param $key
     * @param $vals
     * @throws \dml_exception
     */
    public function ivs_set_grade_to_pass_course_setting($courseid, $key, $vals) {
        global $DB;
        $course_ivs_activities = \mod_ivs\IvsHelper::get_ivs_activities_by_course_and_type($courseid);
        foreach($course_ivs_activities as $ivs){
            if ($key == 'videotest_grade_to_pass') {

                $record = $DB->get_record('grade_items', ['itemmodule' => 'ivs', 'iteminstance' => $ivs->id, 'courseid' => $courseid]);
                if (empty($record)) {
                    ivs_grade_item_update($ivs, NULL);
                    $record = $DB->get_record('grade_items', ['itemmodule' => 'ivs', 'iteminstance' => $ivs->id, 'courseid' => $courseid]);

                }

                $record->gradepass = isset($vals['value']) ? $vals['value'] : 0;
                $DB->update_record('grade_items', $record);
            }
        }
    }

    /**
     * Update grade to pass for single ivs activity
     * @param $ivs
     * @param $key
     * @param $vals
     * @throws \dml_exception
     */
    public function ivs_set_grade_to_pass_activity_setting($ivs, $key, $vals) {
        global $DB;

        if ($key == 'videotest_grade_to_pass') {
            $ivs->gradepass = $vals['value'];
            $DB->update_record('grade_items', $ivs);
        }
    }

    /**
     * Reset ivs gradebook items for whole course
     * @param $courseid
     * @param string $type
     * @throws \dml_exception
     */
    public function ivs_reset_gradebook($courseid, $type='') {
        global $CFG, $DB;

        $allivs = $DB->get_records_sql("
            SELECT q.*, cm.idnumber as cmidnumber, q.course as courseid
            FROM {modules} m
            JOIN {course_modules} cm ON m.id = cm.module
            JOIN {ivs} q ON cm.instance = q.id
            WHERE m.name = 'ivs' AND cm.course = ?", array($courseid));

        foreach ($allivs as $ivs) {
            ivs_grade_item_update($ivs, 'reset');
        }
    }

    /**
     * checks, if ivs activity writes into gradebook
     * @param $ivs
     * @return bool
     * @throws \dml_exception
     */
    public function ivs_gradebook_enabled($ivs){

        global $DB;

        $course = $DB->get_record('course', array('id' => $ivs->course), '*', MUST_EXIST);
        $settingscontroller = new SettingsService();
        $activitysettings = $settingscontroller->get_settings_for_activity($ivs->id, $course->id);
        $exammodeenabled =  $activitysettings['exam_mode_enabled']->value;
        $matchenabled = $activitysettings['match_question_enabled']->value;

        if (!$matchenabled){
            return FALSE;
        }

        if (!$exammodeenabled){
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Get IVS Grade settings
     * @param $ivs
     * @return false|mixed|stdClass
     * @throws \dml_exception
     */
    public function ivs_get_grade_settings($ivs){
        global $DB;
        $course = $DB->get_record('course', array('id' => $ivs->course), '*', MUST_EXIST);
        return $DB->get_record('grade_items', ['itemmodule' => 'ivs', 'iteminstance' => $ivs->id, 'courseid' => $course->id]);
    }

    /**
     * Returns score and description for ivs activity quiz view
     * @param $takes
     * @param $ivs
     * @return array
     * @throws \coding_exception
     */
    public function ivs_gradebook_get_score_info_by_takes($takes, $ivs) {
        $settingsservice = new SettingsService();
        $activitysettings = $settingsservice->get_settings_for_activity($ivs->id, $ivs->course);
        $score = 0;
        $gradingmethod = $activitysettings[SettingsDefinition::SETTING_PLAYER_VIDEOTEST_GRADE_METHOD]->value;

        switch ($gradingmethod) {
            case self::GRADE_METHOD_BEST_ATTEMPT:
                $score = $this->get_grade_item_best_attempt($takes);
                $desc = get_string('ivs_match_config_grade_mode_best_score_label', 'ivs');
                break;
            case self::GRADE_METHOD_AVERAGE:
                $score = $this->get_grade_item_average($takes);
                $desc = get_string('ivs_match_config_grade_mode_average_score_label', 'ivs');
                break;
            case self::GRADE_METHOD_FIRST_ATTEMPT:
                $score = $this->get_grade_item_first_attempt($takes);
                $desc = get_string('ivs_match_config_grade_mode_first_attempt_score_label', 'ivs');
                break;
            case self::GRADE_METHOD_LAST_ATTEMPT:
                $score = $this->get_grade_item_last_attempt($takes);
                $desc = get_string('ivs_match_config_grade_mode_last_attempt_score_label', 'ivs');
                break;
        }
        return ['score' => round($score, 2 ), 'desc' => $desc];
    }

}


