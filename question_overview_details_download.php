<?php
/**
 * Created by PhpStorm.
 * User: Ghostthinker
 * Date: 03.12.2018
 * Time: 12:45
 */

require_once('../../config.php');
require_once($CFG->libdir . '/dataformatlib.php');

use mod_ivs\MoodleMatchController;
use mod_ivs\CourseService;

$dataformat = optional_param('download', '', PARAM_ALPHA);

$player_id = optional_param('player_id', '', PARAM_ALPHANUM);

$cmid = optional_param('cmid', '', PARAM_INT);
$instance_id = optional_param('instance_id', '', PARAM_ALPHANUM);
$cm = get_coursemodule_from_id('ivs', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, true, $cm);

$courseService = new CourseService();
$roleStudent = $DB->get_record('role', array('shortname' => 'student'));
$course_students = $courseService->getCourseMembersbyRole($course->id, $roleStudent->id);

$controller = new MoodleMatchController();

$questions = $controller->match_questions_get_by_video_db_order($player_id);

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

$question_types = [
        'single_choice_question' => get_string('ivs_match_question_summary_question_type_single', 'ivs'),
        'click_question' => get_string('ivs_match_question_summary_question_type_click', 'ivs'),
        'text_question' => get_string('ivs_match_question_summary_question_type_text', 'ivs')
];

foreach ($questions as $question) {


    foreach ($course_students as $course_student) {

        $userAnswers = array();
        $userAnswers[] =
                $controller->match_question_answers_get_by_question_and_user_for_reporting($question['nid'], $course_student->id);

        switch ($question['type']) {
            case 'single_choice_question':

                $sc_answer_detail = $controller->getQuestionAnswersDataSingleChoiceQuestion($userAnswers, $course_student);

                $data[] = array(
                        'col_1' => $question['nid'],
                        'col_2' => $question_types[$question['type']],
                        'col_3' => $question['question_body'],
                        'col_4' => $question['title'],
                        'col_5' => $sc_answer_detail->fullname,
                        'col_6' => $sc_answer_detail->id,
                        'col_7' => $sc_answer_detail->correct,
                        'col_8' => $sc_answer_detail->selected_answer,
                        'col_9' => $sc_answer_detail->retries,
                        'col_10' => $sc_answer_detail->last,
                        'col_11' => $sc_answer_detail->last_selected_answer,
                );
                break;
            case 'click_question':

                $cq_answer_detail = $controller->getQuestionAnswersDataClickQuestion($userAnswers, $course_student);
                $data[] = array(
                        'col_1' => $question['nid'],
                        'col_2' => $question_types[$question['type']],
                        'col_3' => $question['question_body'],
                        'col_4' => $question['title'],
                        'col_5' => $cq_answer_detail->fullname,
                        'col_6' => $cq_answer_detail->id,
                        'col_7' => $cq_answer_detail->first,
                        'col_8' => '-',
                        'col_9' => $cq_answer_detail->retries,
                        'col_10' => $cq_answer_detail->last,
                        'col_11' => '-',
                );
                break;
            case 'text_question':

                $tq_answer_detail = $controller->getQuestionAnswersDataTextQuestion($userAnswers, $course_student);
                $data[] = array(
                        'col_1' => $question['nid'],
                        'col_2' => $question_types[$question['type']],
                        'col_3' => $question['question_body'],
                        'col_4' => $question['title'],
                        'col_5' => $tq_answer_detail->fullname,
                        'col_6' => $tq_answer_detail->id,
                        'col_7' => '-',
                        'col_8' => $tq_answer_detail->first,
                        'col_9' => '-',
                        'col_10' => '-',
                        'col_11' => $tq_answer_detail->last,
                );
                break;
        }
    }
}

$filename = clean_filename($course->shortname . get_string('ivs_match_question_export_summary_details_filename', 'ivs'));

download_as_dataformat($filename, $dataformat, $columns, $data);
