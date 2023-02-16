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
 * File for settings course settings
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

use mod_ivs\gradebook\GradebookService;
use mod_ivs\settings\SettingsCourseForm;

require_once('../../config.php');
require_once('./lib.php');
require_once('./locallib.php');


$contextid = optional_param('contextid', 0, PARAM_INT); // One of this or.
$courseid = optional_param('id', 0, PARAM_INT); // This are required.

global $USER;

$PAGE->set_url('/mod/ivs/settings_course.php', array('id' => $courseid));

if ($contextid) {
    $context = context::instance_by_id($contextid, MUST_EXIST);
    if ($context->contextlevel != CONTEXT_COURSE) {
        throw new moodle_exception('invalidcontext');
    }
    $course = $DB->get_record('course', array('id' => $context->instanceid), '*', MUST_EXIST);
} else {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $context = context_course::instance($course->id, MUST_EXIST);
}
require_login($course, true);
require_capability('mod/ivs:access_course_settings', $context);

// Process heading and set base theme.

$heading = get_string('ivs_settings_title', 'ivs');

$PAGE->set_title($heading);
$PAGE->set_heading($heading);
$PAGE->set_pagelayout('standard');

$PAGE->requires->css(new moodle_url($CFG->httpswwwroot . '/mod/ivs/templates/settings_course.css'));

$mform = new SettingsCourseForm($CFG->wwwroot . '/mod/ivs/settings_course.php?id=' . $courseid,
        ['course_id' => $courseid]);

$settingscontroller = new \mod_ivs\settings\SettingsService();
$globalsettings = $settingscontroller->get_settings_global();

if ($fromform = $mform->get_data()) {
    // In this case you process validated data. $mform->get_data() returns data posted in form.

    foreach ((array) $fromform as $key => $vals) {

        if (!array_key_exists($key, $globalsettings)) {
            continue;
        }

        if (!$globalsettings[$key]->locked) {
            $setting = new \mod_ivs\settings\Setting();
            $setting->name = $key;
            $setting->targettype = 'course';
            $setting->targetid = $courseid;
            $setting->value = isset($vals['value']) ? $vals['value'] : 0;
            $setting->locked = isset($vals['locked']) ? $vals['locked'] : 0;

            $settingscontroller->save_setting($setting);

            $gradebookservice = new GradebookService();
            $gradebookservice->ivs_set_grade_to_pass_course_setting($courseid, $key, $vals);

        }
    }

    redirect($PAGE->url);
} else {

    print $OUTPUT->header();
    echo $OUTPUT->heading(get_string('ivs_player_settings', 'ivs'));
    $mform->display();
    print $OUTPUT->footer();
}
