<?php

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * On this page administrator can change site settings
 *
 * @package   ivs
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Bernhard Hörterer
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

define('IVS_LICENSE_SPOTS_NEARLY_FULL', "spots_nearly_full");
define('IVS_LICENSE_SPOTS_FULL', "spots_full");
define('IVS_LICENSE_SPOTS_OVERBOOKED', "spots_overbooked");
define('IVS_LICENSE_NEARLY_EXPIRED', "duration_nearly_end");

use mod_ivs\license\LicenseCourseForm;
use mod_ivs\license\TestsystemForm;
use mod_ivs\license\PlayerVersionForm;

global $CFG, $DB;

require(__DIR__ . '/../../../config.php');

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/outputcomponents.php');
require_once($CFG->dirroot . '/mod/ivs/lib.php');

admin_externalpage_setup('admin_settings_license');
$viewmode = optional_param('view', 'default', PARAM_ALPHA);
$heading = get_string('modulecategory', 'ivs') . ' ' . get_string('ivs_license', 'ivs');

$PAGE->set_title($heading);
$PAGE->set_heading($heading);

//License Instance Id
$lc = ivs_get_license_controller();

// check core connection
if ($lc->checkIsOnline() === false) {
    \core\notification::error(get_string('ivs_course_license_core_offline', 'ivs'));

    // return error message if license server could not be reached
    echo $OUTPUT->header();

    return;
}

$has_valid_license = $lc->hasActiveLicense(null);

if (!$has_valid_license) {
    $license_package_info = get_string('ivs_package_inactive', 'ivs');
}

// FORM
$mform = new LicenseCourseForm($CFG->wwwroot . '/mod/ivs/admin/admin_settings_license.php');

if ($fromform = $mform->get_data()) {
    if (!empty($fromform->course)) {
        $course_id = intval($fromform->course);
        //Send request
        $status = $lc->activateCourseLicense($course_id, $fromform->license_id);

        if (!$status) {
            \core\notification::error(get_string('ivs_course_license_error_no_licenses_available', 'ivs'));
        } else {
            // Redirect back to the form.
            redirect(new moodle_url('/mod/ivs/admin/admin_settings_license.php'), get_string('ivs_course_license_available', 'ivs'),
                    null, \core\output\notification::NOTIFY_SUCCESS);
        }
    }
}

$testsystemForm = new TestsystemForm($CFG->wwwroot . '/mod/ivs/admin/admin_settings_license.php');
if ($fromform = $testsystemForm->get_data()) {

    $testsystem_id = $fromform->ivs_testsystem[0];
    $status = $lc->setTestsystemInstanceId($testsystem_id);

    if (!$status) {
        \core\notification::error(get_string('ivs_course_license_error_no_licenses_available', 'ivs'));
    } else {
        if ($testsystem_id == '') {
            redirect(new moodle_url('/mod/ivs/admin/admin_settings_license.php'),
                    get_string('ivs_set_testsystem_success_released', 'ivs'),
                    null, \core\output\notification::NOTIFY_SUCCESS);
        } else {
            // Redirect back to the form.
            redirect(new moodle_url('/mod/ivs/admin/admin_settings_license.php'),
                    get_string('ivs_set_testsystem_success', 'ivs'),
                    null, \core\output\notification::NOTIFY_SUCCESS);
        }
    }
}

$player_selection_form = new PlayerVersionForm($CFG->wwwroot . '/mod/ivs/admin/admin_settings_license.php');
if ($fromform = $player_selection_form->get_data()) {
    $status = $lc->getStatus();
    $player_version = $fromform->ivs_player_version['player_version'];
    if ($status->used_player_version == $player_version) {
        \core\notification::info(get_string('ivs_same_player_version', 'ivs'));
    } else {
        $status = $lc->setPlayerVersion($player_version);

        if (!$status) {
            \core\notification::error(get_string('ivs_course_license_error_no_licenses_available', 'ivs'));
        } else {
            // Redirect back to the form.
            redirect(new moodle_url('/mod/ivs/admin/admin_settings_license.php'),
                    get_string('ivs_changed_player_successfully', 'ivs'),
                    null, \core\output\notification::NOTIFY_SUCCESS);
        }
    }
}

// REMOVE Action
if (!empty($_GET['course_id']) && $_GET['course_id'] != "" && !empty($_GET['license_id']) && !empty($_GET['remove']) &&
        $_GET['remove'] == true) {
    $course_id = intval($_GET['course_id']);
    $license_id = intval($_GET['license_id']);
    $release_response = $lc->releaseCourseLicense($course_id, $license_id);

    if ($release_response) {
        redirect(new moodle_url('/mod/ivs/admin/admin_settings_license.php'), get_string('ivs_course_license_released', 'ivs'),
                null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        \core\notification::error(get_string('ivs_course_license_error_release', 'ivs'));
    }
}

// transmit usage to get recent data displayed
$lc->sendUsage();

$PAGE->requires->css(new moodle_url($CFG->httpswwwroot . '/mod/ivs/templates/settings_license.css'));

$result = $DB->get_records('course', ['category' => 1]);

echo $OUTPUT->header();

echo '<div class="ivs-license">';
$renderer = $PAGE->get_renderer('ivs');

$params = [];
$pageUrl = $CFG->wwwroot . '/mod/ivs/admin/admin_settings_license.php';

$course_licenses = $lc->getCourseLicenses([IVS_LICENCSE_ACTIVE], true);
$instance_license = $lc->getInstanceLicenses([IVS_LICENCSE_ACTIVE], true);

$renderable = new \mod_ivs\output\license\settings_license_main_view();
echo $renderer->render($renderable);

if (empty($instance_license) && empty($course_licenses)) {
    $renderable = new \mod_ivs\output\license\settings_license_none_view();
    echo $renderer->render($renderable);
}

$status = $lc->getStatus();

$renderable_data_policy = new \mod_ivs\output\license\settings_license_data_policy_view();
echo $renderer->render($renderable_data_policy);

if (count($instance_license) > 0) {
    foreach ($instance_license as $license) {
        $usage = $license->spots_in_use / $license->spots;
        if ($license->usage == IVS_LICENSE_SPOTS_NEARLY_FULL) {
            \core\notification::info(get_string('ivs_usage_info', 'ivs', [
                    'name' => $license->product_name,
                    'usage' => round($usage * 100),
            ]));
        } else if ($license->usage == IVS_LICENSE_SPOTS_FULL) {
            \core\notification::warning(get_string('ivs_usage_warning', 'ivs', [
                    'name' => $license->product_name,
                    'usage' => round($usage * 100),
            ]));
        }
        $renderable = new \mod_ivs\output\license\settings_license_instance_view($license);
        $PAGE->requires->js_call_amd('mod_ivs/settings_license_instance', 'init', []);
        echo $renderer->render($renderable);
    }
}

if (count($course_licenses) > 0) {
    $mform->display();
    $active_course_licences = 1;
    foreach ($course_licenses as $license) {
        if ($license->course_id == "") {
            continue;
        }
        $active_course_licences++;
        $usage = $license->spots_in_use / $license->spots;
        $course = get_course($license->course_id);

        if ($license->usage == IVS_LICENSE_SPOTS_NEARLY_FULL) {
            \core\notification::info(get_string('ivs_usage_info', 'ivs', [
                    'name' => $course->fullname,
                    'usage' => round($usage * 100),
            ]));
        } else if ($license->usage == IVS_LICENSE_SPOTS_FULL) {
            \core\notification::warning(get_string('ivs_usage_warning', 'ivs', [
                    'name' => $course->fullname,
                    'usage' => round($usage * 100),
            ]));
        } else if ($license->usage == IVS_LICENSE_SPOTS_OVERBOOKED && !empty($instance_license)) {
            \core\notification::error(get_string('ivs_usage_error_with_license', 'ivs', [
                    'name' => $course->fullname,
                    'usage' => round($usage * 100),
                    'product_name' => $instance_license[0]->product_name,

            ]));
        }

        $time = strtotime(date("Y-m-d H:i:s"));
        $resttime = strtotime($license->expires_at) - $time;
        $resttime = round($resttime / 86400);
        if ($license->runtime == IVS_LICENSE_NEARLY_EXPIRED) {
            \core\notification::warning(get_string('ivs_duration_warning', 'ivs', [
                    'name' => $course->fullname,
                    'resttime' => $resttime,
            ]));
        }
    }

    if ($active_course_licences > 0) {
        $renderable = new \mod_ivs\output\license\settings_license_course_view($course_licenses, $instance_license);
        echo $renderer->render($renderable);
    }
}

$modal_params = ['modal_confirm_string' => get_string('ivs_course_license_modal_confirmation', 'ivs')];
$modal_params2 = ['modal_confirm_delete' => get_string('ivs_delete_licence', 'ivs')];
$PAGE->requires->js_call_amd('mod_ivs/settings_license_course', 'init', [
        $modal_params,
        $modal_params2,
]);

$overbooked_course_licences = $lc->getInstanceLicensesByType('course', [IVS_LICENCSE_OVERBOOKED]);
$overbooked_instance_licences = $lc->getInstanceLicensesByType('instance', [IVS_LICENCSE_OVERBOOKED]);

if (!empty($overbooked_course_licences) || !empty($overbooked_instance_licences)) {
    if (!empty($overbooked_instance_licences)) {
        $usage = $overbooked_instance_licences[0]->spots_in_use / $overbooked_instance_licences[0]->spots;
        \core\notification::error(get_string('ivs_usage_error_instance', 'ivs', [
                'name' => $overbooked_instance_licences[0]->product_name,
                'usage' => round($usage * 100),
        ]));
    }
    if (!empty($overbooked_course_licences)) {
        foreach ($overbooked_course_licences as $license) {
            $usage = $license->spots_in_use / $license->spots;

            $course = get_course($license->course_id);
            \core\notification::error(get_string('ivs_usage_error', 'ivs', [
                    'name' => $course->fullname,
                    'usage' => round($usage * 100),
            ]));
        }
    }

    $renderable = new \mod_ivs\output\license\settings_license_course_overbooked_view($overbooked_course_licences,
            $overbooked_instance_licences);
    echo $renderer->render($renderable);
}

$expired_course_licenses = $lc->getInstanceLicensesByType('course', [IVS_LICENCSE_EXPIRED]);
$expired_instance_licenses = $lc->getInstanceLicensesByType('instance', [IVS_LICENCSE_EXPIRED]);
if (!empty($expired_course_licenses) || !empty($expired_instance_licenses)) {
    if (!empty($expired_instance_licenses)) {
        \core\notification::error(get_string('ivs_duration_error_instance', 'ivs',
                ['name' => $expired_instance_licenses[0]->product_name]));
    }
    if (!empty($expired_course_licenses)) {
        foreach ($expired_course_licenses as $license) {
            $course = "";
            if ($license->course_id != "") {
                $course = get_course($license->course_id)->fullname;
            }
            \core\notification::error(get_string('ivs_duration_error', 'ivs', ['name' => $course]));
        }
    }
    $renderable =
            new \mod_ivs\output\license\settings_license_course_expired_view($expired_course_licenses, $expired_instance_licenses);
    echo $renderer->render($renderable);

}

echo '</div>';

if ($status->type == IVS_SYSTEM_TYPE_MAIN) {
    $testsystemForm->display();

} else {
    \core\notification::info(get_string('ivs_testsystem_info_message', 'ivs'));
}

$player_selection_form->display();

echo $renderer->footer();