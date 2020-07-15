<?php

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
        $report->setTimecreated(time());
        $report->setCourseId($course_id);
        $report->setStartDate($startdate);
        $report->setRotation($rotation);
        $report->setFilter($filter);
        $report->setUserId($user_id);

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
                if ($report->getUserId() == $USER->id) {
                    return true;
                }
        }
        return false;
    }

    function save_to_db(Report $report) {
        global $DB;
        $report->setTimemodified(time());

        $db_record = $report->getRecord();

        $db_record['filter'] = serialize($db_record['filter']);

        $save = false;
        if ($report->getId() !== null) {
            $save = $DB->update_record('ivs_report', $db_record);
        } else {
            if ($id = $DB->insert_record('ivs_report', $db_record)) {
                $report->setId($id);
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
        $options = $report->getFilter();
        unset($options['offset']);
        unset($options['limit']);

        $context = \context_course::instance($report->getCourseId());

        //check if the report user has the globaly skip access permission
        $SKIP_ACCESS_CHECK = has_capability('mod/ivs:view_any_comment', $context, $report->getUserId());

        $filter_timecreated_min = null;

        switch ($report->getRotation()) {
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

        $annotations = $annotationService->getAnnotationsByCourse($report->getCourseId(), $SKIP_ACCESS_CHECK, $options, false,
                $report->getUserId());

        return $annotations;
    }

    public function renderMailReport(Report $report, AnnotationService $annotationService, $userTo) {

        global $DB, $PAGE;

        $renderer = $PAGE->get_renderer('ivs');

        $out = "";

        $annotations = $this->getAnnotationsByReport($report, $annotationService);

        $video_cache = array();
        $account_cache = array();

        $grouping = $report->getFilter()['grouping'];

        if (empty($annotations)) {
            return '<p>' . get_string("cockpit_filter_empty", 'ivs') . '</p>';
        }

        /** @var \mod_ivs\annotation $comment */
        foreach ($annotations as $comment) {

            $video_id = $comment->getVideoId();
            $user_id = $comment->getUserId();

            if (empty($video_cache[$video_id])) {

                $course_module = get_coursemodule_from_instance('ivs', $comment->getVideoId(), 0, false, MUST_EXIST);
                $cm = \context_module::instance($course_module->id);
                $ivs = $DB->get_record('ivs', array('id' => $comment->getVideoId()), '*', MUST_EXIST);

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
                $account_cache[$user_id] = IvsHelper::getUser($comment->getUserId());

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
