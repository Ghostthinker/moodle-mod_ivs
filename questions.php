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
 * File for questions
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

require_once('../../config.php');

define('DEFAULT_PAGE_SIZE', 10); // TODO INCREASE THIS.
define('SHOW_ALL_PAGE_SIZE', 5000);

// Pager, sort and settings.
$page = optional_param('page', 0, PARAM_INT); // Which page to show.
$perpage = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT);

$cmid = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('ivs', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

$ivs = $DB->get_record('ivs', array('id' => $cm->instance), '*', MUST_EXIST);

$context = \context_module::instance($cmid);

require_login($course, true, $cm);

// Only allow if permission is correct.
if (!has_capability('mod/ivs:access_match_reports', $context)) {
    throw new moodle_exception('accessdenied', 'admin');
}

$PAGE->set_url('/mod/ivs/questions.php', array('id' => $cm->id));

$heading = get_string('ivs:view:question_overview', 'ivs');

$PAGE->set_title($heading);
$PAGE->set_heading($course->fullname);

$PAGE->requires->css(new moodle_url($CFG->httpswwwroot . '/mod/ivs/templates/question_view.css'));
$PAGE->requires->jquery();

// Breadcrumb.
$PAGE->navbar->add(get_string('ivs:view:question_overview', 'ivs'), new moodle_url('/mod/ivs/questions.php?id=' . $cm->id));

echo $OUTPUT->header();

$controller = new \mod_ivs\MoodleMatchController();

$questions = $controller->match_questions_get_by_video_db_order($ivs->id);

$renderer = $PAGE->get_renderer('ivs');

// Pager.
$page = optional_param('page', 0, PARAM_INT); // Which page to show.
$perpage = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // How many per page.
$offset = $page * $perpage;

$courseservice = new \mod_ivs\CourseService();

$coursestudents = $courseservice->get_course_students($course->id);

$studentanswersummary = [];

echo '<div class="ivs-questions">';
echo '<h3>' . $heading . '</h3>';

?>
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item ">
            <a class="nav-link active" href="#question-summary" data-toggle="tab"
               role="tab"><?php echo get_string("ivs_match_question_answer_menu_label_elements_per_summary", 'ivs') ?></php></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#question-types" data-toggle="tab"
               role="tab"><?php echo get_string("ivs_match_question_answer_menu_label_elements_per_questions", 'ivs') ?></a>
        </li>
    </ul>

    <div class="tab-content question-listing">
        <div class="tab-pane active" id="question-summary" role="tabpanel">
            <?php
            $renderable = new \mod_ivs\output\match\question_summary($ivs, array_values($questions), $cm, $offset, $perpage,
                    $coursestudents);
            echo $renderer->render($renderable);
            ?>
        </div>

        <div class="tab-pane" id="question-types" role="tabpanel">

            <?php
            foreach ($questions as $question) {
                $renderable = new \mod_ivs\output\match\question_overview($question, $cm);
                echo $renderer->render($renderable);
            }
            ?>
            <div class="question-summary-details-download">
                <?php
                echo $OUTPUT->download_dataformat_selector(get_string("ivs_match_download_summary_details_label", 'ivs'),
                        'question_overview_details_download.php', 'download', array('player_id' => $ivs->id, 'cmid' => $cmid));
                ?>
            </div>
        </div>
    </div>

<?php

echo '</div>';

$totalcount = count($questions);

// PAGER.
if ($totalcount > $perpage) {
    echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $PAGE->url);
}

echo $OUTPUT->footer();
