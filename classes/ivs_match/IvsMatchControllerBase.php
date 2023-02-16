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
 * Controller class for match questions
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\ivs_match;

use mod_ivs\ivs_match\MatchResponse;
use mod_ivs\ivs_match\MatchTake;
use mod_ivs\ivs_match\MatchConfig;
use mod_ivs\ivs_match\exception\MatchAlreadyAnsweredException;
use mod_ivs\ivs_match\exception\MatchNoConfigException;
use mod_ivs\ivs_match\exception\MatchQuestionException;
use mod_ivs\ivs_match\exception\MatchQuestionNotFoundException;
use mod_ivs\ivs_match\exception\MatchTakeException;
use mod_ivs\ivs_match\exception\MatchTakeNoRemainingAttemptsException;
use mod_ivs\settings\SettingsService;

/**
 * Class IvsMatchControllerBase
 */
class IvsMatchControllerBase {

    /**
     * @var IIvsMatch
     */
    protected $ivsmatchinterface;

    /**
     * IvsMatchControllerBase constructor.
     *
     * @param \mod_ivs\ivs_match\IIvsMatch $ivsmatchinterface
     */
    public function __construct(IIvsMatch $ivsmatchinterface) {
        $this->ivsmatchinterface = $ivsmatchinterface;
    }

    /**
     * Handle general requests
     * @param string $endpoint
     * @param array $patharguments
     * @param string $method
     * @param array $postdata
     *
     * @return \mod_ivs\ivs_match\MatchResponse
     */
    public function handle_request($endpoint, $patharguments, $method, $postdata) {

        $videoId = $patharguments[0];
        if ($endpoint == "match_answers") {
            return $this->handle_answers_requests($patharguments, $method, $postdata);
        }

        if ($endpoint == "match_context") {
            try {
                return $this->handle_context_requests($patharguments, $method, $postdata);
            } catch (\Exception $e) {
                return new MatchResponse(['message' => $e->getMessage()], 401);
            }
        }

        if ($endpoint == "timing-types") {
            try {
                return $this->handleTimingTypeRequests($patharguments, $method, $postdata);
            } catch (\Exception $e) {
                return new MatchResponse(['message' => $e->getMessage()], 401);
            }
        }

        // Handle get.
        switch (strtoupper($method)) {
            case "GET":
                $takeid = optional_param('take_id', '', PARAM_ALPHANUMEXT);
                // GET ANSWERS.
                try {
                    $questions = $this->load_questions_for_user_by_video($videoId, null, $takeid);
                } catch (exception\MatchQuestionAccessDeniedException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 403);
                }
                return new MatchResponse(array_values($questions));
            case "POST":
                try {
                    $response = $this->ivsmatchinterface->match_question_insert_db($videoId, $postdata);
                    return new MatchResponse($response);
                } catch (exception\MatchQuestionAccessDeniedException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 403);
                }
                break;
            case "PUT":

                try {
                    $response = $this->ivsmatchinterface->match_question_update_db($videoId, $postdata);
                } catch (exception\MatchQuestionAccessDeniedException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 403);
                } catch (exception\MatchQuestionNotFoundException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 404);
                }
                return new MatchResponse($response);

            case "DELETE":
                $questionnid = $patharguments[1];
                try {
                    $response = $this->ivsmatchinterface->match_question_delete_db($questionnid);
                } catch (exception\MatchQuestionAccessDeniedException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 403);
                } catch (exception\MatchQuestionNotFoundException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 404);
                }
                return new MatchResponse($response);
        }

        return new MatchResponse();

    }

    /**
     * Handle answers requests
     * @param array $patharguments
     * @param string $method
     * @param array $postdata
     *
     * @return \mod_ivs\ivs_match\MatchResponse
     */
    protected function handle_answers_requests($patharguments, $method, $postdata) {

        $videonid = $patharguments[0];

        switch (strtoupper($method)) {
            case "GET":
                return new MatchResponse();
            case "POST":
                try {
                    $response = $this->process_match_answer($videonid, $postdata);
                    return new MatchResponse($response);
                } catch (exception\MatchQuestionAccessDeniedException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 403);
                } catch (\Exception $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 404);
                }
            case "DELETE":
            case "PUT":
                break;
        }
    }

    /**
     * Handle the requests
     * @param array $patharguments
     * @param string $method
     * @param array $postdata
     *
     * @return \mod_ivs\ivs_match\MatchResponse
     * @throws \mod_ivs\ivs_match\exception\MatchNoConfigException
     * @throws \mod_ivs\ivs_match\exception\MatchTakeNoRemainingAttemptsException
     */
    protected function handle_context_requests($patharguments, $method, $postdata) {

        $videonid = $patharguments[0];
        $action = $patharguments[1];
        $contextid = $patharguments[2];
        $uid = $this->ivsmatchinterface->get_current_user_id();

        switch (strtoupper($method)) {
            case "GET":
                $assessmentconfig = $this->ivsmatchinterface->assessment_config_get_by_user_and_video($uid, $videonid);
                return new MatchResponse($assessmentconfig);
            case "POST":
                if($action == 'start') {
                    $take = $this->get_match_take_for_user($uid, $videonid, $contextid);
                    return new \mod_ivs\ivs_match\MatchResponse($take);
                }elseif ($action == 'finish') {

                    $this->finish_match_take_for_user($uid, $videonid, $contextid, $postdata);

                    $assessmentconfig = $this->ivsmatchinterface->assessment_config_get_by_user_and_video($uid, $videonid);
                    return new MatchResponse($assessmentconfig);

                }
                break;
            case "PUT":
                break;
            case "DELETE":
                break;
        }
    }

    protected function handleTimingTypeRequests($patharguments, $method, $postdata) {

        global $DB;
        $videoid = $patharguments[0];

        switch (strtoupper($method)) {
            case "GET":
                $ivs = $DB->get_record('ivs', array('id' => $videoid), '*', MUST_EXIST);
                $assessmentconfig = $this->ivsmatchinterface->match_timing_type_get_db($ivs);

                return new MatchResponse($assessmentconfig);
            case "POST":
                try {
                    $response = $this->ivsmatchinterface->match_timing_type_insert_db($videoid, $postdata);
                    return new MatchResponse($response);
                } catch (exception\MatchQuestionAccessDeniedException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 403);
                }
                break;
            case "PUT":

                try {
                    $response = $this->ivsmatchinterface->match_timing_type_update_db($videoid, $postdata);

                    //update edited timing questions
                    $timingquestions = $this->ivsmatchinterface->match_questions_get_by_video_db($videoid);

                    foreach($timingquestions as $timingquestion){
                        if($timingquestion['type'] == 'timing_question'){
                            if($postdata['id'] == $timingquestion['type_data']['timing_type_id']){
                                $timingquestion['duration'] = $postdata['duration'] * 1000;
                                $this->ivsmatchinterface->match_question_update_db($videoid, $timingquestion);
                            }
                        }
                    }
                } catch (exception\MatchQuestionAccessDeniedException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 403);
                } catch (exception\MatchQuestionNotFoundException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 404);
                } catch (\Exception $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 401);
                }
                return new MatchResponse($response);

            case "DELETE":
                $timingtypeid = $patharguments[1];
                try {
                    $response = $this->ivsmatchinterface->match_timing_type_delete_db($videoid, $timingtypeid);

                    //delete all related timing match questions
                    $timingquestions = $this->ivsmatchinterface->match_questions_get_by_video_db($videoid);

                    foreach($timingquestions as $timingquestion){
                        if($timingquestion['type'] == 'timing_question'){
                            if($timingtypeid == $timingquestion['type_data']['timing_type_id']){
                                $this->ivsmatchinterface->match_question_delete_db($timingquestion['nid']);
                            }
                        }
                    }
                } catch (exception\MatchQuestionAccessDeniedException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 403);
                } catch (exception\MatchQuestionNotFoundException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 404);
                }
                return new MatchResponse($response);
        }
    }

    /**
     * Process an answer comming from ep5
     *
     * @param int $videoid
     * @param array $postdata
     * @param null $userid
     * @param bool $skipaccess
     * @return mixed
     * @throws MatchTakeException
     * @throws MatchNoConfigException
     * @throws MatchQuestionNotFoundException
     * @throws MatchAlreadyAnsweredException
     */
    public function process_match_answer($videoid, $postdata, $userid = null, $skipaccess = false) {
        global $COURSE;

        $this->evaluate_answer($postdata);
        $solution = $postdata['solution_data'];

        if (!empty($postdata['take_id'])) {

            $takeid = $postdata['take_id'];
            $questionid = $postdata['question_id'];


            $matchtake = $this->ivsmatchinterface->match_take_get_db($takeid);

            $matchconfig = $this->ivsmatchinterface->match_video_get_config_db($matchtake->contextid, $matchtake->videoid);

            // Return match result without saving it to the db (Demo).
            $coursemodule = get_coursemodule_from_instance('ivs', $videoid , 0, false, MUST_EXIST);
            $context = \context_module::instance($coursemodule->id);
            $savematch = has_capability('mod/ivs:create_match_answers', $context);

            if (!$savematch && !$skipaccess) {
                $response['solution_data'] = $solution;
                return $response;
            }

            $response = $this->ivsmatchinterface->match_question_answer_insert_db($videoid, $postdata, $userid, $skipaccess);
            $this->evaluate_take($postdata['take_id']);
            $response['solution_data'] = $solution;
        } else {
            $response = $postdata;
        }

        $response['solution_data'] = $solution;

        return $response;
    }

    /**
     * Evaluate the take
     * @param int $takeid
     *
     * @return mixed|\mod_ivs\ivs_match\MatchTake
     * @throws \mod_ivs\ivs_match\exception\MatchNoConfigException
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     */
    public function evaluate_take($takeid) {

        $matchtake = $this->ivsmatchinterface->match_take_get_db($takeid);

        $matchconf = $this->ivsmatchinterface->match_video_get_config_db($matchtake->contextid, $matchtake->videoid);

        $takeanswers = $this->ivsmatchinterface->match_question_answers_get_by_take($takeid);

        $questions = $this->ivsmatchinterface->match_questions_get_by_video_db($matchtake->videoid, 'timecode', true);

        $numanswered = 0;
        $numcorrect = 0;
        $numquestions = count($questions);

        foreach ($questions as $questionid => $question) {

            if (!empty($takeanswers[$questionid])) {
                $numanswered++;
                if ($takeanswers[$questionid]['is_correct']) {
                    $numcorrect++;
                }
            }
        }

        if ($numanswered === $numquestions) {
            // Score.
            $matchtake->score = $numcorrect * 100 / $numanswered;
            $matchtake->completed = time();
            $matchtake->status = $matchconf->haspassed($matchtake->score) ? MatchTake::STATUS_PASSED : MatchTake::STATUS_FAILED;

        } else {
            $matchtake->status = MatchTake::STATUS_PROGRESS;
        }

        $this->ivsmatchinterface->match_take_update_db($matchtake);

        // Check all answers. count correct ones etc.

        // Save take and return it.

        return $matchtake;

    }

    /**
     * Calculate succeeded match timing interaction and update finished match take
     * @param $user_id
     * @param $video_id
     * @param $context_id
     * @param $post_data
     * @throws \edubreak_match\exception\MatchNoConfigException
     */
    public function finish_match_take_for_user($user_id, $video_id, $context_id, $post_data) {

        $take_id = $post_data['take_id'];

        $match_take = $this->ivsmatchinterface->match_take_get_db($take_id);
        $match_conf = $this->ivsmatchinterface->match_video_get_config_db($context_id, $video_id);

        $match_take->score = 100 / ($post_data['correct'] + $post_data['incorrect']) * $post_data['correct'];
        $match_take->completed = time();
        $match_take->status = $match_conf->hasPassed($match_take->score ) ?  MatchTake::STATUS_PASSED : MatchTake::STATUS_FAILED;

        $this->ivsmatchinterface->match_take_update_db($match_take);

    }

    /**
     * Add the question to the player
     * @param \stdClass $datadb
     *
     * @return mixed
     */
    protected function to_player_question($datadb) {

        return $datadb;
    }

    /**
     * Validate the question
     * @param int $videonid
     * @param array $postdata
     */
    private function validate_input($videonid, $postdata) {
    }

    /**
     * Evaluate the answer
     * @param array $answerdata
     * @param bool $addsolution
     * @param bool $skipaccesscheck
     *
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionNotFoundException
     */
    public function evaluate_answer(&$answerdata, $addsolution = true, $skipaccesscheck = true) {

        $questionid = $answerdata['question_id'];

        if (empty($questionid)) {
            throw new MatchQuestionNotFoundException();
        }

        // Load from db (does the access check).
        $question = $this->ivsmatchinterface->match_question_get_db($questionid, $skipaccesscheck);

        if (empty($question)) {
            throw new MatchQuestionNotFoundException();
        }

        switch ($question['type']) {
            case "click_question":
                $answerdata['is_evaluated'] = true;
                break;
            case "single_choice_question":
              $checked_ids = explode(",",$answerdata['question_data']['checked_id']);
              $correct_answer = NULL;
              $my_answer_is_correct = FALSE;

              $correct_ids = [];
              $wrongs_ids = [];


              foreach ($question['type_data']['options'] as $o) {
                if ($o["is_correct"]) {
                  if(in_array($o['id'], $checked_ids)) {
                    $correct_ids[] = $o['id'];
                  }else{
                    $wrongs_ids[] = $o['id'];
                  }
                }else{
                  if(in_array($o['id'], $checked_ids)) {
                    $wrongs_ids[] = $o['id'];
                  }else{
                    $correct_ids[] = $o['id'];
                  }
                }
              }

              //add items to object
              $answerdata['is_correct'] = count($wrongs_ids) == 0;

              if($addsolution) {
                $answerdata['solution_data'] = [
                  'correct_ids' => $correct_ids,
                  'wrong_ids' => $wrongs_ids,
                ];
              }
              $answerdata['is_evaluated'] = TRUE;
              break;
            case "text_question":
                $answerdata['is_correct'] = true;
                $answerdata['is_evaluated'] = false;
                break;

        }
    }

    /**
     * Get the solution when answering the question
     * @param array $question
     *
     * @return array|null[]
     */
    public function get_solution_for_answer($question) {

        $solution = [];
        switch ($question['type']) {
            case "single_choice_question":

                $correctanswer = null;
                foreach ($question['type_data']['options'] as $o) {
                    if ($o["is_correct"]) {
                        $correctanswer = $o['id'];
                    }
                }
                $solution = [
                        'correct_id' => $correctanswer
                ];
                break;
        }
        return $solution;
    }

    /**
     * Add the answer to a question from a user
     * @param array $questions
     * @param int $videoid
     * @param int $userid
     * @param int $takeid
     */
    public function add_answers_to_questions(&$questions, $videoid, $userid, $takeid) {

        // 16.08.2018 - 10:40 - SH - This is a temp solution to getthe answers of the current take.
        // We should actually pass the take id in this fucntion.

        $answers = $this->ivsmatchinterface->match_question_answers_get_by_take($takeid);
        foreach ($answers as $questionid => $answer) {
            if (array_key_exists($questionid, $questions)) {
                $answer['solution_data'] = $this->get_solution_for_answer($questions[$questionid]);
                $questions[$questionid]['answer'] = $answer;
            }
        }
    }

    /**
     * Load questions for get request
     * @param int $videoid
     * @param null $userid
     * @param null $takeid
     *
     * @return mixed
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     */
    public function load_questions_for_user_by_video($videoid, $userid = null, $takeid = null) {

        $questions = $this->ivsmatchinterface->match_questions_get_by_video_db($videoid);
        $this->add_answers_to_questions($questions, $videoid, $userid, $takeid);

        return $questions;
    }

    /**
     * Get the number of remaing attempts by user
     *
     * @param int $userid
     * @param int $videoid
     * @param int $contextid
     * @return int
     */
    public function get_remaining_attempts($userid, $videoid, $contextid) {

        $conf = $this->ivsmatchinterface->match_video_get_config_db($contextid);

        if ($conf->has_unlimited_attempts()) {
            return -1;
        }

        $usertakes = $this->ivsmatchinterface->match_takes_get_by_user_and_video_db($userid, $videoid, $contextid);

        $numcompleted = 0;

        foreach ($usertakes as $take) {
            if ($take->is_completed()) {
                $numcompleted++;
            }
        }

        if ($numcompleted < $conf->attempts) {
            return $conf->attempts - $numcompleted;
        }
        return 0;

    }

    /**
     * Get the takes for an user
     * @param int $userid
     * @param int $videoid
     * @param int $contextid
     * @return \mod_ivs\ivs_matchMatchTake|mixed
     * @throws \mod_ivs\ivs_match\exception\MatchTakeNoRemainingAttemptsException
     */
    public function get_match_take_for_user($userid, $videoid, $contextid) {

        $notcompletedtakes = $this->ivsmatchinterface->match_takes_get_by_user_and_video_db($userid, $videoid, $contextid,
                [MatchTake::STATUS_NEW, MatchTake::STATUS_PROGRESS]);

        if (!empty($notcompletedtakes)) {
            return end($notcompletedtakes);
        }

        if ($this->get_remaining_attempts($userid, $videoid, $contextid) === 0) {
            throw new MatchTakeNoRemainingAttemptsException();
        }

        $mt = new MatchTake();
        $mt->contextid = $contextid;
        $mt->videoid = $videoid;
        $mt->userid = $userid;
        $mt->status = MatchTake::STATUS_NEW;
        $mt->contextid = $contextid;
        $mt->created = time();
        $mt->changed = time();
        $mt->score = 0;
        $mt->evaluated = 0;

        $this->ivsmatchinterface->match_take_insert_db($mt);

        return $mt;

    }
}
