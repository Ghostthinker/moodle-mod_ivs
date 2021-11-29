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
 * File for the question overview deatils download
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

require_once('../../config.php');
require_once($CFG->libdir . '/dataformatlib.php');

use mod_ivs\MoodleMatchController;
use mod_ivs\CourseService;

$dataformat = optional_param('download', '', PARAM_ALPHA);

$playerid = optional_param('player_id', '', PARAM_ALPHANUM);

$cmid = optional_param('cmid', '', PARAM_INT);
$instanceid = optional_param('instance_id', '', PARAM_ALPHANUM);
$cm = get_coursemodule_from_id('ivs', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, true, $cm);

$courseservice = new CourseService();
$rolestudent = $DB->get_record('role', array('shortname' => 'student'));
$coursestudents = $courseservice->get_course_membersby_role($course->id, $rolestudent->id);

$controller = new MoodleMatchController();

$questions = $controller->match_questions_get_by_video_db_order($playerid);

$columns = array(
        'col_1' => '',
        'col_2' => '',
        'col_3' => '',
        'col_4' => '',
        'col_5' => '',
        'col_6' => '',
        'col_7' => get_string("ivs_match_question_answer_menu_label_first_single_choice_answer", 'ivs'),
        'col_8' => get_string("ivs_match_question_answer_menu_label_first_single_choice_answer", 'ivs'),
        'col_9' => get_string("ivs_match_question_answer_menu_label_click_retries", 'ivs'),
        'col_10' => get_string("ivs_match_question_summary_details_last_try", 'ivs'),
        'col_11' => get_string("ivs_match_question_summary_details_last_try", 'ivs'),

);

$data[] = array(
        'col_1' => get_string("ivs_match_question_summary_question_id", 'ivs'),
        'col_2' => get_string("ivs_match_question_summary_question_type", 'ivs'),
        'col_3' => get_string("ivs_match_question_summary_question_title", 'ivs'),
        'col_4' => get_string("ivs_match_question_summary_details_label", 'ivs'),
        'col_5' => get_string("ivs_match_question_answer_menu_label_name", 'ivs'),
        'col_6' => get_string("ivs_match_question_answer_menu_label_user_id", 'ivs'),
        'col_7' => get_string("ivs_match_question_answer_menu_label_single_choice_correct", 'ivs'),
        'col_8' => get_string("ivs_match_question_answer_menu_label_last_single_choice_selected_answer", 'ivs'),
        'col_9' => '',
        'col_10' => get_string("ivs_match_question_answer_menu_label_single_choice_correct", 'ivs'),
        'col_11' => get_string("ivs_match_question_answer_menu_label_last_single_choice_selected_answer", 'ivs'),
);

$questiontypes = [
        'single_choice_question' => get_string('ivs_match_question_summary_question_type_single', 'ivs'),
        'click_question' => get_string('ivs_match_question_summary_question_type_click', 'ivs'),
        'text_question' => get_string('ivs_match_question_summary_question_type_text', 'ivs')
];

foreach ($questions as $question) {


    foreach ($coursestudents as $coursestudent) {

        $useranswers = array();
        $useranswers[] =
                $controller->match_question_answers_get_by_question_and_user_for_reporting($question['nid'], $coursestudent->id);

        switch ($question['type']) {
            case 'single_choice_question':

                $scanswerdetail = $controller->get_question_answers_data_single_choice_question($useranswers, $coursestudent);

                $data[] = array(
                        'col_1' => $question['nid'],
                        'col_2' => $questiontypes[$question['type']],
                        'col_3' => $question['question_body'],
                        'col_4' => $question['title'],
                        'col_5' => $scanswerdetail->fullname,
                        'col_6' => $scanswerdetail->id,
                        'col_7' => $scanswerdetail->correct,
                        'col_8' => $scanswerdetail->selected_answer,
                        'col_9' => $scanswerdetail->retries,
                        'col_10' => $scanswerdetail->last,
                        'col_11' => $scanswerdetail->last_selected_answer,
                );
                break;
            case 'click_question':

                $cqanswerdetail = $controller->get_question_answers_data_click_question($useranswers, $coursestudent);
                $data[] = array(
                        'col_1' => $question['nid'],
                        'col_2' => $questiontypes[$question['type']],
                        'col_3' => $question['question_body'],
                        'col_4' => $question['title'],
                        'col_5' => $cqanswerdetail->fullname,
                        'col_6' => $cqanswerdetail->id,
                        'col_7' => $cqanswerdetail->first,
                        'col_8' => '-',
                        'col_9' => $cqanswerdetail->retries,
                        'col_10' => $cqanswerdetail->last,
                        'col_11' => '-',
                );
                break;
            case 'text_question':

                $tqanswerdetail = $controller->get_question_answers_data_text_question($useranswers, $coursestudent);
                $data[] = array(
                        'col_1' => $question['nid'],
                        'col_2' => $questiontypes[$question['type']],
                        'col_3' => $question['question_body'],
                        'col_4' => $question['title'],
                        'col_5' => $tqanswerdetail->fullname,
                        'col_6' => $tqanswerdetail->id,
                        'col_7' => '-',
                        'col_8' => $tqanswerdetail->first,
                        'col_9' => '-',
                        'col_10' => '-',
                        'col_11' => $tqanswerdetail->last,
                );
                break;
        }
    }
}

$filename = clean_filename($course->shortname . get_string('ivs_match_question_export_summary_details_filename', 'ivs'));

if (class_exists ( '\core\dataformat' )) {
    \core\dataformat::download_data($filename, $dataformat, $columns, $data);
} else {
    download_as_dataformat($filename, $dataformat, $columns, $data);
}