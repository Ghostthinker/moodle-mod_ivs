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
 * Download the questions answers
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

$questionid = optional_param('question_id', '', PARAM_ALPHANUM);
$cmid = optional_param('cmid', '', PARAM_INT);
$instanceid = optional_param('instance_id', '', PARAM_ALPHANUM);
$totalcount = optional_param('total_count', '', PARAM_ALPHANUM);

$cm = get_coursemodule_from_id('ivs', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, true, $cm);

$courseservice = new CourseService();
$rolestudent = $DB->get_record('role', array('shortname' => 'student'));
$coursestudents = $courseservice->get_course_membersby_role($course->id, $rolestudent->id);

$controller = new MoodleMatchController();

$useranswers = [];
foreach ($coursestudents as $user) {
    $useranswers[] = $controller->match_question_answers_get_by_question_and_user_for_reporting($questionid, $user->id);
}

$questions = $controller->match_questions_get_by_video_db_order($instanceid);

$answersdata = $controller->get_question_answers_data(array_values($useranswers), $questions, $cmid, $instanceid, $coursestudents,
        $totalcount, null);

$columns = array(
        'col_1' => get_string("ivs_match_question_header_id_label", 'ivs') . $answersdata->id,
        'col_2' => get_string("ivs_match_question_header_type_label", 'ivs') . $answersdata->question_type,
        'col_3' => get_string("ivs_match_question_header_title_label", 'ivs') . strip_tags($answersdata->label)
);

$data[] = array(
        'col_1' => get_string("ivs_match_question_header_question_label", 'ivs') . strip_tags($answersdata->question),
);

switch ($answersdata->question_type) {
    case get_string('ivs_match_question_summary_question_type_single', 'ivs'):

        $data[] = array(
                'col_1' => get_string("ivs_match_question_answer_menu_label_name", 'ivs'),
                'col_2' => get_string("ivs_match_question_answer_menu_label_user_id", 'ivs'),
                'col_3' => get_string("ivs_match_question_answer_menu_label_first_single_choice_answer", 'ivs'),
                'col_4' => get_string("ivs_match_question_answer_menu_label_first_single_choice_answer", 'ivs'),
                'col_5' => get_string("ivs_match_question_answer_menu_label_single_choice_retries", 'ivs'),
                'col_6' => get_string("ivs_match_question_answer_menu_label_last_single_choice_answer", 'ivs'),
        );

        $data[] = array(
                'col_1' => '',
                'col_2' => '',
                'col_3' => get_string("ivs_match_question_answer_menu_label_single_choice_correct", 'ivs'),
                'col_4' => get_string("ivs_match_question_answer_menu_label_last_single_choice_selected_answer", 'ivs'),
                'col_5' => '',
                'col_6' => '',
        );

        foreach ($answersdata->answers as $answer) {

            $answerdetails = $controller->get_question_answers_data_single_choice_question($answer->answer, $answer->course_user);

            $data[] = array(
                    'col_1' => $answerdetails->fullname,
                    'col_2' => $answerdetails->id,
                    'col_3' => $answerdetails->correct,
                    'col_4' => strip_tags($answerdetails->selected_answer),
                    'col_5' => $answerdetails->retries,
                    'col_6' => strip_tags($answerdetails->last),
            );
        }

        break;
    case get_string('ivs_match_question_summary_question_type_click', 'ivs'):

        $data[] = array(
                'col_1' => get_string("ivs_match_question_answer_menu_label_name", 'ivs'),
                'col_2' => get_string("ivs_match_question_answer_menu_label_user_id", 'ivs'),
                'col_3' => get_string("ivs_match_question_answer_menu_label_first_click_answer", 'ivs'),
                'col_4' => get_string("ivs_match_question_answer_menu_label_click_retries", 'ivs'),
                'col_5' => get_string("ivs_match_question_answer_menu_label_last_click_answer", 'ivs'),
        );

        foreach ($answersdata->answers as $answer) {

            $answerdetails = $controller->get_question_answers_data_click_question($answer->answer, $answer->course_user);

            $data[] = array(
                    'col_1' => $answerdetails->fullname,
                    'col_2' => $answerdetails->id,
                    'col_3' => strip_tags($answerdetails->first),
                    'col_4' => $answerdetails->retries,
                    'col_5' => strip_tags($answerdetails->last),
            );
        }

        break;
    case get_string('ivs_match_question_summary_question_type_text', 'ivs'):

        $data[] = array(
                'col_1' => get_string("ivs_match_question_answer_menu_label_name", 'ivs'),
                'col_2' => get_string("ivs_match_question_answer_menu_label_user_id", 'ivs'),
                'col_3' => get_string("ivs_match_question_answer_menu_label_first_text_answer", 'ivs'),
                'col_4' => get_string("ivs_match_question_answer_menu_label_last_text_answer", 'ivs'),
        );

        foreach ($answersdata->answers as $answer) {

            $answerdetails = $controller->get_question_answers_data_text_question($answer->answer, $answer->course_user);

            $data[] = array(
                    'col_1' => $answerdetails->fullname,
                    'col_2' => $answerdetails->id,
                    'col_3' => strip_tags($answerdetails->first),
                    'col_4' => strip_tags($answerdetails->last),
            );
        }
        break;
}

$filename = clean_filename($course->shortname . get_string('ivs_match_question_export_question_filename', 'ivs') . $questionid);

if (class_exists ( '\core\dataformat' )) {
    \core\dataformat::download_data($filename, $dataformat, $columns, $data);
} else {
    download_as_dataformat($filename, $dataformat, $columns, $data);
}
