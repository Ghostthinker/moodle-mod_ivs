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
 * File for the questions to download
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
$cm = get_coursemodule_from_id('ivs', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, true, $cm);

$courseservice = new CourseService();
$rolestudent = $DB->get_record('role', array('shortname' => 'student'));
$coursestudents = $courseservice->get_course_membersby_role($course->id, $rolestudent->id);

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
$questions = $controller->match_questions_get_by_video_db_order($playerid);

foreach ($questions as $question) {

    $questiondata = $controller->get_question_summary_formated($question, $coursestudents);

    $data[] = array(
            'question_id' => $questiondata->question_id,
            'question_title' => $questiondata->question_title,
            'question_body' => $questiondata->question_body,
            'question_type' => $questiondata->question_type,
            'question_first_try' => $questiondata->question_first_try,
            'question_last_try' => $questiondata->question_last_try,
            'question_answered' => $questiondata->question_answered,
    );
}

$filename = clean_filename($course->shortname . get_string('ivs_match_question_export_summary_filename', 'ivs'));

if (class_exists ( '\core\dataformat' )) {
    \core\dataformat::download_data($filename, $dataformat, $columns, $data);
} else {
    download_as_dataformat($filename, $dataformat, $columns, $data);
}


