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
 * This file loads all components to display the cockpit
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

use mod_ivs\IvsHelper;

require_once('../../config.php');
require_once('./lib.php');
require_once('./locallib.php');

define('DEFAULT_PAGE_SIZE', 20); // TODO INCREASE THIS.
define('SHOW_ALL_PAGE_SIZE', 5000);

// Pager, sort and settings.
$page = optional_param('page', 0, PARAM_INT); // Which page to show.
$perpage = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // How many per page.
$sortkey = optional_param('sortkey', null, PARAM_RAW); // Sort key.
$sortorder = optional_param('sortorder', null, PARAM_RAW); // Sort order.
$grouping = optional_param('grouping', null, PARAM_RAW); // Sort order.
$contextid = optional_param('contextid', 0, PARAM_INT); // One of this or.
$courseid = optional_param('id', 0, PARAM_INT); // This are required.

global $USER;

// Filters.

$filterusers = optional_param('filter_users', null, PARAM_INT); // Filter for drawings.
$filterhasdrawing = optional_param('filter_has_drawing', null, PARAM_RAW); // Filter for drawings.
$filterrating = optional_param('filter_rating', null, PARAM_RAW); // Filter for drawings.
$filteraccess = optional_param('filter_access', null, PARAM_RAW); // Filter for drawings.

$PAGE->set_url('/mod/ivs/cockpit.php', array(
        'page' => $page,
        'perpage' => $perpage,
        'sortkey' => $sortkey,
        'sortorder' => $sortorder,
        'contextid' => $contextid,
        'id' => $courseid,
        'filter_users' => $filterusers,
        'filter_has_drawing' => $filterhasdrawing,
        'filter_rating' => $filterrating,
        'filter_access' => $filteraccess,
        'grouping' => $grouping,
));

if ($contextid) {
    $context = context::instance_by_id($contextid, MUST_EXIST);
    if ($context->contextlevel != CONTEXT_COURSE) {
        throw new moodle_exception('invalidcontext');
    }
    $course = $DB->get_record('course', array('id' => $context->instanceid), '*', MUST_EXIST);
} else {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $context = context_course::instance($course->id, MUST_EXIST);
}
require_login($course, true);
require_capability('mod/ivs:access_reports', $context);

// Process heading and set base theme.

$heading = get_string('cockpit_heading', 'ivs');

$PAGE->set_title($heading);
$PAGE->set_heading($heading);
$PAGE->set_pagelayout('standard');

$PAGE->requires->css(new moodle_url($CFG->httpswwwroot . '/mod/ivs/templates/annotation_view.css'));
$PAGE->requires->js(new moodle_url($CFG->httpswwwroot . '/mod/ivs/templates/annotation_view.js'));
$PAGE->requires->jquery();

// This is for admins and everyone with the course permission to manage reports.
$accessreports = has_capability('mod/ivs:access_reports', $context);

// Register services.
$annotationservice = new \mod_ivs\AnnotationService();
$reportservice = new \mod_ivs\ReportService();

// Get Query String to hide filter blocks while editing and creating reports.
$reportaction = optional_param('report_action', null, PARAM_ALPHANUMEXT);


// Filter form is required by filter but alos for options in reptz form.

$rawparameter = [
  'page' => optional_param('page', '0', PARAM_ALPHANUM),
  'perpage' => optional_param('perpage', '20', PARAM_ALPHANUM),
  'sortkey' => optional_param('sortkey', 'timecreated', PARAM_ALPHANUM),
  'sortorder' => optional_param('sortorder', 'DESC', PARAM_ALPHANUM),
  'contextid' => optional_param('contextid', '0', PARAM_ALPHANUM),
  'id' => optional_param('id', '0', PARAM_ALPHANUM),
  'filter_users' => optional_param('filter_users', '0', PARAM_ALPHANUM),
  'filter_has_drawing' => optional_param('filter_has_drawing', '',
    PARAM_ALPHANUM),
  'filter_rating' => optional_param('filter_rating', '', PARAM_ALPHANUM),
  'filter_access' => optional_param('filter_access', '', PARAM_ALPHANUM),
  'grouping' => optional_param('grouping', '', PARAM_ALPHANUM),
];

$filterform = new \mod_ivs\cockpit_filter_form($PAGE, $course, $context, $rawparameter);

if (empty($reportaction)) {
    // Region Sort BLOCK.

    $iconasc = $OUTPUT->pix_icon(
            't/sort_asc',
            get_string('block_filter_timecreated_alt_asc', 'ivs'),
            '',
            array(
                    'class' => $sortkey == 'timecreated' && $sortorder == 'ASC' ? 'ivs-sorticon active' : 'sorticon'
            )
    );

    $icondesc = $OUTPUT->pix_icon(
            't/sort_desc',
            get_string('block_filter_timecreated_alt_desc', 'ivs'),
            '',
            array(
                    'class' => $sortkey == 'timecreated' && $sortorder == 'DESC' ? 'ivs-sorticon active' : 'sorticon'
            )
    );

    // Time create DESC.
    $sorturltimecreateddesc = $PAGE->url;
    $sorturltimecreateddesc->param("sortkey", "timecreated");
    $sorturltimecreateddesc->param("sortorder", "DESC");
    $sorturltimecreateddescout = "<a href=\"$sorturltimecreateddesc\">$icondesc</a>";

    // Time create ASC.
    $sorturltimecreatedasc = $PAGE->url;
    $sorturltimecreatedasc->param("sortkey", "timecreated");
    $sorturltimecreatedasc->param("sortorder", "ASC");
    $sorturltimecreatedascout = "<a href=\"$sorturltimecreatedasc\">$iconasc</a>";

    $iconasc = $OUTPUT->pix_icon(
            't/sort_asc',
            get_string('block_filter_timestamp_alt_asc', 'ivs'),
            '',
            array(
                    'class' => $sortkey == 'timestamp' && $sortorder == 'ASC' ? 'ivs-sorticon active' : 'sorticon'
            )
    );

    $icondesc = $OUTPUT->pix_icon(
            't/sort_desc',
            get_string('block_filter_timestamp_alt_desc', 'ivs'),
            '',
            array(
                    'class' => $sortkey == 'timestamp' && $sortorder == 'DESC' ? 'ivs-sorticon active' : 'sorticon'
            )
    );

    // Timestamp DESC.
    $sorturltimestampdesc = $PAGE->url;
    $sorturltimestampdesc->param("sortkey", "timestamp");
    $sorturltimestampdesc->param("sortorder", "DESC");
    $sorturltimestampdescout = "<a href=\"$sorturltimestampdesc\">$icondesc</a>";

    // Timestamp ASC.
    $sorturltimestampasc = $PAGE->url;
    $sorturltimestampasc->param("sortkey", "timestamp");
    $sorturltimestampasc->param("sortorder", "ASC");
    $sorturltimestampascout = "<a href=\"$sorturltimestampasc\">$iconasc</a>";

    $bc = new block_contents();
    $bc->title = get_string('block_filter_sort', 'ivs');
    $bc->attributes['class'] = 'menu block';
    $bc->content = "<div>" . get_string('block_filter_timecreated', 'ivs') .
            ": $sorturltimecreateddescout $sorturltimecreatedascout </div>";
    $bc->content .= "<div>" . get_string('block_filter_timestamp', 'ivs') .
            ": $sorturltimestampdescout $sorturltimestampascout </div>";
    $PAGE->blocks->add_fake_block($bc, 'side-post');

    // Endregion.

    // Region Filter BLOCK.

    $bc = new block_contents();
    $bc->title = get_string('block_filter_title', 'ivs');
    $bc->attributes['class'] = 'menu block';
    $bc->content = $filterform->render();
    $PAGE->blocks->add_fake_block($bc, 'side-post');
    // Endregion.

    // Region Group BLOCK.
    $bc = new block_contents();
    $bc->title = get_string('block_grouping_title', 'ivs');
    $bc->attributes['class'] = 'menu block';

    // Grouping block urls.
    $urlgroupnone = clone $PAGE->url;
    $urlgroupnone->param("grouping", "none");
    $urlgroupnone->param("page", 0);

    $urlgroupvideo = clone $PAGE->url;
    $urlgroupvideo->param("grouping", "video");
    $urlgroupvideo->param("page", 0);

    $urlgroupperson = clone $PAGE->url;
    $urlgroupperson->param("grouping", "user");
    $urlgroupperson->param("page", 0);

    $groupblock =
            '<input type="radio" name="grouping" value="none" ' . ($grouping == 'none' || empty($grouping) ? "checked" : "") .
            ' onClick="window.location =\'' . $urlgroupnone . '\';" />Keine<br>';
    $groupblock .= '<input type="radio" name="grouping" value="video" ' . ($grouping == 'video' ? ' checked ' : '') .
            '  onClick="window.location =\'' . $urlgroupvideo . '\';" />Video<br>';
    $groupblock .= '<input type="radio" name="grouping" value="user" ' . ($grouping == 'user' ? ' checked ' : '') .
            '  onClick="window.location =\'' . $urlgroupperson . '\';" />Person';

    $bc->content = '<div>' . $groupblock . '</div>';
    $PAGE->blocks->add_fake_block($bc, 'side-post');

}
// Endregion.

// Ehe current page and filter options.
$options = array(
                'offset' => $page * $perpage,
                'limit' => $perpage,
                'sortkey' => $sortkey,
                'sortorder' => $sortorder,
                'grouping' => $grouping

        ) + $filterform->get_active_filter();

// Region REPORT BLOCK.

if ($accessreports) {

    $rawpostparameter = [
      'perpage' => optional_param('grouping', '20', PARAM_ALPHANUM),
      'sortkey' => optional_param('sortkey', '', PARAM_ALPHANUM),
      'sortoder' => optional_param('sortoder', '', PARAM_ALPHANUM),
      'contextid' => optional_param('contextid', '', PARAM_ALPHANUM),
      'id' => optional_param('id', '', PARAM_ALPHANUM),
      'filter_users' => optional_param('filter_users', '', PARAM_ALPHANUM),
      'filter_has_drawing' => optional_param('filter_has_drawing', '',
        PARAM_ALPHANUM),
      'filter_rating' => optional_param('filter_rating', '', PARAM_ALPHANUM),
      'filter_access' => optional_param('filter_access', '', PARAM_ALPHANUM),
      'grouping' => optional_param('grouping', '', PARAM_ALPHANUM),
      'report_start_date' => optional_param('report_start_date', '',
        PARAM_TEXT),
      'report_rotation' => optional_param('report_rotation', '',
        PARAM_ALPHANUM),
      'submit' => optional_param('submit', '', PARAM_ALPHANUM),
    ];

    $reportform = new \mod_ivs\cockpit_report_form($PAGE, $course, $context, $rawpostparameter, $reportservice);

    $outreport = "";

    if (!empty($rawpostparameter['submit'])) {

        $reportform->process_form($courseid, $rawpostparameter, $options, $USER->id);
    }

    $bc = new block_contents();
    $bc->title = get_string("block_report_title", 'ivs');
    $bc->attributes['class'] = 'menu block';
    $bc->content = $reportform->render() . $outreport;
    $PAGE->blocks->add_fake_block($bc, 'side-post');
}

// Endregion.

// Breadcrumb.
$PAGE->navbar->add(get_string('annotation_overview_menu_item', 'ivs'));

// This is for admins and everyone with the course permission to view any comments.
$skipaccesscheck = has_capability('mod/ivs:view_any_comment', $context);

echo $OUTPUT->header();

$annotations = $annotationservice->get_annotations_by_course($courseid, $skipaccesscheck, $options);

$renderer = $PAGE->get_renderer('ivs');

$totalcountdata = $annotationservice->get_annotations_by_course($courseid, $skipaccesscheck, $options, true);
$totalcount = $totalcountdata->total;

// HEADER.

$summary = get_string("cockpit_summary", 'ivs', array("total" => $totalcount));

// ANNOTATIONS.
echo '<div class="ivs-annotations ivs-annotations-report">';

if (empty($annotations)) {
    echo get_string("cockpit_filter_empty", 'ivs');
} else {

    $videocache = array();
    $accountcache = array();

    /** @var \mod_ivs\annotation $comment */
    foreach ($annotations as $comment) {

        $videoid = $comment->get_videoid();
        if (empty($videocache[$videoid])) {

            $coursemodule = get_coursemodule_from_instance('ivs', $videoid, 0, false, MUST_EXIST);
            $ivs = $DB->get_record('ivs', array('id' => $videoid), '*', MUST_EXIST);
            $context = \context_module::instance($coursemodule->id);

            $videocache[$videoid] = array(
                    'cm' => $context,
                    'course_module' => $coursemodule,
                    'ivs' => $ivs,
            );

            if ($grouping == "video") {
                echo "<h2>" . $ivs->name . "</h2>";
            }
        }

        $userid = $comment->get_userid();

        if (empty($accountcache[$userid])) {
            $accountcache[$userid] = IvsHelper::get_user($comment->get_userid());

            if ($grouping == "user") {
                echo "<h2>" . $accountcache[$userid]['fullname'] . "</h2>";
            }
        }

        $renderable = new \mod_ivs\output\annotation_view($comment, $videocache[$videoid]['ivs'],
                $videocache[$videoid]['course_module']);
        echo $renderer->render($renderable);
    }
}
echo '</div>';

// PAGER.
if ($totalcount > $perpage) {
    echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $PAGE->url);
}

echo $OUTPUT->footer();
