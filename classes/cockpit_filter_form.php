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

use \mod_ivs\service;

global $CFG;

defined('MOODLE_INTERNAL') || die();
global $CFG;

class cockpit_filter_form
{

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
    public function __construct($PAGE, $course, $context, $rawparameters)
    {
        $this->PAGE = $PAGE;
        $this->course = $course;
        $this->context = $context;

        $this->_parse_parameters($rawparameters);
    }

    function render()
    {

        // Render user select.
        $out = "";
        $useroptions = $this->get_user_options();
        $out .= $this->_create_select(self::$FILTER_USERS, get_string("users"), $useroptions);

        // Render drawing select.
        $hasdrawingoptions = $this->get_has_drawing_options();
        $out .= $this->_create_select(
            self::$FILTER_HAS_DRAWING,
            get_string("filter_label_has_drawing", 'ivs'),
            $hasdrawingoptions
        );

        // Render rating select.
        $ratingoptions = $this->get_rating_options();
        $out .= $this->_create_select(self::$FILTER_RATING, get_string("filter_label_rating", 'ivs'), $ratingoptions);

        // Render access select.
        $accessoptions = $this->get_access_options();
        $out .= $this->_create_select(self::$FILTER_ACCESS, get_string("filter_label_access", 'ivs'), $accessoptions);

        // Build url and hidden fields.
        $url = clone $this->PAGE->url;
        $action = "$url";

        $params = $url->params();

        // Set all existing GET parameters so paging will include sort etc.
        foreach ($params as $key => $val) {
            // Every filtering will reset the pager to 0 and the actual filter value.
            if ($key == "page" || substr($key, 0, 7) == "filter_") {
                continue;
            }

            $out .= '<input type="hidden" name="' . $key . '" value="' . $val . '" />';
        }

        $out .= "<input type='submit' value='" . get_string("apply_filter", 'ivs') . "'>";
        return "<form class='ivs-annotation-filter-form' method='get' action='" . $action . "'>$out</form>";
    }

    /**
     * Parse RAW user input for query values. BE CAREFUL HERE. This is raw input
     * that gets to sql!
     *
     * @param $rawparameters
     */
    private function _parse_parameters($rawparameters)
    {

        $this->_parse_simple_select_option_input(self::$FILTER_RATING, $rawparameters, $this->get_rating_options());
        $this->_parse_simple_select_option_input(self::$FILTER_ACCESS, $rawparameters, $this->get_access_options());
        $this->_parse_simple_select_option_input(self::$FILTER_USERS, $rawparameters, $this->get_user_options());

        // HAS DRAWINGS.
        $this->parameters[self::$FILTER_HAS_DRAWING] = null;

        if (array_key_exists(self::$FILTER_HAS_DRAWING, $rawparameters)) {
            if ($rawparameters[self::$FILTER_HAS_DRAWING] == 'yes') {
                $this->parameters[self::$FILTER_HAS_DRAWING] = 'yes';
            } elseif ($rawparameters[self::$FILTER_HAS_DRAWING] == 'no') {
                $this->parameters[self::$FILTER_HAS_DRAWING] = 'no';
            }
        }
    }

    /**
     * Parse the raw user input so the parameters only have allowed values
     *
     * @param $key
     * @param $rawparameters
     * @param $options
     */
    private function _parse_simple_select_option_input($key, $rawparameters, $options)
    {
        $this->parameters[$key] = null;

        if (array_key_exists($key, $rawparameters)) {
            $rating = $rawparameters[$key];
            // Only put uid in array if it is an allowed available option.
            $ratingoptions = $options;
            if (array_key_exists($rating, $ratingoptions)) {
                $this->parameters[$key] = $rating;
            }
        }
    }

    /**
     * Get users by course as select options
     */
    private function get_user_options()
    {
        $cs = new CourseService();
        $members = $cs->get_course_members($this->course->id);
        $options = array();

        foreach ($members as $member) {
            $options[$member->id] = $member->firstname . " " . $member->lastname;
        }

        return $options;
    }

    private function get_has_drawing_options()
    {
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
    private function get_rating_options()
    {
        $options = array();

        $options['red'] = get_string("rating_option_red", 'ivs');
        $options['yellow'] = get_string("rating_option_yellow", 'ivs');
        $options['green'] = get_string("rating_option_green", 'ivs');

        return $options;
    }

    private function get_access_options()
    {
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
    private function _create_select($id, $label, $options)
    {

        $optionsout = '<option>' . get_string("filter_all", 'ivs') . '</option>';

        $defaultvalue = !empty($this->parameters[$id]) ? $this->parameters[$id] : null;

        foreach ($options as $k => $v) {
            $selected = $k == $defaultvalue ? ' selected ' : '';

            $optionsout .= '<option value="' . $k . '" ' . $selected . '>' . $v . '</option>';
        }

        return "<div class='form-item'><label for='" . $id . "'>$label</label> <select name=\"$id\" id=\"$id\">" . $optionsout .
                "</select></div>";
    }

    // Custom validation should be added here.
    function validation($data, $files)
    {
        return array();
    }

    public function get_active_filter()
    {
        return $this->parameters;
    }
}
