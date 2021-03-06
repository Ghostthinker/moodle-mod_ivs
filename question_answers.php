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
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

require_once('../../config.php');

define('DEFAULT_PAGE_SIZE', 10); //TODO INCREASE THIS

//pager, sort and settings
$page = optional_param('page', 0, PARAM_INT); // Which page to show.
$perpage = required_param('perpage', PARAM_INT);

$qid = required_param('qid', PARAM_INT);
$cmid = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('ivs', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, true, $cm);

$ivs = $DB->get_record('ivs', array('id' => $cm->instance), '*', MUST_EXIST);

$context = \context_module::instance($cmid);

require_login($course, true, $cm);

// Only allow if permission is correct.
if (!has_capability('mod/ivs:access_match_reports', $context)) {
    print_error('accessdenied', 'admin');
}

$PAGE->set_url('/mod/ivs/question_answers.php',
        array('id' => $cm->id, 'qid' => $qid, 'vid' => $cm->instance, 'perpage' => $perpage));

$heading = get_string('ivs:view:question_overview', 'ivs');

$PAGE->set_title($heading);
$PAGE->set_heading($course->fullname);

$PAGE->requires->css(new moodle_url($CFG->httpswwwroot . '/mod/ivs/templates/question_answers_view.css'));
$PAGE->requires->jquery();

$controller = new \mod_ivs\MoodleMatchController();
$courseService = new \mod_ivs\CourseService();
$roleStudent = $DB->get_record('role', array('shortname' => 'student'));
$courseStudents = $courseService->get_course_membersby_role($course->id, $roleStudent->id);

// Breadcrumb.
$PAGE->navbar->add(get_string('ivs:view:question_overview', 'ivs'), new moodle_url('/mod/ivs/questions.php?id=' . $cm->id));
$PAGE->navbar->add($controller->get_match_question_title($controller->match_question_get_db($qid)),
        new moodle_url('/mod/ivs/question_answers.php?id=' . $cm->id . '&vid=' . $cm->instance . '&qid=' . $qid . '&perpage=' .
                $perpage));

echo $OUTPUT->header();

$offset = $page * $perpage;

$renderer = $PAGE->get_renderer('ivs');

echo '<div class="ivs-questions">';
echo '<h3>' . $heading . '</h3>';

$userAnswers = [];
foreach ($courseStudents as $user) {
    $userAnswers[] = $controller->match_question_answers_get_by_question_and_user_for_reporting($qid, $user->id);
}

$totalcount = count($courseStudents);

$questions = $controller->match_questions_get_by_video_db($cm->instance);
?>
    <div class="tab-content question-listing">
        <div class="tab-pane active" id="question-summary" role="tabpanel">
            <?php
            $renderable =
                    new \mod_ivs\output\match\question_answers_view(array_values($userAnswers), $questions, $cm->id, $cm->instance,
                            $courseStudents, $totalcount);
            echo $renderer->render($renderable);
            ?>
        </div>
    </div>
<?php

// PAGER.
if ($totalcount > $perpage) {
    echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $PAGE->url);
}

echo $OUTPUT->footer();
