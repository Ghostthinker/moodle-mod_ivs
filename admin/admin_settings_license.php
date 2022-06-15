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
 * This file is used to render the admin settings page
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

define('NO_OUTPUT_BUFFERING', true);
define('IVS_LICENSE_SPOTS_NEARLY_FULL', "spots_nearly_full");
define('IVS_LICENSE_SPOTS_FULL', "spots_full");
define('IVS_LICENSE_SPOTS_OVERBOOKED', "spots_overbooked");
define('IVS_LICENSE_NEARLY_EXPIRED', "duration_nearly_end");

use mod_ivs\license\LicenseCourseForm;
use mod_ivs\license\TestsystemForm;
use mod_ivs\license\PlayerVersionForm;

require(__DIR__ . '/../../../config.php');

global $CFG, $DB;

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/outputcomponents.php');
require_once($CFG->dirroot . '/mod/ivs/lib.php');

require_login(null, false);
admin_externalpage_setup('admin_settings_license');
$viewmode = optional_param('view', 'default', PARAM_ALPHA);
$heading = get_string('modulecategory', 'ivs') . ' ' . get_string('ivs_license', 'ivs');

$PAGE->set_title($heading);
$PAGE->set_heading($heading);




// License Instance Id.
$lc = ivs_get_license_controller();

// Check core connection.
if ($lc->check_is_online() === false) {
    \core\notification::error(get_string('ivs_course_license_core_offline', 'ivs'));

    // Return error message if license server could not be reached.
    echo $OUTPUT->header();

    return;
}

$hasvalidlicense = $lc->has_active_license(null);

if (!$hasvalidlicense) {
    $licensepackageinfo = get_string('ivs_package_inactive', 'ivs');
}
// FORM.
$mform = new LicenseCourseForm($CFG->wwwroot . '/mod/ivs/admin/admin_settings_license.php');
$playerelectionform = new PlayerVersionForm($CFG->wwwroot . '/mod/ivs/admin/admin_settings_license.php');
$testsystemform = new TestsystemForm($CFG->wwwroot . '/mod/ivs/admin/admin_settings_license.php');

if (data_submitted() && confirm_sesskey()) {


    if ($fromform = $mform->get_data()) {
        if (!empty($fromform->course)) {
            $courseid = intval($fromform->course);
            // Send request.
            $status = $lc->activate_course_license($courseid, $fromform->license_id);

            if (!$status) {
                \core\notification::error(get_string('ivs_course_license_error_no_licenses_available', 'ivs'));
            } else {
                // Redirect back to the form.
                redirect(new moodle_url('/mod/ivs/admin/admin_settings_license.php'),
                        get_string('ivs_course_license_available', 'ivs'),
                        null, \core\output\notification::NOTIFY_SUCCESS);
            }
        }
    }

    if ($fromform = $testsystemform->get_data()) {

        $testsystemid = $fromform->ivs_testsystem[0];
        $status = $lc->set_testsystem_instance_id($testsystemid);

        if (!$status) {
            \core\notification::error(get_string('ivs_course_license_error_no_licenses_available', 'ivs'));
        } else {
            if ($testsystemid == '') {
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

    if ($fromform = $playerelectionform->get_data()) {
        $status = $lc->get_status();
        $playerversion = $fromform->ivs_player_version['player_version'];
        if (!empty($status) && $status->used_player_version == $playerversion) {
            \core\notification::info(get_string('ivs_same_player_version',
              'ivs'));
        } else {
            $status = $lc->set_player_version($playerversion);

            if (!$status) {
                \core\notification::error(get_string('ivs_course_license_error_no_licenses_available',
                  'ivs'));
            } else {
                // Redirect back to the form.
                redirect(new moodle_url('/mod/ivs/admin/admin_settings_license.php'),
                  get_string('ivs_changed_player_successfully', 'ivs'),
                  null, \core\output\notification::NOTIFY_SUCCESS);
            }
        }
    }
}
// REMOVE Action.
$requiredcourseid = optional_param('course_id', '', PARAM_ALPHANUMEXT);
$requiredlicenseid = optional_param('license_id', '', PARAM_ALPHANUMEXT);
$requiredoperation = optional_param('remove', '', PARAM_ALPHANUMEXT);


if (!empty($requiredcourseid) && $requiredcourseid != "" && !empty($requiredlicenseid) && $requiredoperation == true) {
    $courseid = intval($requiredcourseid);
    $licenseid = intval($requiredlicenseid);
    $releaseresponse = $lc->release_course_license($courseid, $licenseid);

    if ($releaseresponse) {
        redirect(new moodle_url('/mod/ivs/admin/admin_settings_license.php'), get_string('ivs_course_license_released', 'ivs'),
                null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        \core\notification::error(get_string('ivs_course_license_error_release', 'ivs'));
    }
}

// Transmit usage to get recent data displayed.
$lc->send_usage();

$PAGE->requires->css(new moodle_url($CFG->httpswwwroot . '/mod/ivs/templates/settings_license.css'));

$result = $DB->get_records('course', ['category' => 1]);

echo $OUTPUT->header();

echo '<div class="ivs-license">';
$renderer = $PAGE->get_renderer('ivs');

$params = [];
$pageurl = $CFG->wwwroot . '/mod/ivs/admin/admin_settings_license.php';

$courselicenses = $lc->get_course_licenses([IVS_LICENCSE_ACTIVE], true);
$instancelicense = $lc->get_instance_licenses([IVS_LICENCSE_ACTIVE], true);

$renderable = new \mod_ivs\output\license\settings_license_main_view();
echo $renderer->render($renderable);

if (empty($instancelicense) && empty($courselicenses)) {
    $renderable = new \mod_ivs\output\license\settings_license_none_view();
    echo $renderer->render($renderable);
}

$status = $lc->get_status();

$renderabledatapolicy = new \mod_ivs\output\license\settings_license_data_policy_view();
echo $renderer->render($renderabledatapolicy);

if (count($instancelicense) > 0) {
    foreach ($instancelicense as $license) {
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
} else {
    \core\notification::info(get_string('ivs_usage_instance_info', 'ivs', [
            'usage' => $lc->get_num_instance_members()
    ]));
}

if (count($courselicenses) > 0) {
    $mform->display();
    $activecourselicences = 1;
    foreach ($courselicenses as $license) {
        if ($license->course_id == "") {
            continue;
        }
        $activecourselicences++;
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
        } else if ($license->usage == IVS_LICENSE_SPOTS_OVERBOOKED && !empty($instancelicense)) {
            \core\notification::error(get_string('ivs_usage_error_with_license', 'ivs', [
                    'name' => $course->fullname,
                    'usage' => round($usage * 100),
                    'product_name' => $instancelicense[0]->product_name,

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

    if ($activecourselicences > 0) {
        $renderable = new \mod_ivs\output\license\settings_license_course_view($courselicenses, $instancelicense);
        echo $renderer->render($renderable);
    }
}

$modalparams = ['modal_confirm_string' => get_string('ivs_course_license_modal_confirmation', 'ivs')];
$modalparams2 = ['modal_confirm_delete' => get_string('ivs_delete_licence', 'ivs')];
$PAGE->requires->js_call_amd('mod_ivs/settings_license_course', 'init', [
        $modalparams,
        $modalparams2,
]);

$overbookedcourselicences = $lc->get_instance_licenses_by_type('course', [IVS_LICENCSE_OVERBOOKED]);
$overbookedinstancelicences = $lc->get_instance_licenses_by_type('instance', [IVS_LICENCSE_OVERBOOKED]);

if (!empty($overbookedcourselicences) || !empty($overbookedinstancelicences)) {
    if (!empty($overbookedinstancelicences)) {
        $usage = $overbookedinstancelicences[0]->spots_in_use / $overbookedinstancelicences[0]->spots;
        \core\notification::error(get_string('ivs_usage_error_instance', 'ivs', [
                'name' => $overbookedinstancelicences[0]->product_name,
                'usage' => round($usage * 100),
        ]));
    }
    if (!empty($overbookedcourselicences)) {
        foreach ($overbookedcourselicences as $license) {
            $usage = $license->spots_in_use / $license->spots;

            $course = get_course($license->course_id);
            \core\notification::error(get_string('ivs_usage_error', 'ivs', [
                    'name' => $course->fullname,
                    'usage' => round($usage * 100),
            ]));
        }
    }

    $renderable = new \mod_ivs\output\license\settings_license_course_overbooked_view($overbookedcourselicences,
            $overbookedinstancelicences);
    echo $renderer->render($renderable);
}

$expiredcourselicenses = $lc->get_instance_licenses_by_type('course', [IVS_LICENCSE_EXPIRED]);
$expiredinstancelicenses = $lc->get_instance_licenses_by_type('instance', [IVS_LICENCSE_EXPIRED]);

$lc->renderFreemiumInfoText();

if (!empty($expiredcourselicenses) || !empty($expiredinstancelicenses)) {
    if (!empty($expiredinstancelicenses)) {
        \core\notification::error(get_string('ivs_duration_error_instance', 'ivs',
                ['name' => $expiredinstancelicenses[0]->product_name]));
    }
    if (!empty($expiredcourselicenses)) {
        foreach ($expiredcourselicenses as $license) {
            $course = "";
            if ($license->course_id != "") {
                $course = get_course($license->course_id)->fullname;
            }
            \core\notification::error(get_string('ivs_duration_error', 'ivs', ['name' => $course]));
        }
    }
    $renderable =
            new \mod_ivs\output\license\settings_license_course_expired_view($expiredcourselicenses, $expiredinstancelicenses);
    echo $renderer->render($renderable);

}

echo '</div>';

if (!empty($status) && $status->type == IVS_SYSTEM_TYPE_MAIN) {
    $testsystemform->display();

} else {
    if (!empty($status)) {
        \core\notification::info(get_string('ivs_testsystem_info_message', 'ivs'));
    }
}

$playerelectionform->display();

echo $renderer->footer();

