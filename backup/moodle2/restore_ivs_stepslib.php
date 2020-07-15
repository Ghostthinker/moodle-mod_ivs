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

use mod_ivs\annotation;

/**
 * Define all the restore steps that will be used by the restore_ivs_activity_task
 *
 * @package   mod_ivs
 * @category  backup
 * @copyright 2017 Ghostthinker GmbH <info@ghostthinker.de>
 * @license   All Rights Reserved.
 */

/**
 * Structure step to restore one ivs activity
 *
 * @package   mod_ivs
 * @category  backup
 * @copyright 2017 Ghostthinker GmbH <info@ghostthinker.de>
 * @license   All Rights Reserved.
 */
class restore_ivs_activity_structure_step extends restore_activity_structure_step {

    protected $videocomment_cache;

    /**
     * Defines structure of path elements to be processed during the restore
     *
     * @return array of {@link restore_path_element}
     */
    protected function define_structure() {

        $paths = array();

        $paths[] = new restore_path_element('ivs', '/activity/ivs');
        $paths[] = new restore_path_element('ivs_videocomment', '/activity/ivs/videocomments/ivs_videocomment');
        $paths[] = new restore_path_element('ivs_matchquestion', '/activity/ivs/matchquestions/ivs_matchquestion');
        $paths[] = new restore_path_element('ivs_matchtake', '/activity/ivs/matchtakes/ivs_matchtake');
        $paths[] = new restore_path_element('ivs_matchanswer', '/activity/ivs/matchanswers/ivs_matchanswer');
        $paths[] = new restore_path_element('ivs_settings', '/activity/ivs/playersettings/ivs_settings');

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process the given restore path element data
     *
     * @param array $data parsed element data
     */
    protected function process_ivs($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->get_courseid();

        if (empty($data->timecreated)) {
            $data->timecreated = time();
        }

        if (empty($data->timemodified)) {
            $data->timemodified = time();
        }

        if ($data->grade < 0) {
            // Scale found, get mapping.
            $data->grade = -($this->get_mappingid('scale', abs($data->grade)));
        }

        // Create the ivs instance.
        $newitemid = $DB->insert_record('ivs', $data);
        $this->apply_activity_instance($newitemid);
    }

    protected function process_ivs_videocomment($data) {
        global $DB;
        $data = (object) $data;

        //Do not return. if user_id = null -> anonymization
        if (empty($data->user_id)) {
            $user_id = null;
        } else {
            $user_id = $data->user_id;
        }

        $videocomments_setting = $this->get_setting_value('include_videocomments');

        $new_course_id = $this->get_courseid();
        $new_course_context = context_course::instance($new_course_id);

        $user_is_in_new_course = is_enrolled($new_course_context, $user_id, '', true);

        $restore = false;

        if ($user_is_in_new_course || empty($user_id)) {

            switch ($videocomments_setting) {
                case 'all':
                    $restore = true;
                    break;
                case 'none':
                    break;
                case 'teacher only':
                    if (!$data->is_student) {
                        $restore = true;
                        break;
                    }
                    break;
                case 'students only':
                    if ($data->is_student) {
                        $restore = true;
                        break;
                    }
                    break;
                default:
                    $restore = false;
                    break;
            }

            if ($restore) {
                $oldid = $data->id;

                $new_activity_id = $this->get_new_parentid('ivs');
                $data->video_id = $new_activity_id;

                if (!empty($data->parent_id)) {
                    $this->videocomment_cache[$oldid] = $data;
                    return;
                }

                $newitemid = $DB->insert_record('ivs_videocomment', $data);
                $annotation = annotation::retrieve_from_db($newitemid);
                $annotation->write_annotation_access();

                $this->set_mapping('videocomment', $oldid, $newitemid);
            }
        }
    }

    protected function process_ivs_matchquestion($data) {
        global $DB;
        $data = (object) $data;

        $oldid = $data->id;

        $new_activity_id = $this->get_new_parentid('ivs');
        $data->video_id = $new_activity_id;

        $newitemid = $DB->insert_record('ivs_matchquestion', $data);

        $this->set_mapping('matchquestion', $oldid, $newitemid);
    }

    protected function process_ivs_matchtake($data) {
        global $DB;
        $data = (object) $data;

        if (empty($data->user_id)) {
            return;
        }

        $user_id = $data->user_id;

        $match_answers_setting = $this->get_setting_value('include_match');

        $new_course_id = $this->get_courseid();
        $new_course_context = context_course::instance($new_course_id);
        $user_is_in_new_course = is_enrolled($new_course_context, $user_id, '', true);

        //Is user in new course enrolled
        if ($user_is_in_new_course) {
            //Is match answer setting enabled
            if ($match_answers_setting) {
                $oldid = $data->id;

                $new_activity_id = $this->get_new_parentid('ivs');
                $data->video_id = $new_activity_id;

                $data->context_id = $new_activity_id;

                $new_item_id = $DB->insert_record('ivs_matchtake', $data);
                $this->set_mapping('matchtake', $oldid, $new_item_id);
            }
        }
    }

    protected function process_ivs_matchanswer($data) {
        global $DB;
        $data = (object) $data;

        if (empty($data->user_id)) {
            return;
        }

        $user_id = $data->user_id;

        $match_answers_setting = $this->get_setting_value('include_match');

        $new_course_id = $this->get_courseid();
        $new_course_context = context_course::instance($new_course_id);
        $user_is_in_new_course = is_enrolled($new_course_context, $user_id, '', true);

        //Is user in new course enrolled
        if ($user_is_in_new_course) {
            //Is match answer setting enabled
            if ($match_answers_setting) {
                $old_question_id = $data->question_id;
                $new_question_id = $this->get_mapping('matchquestion', $old_question_id);
                $data->question_id = $new_question_id->newitemid;

                $old_take_id = $data->take_id;
                $new_take_id = $this->get_mapping('matchtake', $old_take_id);
                $data->take_id = $new_take_id->newitemid;

                $DB->insert_record('ivs_matchanswer', $data);
            }
        }
    }

    protected function process_ivs_settings($data) {
        global $DB;
        $data = (object) $data;

        if (empty($data->target_id)) {
            return;
        }

        $oldid = $data->id;

        $new_activity_id = $this->get_new_parentid('ivs');
        $data->target_id = $new_activity_id;

        $new_item_id = $DB->insert_record('ivs_settings', $data);
        $this->set_mapping('playersettings', $oldid, $new_item_id);
    }

    /**
     * Post-execution actions
     */
    protected function after_execute() {
        $this->add_related_files('mod_ivs', 'videos', null);
        $this->add_related_files('mod_ivs', 'preview', null);
    }

    /**
     * Sorting recomments after videocomments
     *
     * @throws \dml_exception
     * @throws \restore_step_exception
     */
    protected function after_restore() {
        global $DB;

        if (empty($this->videocomment_cache)) {
            return;
        }

        foreach ($this->videocomment_cache as $old_id => $data) {
            $old_parent_id = $data->parent_id;
            $new_parent_id = $this->get_mapping('videocomment', $old_parent_id);
            $data->parent_id = $new_parent_id->newitemid;
            $newitemid = $DB->insert_record('ivs_videocomment', $data);
            $annotation = annotation::retrieve_from_db($newitemid);
            $annotation->write_annotation_access();
            $data->id = $newitemid;
            $this->set_mapping('videocomment', $old_id, $newitemid);
        }
    }
}
