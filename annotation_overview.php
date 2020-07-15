<?php

require_once('../../config.php');

define('DEFAULT_PAGE_SIZE', 10); //TODO INCREASE THIS
define('SHOW_ALL_PAGE_SIZE', 5000);

//pager, sort and settings
$page = optional_param('page', 0, PARAM_INT); // Which page to show.
$perpage = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // How many per page.

$cmid = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('ivs', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

$ivs = $DB->get_record('ivs', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$PAGE->set_url('/mod/ivs/annotation_overview.php', array('id' => $cm->id));

$heading = get_string('ivs:view:comment_overview', 'ivs');

$PAGE->set_title($heading);
$PAGE->set_heading($heading);

$PAGE->requires->css(new moodle_url($CFG->httpswwwroot . '/mod/ivs/templates/annotation_view.css'));
$PAGE->requires->js(new moodle_url($CFG->httpswwwroot . '/mod/ivs/templates/annotation_view.js'));
$PAGE->requires->jquery();

//breadcrumb
/*
$coursenode = $PAGE->navigation->find($course->id, navigation_node::TYPE_COURSE);
$thingnode1 = $coursenode->add("aaa", new moodle_url('/mod/ivs/annotation_overview.php', array('id' => $cm->id)));
$thingnode = $coursenode->add("bbb", new moodle_url('/mod/ivs/annotation_overview.php', array('id' => $cm->id)));
$thingnode->make_active();
*/

echo $OUTPUT->header();

$offset = $page * $perpage;

$comments = \mod_ivs\annotation::retrieve_from_db_by_video($ivs->id, null, $offset, $perpage);

$comments_for_page = [];

$renderer = $PAGE->get_renderer('ivs');

echo '<div class="ivs-annotations">';

/** @var \mod_ivs\annotation $comment */
foreach ($comments as $comment) {

    $renderable = new \mod_ivs\output\annotation_view($comment, $ivs, $cm);
    echo $renderer->render($renderable);
}

echo '</div>';

$totalcount = \mod_ivs\annotation::retrieve_from_db_by_video($ivs->id, null, 0, 0, true)->total;

//PAGER,
if ($totalcount > $perpage) {
    echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $PAGE->url);
}

echo $OUTPUT->footer();
