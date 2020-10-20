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

namespace mod_ivs;

use Exception;

defined('MOODLE_INTERNAL') || die();

//require_once('../../../config.php');

class ReportService {

    /**
     * @param $course_id
     * @param $startdate
     * @param $rotation
     * @param array $filter
     * @param null $user_id
     * @return \mod_ivs\Report
     * @throws \Exception
     */
    public function createReport($course_id, $startdate, $rotation, $filter = array(), $user_id = null) {

        if ($user_id == null) {
            global $USER;
            $user_id = $USER->id;
        }

        $report = new Report();
        $report->set_timecreated(time());
        $report->set_courseid($course_id);
        $report->set_startdate($startdate);
        $report->set_rotation($rotation);
        $report->set_filter($filter);
        $report->set_userid($user_id);

        $success = $this->save_to_db($report);

        if ($success) {
            return $report;
        } else {
            throw new Exception('cant save report');
        }

    }

    /**
     * Check if current user has access to an operation of a report
     *
     * @param $op
     * @param Report $report
     * @return bool
     */
    function access_check($op, Report $report) {

        global $USER;

        switch ($op) {
            case 'update':
            case 'delete':
                if ($report->get_userid() == $USER->id) {
                    return true;
                }
        }
        return false;
    }

    function save_to_db(Report $report) {
        global $DB;
        $report->set_timemodified(time());

        $db_record = $report->get_record();

        $db_record['filter'] = serialize($db_record['filter']);

        $save = false;
        if ($report->get_id() !== null) {
            $save = $DB->update_record('ivs_report', $db_record);
        } else {
            if ($id = $DB->insert_record('ivs_report', $db_record)) {
                $report->set_id($id);
                $save = true;
            }
        }

        return $save;
    }

    function delete_from_db($id) {
        global $DB;
        return $DB->delete_records('ivs_report', array("id" => $id));

    }

    function retrieve_from_db($id) {
        global $DB;

        $db_record = $DB->get_record('ivs_report', array('id' => $id));

        if (is_object($db_record)) {
            $report = new Report($db_record);
            return $report;
        }
        return null;
    }

    /**
     * Get Array with Reports by course and user
     *
     * @param $course_id
     * @param null $user_id
     * @return array
     */
    public function getReportsByCourse($course_id, $user_id = null) {

        global $DB;
        $reports = array();

        $query = 'SELECT * FROM {ivs_report} WHERE course_id = ?';
        $parameters = array($course_id);

        if ($user_id) {
            $query .= ' AND user_id = ?';
            $parameters[] = $user_id;
        }

        $db_records = $DB->get_records_sql($query, $parameters, 0, 0);

        foreach ($db_records as $record) {
            $reports[$record->id] = new \mod_ivs\Report($record);
        }

        return $reports;
    }

    /**
     * Get Array with Reports by rotation
     *
     * @param $rotation
     * @return array
     */
    public function getReportsByRotation($rotation, $from_start_date = null) {

        global $DB;
        $reports = array();

        $query = 'SELECT * FROM {ivs_report} WHERE rotation = ?';
        $parameters = array($rotation);

        //check if startdate exists
        if ($from_start_date !== null) {
            $query .= ' AND start_date  <= ?';
            $parameters[] = $from_start_date;
        }

        $db_records = $DB->get_records_sql($query, $parameters, 0, 0);

        foreach ($db_records as $record) {
            $reports[$record->id] = new \mod_ivs\Report($record);
        }

        return $reports;
    }

    /**
     * @param $report
     */
    public function getAnnotationsByReport(Report $report, AnnotationService $annotationService) {

        //get the options and unset the limit and offset. we want all
        $options = $report->get_filter();
        unset($options['offset']);
        unset($options['limit']);

        $context = \context_course::instance($report->get_courseid());

        //check if the report user has the globaly skip access permission
        $SKIP_ACCESS_CHECK = has_capability('mod/ivs:view_any_comment', $context, $report->get_userid());

        $filter_timecreated_min = null;

        switch ($report->get_rotation()) {
            case REPORT::ROTATION_DAY:
                $filter_timecreated_min = strtotime("-1 day");
                break;
            case REPORT::ROTATION_WEEK:
                $filter_timecreated_min = strtotime("-1 week");
                break;
            case REPORT::ROTATION_MONTH:
                $filter_timecreated_min = strtotime("-1 month");
                break;

        }

        $options['filter_timecreated_min'] = (int) $filter_timecreated_min;

        $annotations = $annotationService->get_annotations_by_course($report->get_courseid(), $SKIP_ACCESS_CHECK, $options, false,
                $report->get_userid());

        return $annotations;
    }

    public function renderMailReport(Report $report, AnnotationService $annotationService, $userTo) {

        global $DB, $PAGE;

        $renderer = $PAGE->get_renderer('ivs');

        $out = "";

        $annotations = $this->getAnnotationsByReport($report, $annotationService);

        $video_cache = array();
        $account_cache = array();

        $grouping = $report->get_filter()['grouping'];

        if (empty($annotations)) {
            return '<p>' . get_string("cockpit_filter_empty", 'ivs') . '</p>';
        }

        /** @var \mod_ivs\annotation $comment */
        foreach ($annotations as $comment) {

            $video_id = $comment->get_videoid();
            $user_id = $comment->get_userid();

            if (empty($video_cache[$video_id])) {

                $course_module = get_coursemodule_from_instance('ivs', $comment->get_videoid(), 0, false, MUST_EXIST);
                $cm = \context_module::instance($course_module->id);
                $ivs = $DB->get_record('ivs', array('id' => $comment->get_videoid()), '*', MUST_EXIST);

                $video_cache[$video_id] = array(
                        'cm' => $cm,
                        'course_module' => $course_module,
                        'ivs' => $ivs,
                );

                if ($grouping == "video") {
                    $video_link = new \moodle_url('/mod/ivs/view.php', array('id' => $video_id));
                    $out .= "<h2><a href='" . $video_link . "'>" . $ivs->name . "</a></h2>";
                }
            }

            if (empty($account_cache[$user_id])) {
                $account_cache[$user_id] = IvsHelper::get_user($comment->get_userid());

                if ($grouping == "user") {
                    $user_link = new \moodle_url('/user/profile.php', array('id' => $user_id));
                    $out .= "<h2><a href='" . $user_link . "'>" . $account_cache[$user_id]['fullname'] . "</a></h2>";
                }
            }

            $renderable = new \mod_ivs\output\annotation_report_view($comment, $video_cache[$video_id]['ivs'],
                    $video_cache[$video_id]['course_module'], $userTo);
            $out .= $renderer->render($renderable);
        }

        return $out;

    }
}
