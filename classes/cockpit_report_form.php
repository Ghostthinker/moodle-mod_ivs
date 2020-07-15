<?php

namespace mod_ivs;

use core_date;
use DateTime;

global $CFG;
defined('MOODLE_INTERNAL') || die();

class cockpit_report_form {

    protected $PAGE;
    protected $course;
    protected $context;
    protected $parameters;
    private $reportService;

    /**
     * cockpit_filter_form constructor.
     *
     * @param $PAGE
     * @param $course
     * @param $context
     * @param $raw_parameters
     * @param \mod_ivs\ReportService $reportService
     */
    public function __construct($PAGE, $course, $context, $raw_parameters, ReportService $reportService) {
        $this->PAGE = $PAGE;
        $this->course = $course;
        $this->context = $context;

        $this->reportService = $reportService;

        $this->parameters = $this->_parseParameters($raw_parameters);

    }

    function render() {

        global $DB;
        $for_out = "";
        //build url and hidden fields

        $out = "";

        //report parameters
        $report_action = optional_param('report_action', null, PARAM_RAW);
        $report_id = optional_param('report_id', null, PARAM_RAW);

        //Todo: Switch case
        if ($report_action == 'create') {

            $out .= $this->renderForm();
        } else if ($report_action == 'update') {
            if (!empty($report_id)) {

                $report = $this->reportService->retrieve_from_db($report_id);

                //TODO check - is this my report

                //build filter url
                $new_update_url = $this->PAGE->url;
                $new_update_url->param("filter_users", $report->getFilter()['filter_users']);
                $new_update_url->param("grouping", $report->getFilter()['grouping']);
                $new_update_url->param("sortkey", $report->getFilter()['sortkey']);
                $new_update_url->param("sortorder", $report->getFilter()['sortorder']);
                $new_update_url->param("report_action", "update_form");
                $new_update_url->param("report_id", $report->getId());
                redirect($new_update_url);
                return;

            }
        } else if ($report_action == 'delete') {
            if (!empty($report_id)) {

                require_sesskey();

                //access check
                $report = $this->reportService->retrieve_from_db($report_id);
                if (empty($report)) {
                    throw new \Exception("Report not found");
                }

                if ($this->reportService->access_check("delete", $report)) {

                    $this->reportService->delete_from_db($report_id);
                    $this->redirectToCockpit();
                } else {
                    throw new \Exception("Report access denied");
                }
            }
        } else if ($report_action == 'update_form') {
            if (!empty($report_id)) {
                $report = $this->reportService->retrieve_from_db($report_id);

                if (!empty($report->getFilter()['filter_users'])) {
                    $report_user = $DB->get_record('user', array('id' => $report->getFilter()['filter_users']));
                    $username = $report_user->firstname . " " . $report_user->lastname;
                    $out .= "<div>" . get_string("users") . ": " . $username . "</div>";
                }

                if (!empty($report->getFilter()['filter_has_drawing'])) {
                    $out .= "<div>" . get_string("filter_label_has_drawing", 'ivs') . ": " .
                            get_string($report->getFilter()['filter_has_drawing']) . "</div>";
                }

                if (!empty($report->getFilter()['filter_rating'])) {
                    switch ($report->getFilter()['filter_rating']) {
                        case "red":
                            $rating = 'rating_option_red';
                            break;
                        case "yellow":
                            $rating = 'rating_option_yellow';
                            break;
                        case "green":
                            $rating = 'rating_option_green';
                            break;
                    }

                    $out .= "<div>" . get_string("filter_label_rating", 'ivs') . ": " . get_string($rating, 'ivs') . "</div>";
                }

                if (!empty($report->getFilter()['filter_access'])) {
                    $out .= "<div>" . get_string("filter_label_access", 'interactive_video_suite') . ": " .
                            get_string('ivs:acc_label:' . $report->getFilter()['filter_access'], 'ivs') . "</div>";
                }

                if (!empty($report->getFilter()['grouping'])) {
                    $out .= "<div>" . get_string("block_grouping_title", 'interactive_video_suite') . ": " .
                            get_string('ivs:acc_label:group_' . $report->getFilter()['grouping'], 'ivs') . "</div>";
                }

                $out .= $this->renderForm($report);
            }

        } else {
            $out .= $this->renderListing();
        }
        return $out;

    }

    function renderForm($report = null) {
        $out = "";
        $start_date = "";
        $value_rotation = "";
        $url = clone $this->PAGE->url;
        $action = "$url";

        $params = $url->params();

        //$out .= print_r($params, TRUE);
        //$out .= print_r($report, TRUE);

        //set all existing GET parameters so paging will include sort etc
        foreach ($params as $key => $val) {

            //every filtering will reset the pager to 0 and the actual filter value
            if ($key == "page" || substr($key, 0, 7) == "report_") {
                continue;
            }

            $out .= '<input type="hidden" name="' . $key . '" value="' . $val . '" />';

        }

        $start_date = date_create();

        if ($report) {
            $out .= '<input type="hidden" name="report_id" value="' . $report->getId() . '" />';
            $value_rotation = $report->getRotation();

            date_timestamp_set($start_date, date($report->getStartDate()));
            $start_date = date_format($start_date, 'd.m.Y');
        } else {
            date_timestamp_set($start_date, time());
            $start_date = date_format($start_date, 'd.m.Y');
        }

        $options_out = "";

        $options = $this->getRotationOptions();

        foreach ($options as $option => $label) {

            $options_out .= "<option value=" . $option;

            if ($option == $value_rotation) {
                $options_out .= " selected=" . $value_rotation;
            }

            $options_out .= ">" . $label . "</option>";

        }

        $out .= "<br><div class='form-item'><label>" . get_string("report_start_date", 'ivs') .
                "</label><input type=\"text\" name=\"report_start_date\" value=" . $start_date . "></div>";
        $out .= "<div class='form-item'><label>" . get_string("report_rotation", 'ivs') .
                "</label><select name=\"report_rotation\">" . $options_out . "</select></div>";
        $out .= "<input type='submit' name='submit' value='" . get_string("save_report", 'ivs') . "'>";

        return "<form class='annotation-filter-form' method='post' action='" . $action . "'>$out</form>";
    }

    function renderListing() {

        global $USER;

        $action_create_link = clone $this->PAGE->url;
        $action_create_link->param("report_action", "create");

        $reports = $this->reportService->getReportsByCourse($this->course->id, $USER->id);

        $out = "";

        /** @var Report $report */
        foreach ($reports as $report) {

            $filter = $report->getFilter();

            $edit_url = clone $this->PAGE->url;
            $edit_url->param("report_action", "update");
            $edit_url->param("report_id", $report->getId());
            $edit_url->param("filter_users", $filter['filter_users']);

            $delete_url = clone $this->PAGE->url;
            $delete_url->param("report_action", "delete");
            $delete_url->param("report_id", $report->getId());
            $delete_url->param("filter_users", $filter['filter_users']);
            $delete_url->param("sesskey", sesskey());

            $date_string = date_format_string($report->getStartDate(), "%d %h %Y");

            $out .= get_string('block_report_title_single', 'ivs') . ", " .
                    get_string('report_rotation_' . $report->getRotation(), 'interactive_video_suite') . ", " . $date_string .
                    " <div class='form-item-report'> <a href='" . $edit_url . "'>" .
                    get_string("report_edit", 'interactive_video_suite') . "</a> <a href='" . $delete_url . "'>" .
                    get_string("report_delete", 'ivs') . "</a></div><br>";
        }
        $out .= "<br><a href=$action_create_link><input type=\"button\" value='" . get_string("create_report", 'ivs') . "' /></a>";

        $out .= "<p>" . get_string("create_report_hint", 'ivs') . "</p>";
        return $out;

    }

    /**
     * Parse RAW user input for query values. BE CAREFUL HERE. This is raw input
     * that gets to sql!
     *
     * @param $raw_parameters
     */
    private function _parseParameters($raw_parameters) {

        $parsed_parameters = array();

        if (!empty($raw_parameters['report_id'])) {

            $parsed_parameters['report_id'] = $raw_parameters['report_id'];

        }

        return $parsed_parameters;
    }

    /**
     * @return array
     */
    public function getRotationOptions() {
        $options = array(
                Report::ROTATION_DAY => get_string("report_rotation_" . Report::ROTATION_DAY, 'ivs'),
                Report::ROTATION_WEEK => get_string("report_rotation_" . Report::ROTATION_WEEK, 'ivs'),
                Report::ROTATION_MONTH => get_string("report_rotation_" . Report::ROTATION_MONTH, 'ivs'),
        );
        return $options;
    }

    /**
     * Parse the raw user input so the parameters only have allowed values
     *
     * @param $key
     * @param $raw_parameters
     * @param $options
     */
    private function _parseSimpleSelectOptionInput($key, $raw_parameters, $options) {
        $this->parameters[$key] = null;

        if (array_key_exists($key, $raw_parameters)) {
            $rating = $raw_parameters[$key];
            //only put uid in array if it is an allowed available option
            $rating_options = $options;
            if (array_key_exists($rating, $rating_options)) {
                $this->parameters[$key] = $rating;
            }
        }
    }

    public function getActiveFilter() {
        return $this->parameters;
    }

    public function processForm($courseid, $raw_user_post, $options, $user_id) {

        $start_date = $raw_user_post['report_start_date'] ? $start_date = strtotime($raw_user_post['report_start_date']) :
                $start_date = time();

        //check rotation
        $rotation_options = $this->getRotationOptions();

        $rotation = array_key_exists($raw_user_post['report_rotation'], $rotation_options) ? $raw_user_post['report_rotation'] :
                Report::ROTATION_MONTH;
        $report_id = isset($raw_user_post['report_id']) ? (int) $raw_user_post['report_id'] : null;

        //update existing
        if (empty($report_id)) {
            $this->reportService->createReport($courseid, $start_date, $rotation, $options, $user_id);
        } else {

            $report = $this->reportService->retrieve_from_db($report_id);

            //report not found
            if (empty($report)) {
                return;
            }

            if ($this->reportService->access_check("update", $report)) {

                $report->setRotation($rotation);
                $report->setStartDate($start_date);

                $this->reportService->save_to_db($report);
            }

        }
        $this->redirectToCockpit();

    }

    /**
     * Redirect to cokcpit with actrive filters but without report action and ids
     *
     * @throws \moodle_exception
     */
    function redirectToCockpit() {
        /** @var \moodle_url $new_url */
        $new_url = $this->PAGE->url;
        $new_url->remove_params(array("report_action", "report_id"));
        redirect($new_url);
    }
}
