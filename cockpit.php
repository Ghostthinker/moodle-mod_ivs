<?php

use mod_ivs\IvsHelper;

require_once('../../config.php');
require_once('./lib.php');
require_once('./locallib.php');

define('DEFAULT_PAGE_SIZE', 20); //TODO INCREASE THIS
define('SHOW_ALL_PAGE_SIZE', 5000);

//pager, sort and settings
$page = optional_param('page', 0, PARAM_INT); // Which page to show.
$perpage = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // How many per page.
$sortkey = optional_param('sortkey', null, PARAM_RAW); // sort key
$sortorder = optional_param('sortorder', null, PARAM_RAW); // sort order
$grouping = optional_param('grouping', null, PARAM_RAW); // sort order
$contextid = optional_param('contextid', 0, PARAM_INT); // One of this or.
$courseid = optional_param('id', 0, PARAM_INT); // This are required.

global $USER;

//filters

$filter_users = optional_param('filter_users', null, PARAM_INT); // filter for drawings
$filter_has_drawing = optional_param('filter_has_drawing', null, PARAM_RAW); // filter for drawings
$filter_rating = optional_param('filter_rating', null, PARAM_RAW); // filter for drawings
$filter_access = optional_param('filter_access', null, PARAM_RAW); // filter for drawings

$PAGE->set_url('/mod/ivs/cockpit.php', array(
        'page' => $page,
        'perpage' => $perpage,
        'sortkey' => $sortkey,
        'sortorder' => $sortorder,
        'contextid' => $contextid,
        'id' => $courseid,
        'filter_users' => $filter_users,
        'filter_has_drawing' => $filter_has_drawing,
        'filter_rating' => $filter_rating,
        'filter_access' => $filter_access,
        'grouping' => $grouping,
));

if ($contextid) {
    $context = context::instance_by_id($contextid, MUST_EXIST);
    if ($context->contextlevel != CONTEXT_COURSE) {
        print_error('invalidcontext');
    }
    $course = $DB->get_record('course', array('id' => $context->instanceid), '*', MUST_EXIST);
} else {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $context = context_course::instance($course->id, MUST_EXIST);
}
require_login($course, true);
require_capability('mod/ivs:access_reports', $context);

//process heading and set base theme

$heading = get_string('cockpit_heading', 'ivs');

$PAGE->set_title($heading);
$PAGE->set_heading($heading);
//$PAGE->set_context($course_context);
$PAGE->set_pagelayout('standard');

$PAGE->requires->css(new moodle_url($CFG->httpswwwroot . '/mod/ivs/templates/annotation_view.css'));
$PAGE->requires->js(new moodle_url($CFG->httpswwwroot . '/mod/ivs/templates/annotation_view.js'));
$PAGE->requires->jquery();

//This is for admins and everyone with the course permission to manage reports
$ACCESS_REPORTS = has_capability('mod/ivs:access_reports', $context);

//register services
$ANNOTATION_SERVICE = new \mod_ivs\AnnotationService();
$REPORT_SERVICE = new \mod_ivs\ReportService();

//Get Query String to hide filter blocks while editing and creating reports
$report_action = null;
if (!empty($_GET['report_action'])) {
    $report_action = $_GET['report_action'];
}

//filter form is required by filter but alos for options in reptz form
$filter_form = new \mod_ivs\cockpit_filter_form($PAGE, $course, $context, $_GET);

if (empty($report_action)) {
    //region Sort BLOCK

    $iconasc = $OUTPUT->pix_icon(
            't/sort_asc',
            get_string('block_filter_timecreated_alt_asc', 'ivs'),
            '',
            array(
                    'class' => $sortkey == 'timecreated' && $sortorder == 'ASC' ? 'sorticon active' : 'sorticon'
            )
    );

    $icondesc = $OUTPUT->pix_icon(
            't/sort_desc',
            get_string('block_filter_timecreated_alt_desc', 'ivs'),
            '',
            array(
                    'class' => $sortkey == 'timecreated' && $sortorder == 'DESC' ? 'sorticon active' : 'sorticon'
            )
    );

    // Time create DESC
    $sort_url_timecreated_desc = $PAGE->url;
    $sort_url_timecreated_desc->param("sortkey", "timecreated");
    $sort_url_timecreated_desc->param("sortorder", "DESC");
    $sort_url_timecreated_desc_out = "<a href=\"$sort_url_timecreated_desc\">$icondesc</a>";

    // Time create ASC
    $sort_url_timecreated_asc = $PAGE->url;
    $sort_url_timecreated_asc->param("sortkey", "timecreated");
    $sort_url_timecreated_asc->param("sortorder", "ASC");
    $sort_url_timecreated_asc_out = "<a href=\"$sort_url_timecreated_asc\">$iconasc</a>";

    $iconasc = $OUTPUT->pix_icon(
            't/sort_asc',
            get_string('block_filter_timestamp_alt_asc', 'ivs'),
            '',
            array(
                    'class' => $sortkey == 'timestamp' && $sortorder == 'ASC' ? 'sorticon active' : 'sorticon'
            )
    );

    $icondesc = $OUTPUT->pix_icon(
            't/sort_desc',
            get_string('block_filter_timestamp_alt_desc', 'ivs'),
            '',
            array(
                    'class' => $sortkey == 'timestamp' && $sortorder == 'DESC' ? 'sorticon active' : 'sorticon'
            )
    );

    // Timestamp DESC
    $sort_url_timestamp_desc = $PAGE->url;
    $sort_url_timestamp_desc->param("sortkey", "timestamp");
    $sort_url_timestamp_desc->param("sortorder", "DESC");
    $sort_url_timestamp_desc_out = "<a href=\"$sort_url_timestamp_desc\">$icondesc</a>";

    // Timestamp ASC
    $sort_url_timestamp_asc = $PAGE->url;
    $sort_url_timestamp_asc->param("sortkey", "timestamp");
    $sort_url_timestamp_asc->param("sortorder", "ASC");
    $sort_url_timestamp_asc_out = "<a href=\"$sort_url_timestamp_asc\">$iconasc</a>";

    $bc = new block_contents();
    $bc->title = get_string('block_filter_sort', 'ivs');
    $bc->attributes['class'] = 'menu block';
    $bc->content = "<div>" . get_string('block_filter_timecreated', 'ivs') .
            ": $sort_url_timecreated_desc_out $sort_url_timecreated_asc_out </div>";
    $bc->content .= "<div>" . get_string('block_filter_timestamp', 'ivs') .
            ": $sort_url_timestamp_desc_out $sort_url_timestamp_asc_out </div>";
    $PAGE->blocks->add_fake_block($bc, 'side-post');

    //#endregion

    //region Filter BLOCK

    $bc = new block_contents();
    $bc->title = get_string('block_filter_title', 'ivs');
    $bc->attributes['class'] = 'menu block';
    $bc->content = $filter_form->render();
    $PAGE->blocks->add_fake_block($bc, 'side-post');
    //endregion

    //region Group BLOCK
    $bc = new block_contents();
    $bc->title = get_string('block_grouping_title', 'ivs');
    $bc->attributes['class'] = 'menu block';

    //Grouping block urls
    $url_group_none = clone $PAGE->url;
    $url_group_none->param("grouping", "none");
    $url_group_none->param("page", 0);

    $url_group_video = clone $PAGE->url;
    $url_group_video->param("grouping", "video");
    $url_group_video->param("page", 0);

    $url_group_person = clone $PAGE->url;
    $url_group_person->param("grouping", "user");
    $url_group_person->param("page", 0);

    $group_block =
            '<input type="radio" name="grouping" value="none" ' . ($grouping == 'none' || empty($grouping) ? "checked" : "") .
            ' onClick="window.location =\'' . $url_group_none . '\';" />Keine<br>';
    $group_block .= '<input type="radio" name="grouping" value="video" ' . ($grouping == 'video' ? ' checked ' : '') .
            '  onClick="window.location =\'' . $url_group_video . '\';" />Video<br>';
    $group_block .= '<input type="radio" name="grouping" value="user" ' . ($grouping == 'user' ? ' checked ' : '') .
            '  onClick="window.location =\'' . $url_group_person . '\';" />Person';

    $bc->content = '<div>' . $group_block . '</div>';
    $PAGE->blocks->add_fake_block($bc, 'side-post');

}
//endregion

//the current page and filter options
$options = array(
                'offset' => $page * $perpage,
                'limit' => $perpage,
                'sortkey' => $sortkey,
                'sortorder' => $sortorder,
                'grouping' => $grouping

        ) + $filter_form->getActiveFilter();

//region REPORT BLOCK

if ($ACCESS_REPORTS) {

    $report_form = new \mod_ivs\cockpit_report_form($PAGE, $course, $context, $_POST, $REPORT_SERVICE);

    $out_report = "";
    if (!empty($_POST)) {

        if (isset($_POST['submit'])) {


            $report_form->processForm($courseid, $_POST, $options, $USER->id);

        }
    }

    #$report_block = new annotation_report_form_block();
    $bc = new block_contents();
    $bc->title = get_string("block_report_title", 'ivs');
    $bc->attributes['class'] = 'menu block';
    $bc->content = $report_form->render() . $out_report;
    $PAGE->blocks->add_fake_block($bc, 'side-post');
}

//endregion

//breadcrumb
$PAGE->navbar->add(get_string('annotation_overview_menu_item', 'ivs'));

//This is for admins and everyone with the course permission to view any comments
$SKIP_ACCESS_CHECK = has_capability('mod/ivs:view_any_comment', $context);

echo $OUTPUT->header();

$annotations = $ANNOTATION_SERVICE->getAnnotationsByCourse($courseid, $SKIP_ACCESS_CHECK, $options);

$renderer = $PAGE->get_renderer('ivs');

$totalcountData = $ANNOTATION_SERVICE->getAnnotationsByCourse($courseid, $SKIP_ACCESS_CHECK, $options, true);
$totalcount = $totalcountData->total;

//HEADER

$summary = get_string("cockpit_summary", 'ivs', array("total" => $totalcount));

//ANNOTATIONS
echo '<div class="ivs-annotations ivs-annotations-report">';

if (empty($annotations)) {
    echo get_string("cockpit_filter_empty", 'ivs');
} else {

    $video_cache = array();
    $account_cache = array();

    /** @var \mod_ivs\annotation $comment */
    foreach ($annotations as $comment) {

        $video_id = $comment->getVideoId();
        if (empty($video_cache[$video_id])) {

            $course_module = get_coursemodule_from_instance('ivs', $video_id, 0, false, MUST_EXIST);
            $ivs = $DB->get_record('ivs', array('id' => $video_id), '*', MUST_EXIST);
            $context = \context_module::instance($course_module->id);

            $video_cache[$video_id] = array(
                    'cm' => $context,
                    'course_module' => $course_module,
                    'ivs' => $ivs,
            );

            if ($grouping == "video") {
                echo "<h2>" . $ivs->name . "</h2>";
            }
        }

        $user_id = $comment->getUserId();

        if (empty($account_cache[$user_id])) {
            $account_cache[$user_id] = IvsHelper::getUser($comment->getUserId());

            if ($grouping == "user") {
                echo "<h2>" . $account_cache[$user_id]['fullname'] . "</h2>";
            }
        }

        $renderable = new \mod_ivs\output\annotation_view($comment, $video_cache[$video_id]['ivs'],
                $video_cache[$video_id]['course_module']);
        echo $renderer->render($renderable);
    }
}
echo '</div>';
//echo '<div class="cockpit-summary">'.$summary.'</div>';
//PAGER
if ($totalcount > $perpage) {
    echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $PAGE->url);
}

echo $OUTPUT->footer();
