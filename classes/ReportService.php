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
 * Class for the Report Service
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs;

use Exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Class ReportService
 */
class ReportService {

    /**
     * Create an report
     * @param int $courseid
     * @param int $startdate
     * @param int $rotation
     * @param array $filter
     * @param null|int $userid
     *
     * @return \mod_ivs\Report
     * @throws \Exception
     */
    public function create_report($courseid, $startdate, $rotation, $filter = array(), $userid = null) {

        if ($userid == null) {
            global $USER;
            $userid = $USER->id;
        }

        $report = new Report();
        $report->set_timecreated(time());
        $report->set_courseid($courseid);
        $report->set_startdate($startdate);
        $report->set_rotation($rotation);
        $report->set_filter($filter);
        $report->set_userid($userid);

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
     * @param string $op
     * @param Report $report
     * @return bool
     */
    public function access_check($op, Report $report) {

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

    /**
     * Save the report to the db
     * @param \mod_ivs\Report $report
     *
     * @return bool
     */
    public function save_to_db(Report $report) {
        global $DB;
        $report->set_timemodified(time());

        $dbrecord = $report->get_record();

        $dbrecord['filter'] = serialize($dbrecord['filter']);

        $save = false;
        if ($report->get_id() !== null) {
            $save = $DB->update_record('ivs_report', $dbrecord);
        } else {
            if ($id = $DB->insert_record('ivs_report', $dbrecord)) {
                $report->set_id($id);
                $save = true;
            }
        }

        return $save;
    }

    /**
     * Delete an report from the db
     * @param int $id
     *
     * @return mixed
     */
    public function delete_from_db($id) {
        global $DB;
        return $DB->delete_records('ivs_report', array("id" => $id));

    }

    /**
     * Get an Report from the db
     * @param int $id
     *
     * @return \mod_ivs\Report|null
     */
    public function retrieve_from_db($id) {
        global $DB;

        $dbrecord = $DB->get_record('ivs_report', array('id' => $id));

        if (is_object($dbrecord)) {
            $report = new Report($dbrecord);
            return $report;
        }
        return null;
    }

    /**
     * Get Array with Reports by course and user
     *
     * @param int $courseid
     * @param null|int $userid
     *
     * @return array
     */
    public function get_reports_by_course($courseid, $userid = null) {

        global $DB;
        $reports = array();

        $query = 'SELECT * FROM {ivs_report} WHERE course_id = ?';
        $parameters = array($courseid);

        if ($userid) {
            $query .= ' AND user_id = ?';
            $parameters[] = $userid;
        }

        $dbrecords = $DB->get_records_sql($query, $parameters, 0, 0);

        foreach ($dbrecords as $record) {
            $reports[$record->id] = new \mod_ivs\Report($record);
        }

        return $reports;
    }

    /**
     * Get Array with Reports by rotation
     * @param int $rotation
     * @param null $fromstartdate
     *
     * @return array
     */
    public function get_reports_by_rotation($rotation, $fromstartdate = null) {

        global $DB;
        $reports = array();

        $query = 'SELECT * FROM {ivs_report} WHERE rotation = ?';
        $parameters = array($rotation);

        // Check if startdate exists.
        if ($fromstartdate !== null) {
            $query .= ' AND start_date  <= ?';
            $parameters[] = $fromstartdate;
        }

        $dbrecords = $DB->get_records_sql($query, $parameters, 0, 0);

        foreach ($dbrecords as $record) {
            $reports[$record->id] = new \mod_ivs\Report($record);
        }

        return $reports;
    }

    /**
     * Get annotations from a report
     * @param \mod_ivs\Report $report
     * @param \mod_ivs\AnnotationService $annotationservice
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function get_annotations_by_report(Report $report, AnnotationService $annotationservice) {

        // Get the options and unset the limit and offset. we want all.
        $options = $report->get_filter();
        unset($options['offset']);
        unset($options['limit']);

        $context = \context_course::instance($report->get_courseid());

        // Check if the report user has the globaly skip access permission.
        $skipaccesscheck = has_capability('mod/ivs:view_any_comment', $context, $report->get_userid());

        $filtertimecreatedmin = null;

        switch ($report->get_rotation()) {
            case REPORT::ROTATION_DAY:
                $filtertimecreatedmin = strtotime("-1 day");
                break;
            case REPORT::ROTATION_WEEK:
                $filtertimecreatedmin = strtotime("-1 week");
                break;
            case REPORT::ROTATION_MONTH:
                $filtertimecreatedmin = strtotime("-1 month");
                break;

        }

        $options['filter_timecreated_min'] = (int) $filtertimecreatedmin;

        $annotations = $annotationservice->get_annotations_by_course($report->get_courseid(), $skipaccesscheck, $options, false,
                $report->get_userid());

        return $annotations;
    }

    /**
     * Prepare report for mail
     * @param \mod_ivs\Report $report
     * @param \mod_ivs\AnnotationService $annotationservice
     * @param \stdClass $userto
     *
     * @return string
     * @throws \Exception
     */
    public function render_mail_report(Report $report, AnnotationService $annotationservice, $userto) {

        global $DB, $PAGE;

        $renderer = $PAGE->get_renderer('ivs');

        $out = "";

        $annotations = $this->get_annotations_by_report($report, $annotationservice);

        $videocache = array();
        $accountcache = array();

        $grouping = $report->get_filter()['grouping'];

        if (empty($annotations)) {
            return '<p>' . get_string("cockpit_filter_empty", 'ivs') . '</p>';
        }

        /** @var \mod_ivs\annotation $comment */
        foreach ($annotations as $comment) {

            $videoid = $comment->get_videoid();
            $userid = $comment->get_userid();

            if (empty($videocache[$videoid])) {

                $coursemodule = get_coursemodule_from_instance('ivs', $comment->get_videoid(), 0, false, MUST_EXIST);
                $cm = \context_module::instance($coursemodule->id);
                $ivs = $DB->get_record('ivs', array('id' => $comment->get_videoid()), '*', MUST_EXIST);

                $videocache[$videoid] = array(
                        'cm' => $cm,
                        'course_module' => $coursemodule,
                        'ivs' => $ivs,
                );

                if ($grouping == "video") {
                    $videolink = new \moodle_url('/mod/ivs/view.php', array('id' => $videoid));
                    $out .= "<h2><a href='" . $videolink . "'>" . $ivs->name . "</a></h2>";
                }
            }

            if (empty($accountcache[$userid])) {
                $accountcache[$userid] = IvsHelper::get_user($comment->get_userid());

                if ($grouping == "user") {
                    $userlink = new \moodle_url('/user/profile.php', array('id' => $userid));
                    $out .= "<h2><a href='" . $userlink . "'>" . $accountcache[$userid]['fullname'] . "</a></h2>";
                }
            }

            $renderable = new \mod_ivs\output\annotation_report_view($comment, $videocache[$videoid]['ivs'],
                    $videocache[$videoid]['course_module'], $userto);
            $out .= $renderer->render($renderable);
        }

        return $out;

    }
}
