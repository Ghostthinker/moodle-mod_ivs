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
 * Render all annotations
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


echo $OUTPUT->header();

$offset = $page * $perpage;

$comments = \mod_ivs\annotation::retrieve_from_db_by_video($ivs->id, null, $offset, $perpage);

$commentsforpage = [];

$renderer = $PAGE->get_renderer('ivs');

echo '<div class="ivs-annotations">';

$allrenderedcomments = [];
/** @var \mod_ivs\annotation $comment */
foreach ($comments as $comment) {
    $renderable = new \mod_ivs\output\annotation_view($comment, $ivs, $cm);
    $allrenderedcomments[] = $renderable;
    echo $renderer->render($renderable);
}

echo '</div>';

$renderable = new \mod_ivs\output\annotation_download($allrenderedcomments, $ivs, $cm);
echo $renderer->render($renderable);


$totalcount = \mod_ivs\annotation::retrieve_from_db_by_video($ivs->id, null, 0, 0, true)->total;

// PAGER.
if ($totalcount > $perpage) {
    echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $PAGE->url);
}

echo $OUTPUT->footer();
