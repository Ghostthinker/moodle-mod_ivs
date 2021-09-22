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
 * LicenseCourseForm.php
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\license;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once("$CFG->libdir/formslib.php");

use mod_ivs\CourseService;
use moodleform;

/**
 * Class LicenseCourseForm
 *
 */
class LicenseCourseForm extends moodleform {
    /**
     * Definition for the form
     */
    public function definition() {

        $mform = $this->_form;
        global $DB;

        $lc = ivs_get_license_controller();
        $courselicenses = $lc->get_course_licenses([IVS_LICENCSE_ACTIVE], true);
        // Get course_ids of activated licenses.
        $activecourselicenses = [-1];
        foreach ($courselicenses as $license) {
            if (!empty($license->course_id)) {
                $activecourselicenses[] = $license->course_id;
            }
        }

        list($activecoursesdb, $dbparams) = $DB->get_in_or_equal($activecourselicenses, SQL_PARAMS_NAMED, null, false);

        $query = "SELECT id, fullname, shortname from {course} WHERE id $activecoursesdb";
        $courselist = $DB->get_records_sql($query, $dbparams);
        $coursenames = array();

        foreach ($courselist as $id => $course) {
            $coursenames[$id] = $course->fullname;
        }

        $options = array(
                'multiple' => false,
                'noselectionstring' => get_string('ivs_course_selector_none', 'ivs'),
        );

        $coursenames = ['noselectionstring' => get_string('ivs_course_selector_none', 'ivs')] + $coursenames;
        $lc = ivs_get_license_controller();
        $courselicenses = $lc->get_course_licenses([IVS_LICENCSE_ACTIVE]);
        $licenseoptions = $lc->get_course_license_options($courselicenses);

        if (count($licenseoptions) > 0) {
            $mform->addElement('autocomplete', 'course', get_string('ivs_course_license_selector_label', 'ivs'), $coursenames,
                    $options);
            $mform->addElement('select', 'license_id', get_string('ivs_course_license_selector_flat_label', 'ivs'),
                    $licenseoptions);
            $mform->addElement('submit', 'submitbutton', get_string('ivs_activate_course_license_label', 'ivs'));

        } else {
            $mform->addElement('html', '<div class="alert alert-warning alert-block fade in">' .
                    get_string('ivs_course_license_error_no_free_licenses_available', 'ivs') . '</div>');
        }

    }

    /**
     * Custom validation should be added here.
     * @param \stdClass $data
     * @param \stdClass $files
     *
     * @return array
     */
    public function validation($data, $files) {
        $errors = [];

        // Error if no course is selected.
        if (empty($data['course']) || $data['course'] == 'noselectionstring') {
            $errors['course'] = \core\notification::error(get_string('ivs_course_license_error_no_course_selected', 'ivs'));
        }

        return $errors;
    }

}
