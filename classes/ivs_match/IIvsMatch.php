<?php

namespace mod_ivs\ivs_match;

use mod_ivs\ivs_match\exception\MatchNoConfigException;
use mod_ivs\ivs_match\MatchConfig;
use mod_ivs\ivs_match\MatchTake;

interface IIvsMatch {

    /**
     * Get a single match question item
     *
     * @param $question_id
     * @param bool $skip_access
     * @return array
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     */
    public function match_question_get_db($question_id, $skip_access = false);

    /**
     * Get all match question items by video keyed by question_id
     *
     * @param $video_id
     * @param string $order
     * @param bool $skip_access
     * @return mixed
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     */
    public function match_questions_get_by_video_db($video_id, $order = 'timecode', $skip_access = false);

    /**
     * Insert a new match question into the database
     *
     * @param $video_id
     * @param $data
     * @return mixed
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     */
    public function match_question_insert_db($video_id, $data, $skip_access = false);

    /**
     * Insert a new match question into the database
     *
     * @param $video_id
     * @param $data
     * @return mixed
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionNotFoundException
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     */
    public function match_question_update_db($video_id, $data, $skip_access = false);

    /**
     * Delete an exsiting match question from the database
     *
     * @param $question_id
     * @param bool $skip_access
     * @return mixed
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     */
    public function match_question_delete_db($question_id, $skip_access = false);

    /**
     * Insert a new answer for a question
     *
     * @param $video_id
     * @param $data
     *   Holds answer data and question_id as well as the user_id
     * @param null $user_id
     * @param bool $skip_access
     * @return mixed
     */
    public function match_question_answer_insert_db($video_id, $data, $user_id = null, $skip_access = false);

    /**
     * Get a single answer by answer id
     *
     * @param $answer_id
     * @param bool $skip_access
     * @return mixed
     */
    public function match_question_answer_get_db($answer_id, $skip_access = false);

    /**
     * Get a single answer by question and user
     *
     * @param $question_id
     * @param $user_id
     * @param bool $skip_access
     * @return mixed
     */
    public function match_question_answer_get_by_question_and_user_db($question_id, $user_id, $skip_access = false);

    /**
     * Get a collection of answers by video and user keyed by question_id
     *
     * @param $video_id
     * @param $user_id
     * @param bool $skip_access
     * @return mixed
     */
    public function match_question_answers_get_by_video_and_user_db($video_id, $user_id, $skip_access = false);

    /**
     * Get a collection of answers by take
     *
     * @param $take_id
     * @param $only_latest
     *   Only the latest answers for a question - so max is the number of questions
     * @param bool $skip_access
     * @return mixed
     */
    public function match_question_answers_get_by_take($take_id, $only_latest = true, $skip_access = false);

    /**
     * Delete a single answer by answer_id
     *
     * @param $answer_id
     * @param bool $skip_access
     * @return mixed
     */
    public function match_question_answer_delete_db($answer_id, $skip_access = false);


    //new interface functions for takes

    /**
     * Get the Match config by video and optional context_id
     *
     * @param int $context_id
     * @param int $video_id
     * @return \mod_ivs\ivs_match\MatchConfig
     * @throws \mod_ivs\ivs_match\exception\MatchNoConfigException
     */
    public function match_video_get_config_db($context_id, $video_id = null);

    /**
     * Check access to match takes
     *
     * @param $op - view, update
     * @param \mod_ivs\ivs_match\MatchTake $take
     * @return mixed
     * @internal param $take_id
     */
    public function match_take_access($op, MatchTake $take);

    /**
     * Insert a match take in the database
     *
     * @param \mod_ivs\ivs_match\MatchTake $take
     * @return mixed
     */
    public function match_take_insert_db(MatchTake &$take);

    /**
     * Update a match take in the database
     *
     * @param \mod_ivs\ivs_match\MatchTake $take
     * @return mixed
     */
    public function match_take_update_db(MatchTake $take);

    /**
     * Delete a match take from the database
     *
     * @param $take_id
     * @return mixed
     */
    public function match_take_delete_db($take_id);

    /**
     * Get a match take from the database
     *
     * @param $take_id
     * @return \mod_ivs\ivs_match\MatchTake
     */
    public function match_take_get_db($take_id);

    /**
     * Get all match takes by user and context
     *
     * @param $user_id
     * @param $video_id
     * @param null $context_id
     * @param array $status
     * @return MatchTake[]
     */
    public function match_takes_get_by_user_and_video_db($user_id, $video_id, $context_id, $status = []);

    /**
     * Get the unique id of the currently acting user
     *
     * @return int
     */
    public function get_current_user_id();

    public function permission_match_question($op, $video_id, $context_id = null, $user_id = null);

    /**
     * Get an array of assessment configs
     *
     * @param $user_id
     * @param $video_id
     * @param bool $include_pending
     * @return AssessmentConfig[]
     * @throws MatchNoConfigException
     */
    public function assessment_config_get_by_user_and_video($user_id, $video_id, $include_simulation = false);
}
