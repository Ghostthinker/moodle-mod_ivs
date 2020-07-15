<?php

namespace mod_ivs;

use \mod_ivs\service;

global $CFG;

defined('MOODLE_INTERNAL') || die();
global $CFG;

class cockpit_filter_form {

    public static $FILTER_USERS = "filter_users";
    public static $FILTER_HAS_DRAWING = "filter_has_drawing";
    public static $FILTER_RATING = "filter_rating";
    public static $FILTER_ACCESS = "filter_access";

    protected $PAGE;
    protected $course;
    protected $context;
    protected $parameters;

    /**
     * cockpit_filter_form constructor.
     *
     * @param $PAGE
     * @param $course
     * @param $context
     * @param $parameters
     */
    public function __construct($PAGE, $course, $context, $raw_parameters) {
        $this->PAGE = $PAGE;
        $this->course = $course;
        $this->context = $context;

        $this->_parseParameters($raw_parameters);
    }

    function render() {

        //render user select
        $out = "";
        $user_options = $this->getUserOptions();
        $out .= $this->_createSelect(self::$FILTER_USERS, get_string("users"), $user_options);

        //render drawing select
        $has_drawing_options = $this->getHasDrawingOptions();
        $out .= $this->_createSelect(self::$FILTER_HAS_DRAWING, get_string("filter_label_has_drawing", 'ivs'),
                $has_drawing_options);

        //render rating select
        $rating_options = $this->getRatingOptions();
        $out .= $this->_createSelect(self::$FILTER_RATING, get_string("filter_label_rating", 'ivs'), $rating_options);

        //render access select
        $access_options = $this->getAccessOptions();
        $out .= $this->_createSelect(self::$FILTER_ACCESS, get_string("filter_label_access", 'ivs'), $access_options);

        //build url and hidden fields
        $url = clone $this->PAGE->url;
        $action = "$url";

        $params = $url->params();

        //set all existing GET parameters so paging will include sort etc
        foreach ($params as $key => $val) {

            //every filtering will reset the pager to 0 and the actual filter value
            if ($key == "page" || substr($key, 0, 7) == "filter_") {
                continue;
            }

            $out .= '<input type="hidden" name="' . $key . '" value="' . $val . '" />';

        }

        $out .= "<input type='submit' value='" . get_string("apply_filter", 'ivs') . "'>";
        return "<form class='annotation-filter-form' method='get' action='" . $action . "'>$out</form>";
    }

    /**
     * Parse RAW user input for query values. BE CAREFUL HERE. This is raw input
     * that gets to sql!
     *
     * @param $raw_parameters
     */
    private function _parseParameters($raw_parameters) {

        $this->_parseSimpleSelectOptionInput(self::$FILTER_RATING, $raw_parameters, $this->getRatingOptions());
        $this->_parseSimpleSelectOptionInput(self::$FILTER_ACCESS, $raw_parameters, $this->getAccessOptions());
        $this->_parseSimpleSelectOptionInput(self::$FILTER_USERS, $raw_parameters, $this->getUserOptions());

        //HAS DRAWINGS
        $this->parameters[self::$FILTER_HAS_DRAWING] = null;

        if (array_key_exists(self::$FILTER_HAS_DRAWING, $raw_parameters)) {

            if ($raw_parameters[self::$FILTER_HAS_DRAWING] == 'yes') {
                $this->parameters[self::$FILTER_HAS_DRAWING] = 'yes';
            } else if ($raw_parameters[self::$FILTER_HAS_DRAWING] == 'no') {
                $this->parameters[self::$FILTER_HAS_DRAWING] = 'no';
            }
        }

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

    /**
     * Get users by course as select options
     */
    private function getUserOptions() {
        $cs = new CourseService();
        $members = $cs->getCourseMembers($this->course->id);
        $options = array();

        foreach ($members as $member) {
            $options[$member->id] = $member->firstname . " " . $member->lastname;
        }

        return $options;
    }

    private function getHasDrawingOptions() {
        $options = array();

        $options['yes'] = get_string("yes");
        $options['no'] = get_string("no");

        return $options;
    }

    /**
     * Get rating filter options
     *
     * @return array
     */
    private function getRatingOptions() {
        $options = array();

        //$options['none'] = get_string("rating_option_none", 'ivs');
        $options['red'] = get_string("rating_option_red", 'ivs');
        $options['yellow'] = get_string("rating_option_yellow", 'ivs');
        $options['green'] = get_string("rating_option_green", 'ivs');

        return $options;
    }

    private function getAccessOptions() {
        $options = array();

        $options['private'] = get_string('ivs:acc_label:private', 'ivs');
        $options['course'] = get_string('ivs:acc_label:course', 'ivs');
        $options['member'] = get_string('ivs:acc_label:members', 'ivs');
        $options['group'] = get_string('ivs:acc_label:group', 'ivs');
        $options['role'] = get_string('ivs:acc_label:role', 'ivs');

        return $options;
    }

    /**
     * Cerate a simple select list
     *
     * @param $id
     * @param $label
     * @param $options
     * @return string
     */
    private function _createSelect($id, $label, $options) {

        $options_out = '<option>' . get_string("filter_all", 'ivs') . '</option>';

        $default_value = !empty($this->parameters[$id]) ? $this->parameters[$id] : null;

        foreach ($options as $k => $v) {

            $selected = $k == $default_value ? ' selected ' : '';

            $options_out .= '<option value="' . $k . '" ' . $selected . '>' . $v . '</option>';
        }

        return "<div class='form-item'><label for='" . $id . "'>$label</label> <select name=\"$id\" id=\"$id\">" . $options_out .
                "</select></div>";
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }

    public function getActiveFilter() {
        return $this->parameters;
    }
}
