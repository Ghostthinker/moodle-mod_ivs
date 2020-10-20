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

namespace mod_ivs\ivs_match;

use mod_ivs\ivs_match\exception\MatchAlreadyAnsweredException;
use mod_ivs\ivs_match\exception\MatchNoConfigException;
use mod_ivs\ivs_match\exception\MatchQuestionException;
use mod_ivs\ivs_match\exception\MatchQuestionNotFoundException;
use mod_ivs\ivs_match\exception\MatchTakeException;
use mod_ivs\ivs_match\exception\MatchTakeNoRemainingAttemptsException;
use mod_ivs\settings\SettingsService;

class IvsMatchControllerBase {

    /**
     * @var IIvsMatch
     */
    protected $ivsMatchInterface;

    /**
     * IvsMatchControllerBase constructor.
     */
    public function __construct(IIvsMatch $ivsMatchInterface) {
        $this->ivsMatchInterface = $ivsMatchInterface;
    }

    public function handle_request($endpoint, $patharguments, $method, $postdata) {

        $videonid = $patharguments[0];
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

        // Handle get.
        switch (strtoupper($method)) {
            case "GET":
                $takeid = optional_param('take_id','',PARAM_ALPHANUMEXT);
                // GET ANSWERS.
                try {
                    $questions = $this->load_questions_for_user_by_video($videonid, null, $takeid);
                } catch (exception\MatchQuestionAccessDeniedException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 403);
                }
                return new MatchResponse(array_values($questions));
            case "POST":
                try {
                    $response = $this->ivsMatchInterface->match_question_insert_db($videonid, $postdata);
                    return new MatchResponse($response);
                } catch (exception\MatchQuestionAccessDeniedException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 403);
                }
                break;
            case "PUT":

                try {
                    $response = $this->ivsMatchInterface->match_question_update_db($videonid, $postdata);
                } catch (exception\MatchQuestionAccessDeniedException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 403);
                } catch (exception\MatchQuestionNotFoundException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 404);
                }
                return new MatchResponse($response);

            case "DELETE":
                $questionnid = $patharguments[1];
                try {
                    $response = $this->ivsMatchInterface->match_question_delete_db($questionnid);
                } catch (exception\MatchQuestionAccessDeniedException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 403);
                } catch (exception\MatchQuestionNotFoundException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 404);
                }
                return new MatchResponse($response);
        }

        return new MatchResponse();

    }

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

    protected function handle_context_requests($patharguments, $method, $postdata) {

        $videonid = $patharguments[0];
        $contextid = $patharguments[2];
        $uid = $this->ivsMatchInterface->get_current_user_id();

        switch (strtoupper($method)) {
            case "GET":
                $assessmentconfig = $this->ivsMatchInterface->assessment_config_get_by_user_and_video($uid, $videonid);
                return new MatchResponse($assessmentconfig);
            case "POST":
                $take = $this->get_match_take_for_user($uid, $videonid, $contextid);
                return new MatchResponse($take);
                break;
            case "PUT":
                break;
            case "DELETE":
                break;
        }
    }

    /**
     * Process an answer comming from ep5
     *
     * @param $videoid
     * @param $postdata
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

            $matchtake = $this->ivsMatchInterface->match_take_get_db($takeid);

            $matchconfig = $this->ivsMatchInterface->match_video_get_config_db($matchtake->contextid, $matchtake->videoid);


            // return match result without saving it to the db (Demo)
            $coursemodule = get_coursemodule_from_instance('ivs', $videoid , 0, false, MUST_EXIST);
            $context = \context_module::instance($coursemodule->id);
            $savematch = has_capability('mod/ivs:create_match_answers', $context);

            if (!$savematch && !$skipaccess) {
                $response['solution_data'] = $solution;
                return $response;
            }

            if (!$matchconfig->allow_repeat_answers) {

                $answerexisting =
                        $this->ivsMatchInterface->match_question_answer_get_by_question_and_user_db($questionid, $userid,
                                $skipaccess);

                if (!empty($answerexisting)) {
                    throw new MatchAlreadyAnsweredException();
                }
            }

            $response = $this->ivsMatchInterface->match_question_answer_insert_db($videoid, $postdata, $userid, $skipaccess);
            $this->evaluate_take($postdata['take_id']);
            $response['solution_data'] = $solution;
        } else {
            $response = $postdata;
        }

        $response['solution_data'] = $solution;

        return $response;
    }

    public function evaluate_take($takeid) {

        $matchtake = $this->ivsMatchInterface->match_take_get_db($takeid);

        $matchconf = $this->ivsMatchInterface->match_video_get_config_db($matchtake->contextid, $matchtake->videoid);

        if ($matchconf->assessment_type == AssessmentConfig::ASSESSMENT_TYPE_FORMATIVE) {
            return $this->evaluate_formative_take($matchtake);
        }


        $takeanswers = $this->ivsMatchInterface->match_question_answers_get_by_take($takeid);

        $questions = $this->ivsMatchInterface->match_questions_get_by_video_db($matchtake->videoid, 'timecode', true);

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
            $matchtake->status = $matchconf->has_passed($matchtake->score) ? MatchTake::STATUS_PASSED : MatchTake::STATUS_FAILED;

        } else {
            $matchtake->status = MatchTake::STATUS_PROGRESS;
        }

        $this->ivsMatchInterface->match_take_update_db($matchtake);

        // Check all answers. count correct ones etc.

        // Save take and return it.

        return $matchtake;

    }

    protected function to_player_question($datadb) {

        return $datadb;
    }

    private function validate_input($videonid, $postdata) {
        //TODO add some general validaion
        //This should be done for each question type
    }

    public function evaluate_answer(&$answerdata, $addsolution = true, $skipaccesscheck = true) {

        $questionid = $answerdata['question_id'];

        if (empty($questionid)) {
            throw new MatchQuestionNotFoundException();
        }

        // Load from db (does the access check).
        $question = $this->ivsMatchInterface->match_question_get_db($questionid, $skipaccesscheck);

        if (empty($question)) {
            throw new MatchQuestionNotFoundException();
        }

        switch ($question['type']) {
            case "click_question":
                $answerdata['is_evaluated'] = true;
                break;
            case "single_choice_question":
                $checkedid = $answerdata['question_data']['checked_id'];
                $correctanswer = null;
                $myansweriscorrect = false;
                foreach ($question['type_data']['options'] as $o) {
                    if ($o["is_correct"] && $o['id'] == $checkedid) {
                        $myansweriscorrect = true;
                    }
                    if ($o["is_correct"]) {
                        $correctanswer = $o['id'];
                    }
                }

                // Add items to object.
                $answerdata['is_correct'] = $myansweriscorrect;
                if ($addsolution) {
                    $answerdata['solution_data'] = [
                            'correct_id' => $correctanswer
                    ];
                }
                $answerdata['is_evaluated'] = true;
                break;
            case "text_question":
                $answerdata['is_correct'] = true;
                $answerdata['is_evaluated'] = false;
                break;

        }
    }

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

    function add_answers_to_questions(&$questions, $videoid, $userid, $takeid) {

        // 16.08.2018 - 10:40 - SH - This is a temp solution to getthe answers of the current take - we should actually pass the take id in this fucntion.


        $answers = $this->ivsMatchInterface->match_question_answers_get_by_take($takeid);
         foreach ($answers as $questionid => $answer) {
            if (array_key_exists($questionid, $questions)) {
                $answer['solution_data'] = $this->get_solution_for_answer($questions[$questionid]);
                $questions[$questionid]['answer'] = $answer;
            }
        }
    }

    /**
     * Load questions for get request
     *
     * @param $videoid
     * @param $userid
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     */
    function load_questions_for_user_by_video($videoid, $userid = null, $takeid = null) {

        $questions = $this->ivsMatchInterface->match_questions_get_by_video_db($videoid);
        $this->add_answers_to_questions($questions, $videoid, $userid, $takeid);

        return $questions;
    }

    /**
     * Get the number of remaing attempts by user
     *
     * @param $userid
     * @param $videoid
     * @param $contextid
     * @return int
     */
    public function get_remaining_attempts($userid, $videoid, $contextid) {

        $conf = $this->ivsMatchInterface->match_video_get_config_db($contextid);

        if ($conf->has_unlimited_attempts()) {
            return -1;
        }

        $usertakes = $this->ivsMatchInterface->match_takes_get_by_user_and_video_db($userid, $videoid, $contextid);

        $numcompleted = 0;

        foreach ($usertakes as $take) {
            if ($take->completed > 0) {
                $numcompleted++;
            }
        }

        if ($numcompleted < $conf->attempts) {
            return $conf->attempts - $numcompleted;
        }
        return 0;

    }

    /**
     * @param $userid
     * @param $videoid
     * @param $contextid
     * @return \mod_ivs\ivs_matchMatchTake|mixed
     * @throws \mod_ivs\ivs_match\exception\MatchTakeNoRemainingAttemptsException
     */
    public function get_match_take_for_user($userid, $videoid, $contextid) {

        $notcompletedtakes = $this->ivsMatchInterface->match_takes_get_by_user_and_video_db($userid, $videoid, $contextid,
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
        $mt->evaluated = 0;

        $this->ivsMatchInterface->match_take_insert_db($mt);

        return $mt;

    }

    private function evaluate_formative_take($matchtake) {
        return $matchtake;
    }
}
