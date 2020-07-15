<?php
/**
 * Created by PhpStorm.
 * User: Ghostthinker
 * Date: 20.11.2018
 * Time: 13:29
 */

namespace mod_ivs\license;

global $CFG;
require_once("$CFG->libdir/formslib.php");

use mod_ivs\CourseService;
use moodleform;

class LicenseCourseForm extends moodleform {

    public function definition() {

        $mform = $this->_form;
        global $DB;

        $lc = ivs_get_license_controller();
        $course_licenses = $lc->getCourseLicenses([IVS_LICENCSE_ACTIVE], true);
        //get course_ids of activated licenses
        $active_course_licenses = [-1];
        foreach ($course_licenses as $license) {
            if (!empty($license->course_id)) {
                $active_course_licenses[] = $license->course_id;
            }
        }

        list($active_courses_db, $db_params) = $DB->get_in_or_equal($active_course_licenses, SQL_PARAMS_NAMED, null, false);

        $query = "SELECT id, fullname, shortname from {course} WHERE id $active_courses_db";
        $courselist = $DB->get_records_sql($query, $db_params);
        $course_names = array();

        foreach ($courselist as $id => $course) {
            $course_names[$id] = $course->fullname;
        }

        $options = array(
                'multiple' => false,
                'noselectionstring' => get_string('ivs_course_selector_none', 'ivs'),
        );

        $course_names = ['noselectionstring' => get_string('ivs_course_selector_none', 'ivs')] + $course_names;
        $lc = ivs_get_license_controller();
        $course_licenses = $lc->getCourseLicenses([IVS_LICENCSE_ACTIVE]);
        $license_options = $lc->getCourseLicenseOptions($course_licenses);

        if (count($license_options) > 0) {
            $mform->addElement('autocomplete', 'course', get_string('ivs_course_license_selector_label', 'ivs'), $course_names,
                    $options);
            $mform->addElement('select', 'license_id', get_string('ivs_course_license_selector_flat_label', 'ivs'),
                    $license_options);
            $mform->addElement('submit', 'submitbutton', get_string('ivs_activate_course_license_label', 'ivs'));

        } else {
            $mform->addElement('html', '<div class="alert alert-warning alert-block fade in">' .
                    get_string('ivs_course_license_error_no_free_licenses_available', 'ivs') . '</div>');
        }

    }

    //Custom validation should be added here
    function validation($data, $files) {
        $errors = [];

        //Error if no course is selected
        if (empty($data['course']) || $data['course'] == 'noselectionstring') {
            $errors['course'] = \core\notification::error(get_string('ivs_course_license_error_no_course_selected', 'ivs'));
        }

        return $errors;
    }

}
