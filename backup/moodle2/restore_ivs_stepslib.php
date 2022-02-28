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
 * This class process all elements to restore
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

use mod_ivs\annotation;

/**
 * Class restore_ivs_activity_structure_step
 */
class restore_ivs_activity_structure_step extends restore_activity_structure_step {

    /**
     * @var mixed
     */
    protected $videocommentcache;

    /**
     * Defines structure of path elements to be processed during the restore
     *
     * @return array of {@see restore_path_element}
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

    /**
     * Process a videocomment
     * @param \stdClass $data
     */
    protected function process_ivs_videocomment($data) {
        global $DB;
        $data = (object) $data;

        // Do not return. if user_id = null -> anonymization.
        if (empty($data->user_id)) {
            $userid = null;
        } else {
            $userid = $data->user_id;
        }

        $videocommentssetting = $this->get_setting_value('include_videocomments');

        $newcourseid = $this->get_courseid();
        $newcoursecontext = context_course::instance($newcourseid);

        $userisinnewcourse = is_enrolled($newcoursecontext, $userid, '', true);

        $restore = false;

        if ($userisinnewcourse || empty($userid)) {

            switch ($videocommentssetting) {
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

                $newactivityid = $this->get_new_parentid('ivs');
                $data->video_id = $newactivityid;

                if (!empty($data->parent_id)) {
                    $this->videocommentcache[$oldid] = $data;
                    return;
                }

                $newitemid = $DB->insert_record('ivs_videocomment', $data);
                $annotation = annotation::retrieve_from_db($newitemid);
                $annotation->write_annotation_access();

                $this->set_mapping('videocomment', $oldid, $newitemid, true);
            }
        }
    }

    /**
     * Process matchquestions
     * @param \stdClass $data
     */
    protected function process_ivs_matchquestion($data) {
        global $DB;
        $data = (object) $data;

        $oldid = $data->id;

        $newactivityid = $this->get_new_parentid('ivs');
        $data->video_id = $newactivityid;

        $newitemid = $DB->insert_record('ivs_matchquestion', $data);

        $this->set_mapping('matchquestion', $oldid, $newitemid);
    }

    /**
     * Process a matchtake
     * @param \stdClass $data
     */
    protected function process_ivs_matchtake($data) {
        global $DB;
        $data = (object) $data;

        if (empty($data->user_id)) {
            return;
        }

        $userid = $data->user_id;

        $matchanswerssetting = $this->get_setting_value('include_match');

        $newcourseid = $this->get_courseid();
        $newcoursecontext = context_course::instance($newcourseid);
        $userisinnewcourse = is_enrolled($newcoursecontext, $userid, '', true);

        // Is user in new course enrolled.
        if ($userisinnewcourse) {
            // Is match answer setting enabled.
            if ($matchanswerssetting) {
                $oldid = $data->id;

                $newactivityid = $this->get_new_parentid('ivs');
                $data->video_id = $newactivityid;

                $data->context_id = $newactivityid;

                $newitemid = $DB->insert_record('ivs_matchtake', $data);
                $this->set_mapping('matchtake', $oldid, $newitemid);
            }
        }
    }

    /**
     * Process match answers
     * @param \stdClass $data
     */
    protected function process_ivs_matchanswer($data) {
        global $DB;
        $data = (object) $data;

        if (empty($data->user_id)) {
            return;
        }

        $userid = $data->user_id;

        $matchanswerssetting = $this->get_setting_value('include_match');

        $newcourseid = $this->get_courseid();
        $newcoursecontext = context_course::instance($newcourseid);
        $userisinnewcourse = is_enrolled($newcoursecontext, $userid, '', true);

        // Is user in new course enrolled.
        if ($userisinnewcourse) {
            // Is match answer setting enabled.
            if ($matchanswerssetting) {
                $oldquestionid = $data->question_id;
                $newquestionid = $this->get_mapping('matchquestion', $oldquestionid);
                $data->question_id = $newquestionid->newitemid;

                $oldtakeid = $data->take_id;
                $newtakeid = $this->get_mapping('matchtake', $oldtakeid);
                $data->take_id = $newtakeid->newitemid;

                $DB->insert_record('ivs_matchanswer', $data);
            }
        }
    }

    /**
     * Process all ivs settings
     * @param \stdClass $data
     */
    protected function process_ivs_settings($data) {
        global $DB;
        $data = (object) $data;

        if (empty($data->target_id)) {
            return;
        }

        $oldid = $data->id;

        $newactivityid = $this->get_new_parentid('ivs');
        $data->target_id = $newactivityid;

        $newitemid = $DB->insert_record('ivs_settings', $data);
        $this->set_mapping('playersettings', $oldid, $newitemid);
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

        if (!empty($this->videocommentcache)) {

            foreach ($this->videocommentcache as $oldid => $data) {
                $oldparentid = $data->parent_id;
                $newparentid = $this->get_mapping('videocomment', $oldparentid);
                $data->parent_id = isset($newparentid->newitemid) ? $newparentid->newitemid : null;
                $newitemid = $DB->insert_record('ivs_videocomment', $data);
                $annotation = annotation::retrieve_from_db($newitemid);
                $annotation->write_annotation_access();
                $data->id = $newitemid;
                $this->set_mapping('videocomment', $oldid, $newitemid, true);
            }
        }
        $this->add_related_files('mod_ivs', 'audio_annotation', 'videocomment');
    }
}
