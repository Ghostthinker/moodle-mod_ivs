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
$cm = get_coursemodule_from_id('ivs', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, true, $cm);

$courseService = new CourseService();
$roleStudent = $DB->get_record('role', array('shortname' => 'student'));
$course_students = $courseService->getCourseMembersbyRole($course->id, $roleStudent->id);

$columns = array(
        'question_id' => get_string("ivs_match_question_summary_question_id", 'ivs'),
        'question_title' => get_string("ivs_match_question_summary_question_title", 'ivs'),
        'question_body' => get_string("ivs_match_question_summary_question_body", 'ivs'),
        'question_type' => get_string("ivs_match_question_summary_question_type", 'ivs'),
        'question_first_try' => get_string("ivs_match_question_summary_question_first_try", 'ivs'),
        'question_last_try' => get_string("ivs_match_question_summary_question_last_try", 'ivs'),
        'question_answered' => get_string("ivs_match_question_summary_question_answered", 'ivs')
);

$controller = new MoodleMatchController();
$questions = $controller->match_questions_get_by_video_db_order($player_id);

foreach ($questions as $question) {

    $question_data = $controller->getQuestionSummaryFormated($question, $course_students);

    $data[] = array(
            'question_id' => $question_data->question_id,
            'question_title' => $question_data->question_title,
            'question_body' => $question_data->question_body,
            'question_type' => $question_data->question_type,
            'question_first_try' => $question_data->question_first_try,
            'question_last_try' => $question_data->question_last_try,
            'question_answered' => $question_data->question_answered,
    );
}

$filename = clean_filename($course->shortname . get_string('ivs_match_question_export_summary_filename', 'ivs'));

download_as_dataformat($filename, $dataformat, $columns, $data);
