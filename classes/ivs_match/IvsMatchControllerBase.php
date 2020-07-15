<?php

namespace mod_ivs\ivs_match;

use mod_ivs\ivs_match\exception\MatchAlreadyAnsweredException;
use mod_ivs\ivs_match\exception\MatchNoConfigException;
use mod_ivs\ivs_match\exception\MatchQuestionException;
use mod_ivs\ivs_match\exception\MatchQuestionNotFoundException;
use mod_ivs\ivs_match\exception\MatchTakeException;
use mod_ivs\ivs_match\exception\MatchTakeNoRemainingAttemptsException;

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

    public function handleRequest($end_point, $path_arguments, $method, $post_data) {

        $video_nid = $path_arguments[0];
        if ($end_point == "match_answers") {
            return $this->handleAnswersRequests($path_arguments, $method, $post_data);
        }

        if ($end_point == "match_context") {
            try {
                return $this->handleContextRequests($path_arguments, $method, $post_data);
            } catch (\Exception $e) {
                return new MatchResponse(['message' => $e->getMessage()], 401);
            }
        }

        //handle get
        switch (strtoupper($method)) {
            case "GET":

                $take_id = $_GET['take_id'];
                //GET ANSWERS
                try {
                    $questions = $this->loadQuestionsForUserByVideo($video_nid, null, $take_id);
                } catch (exception\MatchQuestionAccessDeniedException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 403);
                }
                return new MatchResponse(array_values($questions));
            case "POST":
                try {
                    $response = $this->ivsMatchInterface->match_question_insert_db($video_nid, $post_data);
                    return new MatchResponse($response);
                } catch (exception\MatchQuestionAccessDeniedException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 403);
                }
                break;
            case "PUT":

                try {
                    $response = $this->ivsMatchInterface->match_question_update_db($video_nid, $post_data);
                } catch (exception\MatchQuestionAccessDeniedException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 403);
                } catch (exception\MatchQuestionNotFoundException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 404);
                }
                return new MatchResponse($response);

            case "DELETE":
                $question_nid = $path_arguments[1];
                try {
                    $response = $this->ivsMatchInterface->match_question_delete_db($question_nid);
                } catch (exception\MatchQuestionAccessDeniedException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 403);
                } catch (exception\MatchQuestionNotFoundException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 404);
                }
                return new MatchResponse($response);
        }

        return new MatchResponse();

    }

    protected function handleAnswersRequests($path_arguments, $method, $post_data) {

        $video_nid = $path_arguments[0];

        switch (strtoupper($method)) {
            case "GET":
                return new MatchResponse();
            case "POST":
                try {

                    $response = $this->processMatchAnswer($video_nid, $post_data);

                    return new MatchResponse($response);
                } catch (exception\MatchQuestionAccessDeniedException $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 403);
                } catch (\Exception $e) {
                    return new MatchResponse(['message' => $e->getMessage()], 404);
                }
                break;
                break;
            case "PUT":
                break;
            case "DELETE":
                break;
        }
    }

    protected function handleContextRequests($path_arguments, $method, $post_data) {

        $video_nid = $path_arguments[0];
        $context_id = $path_arguments[2];
        $uid = $this->ivsMatchInterface->get_current_user_id();

        switch (strtoupper($method)) {
            case "GET":
                $assessment_config = $this->ivsMatchInterface->assessment_config_get_by_user_and_video($uid, $video_nid);
                return new MatchResponse($assessment_config);
            case "POST":
                $take = $this->getMatchTakeForUser($uid, $video_nid, $context_id);
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
     * @param $video_id
     * @param $post_data
     * @param null $user_id
     * @param bool $skip_access
     * @return mixed
     * @throws MatchTakeException
     * @throws MatchNoConfigException
     * @throws MatchQuestionNotFoundException
     * @throws MatchAlreadyAnsweredException
     */
    public function processMatchAnswer($video_id, $post_data, $user_id = null, $skip_access = false) {

        $this->evaluateAnswer($post_data);
        $solution = $post_data['solution_data'];

        if (!empty($post_data['take_id'])) {

            $take_id = $post_data['take_id'];
            $question_id = $post_data['question_id'];

            //TODO load take, get context id from take (NOT ALWAYS SAME AS VIEDO ID)
            $match_take = $this->ivsMatchInterface->match_take_get_db($take_id);

            //TODO get match config for this ideo id
            $match_config = $this->ivsMatchInterface->match_video_get_config_db($match_take->context_id, $match_take->video_id);

            if (!$match_config->allow_repeat_answers) {

                $answer_existing =
                        $this->ivsMatchInterface->match_question_answer_get_by_question_and_user_db($question_id, $user_id,
                                $skip_access);

                if (!empty($answer_existing)) {
                    throw new MatchAlreadyAnsweredException();
                }
            }

            $response = $this->ivsMatchInterface->match_question_answer_insert_db($video_id, $post_data, $user_id, $skip_access);
            $this->evaluateTake($post_data['take_id']);
            $response['solution_data'] = $solution;
        } else {
            $response = $post_data;
        }

        $response['solution_data'] = $solution;

        return $response;
    }

    public function evaluateTake($take_id) {

        $match_take = $this->ivsMatchInterface->match_take_get_db($take_id);

        $match_conf = $this->ivsMatchInterface->match_video_get_config_db($match_take->context_id, $match_take->video_id);

        if ($match_conf->assessment_type == AssessmentConfig::ASSESSMENT_TYPE_FORMATIVE) {
            return $this->evaluateFormativeTake($match_take);
        }

        //TODO check missing take

        //TODO get all answers for this take
        $take_answers = $this->ivsMatchInterface->match_question_answers_get_by_take($take_id);

        //TODO get all questions...
        $questions = $this->ivsMatchInterface->match_questions_get_by_video_db($match_take->video_id, 'timecode', true);

        $num_answered = 0;
        $num_correct = 0;
        $num_questions = count($questions);

        foreach ($questions as $question_id => $question) {

            if (!empty($take_answers[$question_id])) {
                $num_answered++;
                if ($take_answers[$question_id]['is_correct']) {
                    $num_correct++;
                }
            }
        }

        if ($num_answered === $num_questions) {
            //score
            $match_take->score = $num_correct * 100 / $num_answered;
            $match_take->completed = time();
            $match_take->status = $match_conf->hasPassed($match_take->score) ? MatchTake::STATUS_PASSED : MatchTake::STATUS_FAILED;

        } else {
            $match_take->status = MatchTake::STATUS_PROGRESS;
        }

        $this->ivsMatchInterface->match_take_update_db($match_take);

        //check all answers. count correct ones etc

        //save take and return it

        return $match_take;

    }

    protected function toPlayerQuestion($data_db) {

        return $data_db;
    }

    private function validateInput($video_nid, $post_data) {
        //TODO add some general validaion
        //This should be done for each question type
    }

    public function evaluateAnswer(&$answer_data, $add_solution = true, $skip_access_check = true) {

        $question_id = $answer_data['question_id'];

        if (empty($question_id)) {
            throw new MatchQuestionNotFoundException();
        }

        //load from db (does the access check)
        $question = $this->ivsMatchInterface->match_question_get_db($question_id, $skip_access_check);

        if (empty($question)) {
            throw new MatchQuestionNotFoundException();
        }

        switch ($question['type']) {
            case "click_question":
                $answer_data['is_evaluated'] = true;
                break;
            case "single_choice_question":
                $checked_id = $answer_data['question_data']['checked_id'];
                $correct_answer = null;
                $my_answer_is_correct = false;
                foreach ($question['type_data']['options'] as $o) {
                    if ($o["is_correct"] && $o['id'] == $checked_id) {
                        $my_answer_is_correct = true;
                    }
                    if ($o["is_correct"]) {
                        $correct_answer = $o['id'];
                    }
                }

                //add items to object
                $answer_data['is_correct'] = $my_answer_is_correct;
                if ($add_solution) {
                    $answer_data['solution_data'] = [
                            'correct_id' => $correct_answer
                    ];
                }
                $answer_data['is_evaluated'] = true;
                break;
            case "text_question":
                $answer_data['is_correct'] = true;
                $answer_data['is_evaluated'] = false;
                break;

        }
    }

    public function getSolutionForAnswer($question) {

        $solution = [];
        switch ($question['type']) {
            case "single_choice_question":

                $correct_answer = null;
                foreach ($question['type_data']['options'] as $o) {
                    if ($o["is_correct"]) {
                        $correct_answer = $o['id'];
                    }
                }
                $solution = [
                        'correct_id' => $correct_answer
                ];
                break;
        }
        return $solution;
    }

    function addAnswersToQuestions(&$questions, $video_id, $user_id, $take_id) {

        //16.08.2018 - 10:40 - SH - This is a temp solution to getthe answers of the current take - we should actually pass the take id in this fucntion

        //TODO check
        $answers = $this->ivsMatchInterface->match_question_answers_get_by_take($take_id);
        // $answers = $this->ivsMatchInterface->match_question_answers_get_by_video_and_user_db($video_id, $user_id );
        foreach ($answers as $question_id => $answer) {
            if (array_key_exists($question_id, $questions)) {
                $answer['solution_data'] = $this->getSolutionForAnswer($questions[$question_id]);
                $questions[$question_id]['answer'] = $answer;
            }
        }
    }

    /**
     * Load questions for get request
     *
     * @param $video_id
     * @param $user_id
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     */
    function loadQuestionsForUserByVideo($video_id, $user_id = null, $take_id = null) {

        $questions = $this->ivsMatchInterface->match_questions_get_by_video_db($video_id);
        $this->addAnswersToQuestions($questions, $video_id, $user_id, $take_id);

        return $questions;
    }

    /**
     * Get the number of remaing attempts by user
     *
     * @param $user_id
     * @param $video_id
     * @param $context_id
     * @return int
     */
    public function getRemainingAttempts($user_id, $video_id, $context_id) {

        $conf = $this->ivsMatchInterface->match_video_get_config_db($context_id);

        if ($conf->hasUnlimitedAttempts()) {
            return -1;
        }

        $user_takes = $this->ivsMatchInterface->match_takes_get_by_user_and_video_db($user_id, $video_id, $context_id);

        $num_completed = 0;

        foreach ($user_takes as $take) {
            if ($take->completed > 0) {
                $num_completed++;
            }
        }

        if ($num_completed < $conf->attempts) {
            return $conf->attempts - $num_completed;
        }
        return 0;

    }

    /**
     * @param $user_id
     * @param $video_id
     * @param $context_id
     * @return \mod_ivs\ivs_matchMatchTake|mixed
     * @throws \mod_ivs\ivs_match\exception\MatchTakeNoRemainingAttemptsException
     */
    public function getMatchTakeForUser($user_id, $video_id, $context_id) {

        //TODO check existing take in progress
        $not_completed_takes = $this->ivsMatchInterface->match_takes_get_by_user_and_video_db($user_id, $video_id, $context_id,
                [MatchTake::STATUS_NEW, MatchTake::STATUS_PROGRESS]);

        if (!empty($not_completed_takes)) {
            return end($not_completed_takes);
        }

        if ($this->getRemainingAttempts($user_id, $video_id, $context_id) === 0) {
            throw new MatchTakeNoRemainingAttemptsException();
        }

        $mt = new MatchTake();
        $mt->context_id = $context_id;
        $mt->video_id = $video_id;
        $mt->user_id = $user_id;
        $mt->status = MatchTake::STATUS_NEW;
        $mt->context_id = $context_id;
        $mt->created = time();
        $mt->changed = time();
        $mt->evaluated = 0;

        $this->ivsMatchInterface->match_take_insert_db($mt);

        return $mt;

    }

    private function evaluateFormativeTake($match_take) {
        return $match_take;
    }
}
