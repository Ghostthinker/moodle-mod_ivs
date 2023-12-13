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
 * The controller manages the Matchquestions
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs;

use ltiservice_gradebookservices\local\service\gradebookservices;
use mod_ivs\gradebook\GradebookService;
use mod_ivs\ivs_match\MatchTimingType;
use mod_ivs\ivs_match\question\QuestionSummary;
use mod_ivs\ivs_match\AssessmentConfig;
use mod_ivs\ivs_match\IvsMatchControllerBase;
use mod_ivs\ivs_match\exception\MatchNoConfigException;
use mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException;
use mod_ivs\ivs_match\exception\MatchQuestionException;
use mod_ivs\ivs_match\exception\MatchQuestionNotFoundException;
use mod_ivs\ivs_match\exception\MatchTakeException;
use mod_ivs\ivs_match\IIvsMatch;
use mod_ivs\ivs_match\MatchConfig;
use mod_ivs\ivs_match\MatchTake;
use mod_ivs\ivs_match\timing\MatchTimingTakeResult;
use mod_ivs\IvsHelper;
use mod_ivs\output\match\question_click_answer_view;
use mod_ivs\output\match\timing_question_answer_view;
use mod_ivs\output\match\timing_type_answer_view;
use mod_ivs\output\match\question_single_choice_answer_view;
use mod_ivs\output\match\question_text_answer_view;
use mod_ivs\settings\SettingsDefinition;
use mod_ivs\settings\SettingsService;
use moodle_url;
use stdClass;

/**
 * Class MoodleMatchController
 *
 */
class MoodleMatchController extends IvsMatchControllerBase implements IIvsMatch {

    /**
     * @var float Overlay offset in x direction
     */
    protected $overlayoffsetx = 2.3;

    /**
     * @var float Overlay offset in y direction
     */
    protected $overlayoffsety = 4.28;

    /**
     * MoodleMatchController constructor.
     */
    public function __construct() {
        parent::__construct($this);
    }

    /**
     * Override request handle to serve to reponse in proper way
     * @param string $endpoint
     * @param array $patharguments
     * @param string $method
     * @param array $postdata
     *
     * @return \mod_ivs\ivs_match\MatchResponse|void
     */
    public function handle_request($endpoint, $patharguments, $method, $postdata) {

        $response = parent::handle_request($endpoint, $patharguments, $method, $postdata);

        header("Content-type: application/json; charset=utf-8");
        http_response_code($response->get_status());
        print json_encode($response->get_data());
        exit;
    }

    /**
     * Get a single match question item
     *
     * @param int $questionid
     * @param bool $skipaccess
     * @return array
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionNotFoundException
     */
    public function match_question_get_db($questionid, $skipaccess = false) {

        global $DB;
        $questionfromdb = $DB->get_record('ivs_matchquestion', array('id' => $questionid));

        if (empty($questionfromdb->id)) {
            throw new MatchQuestionNotFoundException();
        }

        $questionfromdb =  $this->record_to_player_question((array) $questionfromdb);
        return $questionfromdb;

    }

    /**
     * Get all match question items by video keyed by question_id
     *
     * @param int $videoid
     * @param string $order
     * @param bool $skipaccess
     * @return mixed
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     */
    public function match_questions_get_by_video_db($videoid, $order = 'timecode', $skipaccess = false) {
        global $DB;
        $questionsfromdb = $DB->get_records('ivs_matchquestion', array('video_id' => $videoid));

        $questions = array();

        foreach ($questionsfromdb as $question) {
            $questions[$question->id] = $this->record_to_player_question((array) $question);
        }

        $questions = $this->match_questions_reformat_timing_questions($videoid, $questions);

        return $questions;
    }

    /**
     * Get all match question items by video keyed by question_id
     *
     * @param int $videoid
     * @param string $order
     * @param bool $skipaccess
     *
     * @return mixed
     * @throws \dml_exception
     */
    public function match_questions_get_by_video_db_order($videoid, $order = 'timecode', $skipaccess = false) {
        global $DB;

        $sql = 'SELECT * FROM {ivs_matchquestion} mq WHERE mq.video_id = ?';
        if ($order === 'timecode') {
            // ORDER BY keyword sorts the records in ascending order by default.
            $sql = 'SELECT * FROM {ivs_matchquestion} mq WHERE mq.video_id = ? ORDER BY time_stamp ASC';
        }
        $records = $DB->get_records_sql($sql, [$videoid]);

        $questions = array();
        foreach ($records as $question) {
            $questions[$question->id] = $this->record_to_player_question((array) $question);
        }

        $questions = $this->match_questions_reformat_timing_questions($videoid, $questions);

        return $questions;
    }

    /**
     * Insert a new match question into the database
     * @param int $videoid
     * @param array $data
     * @param false $skipaccess
     *
     * @return array|mixed
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     */
    public function match_question_insert_db($videoid, $data, $skipaccess = false) {

        if (!$skipaccess) {
            if (!$this->has_edit_access($videoid)) {
                throw new MatchQuestionAccessDeniedException(null, "Access denied");
            }
            // When not skipping access, always the current user to prevent uid spoofing.
            global $USER;
            $data['user_id'] = $USER->id;
        }

        $record = $this->get_question_record_from_request($videoid, $data);
        $record['timecreated'] = time();
        $this->save_question_db($record);

        return $this->record_to_player_question($record);

    }

    /**
     * Save the match question
     * @param array $record
     */
    private function save_question_db(&$record) {
        global $DB;

        if (empty($record['id'])) {
            $id = $DB->insert_record('ivs_matchquestion', $record, true, true);
            $record['id'] = $id;
        } else {
            $DB->update_record('ivs_matchquestion', $record, true);
        }

    }

    /**
     * Insert a new match question into the database
     * @param int $videoid
     * @param array $data
     * @param false $skipaccess
     *
     * @return array|mixed
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     */
    public function match_question_update_db($videoid, $data, $skipaccess = false) {

        global $DB;

        if (!$skipaccess) {
            if (!$this->has_edit_access($videoid)) {
                throw new MatchQuestionAccessDeniedException(null, "Access denied");
            }

        }

        $existingrecord = $DB->get_record('ivs_matchquestion', array('id' => $data['nid']));

        // Cannot change this field so use it from existing question.
        $data['user_id'] = $existingrecord->user_id;

        $record = $this->get_question_record_from_request($videoid, $data);
        $record['timemodified'] = time();
        $this->save_question_db($record);
        return $this->record_to_player_question($record);
    }

    /**
     * Delete an exsiting match question from the database
     *
     * @param int $questionid
     * @param bool $skipaccess
     * @return mixed
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     */
    public function match_question_delete_db($questionid, $skipaccess = false) {

        global $DB;
        $questionfromdb = $DB->get_record('ivs_matchquestion', array('id' => $questionid));

        if (!$skipaccess) {
            if (!$this->has_edit_access($questionfromdb->video_id)) {
                throw new MatchQuestionAccessDeniedException(null, "Access denied");
            }
        }

        $DB->delete_records('ivs_matchanswer', array('question_id' => $questionid));
        $DB->delete_records('ivs_matchquestion', array('id' => $questionid));

    }

    /**
     * Save a new created match answer
     *
     * @param int $videoid
     * @param array $data
     *   Holds answer data and question_id as well as the user_id
     * @param null $userid
     * @param bool $skipaccess
     * @return mixed
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionException
     */
    public function match_question_answer_insert_db($videoid, $data, $userid = null, $skipaccess = false) {
        global $DB, $USER;

        if (!$userid) {

            $userid = $USER->id;
        }

        $questionid = $data['question_id'];

        if (empty($questionid)) {
            throw new MatchQuestionNotFoundException();
        }

        $questionfromdb = $DB->get_record('ivs_matchquestion', array('id' => $questionid));

        if (!$skipaccess) {

            if (!$this->may_answer_question($questionfromdb)) {
                throw new MatchQuestionAccessDeniedException(null, "Access denied");
            }
        }

        $record = [
                'question_id' => $questionfromdb->id,
                'user_id' => $userid,
                'data' => !empty($data['question_data']) ? serialize($data['question_data']) : serialize([]),
                'take_id' => $data['take_id'],
                'correct' => !empty($data['is_correct']),
                'timecreated' => time(),
                'timemodified' => time(),
                'evaluated' => !empty($data['is_evaluated']) ? 1 : 0,
                'score' => !empty($data['is_correct']) ? 100 : 0,
        ];

        $id = $DB->insert_record('ivs_matchanswer', $record, true, true);
        $record['id'] = $id;

        return $this->record_to_player_answer((array) $record);

    }

    /**
     * Get a single answer by answer id
     *
     * @param int $answerid
     * @param bool $skipaccess
     * @return mixed
     */
    public function match_question_answer_get_db($answerid, $skipaccess = false) {
        global $DB;

        $answerfromdb = $DB->get_record('ivs_matchanswer', array('id' => $answerid));

        if (empty($answerfromdb->id)) {
            throw new MatchQuestionNotFoundException();
        }

        return $this->record_to_player_answer((array) $answerfromdb);

    }

    /**
     * Get a all answers by question and students
     * @param int $questionid
     * @param false $skipaccess
     *
     * @return array
     */
    public function match_question_answers_get_by_question($questionid, $skipaccess = false) {
        global $DB;

        $record = $DB->get_records('ivs_matchanswer', array(
                'question_id' => $questionid
        ));

        $answers = array();
        foreach ($record as $answer) {
            $answers[$answer->id] = $this->record_to_player_answer((array) $answer);
        }
        return $answers;
    }

    /**
     * Get a single answer by question and user
     * @param int $questionid
     * @param int $userid
     * @param false $skipaccess
     *
     * @return array|mixed
     */
    public function match_question_answer_get_by_question_and_user_db($questionid, $userid, $skipaccess = false) {
        global $DB;
        $record = $DB->get_record('ivs_matchanswer', array(
                'question_id' => $questionid,
                'user_id' => $userid
        ));

        return $this->record_to_player_answer((array) $record);
    }

    /**
     * Get a match answer by question and user
     * @param int $questionid
     * @param int $userid
     * @param false $skipaccess
     *
     * @return array
     */
    public function match_question_answers_get_by_question_and_user_db($questionid, $userid, $skipaccess = false) {
        global $DB;
        $record = $DB->get_records('ivs_matchanswer', array(
                'question_id' => $questionid,
                'user_id' => $userid
        ));

        $answers = array();
        foreach ($record as $answer) {
            $answers[$answer->id] = $this->record_to_player_answer((array) $answer);
        }

        return $answers;
    }

    /**
     * Get match answers by question
     * @param int $questionid
     * @param false $skipaccess
     *
     * @return array
     */
    public function match_question_answers_get_by_question_db($questionid, $skipaccess = false) {
        global $DB;
        $record = $DB->get_records('ivs_matchanswer', array('question_id' => $questionid));

        $answers = array();
        foreach ($record as $answer) {
            $answers[$answer->id] = $this->record_to_player_answer((array) $answer);
        }
        return $answers;
    }

    /**
     * Gets all necessary data for reporting
     *
     * @param int $questionid
     * @param int $userid
     * @param bool $skipaccess
     * @return array
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionNotFoundException
     */
    public function match_question_answers_get_by_question_and_user_for_reporting($questionid, $userid, $skipaccess = false) {

        $record = $this->match_question_answers_get_by_question_and_user_db($questionid, $userid, $skipaccess);

        $last = array_pop($record);
        $first = array_shift($record);
        $question = $this->match_question_get_db($questionid);

        $detail = array(
                'title' => $question['title'],
                'type' => $question['type'],
                'question' => $question,
                'userData' => $question['userdata'],
                'answers' => [$first, $last]
        );

        return $detail;
    }

    /**
     * Gets all necessary data for reporting
     *
     * @param int $questionid
     * @param int $userid
     * @param bool $skipaccess
     * @return array
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionNotFoundException
     */
    public function match_question_answers_get_by_timing_type_and_user_for_reporting($currenttimingtype, $userid, $videoid, $skipaccess = false) {

        //get all answers by timing type
        $timingtypeanswers = $this->match_question_answers_get_by_timing_type_and_user_db($currenttimingtype->id, $userid, $videoid, $skipaccess);
        $detail = [];

        $counterfirst = 0;
        $counterlast = 0;

        foreach ($timingtypeanswers as $questionid => $answers){
            if (!empty($answers)){
                $answers = array_values($answers);
                if ($answers[0]['is_correct']){
                    $counterfirst++;
                }
                if (end($answers)['is_correct']){
                    $counterlast++;
                }
            }
        }

        $detail = array(

            'userid' => $userid,
            'type' => 'timing_question',
            'videoid' => $videoid,
            'question' => [
                'nid' => $currenttimingtype->id,
                'title' => $currenttimingtype->title,
                'question_body' => $currenttimingtype->description,
            ],
            'answers' => [$counterfirst, $counterlast]
        );

        return $detail;
    }

    /**
     * Get a collection of answers by video and user keyed by question_id
     *
     * @param int $videoid
     * @param int $userid
     * @param bool $skipaccess
     * @return mixed
     */
    public function match_question_answers_get_by_video_and_user_db($videoid, $userid, $skipaccess = false) {
        global $DB, $USER;

        if (!$userid) {

            $userid = $USER->id;
        }

        $sql = "select * from {ivs_matchanswer} mqa
              inner join {ivs_matchquestion} mq
              on mqa.question_id = mq.id
              where mq.video_id = ?
              and mqa.user_id = ?";

        $records = $DB->get_records_sql($sql, [$videoid, $userid]);

        $answers = array();

        foreach ($records as $answer) {
            $answers[$answer->id] = $this->record_to_player_answer((array) $answer);
        }

        return $answers;
    }

    /**
     * Delete a single answer by answer_id
     *
     * @param int $answerid
     * @param bool $skipaccess
     * @return mixed
     */
    public function match_question_answer_delete_db($answerid, $skipaccess = false) {
        // TODO: Implement match_question_answer_delete_db() method.
    }

    /**
     * Has access to edit match questions
     *
     * @param int $videoid
     * @return bool
     * @throws \coding_exception
     */
    public function has_edit_access($videoid) {

        $coursemodule = get_coursemodule_from_instance('ivs', $videoid, 0, false, MUST_EXIST);
        $context = \context_module::instance($coursemodule->id);

        return has_capability('mod/ivs:edit_match_questions', $context);
    }

    /**
     * Get questions from request
     * @param int $videoid
     * @param array $postdata
     *
     * @return array
     */
    private function get_question_record_from_request($videoid, $postdata) {

        $record = array(
                'type' => clean_param($postdata['type'], PARAM_TEXT),
                'title' => clean_param($postdata['title'], PARAM_TEXT),
                'time_stamp' => clean_param($postdata['timestamp'], PARAM_INT),
                'duration' => clean_param($postdata['duration'], PARAM_INT),
                'question_body' => clean_param($postdata['question_body'], PARAM_TEXT),
                'video_id' => clean_param($videoid, PARAM_INT),
        );

        if (array_key_exists('user_id', $postdata)) {
            $record['user_id'] = is_null($postdata['user_id']) ? null : clean_param($postdata['user_id'], PARAM_INT);
        }

        if (!empty($postdata['nid'])) {
            $record['id'] = $postdata['nid'];
        }

        $record['type_data'] = serialize($postdata['type_data']);

        $top = clean_param($postdata['offset']['top'] ?? null, PARAM_FLOAT);
        $left = clean_param($postdata['offset']['left'] ?? null, PARAM_FLOAT);

        $extradata = [
                'offset' => [
                        'top' => !empty($top) ? $top : $this->overlayoffsety,
                        'left' => !empty($left) ? $left : $this->overlayoffsetx,
                ]
        ];

        $record['extra'] = serialize($extradata);

        return $record;
    }

    /**
     * Prepare record for player question
     * @param array $record
     *
     * @return array
     */
    private function record_to_player_question($record) {

        if (!empty($record['extra'])) {
            $extradata = unserialize($record['extra']);
        } else {
            $extradata = [
                    'offset' => [
                            'top' => $this->overlayoffsety,
                            'left' => $this->overlayoffsetx
                    ]
            ];
        }

        $data = [
                'nid' => $record['id'],
                'title' => $record['title'],
                'question_body' => $record['question_body'],
                'video' => $record['video_id'],
                'timestamp' => $record['time_stamp'],
                'duration' => $record['duration'],
                'type' => $record['type'],
                'type_data' => unserialize($record['type_data']),
                'userdata' => \mod_ivs\IvsHelper::get_user_data_for_player($record['user_id']),
                'offset' => $extradata['offset']
        ];

        // 08.05.2019 - 16:54 - BH Update Feedback Single Choice question form 2.x to 3.x versions.
        if ($data['type'] == 'single_choice_question') {
            foreach ($data['type_data']['options'] as &$option) {
                if (array_key_exists('feedback', $option) && is_string($option['feedback'])) {
                    $option['feedback_text'] = $option['feedback'];
                    unset($option['feedback']);
                }
            }
        }

        if ($data['type'] == 'timing_question'){
            $timingtypes = $this->match_timing_type_get_db($record['video_id']);
            foreach ($timingtypes as $timingtype) {
                if ($timingtype->id === $data['type_data']['timing_type_id']) {
                    $data['title'] = array_key_exists('timemodified',$record) ? $data['title'] : $this->match_format_question_time($record['time_stamp']) . ' - ' . $this->match_format_question_time($record['time_stamp'] + $record['duration']) . ' ' . $timingtype->label;
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Transform db to json object
     *
     * @param array $record
     * @return array
     */
    private function record_to_player_answer($record) {

        return [
                'id' => $record['id'],
                'user_id' => $record['user_id'],
                'is_correct' => !empty($record['correct']) ? true : false,
                'is_evaluated' => !empty($record['evaluated']) ? true : false,
                'question_data' => is_array($record['data']) ? $record['data'] : unserialize($record['data']),
                'question_id' => $record['question_id'],
                'created' => $record['timecreated'],
        ];
    }

    /**
     * Check if the question can be answered
     * @param \stdClass $question
     *
     * @return mixed
     */
    private function may_answer_question($question) {

        $coursemodule = get_coursemodule_from_instance('ivs', $question->video_id, 0, false, MUST_EXIST);
        $context = \context_module::instance($coursemodule->id);

        return has_capability('mod/ivs:create_match_answers', $context);
    }

    /**
     * Get a collection of answers by take
     * @param int $takeid
     * @param bool $onlylatest
     * @param false $skipaccess
     *
     * @return array
     */
    public function match_question_answers_get_by_take($takeid, $onlylatest = true, $skipaccess = false) {

        if (empty($takeid) || $takeid === 'null') {
            return array();
        }

        global $DB;
        $sql = "select * from {ivs_matchanswer} mqa
              where mqa.take_id = ?";

        $records = $DB->get_records_sql($sql, [$takeid]);

        $answers = array();

        foreach ($records as $answer) {
            if ($onlylatest) {
                $answers[$answer->question_id] = $this->record_to_player_answer((array) $answer);
            } else {
                $answers[] = $this->record_to_player_answer((array) $answer);
            }
        }

        return $answers;
    }

    /**
     * Get the Match config by video and optional context_id
     *
     * @param int $contextid
     * @param int $videoid
     * @return MatchConfig
     * @throws MatchNoConfigException
     */
    public function match_video_get_config_db($contextid, $videoid = null) {

        global $DB;
        $ivs = $DB->get_record('ivs', array('id' => $contextid), '*', MUST_EXIST);
        $moodlematchcontroller = new MoodleMatchController();
        $settingscontroller = new SettingsService();
        $activitysettings = $settingscontroller->get_settings_for_activity($ivs->id, $ivs->course);

        if ($activitysettings['exam_mode_enabled']->value) {
            if ($activitysettings['match_question_enabled']->value == AssessmentConfig::ASSESSMENT_TYPE_QUIZ) {
                return $moodlematchcontroller->get_quiz_match_config($ivs);
            } else if($activitysettings['match_question_enabled']->value == AssessmentConfig::ASSESSMENT_TYPE_TIMING) {
                return $moodlematchcontroller->get_timing_match_config($ivs);
            }
        }
        return $moodlematchcontroller->get_formative_match_config($ivs, $activitysettings['match_question_enabled']->value);
    }

    /**
     * Check access to match takes
     *
     * @param string $op - view, update
     * @param MatchTake $take
     * @return mixed
     */
    public function match_take_access($op, MatchTake $take) {
        // TODO: Implement match_take_access() method.
    }

    /**
     * Insert a match take in the database
     *
     * @param MatchTake $take
     * @return mixed
     */
    public function match_take_insert_db(MatchTake &$take) {
        return $this->savetakedb($take);
    }

    /**
     * Update a match take in the database
     *
     * @param MatchTake $take
     * @return mixed
     */
    public function match_take_update_db(MatchTake $take) {
        return $this->savetakedb($take);
    }

    /**
     * Update or insert a take
     *
     * @param \mod_ivs\ivs_match\MatchTake $take
     */
    private function savetakedb(MatchTake $take) {

        global $DB, $USER;

        if (!$take->userid) {

            $take->userid = $USER->id;
        }

        $dbdata = (object) [
                'video_id' => $take->videoid,
                'status' => $take->status,
                'context_id' => $take->contextid,
                'user_id' => $take->userid,
                'evaluated' => $take->evaluated,
                'score' => $take->score,
                'timecreated' => $take->created,
                'timemodified' => time(),
                'timecompleted' => $take->completed,
        ];

        if ($take->id) {
            $dbdata->id = $take->id;
        }

        if (empty($dbdata->id)) {
            $id = $DB->insert_record('ivs_matchtake', $dbdata, true, true);
            if (empty($id)) {
                throw new MatchTakeException();
            }
            $take->id = $id;
        }else{
            $DB->update_record('ivs_matchtake', $dbdata, true);
        }

        $ivs = $DB->get_record('ivs', array('id' => $take->videoid), '*', MUST_EXIST);
        ivs_update_grades($ivs, $take);

        return $take;

    }

    /**
     * Delete a match take from the database
     *
     * @param int $takeid
     * @return mixed
     */
    public function match_take_delete_db($takeid) {
        global $DB;

        $DB->delete_records('ivs_matchtake', array('id' => $takeid));
    }

    /**
     * Get a match take from the database
     *
     * @param int $takeid
     * @return MatchTake
     */
    public function match_take_get_db($takeid) {
        global $DB;

        $record = $DB->get_record('ivs_matchtake', array(
                'id' => $takeid,
        ));

        if ($record) {
            return $this->match_take_from_moodle_db_record((array) $record);
        }
    }

    /**
     * Get all match takes by user and context
     *
     * @param int $userid
     * @param int $videoid
     * @param null $contextid
     * @param array $status
     * @return MatchTake[]
     */
    public function match_takes_get_by_user_and_video_db($userid, $videoid, $contextid, $status = []) {

        global $DB, $USER;

        $contextid = $videoid;
        if (!$userid) {
            $userid = $USER->id;
        }


        $params = [
            'video_id' => $videoid,
            'user_id' => $userid,
            'context_id' => $contextid,
        ];

        $sql = "SELECT *
                  FROM {ivs_matchtake} 
                 WHERE video_id = :video_id
                       AND user_id = :user_id
                       AND context_id = :context_id";

        if (!empty($status)){
            $sql .= " AND status IN ('new', 'progress')";
        }



        $records = $DB->get_recordset_sql($sql, $params);

        $takes = array();

        foreach ($records as $record) {
            $takes[] = $this->match_take_from_moodle_db_record((array) $record);
        }

        return $takes;

    }

    /**
     * Prepare match take for saving
     * @param array $record
     *
     * @return \mod_ivs\ivs_match\MatchTake
     */
    private function match_take_from_moodle_db_record($record) {

        $mt = new MatchTake();
        $mt->id = $record['id'];
        $mt->evaluated = $record['evaluated'];
        $mt->score = $record['score'];
        $mt->created = $record['timecreated'];
        $mt->changed = $record['timemodified'];
        $mt->contextid = $record['context_id'];
        $mt->videoid = $record['video_id'];
        $mt->userid = $record['user_id'];
        $mt->completed = $record['timecompleted'];
        $mt->status = $record['status'];

        return $mt;
    }

    /**
     * Get the unique id of the currently acting user
     *
     * @return int
     */
    public function get_current_user_id() {
        global $USER;
        return $USER->id;

    }

    /**
     * Check permission for match question
     * @param string $op
     * @param int $videoid
     * @param null $contextid
     * @param null $userid
     */
    public function permission_match_question($op, $videoid, $contextid = null, $userid = null) {
        // TODO: Implement permission_match_question() method.
    }

    /**
     * Returns assessment type options
     * @return array
     */
    public function get_assessment_type_options(){
        return[
            AssessmentConfig::ASSESSMENT_TYPE_NONE => get_string('ivs_match_config_assessment_mode_none', 'ivs'),
            AssessmentConfig::ASSESSMENT_TYPE_QUIZ => get_string('ivs_match_config_assessment_mode_quiz', 'ivs'),
            AssessmentConfig::ASSESSMENT_TYPE_TIMING => get_string('ivs_match_config_assessment_mode_timing', 'ivs'),
        ];
    }

    /**
     * Get an array of assessment configs
     *
     * @param int $userid
     * @param int $videoid
     * @param bool $includesimulation
     * @return AssessmentConfig[]
     * @throws MatchNoConfigException
     */
    public function assessment_config_get_by_user_and_video($userid, $videoid, $includesimulation = false) {

        global $DB;
        $ivs = $DB->get_record('ivs', array('id' => $videoid), '*', MUST_EXIST);
        $moodlematchcontroller = new MoodleMatchController();

        $settingscontroller = new SettingsService();
        $activitysettings = $settingscontroller->get_settings_for_activity($videoid, $ivs->course);

        if ($activitysettings['exam_mode_enabled']->value != 0 || $activitysettings['match_question_enabled']->value == AssessmentConfig::ASSESSMENT_TYPE_TIMING) {
            $assessmentconfig = $moodlematchcontroller->get_videotest_assessment_config_by_user($userid, $ivs);
        }else{
            $assessmentconfig = $moodlematchcontroller->get_formative_assessment_config($userid, $ivs);
        }

        return $assessmentconfig;
    }

    /**
     * Returns all videos with match enabled
     *
     * @param array $videos
     * @return array
     */
    public function get_match_enabled_videos($videos) {

        $settingscontroller = new SettingsService();

        $matchvideos = [];
        foreach ($videos as $video) {
            $activitysettings = $settingscontroller->get_settings_for_activity($video->id, $video->course);

            if ($activitysettings['match_question_enabled']->value) {
                $matchvideos[] = $video;
            }
        }
        return $matchvideos;
    }

    /**
     * Get single Choice Data for Reporting
     *
     * @param array $reportinganswers
     * @param \stdClass $user
     * @return array
     */
    public function getsinglechoicereportingdata($reportinganswers, $user) {

        $controller = new MoodleMatchController();

        foreach ($reportinganswers as $key => $answer) {

            $singlechoicedata['correct'] = '-';
            $singlechoicedata['last'] = '-';
            $singlechoicedata['retries'] = '-';
            $singlechoicedata['selected_answer'] = '-';
            $singlechoicedata['last_selected_answer'] = '-';

            if (!empty($answer['answers'][1]) && $answer['answers'][1]['user_id'] === $user->id) {

                $userid = $user->id;
                $answers = $controller->match_question_answers_get_by_question_and_user_db($answer['question']['nid'], $userid);
                $numanswers = count($answers);

                if ($numanswers > 0) {
                    $singlechoicedata['retries'] = $numanswers - 1;

                    $lastanswer = $answer['answers'][1];
                    if (!empty($answer['answers'][0])) {
                        $firstanswer = $answer['answers'][0];
                    } else {
                        $firstanswer = $answer['answers'][1];
                    }

                    $singlechoicedata['correct'] = !empty($firstanswer['is_correct']) ? 1 : 0;
                    $singlechoicedata['last'] = !empty($lastanswer['is_correct']) ? 1 : 0;

                }

                // First selected answer.
                $singlechoicedata['selected_answer'] = $this->get_first_selected_single_choice_answer($answer);

                // Last selected answer.
                $singlechoicedata['last_selected_answer'] = $this->get_last_selected_single_choice_answer($answer);

                break;

            }
        }

        return $singlechoicedata;
    }

    /**
     * Returns index of first selected single choice answer
     *
     * @param array $answer
     */
    public function get_first_selected_single_choice_answer($answer) {

        $singechoiceanswerindex = 0;

        if (empty($answer['answers'][0])) {
            $checkedids = explode(',', $answer['answers'][1]['question_data']['checked_id']);
        } else {
            $checkedids = explode(',', $answer['answers'][0]['question_data']['checked_id']);
        }

        $singlechoicequestions = $answer['question']['type_data']['options'];
        $selected_answers = [];
        foreach ($singlechoicequestions as $question) {
            if (in_array($question['id'], $checkedids)){
            $selected_answers[] = $question['description'];
            }
        }

        return implode(', ', $selected_answers);
    }

    /** returns index of last selected single choice answer
     *
     * @param array $answer
     */
    public function get_last_selected_single_choice_answer($answer) {
        $singechoiceanswerindex = 0;
        $checkedid = $answer['answers'][1]['question_data']['checked_id'];

        $singlechoicequestions = $answer['question']['type_data']['options'];

        foreach ($singlechoicequestions as $question) {
            $singechoiceanswerindex++;
            if ($question['id'] == $checkedid) {
                break;
            }

        }

        return $singechoiceanswerindex;
    }

    /**
     * Get the title from a match question
     * @param array $question
     *
     * @return mixed
     */
    public function get_match_question_title($question) {
        return !empty($question['title']) ? $question['title'] : shorten_text($question['question_body']);
    }

    /**
     * Get the title from a match question
     * @param array $question
     *
     * @return mixed
     */
    public function get_match_question_timing_title($timingtype) {
        return !empty($timingtype->description) ? ($timingtype->title . ': ' . shorten_text($timingtype->description)) : $timingtype->label;
    }

    /**
     * Get question summary raw data
     *
     * @param array $question
     * @param array $coursestudents
     * @return \mod_ivs\ivs_match\question\QuestionSummary
     */
    public function get_question_summary_data($question, $coursestudents) {

        $questionsummary = new QuestionSummary();

        $questionsummary->question_id = $question['nid'];
        $questionsummary->question_title = $question['title'];
        $questionsummary->question_body = $question['question_body'];
        $questionsummary->num_students_total = count($coursestudents);
        $questionsummary->num_students_participation = 0;
        $questionsummary->first_attempt_correct = 0;
        $questionsummary->last_attempt_correct = 0;
        $questionsummary->question_type = $question['type'];

        $answers = $this->match_question_answers_get_by_question($question['nid']);

        $useranswerdata = [];

        foreach ($answers as $answer) {

            $userid = $answer['user_id'];

            // Only student answers.
            if (!array_key_exists($userid, $coursestudents)) {
                continue;
            }
            if (empty($useranswerdata[$userid])) {
                $useranswerdata[$userid] = [
                        'first_attempt_correct' => $answer['is_correct'],
                        'last_attempt_correct' => $answer['is_correct'],
                        'num_tries' => 1
                ];
            } else {
                $useranswerdata[$userid]['last_attempt_correct'] = $answer['is_correct'];
                $useranswerdata[$userid]['num_tries']++;
            }
        }

        foreach ($useranswerdata as $answerdata) {
            if ($answerdata['first_attempt_correct']) {
                $questionsummary->first_attempt_correct++;
            }
            if ($answerdata['last_attempt_correct']) {
                $questionsummary->last_attempt_correct++;
            }
        }

        $questionsummary->num_students_participation = count($useranswerdata);

        return $questionsummary;
    }

    /**
     * Get question summary formated data
     *
     * @param array $question
     * @param array $coursestudents
     * @return \mod_ivs\ivs_match\question\QuestionSummary
     */
    public function get_question_summary_formated($question, $coursestudents) {
        $questionsummary = $this->get_question_summary_data($question, $coursestudents);
        $data = new \stdClass();

        $data->question_id = $questionsummary->question_id;
        $data->question_title = $questionsummary->question_title;
        $data->question_body = $questionsummary->question_body;

        switch ($questionsummary->question_type) {
            case 'text_question':
                $data->question_type = get_string('ivs_match_question_summary_question_type_text', 'ivs');
                $data->question_first_try = 'N/A';
                $data->question_last_try = 'N/A';
                break;
            case 'click_question':
                $data->question_type = get_string('ivs_match_question_summary_question_type_click', 'ivs');
                $data->question_first_try = $questionsummary->num_students_participation == 0 ? '0%' :
                        round($questionsummary->first_attempt_correct * 100 / $questionsummary->num_students_participation, 0) .
                        '%';
                $data->question_last_try = $questionsummary->num_students_participation == 0 ? '0%' :
                        round($questionsummary->last_attempt_correct * 100 / $questionsummary->num_students_participation, 0) .
                        '%';
                break;
            case 'single_choice_question':
                $data->question_type = get_string('ivs_match_question_summary_question_type_single', 'ivs');
                $data->question_first_try = $questionsummary->num_students_participation == 0 ? '0%' :
                        round($questionsummary->first_attempt_correct * 100 / $questionsummary->num_students_participation, 0) .
                        '%';
                $data->question_last_try = $questionsummary->num_students_participation == 0 ? '0%' :
                        round($questionsummary->last_attempt_correct * 100 / $questionsummary->num_students_participation, 0) .
                        '%';
                break;
            case 'timing_question':
                $data->question_type = get_string('ivs_match_question_summary_question_type_timing', 'ivs');
                $data->question_first_try = $questionsummary->num_students_participation == 0 ? '0%' :
                    round($questionsummary->first_attempt_correct * 100 / $questionsummary->num_students_participation, 0) .
                    '%';
                $data->question_last_try = $questionsummary->num_students_participation == 0 ? '0%' :
                    round($questionsummary->last_attempt_correct * 100 / $questionsummary->num_students_participation, 0) .
                    '%';
                break;
        }

        $data->question_answered = $questionsummary->num_students_participation . ' / ' . $questionsummary->num_students_total;

        return $data;
    }

    /**
     * Render latex
     * @param string $text
     * @param false $onlyinline
     *
     * @return mixed
     */
    public function ivs_prepare_latex_for_rendering($text, $onlyinline = false) {
        if ($onlyinline) {
            $text = str_replace('$$', '$', $text);
            $text = str_replace('$', '$$', $text);
            $text = str_replace('\[', '$$', $text);
            $text = str_replace('\]', '$$', $text);
            $text = str_replace('\(', '$$', $text);
            $text = str_replace('\)', '$$', $text);
        } else {
            $text = str_replace('\[', '$$', $text);
            $text = str_replace('\]', '$$', $text);
            $text = str_replace('\(', '$', $text);
            $text = str_replace('\)', '$', $text);
        }
        $text = format_text($text, FORMAT_MARKDOWN);
        return $text;
    }

    /**
     * Get for a question the answers
     * @param array $detailarray
     * @param array $questions
     * @param int $cmid
     * @param int $videoid
     * @param array $courseusers
     * @param int $totalcount
     * @param \mod_ivs\output\renderer $output
     *
     * @return \stdClass
     */
    public function get_question_answers_data($detailarray, $questions, $cmid, $videoid, $courseusers, $totalcount, $output) {
        if (empty($detailarray)) {
            return null;
        }
        $data = new \stdClass;

        $controller = $this;

        $data->id = $detailarray[0]['question']['nid'];
        $data->label = $controller->get_match_question_title($detailarray[0]['question']);
        $data->label = $this->ivs_prepare_latex_for_rendering($data->label, true);
        $data->question = $detailarray[0]['question']['question_body'];
        if (strlen($detailarray[0]['question']['title']) > 0) {
            $data->question = $detailarray[0]['question']['title'] . ': ' . $data->question;
        } else {
            $data->question = $data->question;
        }
        $data->question = $this->ivs_prepare_latex_for_rendering($data->question, true);
        $data->answers = [];

        // Pager.
        $page = optional_param('page', 0, PARAM_INT); // Which page to show.

        if (!empty($output)) {
            $perpage = required_param('perpage', PARAM_INT); // How many per page.
        } else {
            $perpage = $totalcount;
        }

        $offset = $page * $perpage;

        // Render Replies.
        $answerusers = [];
        foreach ($courseusers as $key => $courseuser) {
            $answerusers[] = $courseuser;
        }

        $data->text_question = false;
        $data->single_choice_question = false;
        $data->click_question = false;
        $data->timing_question = false;
        $timing_types = false;

        for ($i = $offset; $i < $offset + $perpage; $i++) {
            if ($i == $totalcount) {
                break;
            }
            $answer = $detailarray;

            // Render mustache depending on question type.
            switch ($detailarray[0]['type']) {
                case 'text_question':
                    $data->question_type = get_string('ivs_match_question_summary_question_type_text', 'ivs');
                    $data->text_question = true;
                    $renderable = new question_text_answer_view($answer, $answerusers[$i]);
                    break;
                case 'click_question':
                    $data->question_type = get_string('ivs_match_question_summary_question_type_click', 'ivs');
                    $data->click_question = true;
                    $renderable = new question_click_answer_view($answer, $answerusers[$i]);
                    break;
                case 'single_choice_question':
                    $data->question_type = get_string('ivs_match_question_summary_question_type_single', 'ivs');
                    $data->single_choice_question = true;
                    $renderable = new question_single_choice_answer_view($answer, $answerusers[$i]);
                    break;
                case 'timing_question':
                    $data->question_type = get_string('ivs_match_question_summary_question_type_timing', 'ivs');
                    $data->timing_question = true;
                    $url = $_SERVER['REQUEST_URI'];
                    if (strpos($url, 'question_answers.php')){
                        $renderable = new timing_question_answer_view($answer, $answerusers[$i]);
                        $timing_types = true;
                    }else{
                        $renderable = new timing_type_answer_view($answer, $answerusers[$i]);
                    }

                    break;
            }

            if (!empty($output)) {
                $data->answers[] = $output->render($renderable);
            } else {
                $data->answers[] = $renderable;
            }
            // Header Labels.
            $data->id_label = get_string('ivs_match_question_header_id_label', 'ivs');
            $data->type_label = get_string('ivs_match_question_header_type_label', 'ivs');
            $data->title_label = get_string('ivs_match_question_header_title_label', 'ivs');
            $data->question_label = get_string('ivs_match_question_header_question_label', 'ivs');

        }
        if (!empty($output)) {
            // Render all Questions in Dropdown.
            if ($timing_types){
                foreach ($questions as $question) {

                    $label = $controller->get_match_question_title($question);

                    $questionurl = new moodle_url('/mod/ivs/question_answers.php?id=' . $cmid . '&vid=' . $videoid . '&qid=' .
                            $question['nid'] . '&perpage=10');
                    $selected = required_param('qid', PARAM_INT) == $question['nid'] ? 'selected' : '';
                    $data->dropdown_options[] = '<option value="' . $questionurl . '" ' . $selected . '>' . $label . '</option>';
                }
            }else{
                $timingtypes = $controller->match_timing_type_get_db($videoid);
                foreach ($timingtypes as $timingtype) {

                    $label = $controller->get_match_question_timing_title($timingtype);

                    $questionurl = new moodle_url('/mod/ivs/question_type_answers.php?id=' . $cmid . '&vid=' . $videoid . '&qid=' .
                        $timingtype->id . '&perpage=10');
                    $qid = required_param('qid', PARAM_ALPHANUMEXT);
                    $selected = $qid == $timingtype->id ? 'selected' : '';
                    $data->dropdown_options[] = '<option value="' . $questionurl . '" ' . $selected . '>' . $label . '</option>';
                }
            }

            // Render Pager Options in Dropdown.
            $pagerurl = new moodle_url('/mod/ivs/question_answers.php?id=' . $cmid . '&vid=' . $videoid . '&qid=' . $data->id);

            if (required_param('perpage', PARAM_INT) == 10) {
                $data->pager_options[] = '<option selected value="' . $pagerurl . '&perpage=10">10</option>';
                $data->pager_options[] = '<option value="' . $pagerurl . '&perpage=100">100</option>';
            } else {
                $data->pager_options[] = '<option value="' . $pagerurl . '&perpage=10">10</option>';
                $data->pager_options[] = '<option selected value="' . $pagerurl . '&perpage=100">100</option>';
            }
        }

        // Translations.
        $data->name = get_string("ivs_match_question_answer_menu_label_name", 'ivs');
        $data->user_id = get_string("ivs_match_question_answer_menu_label_user_id", 'ivs');
        $data->first_text_answer = get_string("ivs_match_question_answer_menu_label_first_text_answer", 'ivs');
        $data->last_text_answer = get_string("ivs_match_question_answer_menu_label_last_text_answer", 'ivs');
        $data->elements = get_string("ivs_match_question_answer_menu_label_elements_per_page", 'ivs');
        $data->first_click_answer = get_string("ivs_match_question_answer_menu_label_first_click_answer", 'ivs');
        $data->last_click_answer = get_string("ivs_match_question_answer_menu_label_last_click_answer", 'ivs');
        $data->click_retries = get_string("ivs_match_question_answer_menu_label_click_retries", 'ivs');
        $data->first_single_choice_answer = get_string("ivs_match_question_answer_menu_label_first_single_choice_answer", 'ivs');
        $data->single_choice_retries = get_string("ivs_match_question_answer_menu_label_single_choice_retries", 'ivs');
        $data->last_single_choice_answer = get_string("ivs_match_question_answer_menu_label_last_single_choice_answer", 'ivs');
        $data->single_choice_correct = get_string("ivs_match_question_answer_menu_label_single_choice_correct", 'ivs');
        $data->single_choice_selected_answer =
                get_string("ivs_match_question_answer_menu_label_last_single_choice_selected_answer", 'ivs');
        $data->first_timing_answer = get_string("ivs_match_question_answer_menu_label_first_timing_answer", 'ivs');
        $data->last_timing_answer = get_string("ivs_match_question_answer_menu_label_last_timing_answer", 'ivs');
        return $data;
    }

    /**
     * Get all answers for a single choice question
     * @param array $answer
     * @param array $courseuser
     *
     * @return \stdClass
     */
    public function get_question_answers_data_single_choice_question($answer, $courseuser) {
        $data = new \stdClass;

        $user = IvsHelper::get_user($courseuser->id);
        $controller = $this;

        $singlechoicedata = $controller->getsinglechoicereportingdata($answer, $courseuser);

        $data->fullname = $user['fullname'];
        $data->id = $courseuser->id;
        $data->correct = $singlechoicedata['correct'];
        $data->selected_answer = $singlechoicedata['selected_answer'];
        $data->retries = $singlechoicedata['retries'];
        $data->last = $singlechoicedata['last'];
        $data->last_selected_answer = $singlechoicedata['last_selected_answer'];

        return $data;
    }

    /**
     * Get all answers for a click questions
     * @param array $answer
     * @param \stdClass $courseuser
     *
     * @return \stdClass
     */
    public function get_question_answers_data_click_question($answer, $courseuser) {
        $data = new \stdClass;

        $user = IvsHelper::get_user($courseuser->id);

        $data->fullname = $user['fullname'];
        $data->id = $courseuser->id;

        $controller = $this;

        foreach ($answer as $key => $value) {

            $data->first = '-';
            $data->last = '-';
            $data->retries = '-';
            if (!empty($value['answers'][1]) && $value['answers'][1]['user_id'] === $courseuser->id) {

                $userid = $courseuser->id;
                $answers = $controller->match_question_answers_get_by_question_and_user_db($value['question']['nid'], $userid);
                $numanswers = count($answers);

                if ($numanswers > 0) {
                    $data->retries = $numanswers - 1;

                    $lastanswer = $value['answers'][1];
                    if (!empty($value['answers'][0])) {
                        $firstanswer = $value['answers'][0];
                    } else {
                        $firstanswer = $value['answers'][1];
                    }

                    $data->first = !empty($firstanswer['is_correct']) ? 1 : 0;
                    $data->last = !empty($lastanswer['is_correct']) ? 1 : 0;

                }

                break;

            }
        }

        return $data;
    }

    /**
     * Get answer data for a text question
     * @param array $answer
     * @param \stdClass $courseuser
     *
     * @return \stdClass
     */
    public function get_question_answers_data_text_question($answer, $courseuser) {
        $data = new \stdClass;

        $user = IvsHelper::get_user($courseuser->id);

        $data->fullname = $user['fullname'];
        $data->id = $courseuser->id;

        foreach ($answer as $key => $value) {
            if ($value['answers'][1]['user_id'] === $courseuser->id) {
                if ($value['answers'][0] == null && $value['answers'][1]['question_data']) {
                    $data->last = $value['answers'][1] !== null ? implode(' ', $value['answers'][1]['question_data']) : '';
                    $data->first = $data->last;
                } else {
                    $data->first = $value['answers'][0] !== null ? implode(' ', $value['answers'][0]['question_data']) : '';
                    $data->last = $value['answers'][1] !== null ? implode(' ', $value['answers'][1]['question_data']) : '';
                }
                break;

            } else {
                $data->first = "-";
                $data->last = "-";
            }
        }

        $data->first = $this->ivs_prepare_latex_for_rendering($data->first);
        $data->last = $this->ivs_prepare_latex_for_rendering($data->last);
        return $data;
    }

    /**
     * Get all answers for a timing questions
     * @param array $answer
     * @param \stdClass $courseuser
     *
     * @return \stdClass
     */
    public function get_question_answers_data_timing_type($answer, $courseuser) {
        $data = new \stdClass;

        $user = IvsHelper::get_user($courseuser->id);
        $controller = $this;

        $data->fullname = $user['fullname'];
        $data->id = $courseuser->id;

        foreach ($answer as $key => $value) {

            $questions = $controller->match_questions_get_by_video_db($value['videoid']);
            $timingquestioncount = 0;
            foreach($questions as $question){
                if ($value['question']['nid'] == $question['type_data']['timing_type_id']){
                    $timingquestioncount++;
                }
            }


            $data->first = '0/' . $timingquestioncount;
            $data->last = '0/' . $timingquestioncount;
            $data->retries = '-';

            $takes = $controller->match_takes_get_by_user_and_video_db($user['user']->id, $value['videoid'], $value['videoid']);
            $numtakes = count($takes);



            if ($numtakes > 0) {
                $data->retries = $numtakes - 1;
                if ($value['userid'] === $courseuser->id) {
                    $data->first = $value['answers'][0] . '/' . $timingquestioncount;
                    $data->last = $value['answers'][1] . '/' . $timingquestioncount;
                    break;
                }
            }
        }
        return $data;
    }

    /**
     * Get all answers for a timing questions
     * @param array $answer
     * @param \stdClass $courseuser
     *
     * @return \stdClass
     */
    public function get_question_answers_data_timing_question($answer, $courseuser) {
        $data = new \stdClass;

        $user = IvsHelper::get_user($courseuser->id);

        $data->fullname = $user['fullname'];
        $data->id = $courseuser->id;

        $controller = $this;

        foreach ($answer as $key => $value) {

            $data->first = '-';
            $data->last = '-';
            $data->retries = '-';
            if (!empty($value['answers'][1]) && $value['answers'][1]['user_id'] === $courseuser->id) {

                $userid = $courseuser->id;
                $answers = $controller->match_question_answers_get_by_question_and_user_db($value['question']['nid'], $userid);
                $numanswers = count($answers);

                if ($numanswers > 0) {
                    $data->retries = $numanswers - 1;

                    $lastanswer = $value['answers'][1];
                    if (!empty($value['answers'][0])) {
                        $firstanswer = $value['answers'][0];
                    } else {
                        $firstanswer = $value['answers'][1];
                    }

                    $data->first = !empty($firstanswer['is_correct']) ? 1 : 0;
                    $data->last = !empty($lastanswer['is_correct']) ? 1 : 0;

                }

                break;

            }
        }

        return $data;
    }

    private function get_formative_assessment_config($userid, $ivs) {

        $videoid = $ivs->id;

        if ($this->has_edit_access($videoid)) {

            $assconf = new AssessmentConfig();
            $assconf->context_id = null;
            $assconf->context_label = $this->get_ivs_videotest_editing_label($ivs);
            $assconf->matchConfig = $this->match_video_get_config_db($videoid);
            $assconf->takes_left = -1;
            $assconf->takes = [];
            $assconf->status = AssessmentConfig::TAKES_LEFT_NEW;
            $assconf->status_description = get_string("ivs_match_context_label_help", 'ivs');
            $assessmentconfig[] = $assconf;

        }

        $assconf = new AssessmentConfig();
        $assconf->context_id = $videoid;
        $assconf->context_label = $this->get_ivs_videotest_context_label($ivs);
        $assconf->matchConfig = $this->match_video_get_config_db($videoid);
        $assconf->takes_left = 1;
        $assconf->takes = $this->match_takes_get_by_user_and_video_db($userid, $videoid, $videoid);
        $assconf->status = AssessmentConfig::TAKES_LEFT_NEW;
        $assconf->status_description = get_string("ivs_match_config_assessment_mode_formative_help", 'ivs');

        $assessmentconfig[] = $assconf;

        return $assessmentconfig;

        }

    private function get_videotest_assessment_config_by_user($userid, $ivs) {

        $contextid = $ivs->id;
        $videoid = $ivs->id;
        $settingscontroller = new SettingsService();
        $activitysettings = $settingscontroller->get_settings_for_activity($videoid, $ivs->course);

        $gradebookservice = new GradebookService();
        $grade_methods = $gradebookservice->ivs_get_grade_method_options();

        if ($this->has_edit_access($videoid)) {

            $assconf = new AssessmentConfig();
            $assconf->context_id = null;
            $assconf->context_label = $this->get_ivs_videotest_editing_label($ivs);
            $assconf->matchConfig = $this->match_video_get_config_db($videoid);
            $assconf->takes_left = $this->get_remaining_attempts($userid, $videoid, $contextid);
            $assconf->takes = [];
            $assconf->status = AssessmentConfig::TAKES_LEFT_NEW;
            $assconf->status_description = get_string("ivs_match_context_label_help", 'ivs');
            $assessmentconfig[] = $assconf;

        }

        $assconf = new AssessmentConfig();
        $assconf->context_id = $videoid;
        $assconf->context_label = $this->get_ivs_videotest_context_label($ivs);
        $assconf->matchConfig = $this->match_video_get_config_db($videoid);
        $assconf->takes_left = $activitysettings['exam_mode_enabled']->value ? $this->get_remaining_attempts($userid, $videoid, $contextid) : 1;
        $assconf->takes = $this->match_takes_get_by_user_and_video_db($userid, $videoid, $contextid);
        $assconf->grade_method = $grade_methods[$activitysettings[SettingsDefinition::SETTING_PLAYER_VIDEOTEST_GRADE_METHOD]->value];
        $assconf->exam_enabled = (int)$activitysettings[SettingsDefinition::SETTING_PLAYER_EXAM_ENABLED]->value;

        $num_takes = count($assconf->takes);
        $already_passed = FALSE;

        if ($assconf->matchConfig->is_timing_mode()){
            $assconf->matchConfig->rate .= $this->get_success_rate_points_label($videoid, $assconf);
        }

        if ($num_takes == 0) {
            $assconf->status = AssessmentConfig::TAKES_LEFT_NEW;
            $assconf->status_description = get_string("ivs_match_config_status_not_started_label", 'ivs');
        }
        else {

            $take_in_progress = NULL;

            /** @var MatchTake $take */
            foreach ($assconf->takes as $take) {

                if(!$take->is_completed()) {
                    $take_in_progress = $take;
                }
            }

            $score = $gradebookservice->ivs_gradebook_get_score_by_takes($assconf->takes, $ivs);

            if ($score >= $assconf->matchConfig->rate){
                $already_passed = TRUE;
            }

            if ($assconf->matchConfig->is_quiz_mode()){
                $this->get_quiz_status($assconf, $already_passed, $score, $take_in_progress);
            }

            if ($assconf->matchConfig->is_timing_mode()){
                $this->get_timing_status($assconf, $already_passed, $ivs);
            }
        }

        $assessmentconfig[] = $assconf;

        return $assessmentconfig;
    }

    private function get_ivs_videotest_editing_label($ivs){

        $settingscontroller = new SettingsService();
        $activitysettings = $settingscontroller->get_settings_for_activity($ivs->id, $ivs->course);

        if($activitysettings['match_question_enabled']->value == AssessmentConfig::ASSESSMENT_TYPE_TIMING){
            return get_string("ivs_match_context_label_timing", 'ivs');
        }

        return get_string("ivs_match_context_label", 'ivs');

    }

    private function get_quiz_status(&$assconf, $already_passed, $score, $take_in_progress) {

        if ($already_passed) {
            $assconf->status_description = get_string("ivs_match_config_status_passed_label", 'ivs') . $score . '%';
            $assconf->status = AssessmentConfig::NO_TAKES_LEFT_COMPLETED_SUCCESS;
            if($assconf->takes_left > 0 || $assconf->matchConfig->attempts == 0) {
                $assconf->status = AssessmentConfig::TAKES_LEFT_COMPLETED_SUCCESS;
            }
        }
        elseif ($assconf->takes_left == 0) {
            $assconf->status = AssessmentConfig::NO_TAKES_LEFT_COMPLETED_FAILED;
            $assconf->status_description = get_string("ivs_match_config_status_failed_label", 'ivs') . $score. '%';
        }else{
            $assconf->status = AssessmentConfig::TAKES_LEFT_PROGRESS;
            if($take_in_progress) {
                $assconf->status_description = get_string("ivs_match_config_status_progress_label", 'ivs');
            }else{
                $assconf->status_description = get_string("ivs_match_config_status_not_passed_label", 'ivs') . $score . '%';
            }
        }
    }

    private function get_timing_status(&$assconf, $already_passed, $ivs) {
        $gradebookservice = new GradebookService();

        if ($already_passed) {
            $assconf->status_description = get_string("ivs_match_config_timing_status_passed_label", 'ivs');
            $assconf->status = AssessmentConfig::NO_TAKES_LEFT_COMPLETED_SUCCESS_NO_SUMMARY;
            if($assconf->takes_left > 0 || $assconf->matchConfig->attempts == 0) {
                $assconf->status = AssessmentConfig::TAKES_LEFT_COMPLETED_SUCCESS;
            }
        }
        elseif ($assconf->takes_left == 0) {
            $assconf->status = AssessmentConfig::NO_TAKES_LEFT_COMPLETED_FAILED;
            $assconf->status_description = get_string("ivs_match_config_timing_status_not_passed_label", 'ivs');
        }else{
            $assconf->status = AssessmentConfig::TAKES_LEFT_PROGRESS;
            $assconf->status_description = get_string("ivs_match_config_timing_status_not_passed_label", 'ivs');
        }

        $assconf->status_description .= $gradebookservice->get_rendered_timing_take_summary($assconf->takes, $ivs);
    }



    private function get_quiz_match_config($ivs) {
        global $DB;
        $gradebookservice = new GradebookService();
        $course = $DB->get_record('course', array('id' => $ivs->course), '*', MUST_EXIST);
        $gradesettings = $gradebookservice->ivs_get_grade_settings($ivs);
        $settingscontroller = new SettingsService();
        $activitysettings = $settingscontroller->get_settings_for_activity($ivs->id, $course->id);

        $mc = new MatchConfig();

        $mc->assessment_type = 'TAKES';
        $mc->allow_repeat_answers = false;
        $mc->player_controls_enabled = (int) $activitysettings['player_controls_enabled']->value;
        $mc->rate = !empty($gradesettings) ? (int)$gradesettings->gradepass : 100;
        $mc->attempts = $activitysettings[SettingsDefinition::SETTING_PLAYER_VIDEOTEST_ATTEMPTS]->value;
        $mc->show_feedback = false;
        $mc->show_solution = false;

        return $mc;
    }

    private function get_formative_match_config($ivs) {

        $mc = new MatchConfig();

        $settingscontroller = new SettingsService();
        $activitysettings = $settingscontroller->get_settings_for_activity($ivs->id, $ivs->course);

        $mc->assessment_type = $activitysettings['match_question_enabled']->value == AssessmentConfig::ASSESSMENT_TYPE_QUIZ ? 'TAKES' : 'TIMING_TAKES';
        $mc->rate = 100;
        $mc->attempts = 0;
        $mc->allow_repeat_answers = true;
        $mc->player_controls_enabled = (int) $activitysettings['player_controls_enabled']->value;
        $mc->show_solution = true;
        $mc->show_feedback = false;

        return $mc;
    }

    private function get_timing_match_config($ivs) {
        global $DB;
        $gradebookservice = new GradebookService();
        $course = $DB->get_record('course', array('id' => $ivs->course), '*', MUST_EXIST);
        $gradesettings = $gradebookservice->ivs_get_grade_settings($ivs);
        $settingscontroller = new SettingsService();
        $activitysettings = $settingscontroller->get_settings_for_activity($ivs->id, $course->id);

        $mc = new MatchConfig();

        $mc->assessment_type = 'TIMING_TAKES';
        $mc->allow_repeat_answers = false;
        $mc->player_controls_enabled = (int) $activitysettings['player_controls_enabled']->value;
        $mc->rate = !empty($gradesettings) ? (int)$gradesettings->gradepass : 100;
        $mc->attempts = $activitysettings[SettingsDefinition::SETTING_PLAYER_VIDEOTEST_ATTEMPTS]->value;
        $mc->show_feedback = $activitysettings[SettingsDefinition::SETTING_PLAYER_SHOW_REALTIME_RESULTS]->value;
        $mc->show_solution = false;

        return $mc;
    }

    private function get_ivs_videotest_context_label($ivs) {
        $settingscontroller = new SettingsService();
        $activitysettings = $settingscontroller->get_settings_for_activity($ivs->id, $ivs->course);

        switch ($activitysettings['match_question_enabled']->value){
            case AssessmentConfig::ASSESSMENT_TYPE_QUIZ:
                return get_string("ivs_match_config_assessment_mode_quiz", 'ivs');
            case AssessmentConfig::ASSESSMENT_TYPE_TIMING:
                return get_string("ivs_match_config_assessment_mode_timing", 'ivs');
            default:
                if (!$activitysettings['exam_mode_enabled']->value){
                    return get_string("ivs_match_config_assessment_mode_formative", 'ivs');
                }
        }
        return get_string("ivs_match_context_label", 'ivs');
    }


    public function match_timing_type_get_db($videoid, $skip_access = FALSE) {
        global $DB;
        $ivs = $DB->get_record('ivs', array('id' => $videoid), '*', MUST_EXIST);

        $timingtypes = [];

        if(!empty($ivs->match_config)) {
            $matchconfig = json_decode($ivs->match_config  ?? '', TRUE);

            foreach ($matchconfig['timing_types'] as $timingtype){
                $timingtypes[] = new MatchTimingType($timingtype);
            }

        }
        return $timingtypes;
    }

    public function match_timing_type_insert_db($videoid, $data, $user_id = NULL, $skip_access = FALSE) {
        global $DB;

        $ivs = $DB->get_record('ivs', array('id' => $videoid), '*', MUST_EXIST);

        return $this->saveTimingType($data, $ivs, $skip_access);

    }

    public function match_timing_type_update_db($videoid, $data, $user_id = NULL, $skip_access = FALSE) {
        global $DB;

        $ivs = $DB->get_record('ivs', array('id' => $videoid), '*', MUST_EXIST);

        return $this->saveTimingType($data, $ivs, $skip_access);
    }

    public function match_timing_type_delete_db($videoid, $timing_type_id, $skip_access = FALSE) {
        global $DB;

        $ivs = $DB->get_record('ivs', array('id' => $videoid), '*', MUST_EXIST);

        if (!$this->has_edit_access($videoid) && !$skip_access){
            throw new MatchQuestionAccessDeniedException(null, "Access denied");
        }

        $matchtimingtypes = $this->match_timing_type_get_db($videoid, TRUE);

        //check if command id exists
        foreach ($matchtimingtypes as $i => $matchtimingtype) {
            if ($matchtimingtype->id === $timing_type_id) {
                unset($matchtimingtypes[$i]);
            }
        }
        $this->update_timing_types($ivs, $matchtimingtypes);
    }

    protected function saveTimingType($post_data, $ivs, $skip_access = FALSE) {
        global $DB;

        $videoid = $ivs->id;

        if (!$this->has_edit_access($videoid) && !$skip_access){
            throw new MatchQuestionAccessDeniedException(null, "Access denied");
        }


        $postData = json_decode(json_encode($post_data));

        //parse data
        if (property_exists($postData, 'id')) {
            $id = $postData->id;
        }

        if (empty($id)) {
            //generate uuid
            $id = uniqid();
        }

        if (property_exists($postData, 'type')) {
            $timing_type_array['type'] = $postData->type;
        }
        if (property_exists($postData, 'duration')) {
            $timing_type_array['duration'] = $postData->duration;
        }
        if (property_exists($postData, 'title')) {
            $timing_type_array['title'] = $postData->title;
        }
        if (property_exists($postData, 'btn')) {
            $postDataBtn = $postData->btn;
        }

        $position = explode(',', $postDataBtn->position);
        $new_pos = [];
        foreach ($position as $pos){
            $new_pos[] = $pos;
        }

        $timing_type_array = [
                'title' => $postData->title,
                'duration' => $postData->duration,
                'weight' => $postData->weight,
                'btn' => [
                        'label' =>  $postDataBtn->label,
                        'position' =>  implode(',', $new_pos),
                        'shortcut' =>  $postDataBtn->shortcut,
                        'score' =>  $postDataBtn->score,
                        'style' =>  $postDataBtn->style,
                        'description' => $postDataBtn->description,
                        'cooldown' => $postDataBtn->cooldown
                ]
        ];
        $timing_type_array['id'] = $id ?? null;


        $matchtimingtype = new MatchTimingType($timing_type_array);

        $timing_types = $this->match_timing_type_get_db($videoid, $skip_access);


        $is_new = TRUE;
        foreach ($timing_types as &$timing_type) {
            if ($timing_type->id == $matchtimingtype->id) {
                $timing_type = $matchtimingtype;
                $is_new = FALSE;
            }
        }

        if ($is_new) {

            $timing_types[] = $matchtimingtype;
        }

        $this->update_timing_types($ivs, $timing_types);

        return $matchtimingtype;

    }

    private function update_timing_types($ivs, $matchtimingtypes = []) {

        global $DB;
        if(empty($ivs->match_config)) {
            $match_settings = [];
        }else {
            $match_settings = json_decode($ivs->match_config ?? '', TRUE);
        }

        $matchtimingtypesjson = array_map( function ($timingtype){
            return $timingtype->to_player_json();
        }, $matchtimingtypes);

        $match_settings['timing_types'] = $matchtimingtypesjson;
        $ivs->match_config = json_encode($match_settings);
        $DB->update_record('ivs', $ivs);

    }

    private function get_success_rate_points_label($videoid, $assconf) {
        $successratelabel = '';
        $pointstotal = 0;

        global $DB;
        $matchcontroller = new MoodleMatchController();
        $matchquestions = $DB->get_records('ivs_matchquestion', array('video_id' => $videoid));
        $timingtypes = $matchcontroller->match_timing_type_get_db($videoid);
        foreach ($matchquestions as $matchquestion) {
            $type_data = unserialize($matchquestion->type_data);
            if(!empty($type_data['timing_type_id'])) {
                $timingtype = MatchTimingTakeResult::find_object_by_id($type_data['timing_type_id'], $timingtypes);
                $pointstotal += $timingtype->score;
            }
        }


        $pointstosuccess = $pointstotal / 100 * $assconf->matchConfig->rate;
        $successratelabel = '% (' . $pointstosuccess . ' ' . get_string('ivs_grademethod_timing_take_summary_points', 'ivs') . ')';
        return $successratelabel;
    }

    private function match_questions_reformat_timing_questions($videoid, $questions) {
        $formatedquestions = [];

        foreach($questions as $question){
            $formatedquestions[$question['nid']] = $question;
        }
        return $formatedquestions;
    }

    private function match_format_question_time($milliseconds) {
            return sprintf('%02d:%02d', floor(($milliseconds % 3600000) / 60000), floor(($milliseconds % 60000) / 1000));
    }

    private function match_question_answers_get_by_timing_type_and_user_db( $timingtypeid, $userid, $videoid, $skipaccess) {


        $matchquestions = $this->match_questions_get_by_video_db($videoid);

        foreach ($matchquestions as $questionId => $question){
            if ($question['type'] !== 'timing_question'){
                unset($matchquestions[$questionId]);
                continue;
            }

            if ($question['type_data']['timing_type_id'] !== $timingtypeid){
                unset($matchquestions[$questionId]);
            }
        }


        global $DB;
        $timingtypeanswers = [];
        foreach($matchquestions as $id => $matchquestion){


            $record = $DB->get_records('ivs_matchanswer', array(
                'question_id' => $id,
                'user_id' => $userid
            ));

            $answers = [];
            foreach ($record as $answer) {
                $answers[$answer->id] = $this->record_to_player_answer((array) $answer);
            }
            $timingtypeanswers[$id] = $answers;
        }

        return $timingtypeanswers;
    }

    public function match_timing_get_current_timing_type($instance, $qid)
    {
        //Get current timing type
        $timingtypes = $this->match_timing_type_get_db($instance);
        $currenttimingtype = null;
        foreach ($timingtypes as $timingtype){
            if ($timingtype->id === $qid){
                $currenttimingtype = $timingtype;
                break;
            }
        }
        return $currenttimingtype;
    }
}
