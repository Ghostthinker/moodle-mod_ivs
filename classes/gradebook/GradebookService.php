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
use core_table\local\filter\string_filter;
use enrol_self\self_test;
use Helper\MoodleHelper;
use mod_ivs\exception\ivs_exception;
use mod_ivs\ivs_match\AssessmentConfig;
use mod_ivs\ivs_match\timing\MatchTimingTakeResult;
use mod_ivs\MoodleMatchController;
use mod_ivs\settings\SettingsDefinition;
use mod_ivs\settings\SettingsService;
use stdClass;

/**
 * Class GradebookService
 */
#[\AllowDynamicProperties]
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
    public function get_best_score_by_takes($takes) {

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
    private function get_average_score_by_takes($takes) {

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
    private function get_first_score_by_takes($takes) {
        $score = $takes[0]->score ?? 0;
        return $score;
    }

    /**
     * returns score of last attempt of an ivs activity
     * @param $takes
     * @return int
     */
    private function get_last_score_by_takes($takes) {
        $take = end($takes);
        $score = $take->score ?? 0;
        return $score;
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
     * @return float
     * @throws \coding_exception
     */
    public function ivs_gradebook_get_score_by_takes($takes, $ivs) {
        $settingsservice = new SettingsService();
        $activitysettings = $settingsservice->get_settings_for_activity($ivs->id, $ivs->course);
        $gradingmethod = $activitysettings[SettingsDefinition::SETTING_PLAYER_VIDEOTEST_GRADE_METHOD]->value;

        switch ($gradingmethod) {
            case self::GRADE_METHOD_BEST_ATTEMPT:
                $score = $this->get_best_score_by_takes($takes);
                break;
            case self::GRADE_METHOD_AVERAGE:
                $score = $this->get_average_score_by_takes($takes);
                break;
            case self::GRADE_METHOD_FIRST_ATTEMPT:
                $score = $this->get_first_score_by_takes($takes);
                break;
            case self::GRADE_METHOD_LAST_ATTEMPT:
                $score = $this->get_last_score_by_takes($takes);
                break;
        }

        return round($score, 2 );
    }

    public function ivs_gradebook_get_timing_take_summary_data_by_grade_method($takes, $ivs){
        $settingsservice = new SettingsService();
        $activitysettings = $settingsservice->get_settings_for_activity($ivs->id, $ivs->course);
        $gradingmethod = $activitysettings[SettingsDefinition::SETTING_PLAYER_VIDEOTEST_GRADE_METHOD]->value;

        switch ($gradingmethod) {
            case self::GRADE_METHOD_BEST_ATTEMPT:
                $matchtimingresult = $this->get_best_timing_take_summary_by_takes($takes);
                break;
            case self::GRADE_METHOD_AVERAGE:
                $matchtimingresult = $this->get_average_timing_take_summary_by_takes($takes);
                break;
            case self::GRADE_METHOD_FIRST_ATTEMPT:
                $matchtimingresult = $this->get_first_timing_take_summary_by_takes($takes);
                break;
            case self::GRADE_METHOD_LAST_ATTEMPT:
                $matchtimingresult = $this->get_last_timing_take_summary_by_takes($takes);
                break;
        }

        return $matchtimingresult;
    }

    private function get_best_timing_take_summary_by_takes($takes){
        $score = null;
        foreach ($takes as $take) {
            if (isset($take->score) && ($score === null || $take->score > $score)) {
                $score = $take->score;
                $besttake = $take;
            }
        }

        $matchtimingresult = $this->get_evaluated_timing_type_result_by_take($besttake);

        return $matchtimingresult;

    }



    private function get_average_timing_take_summary_by_takes($takes){

        $numtakes = count($takes);
        $matchtimingtakeresultavg = new MatchTimingTakeResult();

        foreach ($takes as $take) {
            $matchtimingtakeresult = $this->get_evaluated_timing_type_result_by_take($take);

            $matchtimingtakeresultavg->pointsuser += $matchtimingtakeresult->pointsuser;
            $matchtimingtakeresultavg->pointstotal += $matchtimingtakeresult->pointstotal;

            foreach($matchtimingtakeresult->summary as $k => $v){
                $matchtimingtakeresultavg->summary[$k]['timing_type'] = $v['timing_type'];
                if (array_key_exists('sum_points', $matchtimingtakeresultavg->summary[$k])){
                    $matchtimingtakeresultavg->summary[$k]['sum_points'] += $v['sum_points'];
                }else{
                    $matchtimingtakeresultavg->summary[$k]['sum_points'] = $v['sum_points'];
                }

                if (array_key_exists('num_correct', $matchtimingtakeresultavg->summary[$k])){
                    $matchtimingtakeresultavg->summary[$k]['num_correct'] += $v['num_correct'];
                }else{
                    $matchtimingtakeresultavg->summary[$k]['num_correct'] = $v['num_correct'];
                }
            }

        }

        $matchtimingtakeresultavg->pointsuser = $matchtimingtakeresultavg->pointsuser / $numtakes;
        $matchtimingtakeresultavg->pointstotal = $matchtimingtakeresultavg->pointstotal / $numtakes;
        foreach($matchtimingtakeresultavg->summary as $k => $v){

            $matchtimingtakeresultavg->summary[$k]['num_correct'] = $v['num_correct']  / $numtakes;
            $matchtimingtakeresultavg->summary[$k]['sum_points'] = $v['num_correct'] * $v['timing_type']->score / $numtakes;
        }

        $matchtimingtakeresultavg->calculate_score();

        return $matchtimingtakeresultavg;

    }

    private function get_first_timing_take_summary_by_takes($takes){
        $firsttake = $takes[0];
        $matchtimingresult = $this->get_evaluated_timing_type_result_by_take($firsttake);
        return $matchtimingresult;
    }

    private function get_last_timing_take_summary_by_takes($takes){
        $lasttake = end($takes);
        $matchtimingresult = $this->get_evaluated_timing_type_result_by_take($lasttake);
        return $matchtimingresult;
    }

    private function get_evaluated_timing_type_result_by_take($take){
        $matchcontroller = new MoodleMatchController();

        $matchtake = $matchcontroller->match_take_get_db($take->id);
        $takeanswers = $matchcontroller->match_question_answers_get_by_take($take->id);
        $questions = $matchcontroller->match_questions_get_by_video_db($matchtake->videoid, 'timecode', true);
        $timingtypes = $matchcontroller->match_timing_type_get_db($matchtake->videoid);
        $matchtimingresult = MatchTimingTakeResult::evaluate_take($timingtypes, $questions,$takeanswers);

        return $matchtimingresult;
    }

    public function get_rendered_timing_take_summary($takes, $ivs) {
        global $DB;
        $course = $DB->get_record('course', array('id' => $ivs->course), '*', MUST_EXIST);
        $settingscontroller = new SettingsService();
        $activitysettings = $settingscontroller->get_settings_for_activity($ivs->id, $course->id);
        $timingtakesummaryenabled =  $activitysettings['show_timing_take_summary']->value;

        if ($timingtakesummaryenabled) {

            $timingtakesummary = '</li></ul><hr><ul>';
            $matchtimingresult = $this->ivs_gradebook_get_timing_take_summary_data_by_grade_method($takes, $ivs);

            foreach ($matchtimingresult->summary as $k => $v) {
                $timingtakesummary .= '<li>' . $v['timing_type']->label . ': <br>' . $v['num_correct'] . ' ' . get_string('ivs_grademethod_timing_take_summary_korrekt', 'ivs') . ' (' . $v['sum_points'] . ' ' . get_string('ivs_grademethod_timing_take_summary_points', 'ivs') . ')</li>';
            }
            $timingtakesummary .= '<li><strong>' . get_string('ivs_grademethod_timing_take_summary_pointsuser', 'ivs') . ' ' . $matchtimingresult->score . '% (' . $matchtimingresult->pointsuser . ' ' . get_string('ivs_grademethod_timing_take_summary_points', 'ivs') . ')</strong></li>';
            $timingtakesummary .= '</ul><ul>';

            return $timingtakesummary;

        }else{
            $timingtakesummary = '</li></ul><hr><br>' . get_string('ivs_grademethod_timing_take_summary_thanks', 'ivs') . '<ul>';
            return $timingtakesummary;
        }
    }


}


