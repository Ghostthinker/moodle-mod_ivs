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
 * Class to handle user data
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\privacy;

use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\contextlist;
use \core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\helper as request_helper;
use \core_privacy\local\request\transform;
use \core_privacy\local\request\writer;
use \core_privacy\local\request\userlist;
use \core_privacy\local\request\approved_userlist;

/**
 * Class provider
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\subsystem\provider,
    \core_privacy\local\request\subsystem\plugin_provider,
    \core_privacy\local\request\core_userlist_provider
{

    /**
     * Get all metadata
     * @param \core_privacy\local\metadata\collection $collection
     *
     * @return \core_privacy\local\metadata\collection
     */
    public static function get_metadata(collection $collection): collection {

        $collection->add_database_table(
            'ivs_matchanswer',
            [
                'user_id' => 'privacy:metadata:ivs_matchanswer:user_id',
                'data' => 'privacy:metadata:ivs_matchanswer:data',
            ],
            'privacy:metadata:ivs_matchanswer'
        );

        $collection->add_database_table(
            'ivs_matchquestion',
            [
                'user_id' => 'privacy:metadata:ivs_matchquestion:user_id',
            ],
            'privacy:metadata:ivs_matchquestion'
        );

        $collection->add_database_table(
            'ivs_matchtake',
            [
                'user_id' => 'privacy:metadata:ivs_matchtake:user_id',
            ],
            'privacy:metadata:ivs_matchtake'
        );

        $collection->add_database_table(
            'ivs_report',
            [
                'user_id' => 'privacy:metadata:ivs_report:user_id',
            ],
            'privacy:metadata:ivs_report'
        );

        $collection->add_database_table(
            'ivs_videocomment',
            [
                'user_id' => 'privacy:metadata:ivs_videocomment:user_id',
                'body' => 'privacy:metadata:ivs_videocomment:body',
            ],
            'privacy:metadata:ivs_videocomment'
        );

        return $collection;
    }

    /**
     * Get all users in a context
     * @param \core_privacy\local\request\userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $params = [
            'instanceid' => $context->instanceid,
            'modulename' => 'ivs',
        ];

        // Get user which made matchanswers.
        $sql = "SELECT ma.user_id
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {ivs} f ON f.id = cm.instance
                  JOIN {ivs_matchanswer} ma ON ma.question_id = f.id
                 WHERE cm.id = :instanceid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Get users which have a matchtake.
        $sql = "SELECT mt.user_id
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {ivs} ivs ON ivs.id = cm.instance
                  JOIN {ivs_matchtake} mt ON mt.video_id = ivs.id
                 WHERE cm.id = :instanceid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Get user which have a videocomment.
        $sql = "SELECT vc.user_id
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {ivs} ivs ON ivs.id = cm.instance
                  JOIN {ivs_videocomment} vc ON vc.video_id = ivs.id
                 WHERE cm.id = :instanceid";
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export user data for a activity
     * @param \core_privacy\local\request\approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist)) {
            return;
        }

        $user = $contextlist->get_user();
        $userid = $user->id;

        list($contextsql, $contextparams) =
            $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);
        $params = $contextparams;

        $sql =
            "SELECT id AS videocommentid, video_id AS videoid FROM {ivs_videocomment}
             WHERE user_id = :user_id AND video_id {$contextsql}";
        $params['user_id'] = $userid;
        $videocomments = $DB->get_records_sql_menu($sql, $params);

        $sql =
            "SELECT id AS takeid, video_id AS videoid FROM {ivs_matchtake} WHERE user_id = :user_id AND video_id {$contextsql}";
        $params['user_id'] = $userid;
        $takes = $DB->get_records_sql_menu($sql, $params);

        $sql =
            "SELECT id AS takeid, question_id AS answerid FROM {ivs_matchanswer}
             WHERE user_id = :user_id AND question_id {$contextsql}";
        $params['user_id'] = $userid;
        $matchanswers = $DB->get_records_sql_menu($sql, $params);

        $sql = "SELECT
                    c.id AS contextid,
                    f.*,
                    cm.id AS cmid
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid
                  JOIN {ivs} f ON f.id = cm.instance
                 WHERE (
                    c.id {$contextsql}
                )
        ";

        $params += $contextparams;

        // Keep a mapping of forumid to contextid.
        $mappings = [];

        $ivss = $DB->get_recordset_sql($sql, $params);
        foreach ($ivss as $ivs) {

            $mappings[$ivs->id] = $ivs->contextid;

            $context = \context::instance_by_id($mappings[$ivs->id]);

            // Store the main forum data.
            $data = request_helper::get_context_data($context, $user);
            writer::with_context($context)
                ->export_data([], $data);
            request_helper::export_context_files($context, $user);

            // Store relevant metadata about this forum instance.
            if (isset($videocomments[$ivs->contextid])) {
                $exportpath = array_merge([],
                    [get_string('privacy:metadata:ivs_videocomment', 'mod_ivs')]);
                writer::with_context(\context_module::instance($ivs->cmid))
                    ->export_metadata($exportpath, 'videocomment', 1,
                        get_string('privacy:metadata:ivs_videocomment:videocommment',
                            'mod_ivs'));
            }
            if (isset($takes[$ivs->contextid])) {
                $exportpath = array_merge([],
                    [get_string('privacy:metadata:ivs_takes', 'mod_ivs')]);
                writer::with_context(\context_module::instance($ivs->cmid))
                    ->export_metadata($exportpath, 'takes', 1,
                        get_string('privacy:metadata:ivs_takes:takes', 'mod_ivs'));

            }
            if (isset($matchanswers[$ivs->contextid])) {
                $exportpath = array_merge([],
                    [get_string('privacy:metadata:ivs_matchanswer', 'mod_ivs')]);
                writer::with_context(\context_module::instance($ivs->cmid))
                    ->export_metadata($exportpath, 'matchanswer', 1,
                        get_string('privacy:metadata:ivs_matchanswer:matchanswer',
                            'mod_ivs'));
            }

        }
        $ivss->close();

        if (!empty($mappings)) {
            // Store all discussion data for this ivs.
            static::export_videocomment_data($userid, $mappings);

            // Store all post data for this ivs.
            static::export_matchanswer_data($userid, $mappings);
        }

    }

    /**
     * Export videocomments
     * @param int $userid
     * @param array $mappings
     *
     * @return array
     */
    protected static function export_videocomment_data(int $userid,
                                                       array $mappings) {
        global $DB;

        // Find all of the discussions, and discussion subscriptions for this forum.

        list($ivsinsql, $ivsparams) =
            $DB->get_in_or_equal(array_keys($mappings), SQL_PARAMS_NAMED);

        $sql =
            "SELECT * FROM {ivs_videocomment} WHERE video_id ${ivsinsql} AND user_id = :user_id";

        $params = [
            'user_id' => $userid,
        ];

        $params += $ivsparams;

        $ivsswithdata = [];
        $videocomments = $DB->get_recordset_sql($sql, $params);
        foreach ($videocomments as $videocomment) {
            // No need to take timestart into account as the user has some involvement already.
            // Ignore discussion timeend as it should not block access to user data.
            $ivsswithdata[$videocomment->id] = true;
            $context = \context::instance_by_id($mappings[$videocomment->id]);

            $videocommentdata = (object)[
                'id' => format_string($videocomment->id),
                'body' => transform::yesno((bool)$videocomment->body),
                'timecreated' => transform::datetime($videocomment->timecreated),
                'timemodified' => transform::datetime($videocomment->timemodified),
                'time_stamp' => transform::yesno($videocomment->time_stamp),
            ];

            // Store the discussion content.
            writer::with_context($context)
                ->export_data($videocomment, $videocommentdata);

            // Forum discussions do not have any files associately directly with them.
        }

        $videocomments->close();

        return $ivsswithdata;
    }

    /**
     * Export match answers
     * @param int $userid
     * @param array $mappings
     *
     * @return array
     */
    protected static function export_matchanswer_data(int $userid,
                                                      array $mappings) {
        global $DB;

        // Find all of the discussions, and discussion subscriptions for this forum.

        list($ivsinsql, $ivsparams) =
            $DB->get_in_or_equal(array_keys($mappings), SQL_PARAMS_NAMED);

        $sql =
            "SELECT * FROM {ivs_matchanswer} WHERE question_id ${ivsinsql} AND user_id = :user_id";

        $params = [
            'user_id' => $userid,
        ];

        $params += $ivsparams;

        $ivsswithdata = [];
        $matchanswers = $DB->get_recordset_sql($sql, $params);
        foreach ($matchanswers as $matchanswer) {
            // No need to take timestart into account as the user has some involvement already.
            // Ignore discussion timeend as it should not block access to user data.
            $ivsswithdata[$matchanswer->id] = true;
            $context = \context::instance_by_id($mappings[$matchanswer->id]);

            $matchanswerdata = (object)[
                'id' => format_string($matchanswer->id),
                'question_id' => format_string($matchanswer->question_id),
                'take_id' => format_string($matchanswer->take_id),
                'correct' => format_string($matchanswer->correct),
                'data' => format_string($matchanswer->data),
                'score' => format_string($matchanswer->score),
                'evaluated' => format_string($matchanswer->evaluated),
                'timecreated' => transform::datetime($matchanswer->timecreated),
                'timemodified' => transform::datetime($matchanswer->timemodified),
                'time_stamp' => transform::yesno($matchanswer->time_stamp),
            ];

            // Store the discussion content.
            writer::with_context($context)
                ->export_data($matchanswer, $matchanswerdata);

            // Forum discussions do not have any files associately directly with them.
        }

        $matchanswers->close();

        return $ivsswithdata;
    }

    /**
     * Get contexts for a user id
     * @param int $userid
     *
     * @return \core_privacy\local\request\contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new \core_privacy\local\request\contextlist();

        $params = [
            'modname' => 'ivs',
            'contextlevel' => CONTEXT_MODULE,
            'user_id' => $userid,
        ];

        $sql = "SELECT c.id
                 FROM {context} c
           INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
           INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
           INNER JOIN {ivs} ivs ON ivs.id = cm.instance
            LEFT JOIN {ivs_videocomment} vc ON vc.video_id = ivs.id
                WHERE (
                vc.user_id        = :user_id
                )
        ";

        $contextlist->add_from_sql($sql, $params);

        $sql = "SELECT c.id
                 FROM {context} c
           INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
           INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
           INNER JOIN {ivs} ivs ON ivs.id = cm.instance
            LEFT JOIN {ivs_matchtake} mt ON mt.video_id = ivs.id
                WHERE (
                mt.user_id        = :user_id
                )
        ";

        $contextlist->add_from_sql($sql, $params);

        $sql = "SELECT c.id
                 FROM {context} c
           INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
           INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
           INNER JOIN {ivs} ivs ON ivs.id = cm.instance
            LEFT JOIN {ivs_matchanswer} ma ON ma.question_id = ivs.id
                WHERE (
                ma.user_id        = :user_id
                )
        ";

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Delete data for a single user
     * @param \core_privacy\local\request\approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            $ivsid = $DB->get_field('course_modules', 'instance',
                ['id' => $context->instanceid], MUST_EXIST);

            // Delete Operation for videocomments.
            $DB->delete_records('ivs_videocomment',
                ['video_id' => $ivsid, 'user_id' => $userid]);

            // Delete Operation for matchanswers.
            $questions =
                $DB->get_records_sql('SELECT id FROM {ivs_matchquestion} WHERE video_id = ?',
                    [$ivsid]);
            foreach ($questions as $question) {
                $DB->delete_records('ivs_matchanswer',
                    ['question_id' => $question->id, 'user_id' => $userid]);
            }

            // Delete Operations for matchtake.
            $DB->delete_records('ivs_matchtake',
                ['video_id' => $ivsid, 'user_id' => $userid]);

        }
    }

    /**
     * Delete data for users
     * @param \core_privacy\local\request\approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        $ivsid = $DB->get_field('course_modules', 'instance',
            ['id' => $context->instanceid], MUST_EXIST);

        list($userinsql, $userinparams) =
            $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $params = array_merge(['video_id' => $ivsid], $userinparams);
        $paramsmatchanswer = array_merge(['question_id' => $ivsid], $userinparams);

        $DB->delete_records_select('ivs_videocomment',
            "video_id = :video_id AND userid {$userinsql}", $params);
        $DB->delete_records_select('ivs_matchtake',
            "video_id = :video_id AND userid {$userinsql}", $params);
        $DB->delete_records_select('ivs_matchanswer',
            "question_id = :question_id AND userid {$userinsql}", $paramsmatchanswer);
    }

    /**
     * Delete all data for users in a context
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $ivs = get_coursemodule_from_id('ivs', $context->instanceid);
        if (!$ivs) {
            return;
        }

        $DB->delete_records('ivs_videocomment', ['video_id' => $ivs->instance]);
        $DB->delete_records('ivs_matchtake', ['video_id' => $ivs->instance]);
        $DB->delete_records('ivs_matchanswer', ['question_id' => $ivs->instance]);
    }

}
