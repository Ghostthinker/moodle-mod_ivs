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

$question_id = optional_param('question_id', '', PARAM_ALPHANUM);
$cmid = optional_param('cmid', '', PARAM_INT);
$instance_id = optional_param('instance_id', '', PARAM_ALPHANUM);
$total_count = optional_param('total_count', '', PARAM_ALPHANUM);

$cm = get_coursemodule_from_id('ivs', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, true, $cm);

$courseService = new CourseService();
$roleStudent = $DB->get_record('role', array('shortname' => 'student'));
$course_students = $courseService->getCourseMembersbyRole($course->id, $roleStudent->id);

$controller = new MoodleMatchController();

$userAnswers = [];
foreach ($course_students as $user) {
    $userAnswers[] = $controller->match_question_answers_get_by_question_and_user_for_reporting($question_id, $user->id);
}

$questions = $controller->match_questions_get_by_video_db_order($instance_id);

$answers_data = $controller->getQuestionAnswersData(array_values($userAnswers), $questions, $cmid, $instance_id, $course_students,
        $total_count, null);

$columns = array(
        'col_1' => get_string("ivs_match_question_header_id_label", 'ivs') . $answers_data->id,
        'col_2' => get_string("ivs_match_question_header_type_label", 'ivs') . $answers_data->question_type,
        'col_3' => get_string("ivs_match_question_header_title_label", 'ivs') . $answers_data->label
);

$data[] = array(
        'col_1' => get_string("ivs_match_question_header_question_label", 'ivs') . $answers_data->question,
);

switch ($answers_data->question_type) {
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

        foreach ($answers_data->answers as $answer) {

            $answer_details = $controller->getQuestionAnswersDataSingleChoiceQuestion($answer->answer, $answer->course_user);

            $data[] = array(
                    'col_1' => $answer_details->fullname,
                    'col_2' => $answer_details->id,
                    'col_3' => $answer_details->correct,
                    'col_4' => $answer_details->selected_answer,
                    'col_5' => $answer_details->retries,
                    'col_6' => $answer_details->last,
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

        foreach ($answers_data->answers as $answer) {

            $answer_details = $controller->getQuestionAnswersDataClickQuestion($answer->answer, $answer->course_user);

            $data[] = array(
                    'col_1' => $answer_details->fullname,
                    'col_2' => $answer_details->id,
                    'col_3' => $answer_details->first,
                    'col_4' => $answer_details->retries,
                    'col_5' => $answer_details->last,
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

        foreach ($answers_data->answers as $answer) {

            $answer_details = $controller->getQuestionAnswersDataTextQuestion($answer->answer, $answer->course_user);

            $data[] = array(
                    'col_1' => $answer_details->fullname,
                    'col_2' => $answer_details->id,
                    'col_3' => $answer_details->first,
                    'col_4' => $answer_details->last,
            );
        }
        break;
}

$filename = clean_filename($course->shortname . get_string('ivs_match_question_export_question_filename', 'ivs') . $question_id);

download_as_dataformat($filename, $dataformat, $columns, $data);
