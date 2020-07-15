<?php
/*************************************************************************
 *
 * GHOSTTHINKER CONFIDENTIAL
 * __________________
 *
 *  2006 - 2017 Ghostthinker GmbH
 *  All Rights Reserved.
 *
 * NOTICE:  All information contained herein is, and remains
 * the property of Ghostthinker GmbH and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Ghostthinker GmbH
 * and its suppliers and may be covered by German and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Ghostthinker GmbH.
 */

/**
 * Define all the backup steps that will be used by the backup_ivs_activity_task
 *
 * @package   mod_ivs
 * @category  backup
 * @copyright 2017 Ghostthinker GmbH <info@ghostthinker.de>
 * @license   All Rights Reserved.
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define the complete ivs structure for backup, with file and id annotations
 *
 * @package   mod_ivs
 * @category  backup
 * @copyright 2017 Ghostthinker GmbH <info@ghostthinker.de>
 * @license   All Rights Reserved.
 */
class backup_ivs_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the backup structure of the module
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        // Get know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Get anonymize setting
        $anonymize = $this->get_setting_value('anonymize');

        // Get match answer setting
        //$match_answers_setting = $this->get_setting_value('include_match');

        // Define the root element describing the ivs instance.
        $ivs = new backup_nested_element('ivs', array('id'), array(
                'name',
                'intro',
                'introformat',
                'grade',
                'duration',
                'playbackcommands',
                'timecreated',
                'timemodified',
                'videourl',
                'match_enabled',
                'match_config'
        ));

        $ivs_videocomments = new backup_nested_element('videocomments');

        $videocomment = new backup_nested_element('ivs_videocomment', array('id'), array(
                'body',
                'video_id',
                'parent_id',
                'time_stamp',
                'duration',
                'thumbnail',
                'user_id',
                'timecreated',
                'timemodified',
                'additional_data',
                'access_view',
                'is_student'
        ));

        //Backup Match Questions
        $ivs_matchquestions = new backup_nested_element('matchquestions');

        $match_questions = new backup_nested_element('ivs_matchquestion', array('id'), array(
                'type',
                'title',
                'time_stamp',
                'duration',
                'extra',
                'type_data',
                'question_body',
                'video_id',
                'user_id',
                'timecreated',
                'timemodified',
        ));

        //Backup Match Questions
        $ivs_matchtakes = new backup_nested_element('matchtakes');

        $match_takes = new backup_nested_element('ivs_matchtake', array('id'), array(
                'video_id',
                'context_id',
                'user_id',
                'evaluated',
                'score',
                'status',
                'timecreated',
                'timemodified',
                'timecompleted'
        ));

        //Backup Match Answers
        $ivs_matchanswers = new backup_nested_element('matchanswers');

        $match_answers = new backup_nested_element('ivs_matchanswer', array('id'), array(
                'question_id',
                'take_id',
                'user_id',
                'data',
                'correct',
                'timecreated',
                'timemodified',
                'evaluated',
                'score'
        ));

        //Backup Player Settings
        $ivs_settings = new backup_nested_element('playersettings');

        $player_settings = new backup_nested_element('ivs_settings', array('id'), array(
                'target_id',
                'target_type',
                'name',
                'value',
                'locked'
        ));

        //Build the tree
        $ivs->add_child($ivs_videocomments);
        $ivs->add_child($ivs_matchquestions);
        $ivs->add_child($ivs_matchtakes);
        $ivs->add_child($ivs_matchanswers);
        $ivs->add_child($ivs_settings);

        $ivs_videocomments->add_child($videocomment);
        $ivs_matchquestions->add_child($match_questions);
        $ivs_matchtakes->add_child($match_takes);
        $ivs_matchanswers->add_child($match_answers);
        $ivs_settings->add_child($player_settings);

        // Define data sources.
        $ivs->set_source_table('ivs', array('id' => backup::VAR_ACTIVITYID));

        if ($anonymize) {
            $videocomment->set_source_sql('SELECT DISTINCT(vc.id),vc.body,vc.video_id,vc.parent_id,vc.time_stamp,vc.duration,vc.thumbnail,vc.timecreated,vc.timemodified,vc.additional_data,vc.access_view,
  (SELECT count(*)
  FROM {role_assignments} ra
  INNER JOIN {role} r on ra.roleid = r.id
  INNER JOIN {context} c ON ra.contextid = c.id
   WHERE ra.userid = vc.user_id
   AND c.instanceid = ep.course
   AND r.shortname = \'student\') as is_student
FROM {ivs_videocomment} vc
  INNER JOIN {ivs} ep ON ep.id = vc.video_id
  INNER JOIN {context} cc ON cc.instanceid = ep.course
WHERE video_id = ?;',
                    array(backup::VAR_ACTIVITYID));
        } else if ($userinfo) {
            $videocomment->set_source_sql('SELECT DISTINCT(vc.id),vc.user_id, vc.body,vc.video_id,vc.parent_id,vc.time_stamp,vc.duration,vc.thumbnail,vc.timecreated,vc.timemodified,vc.additional_data,vc.access_view,
 (SELECT count(*)
  FROM {role_assignments} ra
  INNER JOIN {role} r on ra.roleid = r.id
  INNER JOIN {context} c ON ra.contextid = c.id
   WHERE ra.userid = vc.user_id
   AND c.instanceid = ep.course
   AND r.shortname = \'student\') as is_student
FROM {ivs_videocomment} vc
  INNER JOIN {ivs} ep ON ep.id = vc.video_id
  INNER JOIN {context} cc ON cc.instanceid = ep.course
WHERE video_id = ?;',
                    array(backup::VAR_ACTIVITYID));
        }

        $match_questions->set_source_sql('
            SELECT *
              FROM {ivs_matchquestion}
             WHERE video_id = ?',
                array(backup::VAR_ACTIVITYID));

        if ($anonymize) {
            $match_takes->set_source_sql('
        SELECT id,
         video_id ,
         context_id ,
         evaluated ,
         score ,
         status ,
         timecreated ,
         timemodified ,
         timecompleted
        FROM {ivs_matchtake}
        WHERE video_id = ?',
                    array(backup::VAR_ACTIVITYID));
        } else {
            $match_takes->set_source_sql('
            SELECT *
              FROM {ivs_matchtake}
             WHERE video_id = ?',
                    array(backup::VAR_ACTIVITYID));
        }

        if ($anonymize) {
            $match_answers->set_source_sql('
           SELECT mqa.id, mqa.question_id, mqa.take_id, mqa.data, mqa.correct, mqa.timecreated, mqa.timemodified, mqa.evaluated, mqa.score
            FROM {ivs_matchanswer} mqa
            INNER JOIN {ivs_matchquestion} mq
            ON mqa.question_id = mq.id
            WHERE mq.video_id = ?',
                    array(backup::VAR_ACTIVITYID));
        } else {
            $match_answers->set_source_sql('
           SELECT mqa.*
            FROM {ivs_matchanswer} mqa
            INNER JOIN {ivs_matchquestion} mq
            ON mqa.question_id = mq.id
            WHERE mq.video_id = ?',
                    array(backup::VAR_ACTIVITYID));
        }

        //Player settings activity
        $player_settings->set_source_sql('
            SELECT *
              FROM {ivs_settings}
             WHERE target_id = ? AND target_type = \'activity\'',
                array(backup::VAR_ACTIVITYID));

        // Define id annotations
        $videocomment->annotate_ids('user', 'user_id');
        $match_questions->annotate_ids('user', 'user_id');
        $match_takes->annotate_ids('user', 'user_id');
        $match_answers->annotate_ids('user', 'user_id');

        // Define file annotations (we do not use itemid in this example).
        $ivs->annotate_files('mod_ivs', 'videos', null);
        $ivs->annotate_files('mod_ivs', 'preview', null);

        return $this->prepare_activity_structure($ivs);
    }
}
