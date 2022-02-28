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
 * This class is used to backup a ivs activity
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Class backup_ivs_activity_structure_step
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

        // Get anonymize setting.
        $anonymize = $this->get_setting_value('anonymize');

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

        $ivsvideocomments = new backup_nested_element('videocomments');

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

        // Backup Match Questions.
        $ivsmatchquestions = new backup_nested_element('matchquestions');

        $matchquestions = new backup_nested_element('ivs_matchquestion', array('id'), array(
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

        // Backup Match Questions.
        $ivsmatchtakes = new backup_nested_element('matchtakes');

        $matchtakes = new backup_nested_element('ivs_matchtake', array('id'), array(
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

        // Backup Match Answers.
        $ivsmatchanswers = new backup_nested_element('matchanswers');

        $matchanswers = new backup_nested_element('ivs_matchanswer', array('id'), array(
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

        // Backup Player Settings.
        $ivssettings = new backup_nested_element('playersettings');

        $playersettings = new backup_nested_element('ivs_settings', array('id'), array(
                'target_id',
                'target_type',
                'name',
                'value',
                'locked'
        ));

        // Build the tree.
        $ivs->add_child($ivsvideocomments);
        $ivs->add_child($ivsmatchquestions);
        $ivs->add_child($ivsmatchtakes);
        $ivs->add_child($ivsmatchanswers);
        $ivs->add_child($ivssettings);

        $ivsvideocomments->add_child($videocomment);
        $ivsmatchquestions->add_child($matchquestions);
        $ivsmatchtakes->add_child($matchtakes);
        $ivsmatchanswers->add_child($matchanswers);
        $ivssettings->add_child($playersettings);

        // Define data sources.
        $ivs->set_source_table('ivs', array('id' => backup::VAR_ACTIVITYID));

        if ($anonymize) {
            $videocomment->set_source_sql('SELECT DISTINCT(vc.id),vc.body,vc.video_id,vc.parent_id,vc.time_stamp,
                vc.duration,vc.thumbnail,vc.timecreated,vc.timemodified,vc.additional_data,vc.access_view,
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
            $videocomment->set_source_sql('SELECT DISTINCT(vc.id),vc.user_id, vc.body,vc.video_id,vc.parent_id,vc.time_stamp,
                vc.duration,vc.thumbnail,vc.timecreated,vc.timemodified,vc.additional_data,vc.access_view,
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

        $matchquestions->set_source_sql('
            SELECT *
              FROM {ivs_matchquestion}
             WHERE video_id = ?',
                array(backup::VAR_ACTIVITYID));

        if ($anonymize) {
            $matchtakes->set_source_sql('
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
            $matchtakes->set_source_sql('
            SELECT *
              FROM {ivs_matchtake}
             WHERE video_id = ?',
                    array(backup::VAR_ACTIVITYID));
        }

        if ($anonymize) {
            $matchanswers->set_source_sql('
           SELECT mqa.id, mqa.question_id, mqa.take_id, mqa.data, mqa.correct,
                  mqa.timecreated, mqa.timemodified, mqa.evaluated, mqa.score
            FROM {ivs_matchanswer} mqa
            INNER JOIN {ivs_matchquestion} mq
            ON mqa.question_id = mq.id
            WHERE mq.video_id = ?',
                    array(backup::VAR_ACTIVITYID));
        } else {
            $matchanswers->set_source_sql('
           SELECT mqa.*
            FROM {ivs_matchanswer} mqa
            INNER JOIN {ivs_matchquestion} mq
            ON mqa.question_id = mq.id
            WHERE mq.video_id = ?',
                    array(backup::VAR_ACTIVITYID));
        }

        // Player settings activity.
        $playersettings->set_source_sql('
            SELECT *
              FROM {ivs_settings}
             WHERE target_id = ? AND target_type = \'activity\'',
                array(backup::VAR_ACTIVITYID));

        // Define id annotations.
        $videocomment->annotate_ids('user', 'user_id');
        $matchquestions->annotate_ids('user', 'user_id');
        $matchtakes->annotate_ids('user', 'user_id');
        $matchanswers->annotate_ids('user', 'user_id');

        // Define file annotations (we do not use itemid in this example).
        $ivs->annotate_files('mod_ivs', 'videos', null);
        $ivs->annotate_files('mod_ivs', 'preview', null);
        $ivs->annotate_files('mod_ivs', 'audio_annotation', null);

        return $this->prepare_activity_structure($ivs);
    }
}
