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
 * Interface class for the match plugin
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\ivs_match;

use mod_ivs\ivs_match\exception\MatchNoConfigException;
use mod_ivs\ivs_match\MatchConfig;
use mod_ivs\ivs_match\MatchTake;

/**
 * Interface IIvsMatch
 */
interface IIvsMatch {

    /**
     * Get a single match question item
     *
     * @param int $questionid
     * @param bool $skipaccess
     * @return array
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     */
    public function match_question_get_db($questionid, $skipaccess = false);

    /**
     * Get all match question items by video keyed by question_id
     *
     * @param int $videoid
     * @param string $order
     * @param bool $skipaccess
     * @return mixed
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     */
    public function match_questions_get_by_video_db($videoid, $order = 'timecode', $skipaccess = false);

    /**
     * Insert a new match question into the database
     *
     * @param int $videoid
     * @param \stdClass $data
     * @param bool $skipaccess
     * @return mixed
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     */
    public function match_question_insert_db($videoid, $data, $skipaccess = false);

    /**
     * Insert a new match question into the database
     *
     * @param int $videoid
     * @param \stdClass $data
     * @param bool $skipaccess
     * @return mixed
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionNotFoundException
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     */
    public function match_question_update_db($videoid, $data, $skipaccess = false);

    /**
     * Delete an exsiting match question from the database
     *
     * @param int $questionid
     * @param bool $skipaccess
     * @return mixed
     * @throws \mod_ivs\ivs_match\exception\MatchQuestionAccessDeniedException
     */
    public function match_question_delete_db($questionid, $skipaccess = false);

    /**
     * Insert a new answer for a question
     *
     * @param int $videoid
     * @param \stdClass $data
     *   Holds answer data and question_id as well as the user_id
     * @param null $userid
     * @param bool $skipaccess
     * @return mixed
     */
    public function match_question_answer_insert_db($videoid, $data, $userid = null, $skipaccess = false);

    /**
     * Get a single answer by answer id
     *
     * @param int $answerid
     * @param bool $skipaccess
     * @return mixed
     */
    public function match_question_answer_get_db($answerid, $skipaccess = false);

    /**
     * Get a single answer by question and user
     *
     * @param int $questionid
     * @param int $userid
     * @param bool $skipaccess
     * @return mixed
     */
    public function match_question_answer_get_by_question_and_user_db($questionid, $userid, $skipaccess = false);

    /**
     * Get a collection of answers by video and user keyed by question_id
     *
     * @param int $videoid
     * @param int $userid
     * @param bool $skipaccess
     * @return mixed
     */
    public function match_question_answers_get_by_video_and_user_db($videoid, $userid, $skipaccess = false);

    /**
     * Get a collection of answers by take
     *
     * @param int $takeid
     * @param bool $onlylatest
     *   Only the latest answers for a question - so max is the number of questions
     * @param bool $skipaccess
     * @return mixed
     */
    public function match_question_answers_get_by_take($takeid, $onlylatest = true, $skipaccess = false);

    /**
     * Delete a single answer by answer_id
     *
     * @param int $answerid
     * @param bool $skipaccess
     * @return mixed
     */
    public function match_question_answer_delete_db($answerid, $skipaccess = false);


    // New interface functions for takes.

    /**
     * Get the Match config by video and optional context_id
     *
     * @param int $contextid
     * @param int $videoid
     * @return \mod_ivs\ivs_match\MatchConfig
     * @throws \mod_ivs\ivs_match\exception\MatchNoConfigException
     */
    public function match_video_get_config_db($contextid, $videoid = null);

    /**
     * Check access to match takes
     *
     * @param string $op - view, update
     * @param \mod_ivs\ivs_match\MatchTake $take
     * @return mixed
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
     * @param int $takeid
     * @return mixed
     */
    public function match_take_delete_db($takeid);

    /**
     * Get a match take from the database
     *
     * @param int $takeid
     * @return \mod_ivs\ivs_match\MatchTake
     */
    public function match_take_get_db($takeid);

    /**
     * Get all match takes by user and context
     *
     * @param int $userid
     * @param int $videoid
     * @param null $contextid
     * @param array $status
     * @return MatchTake[]
     */
    public function match_takes_get_by_user_and_video_db($userid, $videoid, $contextid, $status = []);

    /**
     * Get the unique id of the currently acting user
     *
     * @return int
     */
    public function get_current_user_id();

    /**
     * Get the permission for a match question
     * @param string $op
     * @param int $videoid
     * @param null $contextid
     * @param null $userid
     *
     * @return mixed
     */
    public function permission_match_question($op, $videoid, $contextid = null, $userid = null);

    /**
     * Get an array of assessment configs
     *
     * @param int $userid
     * @param int $videoid
     * @param bool $includesimulation
     * @return AssessmentConfig[]
     * @throws MatchNoConfigException
     */
    public function assessment_config_get_by_user_and_video($userid, $videoid, $includesimulation = false);

    public function match_timing_type_get_db($ivs, $skip_access = FALSE);
    public function match_timing_type_insert_db($videoid, $data, $user_id = NULL, $skip_access = FALSE);
    public function match_timing_type_update_db($videoid, $data, $user_id = NULL, $skip_access = FALSE);
    public function match_timing_type_delete_db($videoid, $timingtypeid, $skip_access = FALSE);

}
