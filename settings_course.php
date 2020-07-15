<?php

use mod_ivs\settings\SettingsCourseForm;

require_once('../../config.php');
require_once('./lib.php');
require_once('./locallib.php');
//require_once($CFG->dirroot . '/mod/ivs/classes/settings/SettingsCourse.php');

$contextid = optional_param('contextid', 0, PARAM_INT); // One of this or.
$courseid = optional_param('id', 0, PARAM_INT); // This are required.

global $USER;

$PAGE->set_url('/mod/ivs/settings_course.php', array('id' => $courseid));

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
require_capability('mod/ivs:access_course_settings', $context);

//process heading and set base theme

$heading = get_string('ivs_settings_title', 'ivs');

$PAGE->set_title($heading);
$PAGE->set_heading($heading);
$PAGE->set_pagelayout('standard');

$PAGE->requires->css(new moodle_url($CFG->httpswwwroot . '/mod/ivs/templates/settings_course.css'));

$mform = new SettingsCourseForm($CFG->wwwroot . '/mod/ivs/settings_course.php?id=' . $courseid,
        ['course_id' => $courseid]);

$settingsController = new \mod_ivs\settings\SettingsService();
$global_settings = $settingsController->getSettingsGlobal();

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.

    foreach ((array) $fromform as $key => $vals) {

        if (!array_key_exists($key, $global_settings)) {
            continue;
        }

        if (!$global_settings[$key]->locked) {
            $setting = new \mod_ivs\settings\Setting();
            $setting->name = $key;
            $setting->target_type = 'course';
            $setting->target_id = $courseid;
            $setting->value = isset($vals['value']) ? $vals['value'] : 0;
            $setting->locked = isset($vals['locked']) ? $vals['locked'] : 0;

            $settingsController->saveSetting($setting);
        }
    }

    redirect($PAGE->url);
} else {

    print $OUTPUT->header();
    $mform->display();
    print $OUTPUT->footer();
}

