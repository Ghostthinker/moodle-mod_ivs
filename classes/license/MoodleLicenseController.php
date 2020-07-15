<?php

namespace mod_ivs\license;

use ArrayIterator;

defined('MOODLE_INTERNAL') || die();

define('IVS_CORE_URL', 'http://interactive-video-suite.de');
define('IVS_CORE_API_PREFIX', '/wp-json/interactive-video-suite/v1.0');
define('IVS_CORE_API_CALLBACK_REGISTER', '/client/register');
define('IVS_CORE_API_CALLBACK_STATUS', '/client/status');
define('IVS_CORE_API_CALLBACK_CDN', '/client/cdn/src');
define('IVS_CORE_API_CALLBACK_ACTIVATE', '/client/activate');
define('IVS_CORE_API_CALLBACK_RELEASE', '/client/release');
define('IVS_CORE_API_CALLBACK_USAGE', '/client/usage');
define('IVS_CORE_API_CALLBACK_INSTANCE', '/client/instances');
define('IVS_CORE_CRON_WAITING_TIME', 129600);
define('IVS_LICENCSE_ACTIVE', 1);
define('IVS_LICENCSE_OVERBOOKED', 2);
define('IVS_LICENCSE_EXPIRED', 3);
define('IVS_SYSTEM_TYPE_MAIN', 'main');
define('IVS_SYSTEM_TYPE_TEST', 'testsystem');
define('IVS_ACTION_TESTSYSTEM', 'test');
define('IVS_ACTION_PLAYERVERSION', 'player');

class MoodleLicenseController implements ILicenseController {

    public function generateInstanceId() {

        // prevent overriding existing instance id
        $instanceID = get_config('mod_ivs', 'ivs_instance_id');
        if (!empty($instanceID)) {
            return false;
        }

        set_config('ivs_installation_date', date('Y-m-d H:i:s', time()), 'mod_ivs');
        if ($response = $this->coreRegister($instanceID)) {
            $response_obj = json_decode($response);
            set_config('ivs_instance_id', $response_obj->instance_id, 'mod_ivs');
            set_config('ivs_schedule_task', date('Y-m-d H:i:s', time()), 'mod_ivs');
            return $response_obj->instance_id;
        }
        return false;
    }

    /**
     * @param $instance_id
     *
     * @return bool|string
     * @throws \dml_exception
     */
    public function coreRegister($instance_id) {
        global $CFG;

        $request_data = [
                'instance_id' => $instance_id,
                'system_name' => "Moodle",
                'system_ip' => $_SERVER['SERVER_ADDR'],
                'version_lms' => $CFG->release,
                'version_plugin' => get_config('mod_ivs', 'version'),
                'installation_date' => get_config('mod_ivs', 'ivs_installation_date'),
        ];

        $result = $this->sendRequest("coreRegister", $request_data);

        return $result;
    }

    /**
     * get InstanceID stored in config
     *
     * @return mixed
     * @throws \dml_exception
     */
    public function getInstanceId() {
        $instanceID = get_config('mod_ivs', 'ivs_instance_id');
        if (empty($instanceID)) {
            $instanceID = $this->generateInstanceId();
        }
        return $instanceID;
    }

    /**
     * get current license
     *
     * @param null $context
     *
     * @return mixed|null
     * @throws \dml_exception
     */
    public function getActiveLicense($context = null) {
        // check licenses

        // 1)   do we have course licenses?
        // 1.1) check if we have a valid license for active course
        //      check if license is active for a special course

        if (!empty($context['course'])) {
            $course_licenses = $this->getCourseLicenses([IVS_LICENCSE_ACTIVE]);
            foreach ($course_licenses as $license) {
                if ($license->course_id == $context['course']->id) {
                    return $license;
                }
            }
        }

        // 2.)  check if we  have an instance license
        //      check course_id from context

        $instance_licenses = $this->getInstanceLicenses([IVS_LICENCSE_ACTIVE]);
        if ($instance_licenses != null) {
            if (count($instance_licenses) > 0) {
                return current($instance_licenses);
            }
        }

        return null;
    }

    /**
     * check if there is a active license
     *
     * @param null $context e.g. course
     *
     * @return bool
     * @throws \dml_exception
     */
    public function hasActiveLicense($context = null) {
        // check licenses
        $status = $this->getStatus();
        if ($this->cronRuntimeTooOld()) {
            $this->sendUsage();
            $this->setLastRuntime();
        }
        if (empty($context) && !empty($status->active)) {
            return true;
        }

        $active_license = $this->getActiveLicense($context);
        return !empty($active_license);
    }

    /**
     * @param bool $reset
     *
     * @return mixed
     * @throws \dml_exception
     */
    public function getStatus($reset = false) {
        global $CFG;
        static $status;
        if (!$reset && !empty($status)) {
            return $status;
        }

        $instance_id = $this->getInstanceId();

        $request_data = [
                'instance_id' => $instance_id,
                'version_lms' => $CFG->release,
                'version_plugin' => get_config('mod_ivs', 'version'),
        ];

        $status_response = $this->sendRequest("status", $request_data);
        $status = $status_response != false ? json_decode($status_response) : false;
        return $status;
    }

    /**
     * @param $status
     *
     * @return mixed
     */
    public function getLicenseType($status) {
        return $status['type'];
    }

    /**
     * @return array|bool|string
     * @throws \dml_exception
     */
    public function getCDNSource($license_id) {

        $instance_id = $this->getInstanceId();

        $request_data = [
                'instance_id' => $instance_id,
                'license_id' => $license_id,
        ];

        $result = $this->sendRequest("callback_cdn", $request_data);

        return $result != false ? json_decode($result) : $result;
    }

    /**
     * @return bool
     */
    function checkIsOnline() {
        $domain = $this->getCoreUrl(true);
        $curlInit = curl_init($domain);
        curl_setopt($curlInit, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curlInit, CURLOPT_HEADER, true);
        curl_setopt($curlInit, CURLOPT_NOBODY, true);
        curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, true);

        //get answer
        $response = curl_exec($curlInit);

        curl_close($curlInit);
        if ($response) {
            return true;
        }
        return false;
    }

    /**
     * send POST request
     *
     * @param $url
     * @param $requestData
     *
     * @return bool|string
     */
    protected function sendCurlRequest($path, $method = "POST", $requestData) {

        $core_url = $this->getCoreUrl(true);
        $url = $core_url . IVS_CORE_API_PREFIX . $path;

        //url-ify the data for the POST
        $requestJSON = json_encode($requestData);

        //open connection
        $ch = curl_init($url);

        switch ($method) {
            case "POST":
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $requestJSON);
                break;
            case "GET":
                break;
            case "PUT":
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $requestJSON);
                break;
            case "PATCH":
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $requestJSON);
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (!curl_errno($ch)) {

            switch ($http_code) {
                case 200:
                case 201:  # OK -> created
                    break;
                default:
                    // e.g. 409
                    return false;
            }
        }

        curl_close($ch);

        return $result;
    }

    /**
     * @param $course_id
     * @param $license_id
     *
     * @return bool|string
     * @throws \dml_exception
     */
    public function activateCourseLicense($course_id, $license_id) {

        $request_data = [
                "instance_id" => $this->getInstanceId(),
                "license_id" => $license_id,
                "course_id" => $course_id,
        ];

        return $this->sendRequest("activate", $request_data);
    }

    /**
     * @param $course_id
     * @param $license_id
     *
     * @return bool|string
     * @throws \dml_exception
     */
    public function releaseCourseLicense($course_id, $license_id) {

        $request_data = [
                "instance_id" => $this->getInstanceId(),
                "license_id" => $license_id,
                "course_id" => $course_id,
        ];

        return $this->sendRequest("release", $request_data);

    }

    /**
     * @param bool $reset
     *
     * @return array
     */
    public function getCourseLicenses($licenseStatus, $reset = false) {
        $course_licenses = $this->getInstanceLicensesByType('course', $licenseStatus, $reset);

        return $course_licenses;
    }

    /**
     * @param bool $reset
     *
     * @return array
     */
    public function getInstanceLicenses($licenseStatus, $reset = false) {
        $instance_licenses = $this->getInstanceLicensesByType('instance', $licenseStatus, $reset);

        return $instance_licenses;
    }

    /**
     * @param $type
     * @param $licenseStatus
     * @param bool $reset
     *
     * @return array
     * @throws \dml_exception
     */
    public function getInstanceLicensesByType($type, $licenseStatus, $reset = false) {
        $status = $this->getStatus($reset);
        $licenses = [];

        if (in_array(IVS_LICENCSE_ACTIVE, $licenseStatus)) {
            if (!empty($status->active)) {
                foreach ($status->active->licenses as $license) {
                    if ($license->type == $type) {
                        $licenses[] = $license;
                    }
                }
            }
        }

        if (in_array(IVS_LICENCSE_OVERBOOKED, $licenseStatus)) {
            if (!empty($status->overbooked)) {
                foreach ($status->overbooked->licenses as $license) {
                    if ($license->type == $type) {
                        $licenses[] = $license;
                    }
                }
            }
        }

        if (in_array(IVS_LICENCSE_EXPIRED, $licenseStatus)) {
            if (!empty($status->expired)) {
                foreach ($status->expired->licenses as $license) {
                    if ($license->type == $type) {
                        $licenses[] = $license;
                    }
                }
            }
        }

        return $licenses;

    }

    /**
     * @return \stdClass
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function getSettingsLicenseNoneData() {
        $data = new \stdClass;

        $data->current_package_label = get_string('ivs_package_label', 'ivs');
        $data->current_package = get_string('ivs_package_inactive', 'ivs');
        $data->shop_hint = get_string('ivs_shop_hint', 'ivs');

        return $data;
    }

    /**
     * @return string
     */
    public function getCoreUrl($internal = false) {
        global $CFG;
        // overriden CORE URL
        if (!$internal && !empty($CFG->IVS_CORE_DOCKER_URL)) {
            return $CFG->IVS_CORE_DOCKER_URL;
        }
        if (!empty($CFG->IVS_CORE_URL)) {
            return $CFG->IVS_CORE_URL;
        }
        return IVS_CORE_URL;
    }

    /**
     * @param $course_licenses
     * @param $output
     *
     * @return \stdClass
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function getSettingsLicenseCourseData($course_licenses, $instance_licenses, $output) {
        global $CFG;
        $data = new \stdClass;

        $lc = $this;

        $package_info = $lc->getCurrentLicensePackageInfo($course_licenses);

        $data->license_package_label = get_string('ivs_package_label', 'ivs');
        $data->license_package_info = $package_info['assigned_course_licenses'] . '/' . $package_info['max_course_licenses'] . ' ' .
                get_string('ivs_current_package_courses_label', 'ivs');

        $data->course_title = get_string('ivs_course_title', 'ivs');
        $data->course_spots_title = get_string('ivs_course_spots_title', 'ivs');
        $data->course_package_title = get_string('ivs_course_package_title', 'ivs');
        $data->course_reassign_title = get_string('ivs_course_package_reassign', 'ivs');
        $data->remove_icon = $output->image_url('move-icon', 'ivs');

        $course_licenses_assigned = [];

        foreach ($course_licenses as $course_license) {

            if (!empty($course_license->course_id)) {
                $course = get_course($course_license->course_id);
                $dateformat = get_string('strftimedatefullshort', 'langconfig');
                $course_licenses_assigned[$course_license->course_id]['title'] = $course->fullname;
                $course_licenses_assigned[$course_license->course_id]['course_spots'] =
                        $course_license->spots_in_use . '/' . $course_license->spots;
                if ($course_license->overbooked_spots > 0 && !empty($instance_licenses)) {
                    $course_licenses_assigned[$course_license->course_id]['course_spots'] =
                            $course_license->spots_in_use . '/' . $course_license->spots . ' ' .
                            get_string('ivs_move_user_to_instance_from_course', 'ivs', [
                                    'overbooked_spots' => $course_license->overbooked_spots,
                                    'product_name' => $instance_licenses[0]->product_name,
                            ]);
                }
                $course_licenses_assigned[$course_license->course_id]['product_name'] =
                        $course_license->product_name . " (" . strftime($dateformat, strtotime($course_license->created_at)) .
                        " - " . strftime($dateformat, strtotime($course_license->expires_at)) . ")";
                $course_licenses_assigned[$course_license->course_id]['remove_link'] =
                        $CFG->wwwroot . '/mod/ivs/admin/admin_settings_license.php?course_id=' . $course_license->course_id .
                        '&license_id=' . $course_license->id . '&remove=true';
            }
        }

        $data->course_license_has_items = count($course_licenses_assigned) > 0;
        $data->course_license = new ArrayIterator($course_licenses_assigned);

        return $data;

    }

    public function getSettingsOverbookedLicenseData($course_licenses, $instance_licences, $output) {
        global $CFG;
        $data = new \stdClass;

        $data->license_package_label = get_string('ivs_package_label_overbooked', 'ivs');
        $data->course_title = get_string('ivs_course_title', 'ivs');
        $data->course_spots_title = get_string('ivs_course_spots_title', 'ivs');
        $data->course_package_title = get_string('ivs_course_package_title', 'ivs');
        $data->course_reassign_title = get_string('ivs_course_package_reassign', 'ivs');
        $data->remove_icon = $output->image_url('move-icon', 'ivs');

        $course_licenses_overbooked = [];
        $instance_licenses_overbooked = [];
        foreach ($course_licenses as $course_license) {

            if (!empty($course_license->course_id)) {
                $course = get_course($course_license->course_id);
                $dateformat = get_string('strftimedatefullshort', 'langconfig');
                $course_licenses_overbooked[$course_license->id]['title'] = $course->fullname;
                $course_licenses_overbooked[$course_license->id]['course_spots'] =
                        $course_license->spots_in_use . '/' . $course_license->spots;
                $course_licenses_overbooked[$course_license->id]['product_name'] =
                        $course_license->product_name . " (" . strftime($dateformat, strtotime($course_license->created_at)) .
                        " - " . strftime($dateformat, strtotime($course_license->expires_at)) . ")";
                $course_licenses_overbooked[$course_license->id]['remove_link'] =
                        $CFG->wwwroot . '/mod/ivs/admin/admin_settings_license.php?course_id=' . $course_license->course_id .
                        '&license_id=' . $course_license->id . '&remove=true';
            }
        }

        if (!empty($instance_licences)) {
            $dateformat = get_string('strftimedatefullshort', 'langconfig');
            $instance_licenses_overbooked[$instance_licences[0]->id]['title'] = "Instance Flat";
            $instance_licenses_overbooked[$instance_licences[0]->id]['course_spots'] =
                    $instance_licences[0]->spots_in_use . '/' . $instance_licences[0]->spots;
            $instance_licenses_overbooked[$instance_licences[0]->id]['product_name'] = $instance_licences[0]->product_name . " (" .
                    strftime($dateformat, strtotime($instance_licences[0]->created_at)) . " - " .
                    strftime($dateformat, strtotime($instance_licences[0]->expires_at)) . ")";
        }

        $data->course_license_has_items = count($course_licenses_overbooked) + count($instance_licenses_overbooked);
        $data->course_license = new ArrayIterator($course_licenses_overbooked);
        $data->instance_licences = new ArrayIterator($instance_licenses_overbooked);
        return $data;
    }

    public function getSettingsExpiredLicenseData($course_licenses, $instance_licences, $output) {
        global $CFG;
        $data = new \stdClass;

        $data->license_package_label = get_string('ivs_package_label_expired', 'ivs');
        $data->course_title = get_string('ivs_course_title', 'ivs');
        $data->course_spots_title = get_string('ivs_course_spots_title', 'ivs');
        $data->course_package_title = get_string('ivs_course_package_title', 'ivs');
        $data->course_delete_title = get_string('ivs_course_package_delete', 'ivs');
        $data->remove_icon = $output->image_url('delete_black', 'ivs');

        $course_licenses_expired = [];
        $instance_licenses_expired = [];

        foreach ($course_licenses as $course_license) {
            $course = "";
            if (!empty($course_license->course_id)) {
                $course = get_course($course_license->course_id)->fullname;
            }
            $dateformat = get_string('strftimedatefullshort', 'langconfig');
            $course_licenses_expired[$course_license->id]['title'] = $course;
            $course_licenses_expired[$course_license->id]['course_spots'] =
                    $course_license->spots_in_use . '/' . $course_license->spots;
            $course_licenses_expired[$course_license->id]['product_name'] =
                    $course_license->product_name . " (" . strftime($dateformat, strtotime($course_license->created_at)) . " - " .
                    strftime($dateformat, strtotime($course_license->expires_at)) . ")";
            $course_licenses_expired[$course_license->id]['remove_link'] =
                    $CFG->wwwroot . '/mod/ivs/admin/admin_settings_license.php?course_id=' . $course_license->course_id .
                    '&license_id=' . $course_license->id . '&remove=true';

        }
        if (!empty($instance_licences)) {
            $dateformat = get_string('strftimedatefullshort', 'langconfig');
            $instance_licenses_expired[$instance_licences[0]->id]['title'] = "Instance Flat";
            $instance_licenses_expired[$instance_licences[0]->id]['course_spots'] =
                    $instance_licences[0]->spots_in_use . '/' . $instance_licences[0]->spots;
            $instance_licenses_expired[$instance_licences[0]->id]['product_name'] = $instance_licences[0]->product_name . " (" .
                    strftime($dateformat, strtotime($instance_licences[0]->created_at)) . " - " .
                    strftime($dateformat, strtotime($instance_licences[0]->expires_at)) . ")";
            $instance_licenses_expired[$instance_licences[0]->id]['remove_link'] =
                    $CFG->wwwroot . '/mod/ivs/admin/admin_settings_license.php?course_id=' . $instance_licences[0]->type .
                    '&license_id=' . $instance_licences[0]->id . '&remove=true';
        }

        $data->course_license_has_items = count($course_licenses_expired) + count($instance_licenses_expired);
        $data->course_license = new ArrayIterator($course_licenses_expired);
        $data->instance_license = new ArrayIterator($instance_licenses_expired);

        return $data;
    }

    /**
     * @param $license
     *
     * @return \stdClass
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function getSettingsLicenseInstanceData($license) {
        $data = new \stdClass;
        $lc = $this;

        $data->instance_id_label = get_string('ivs_instance_id_label', 'ivs');
        $data->license_instance_id = $lc->getInstanceId();
        $data->license_package_label = get_string('ivs_package_label', 'ivs');
        $data->license_package_info = $license->product_name;
        $data->manage_license_label = get_string('ivs_package_button_label', 'ivs');
        $data->manage_license_href = $this->getCoreUrl();

        $data->license_instance_view_data = $lc->getLicenseInstanceViewData($license);

        return $data;
    }

    /**
     * @param $course_licenses
     *
     * @return array
     */
    public function getCurrentLicensePackageInfo($course_licenses) {
        $assigned_course_licenses = 0;
        $max_course_licenses = 0;

        foreach ($course_licenses as $course) {
            $max_course_licenses++;
            if (!empty($course->course_id)) {
                $assigned_course_licenses++;
            }
        }
        return [
                'assigned_course_licenses' => $assigned_course_licenses,
                'max_course_licenses' => $max_course_licenses,
        ];
    }

    /**
     * @param $license
     *
     * @return array
     */
    public function getLicenseInstanceViewData($license) {
        $created_at = strtotime($license->created_at);
        $expires_at = strtotime($license->expires_at);
        $date_now = time();
        $runtime_rest = $expires_at - $date_now;
        if ($runtime_rest < 0) {
            $runtime_percentage = 1;
        } else {
            $runtime_complete = $expires_at - $created_at;
            $runtime_percentage = ($runtime_rest / $runtime_complete) / 100;
        }
        $spots = $license->spots;
        $spots_in_use = $license->spots_in_use;
        $spots_percentage = ($spots_in_use / $spots);
        $dateformat = get_string('strftimedatefullshort', 'langconfig');
        $view_data = [
                'expires_at' => strftime($dateformat, $expires_at),
                'runtime_percentage' => $runtime_percentage,
                'spots_left' => $license->spots - $spots_in_use > 0 ? $license->spots - $spots_in_use : 0,
                'spots_in_use' => $spots_in_use,
                'spots_percentage' => $spots_percentage <= 1.0 ? $spots_percentage : 1.0,
                'runtime_percentage_label' => number_format($runtime_percentage * 100, 0) . ' %',
                'spots_percentage_label' => number_format($spots_percentage * 100, 0) . ' %',
        ];
        return $view_data;
    }

    /**
     * @param $course_licenses
     *
     * @return array
     */
    public function getCourseLicenseOptions($course_licenses) {
        $course_license_options = [];

        $dateformat = get_string('strftimedatefullshort', 'langconfig');
        foreach ($course_licenses as $course_license) {
            if (empty($course_license->course_id)) {
                $course_license_options[$course_license->id] =
                        $course_license->product_name . ' (' . strftime($dateformat, strtotime($course_license->created_at)) .
                        ' - ' . strftime($dateformat, strtotime($course_license->expires_at)) . ')';
            }
        }

        return $course_license_options;
    }

    public function getAllUserFromInstance() {
        global $DB;
        $sql = "SELECT * FROM {user} WHERE suspended = 0";
        $users = $DB->get_records_sql($sql);

        return $users;
    }

    public function getUserFromCourse($course_id) {
        global $DB;
        $sql = "SELECT u.id
    FROM {user} u
    JOIN {user_enrolments} ue ON u.id = ue.userid
    JOIN {enrol} e ON e.id = ue.enrolid
    WHERE suspended = 0 AND e.courseid = $course_id";
        $user = $DB->get_records_sql($sql);
        return $user;
    }

    /**
     * Sends the actual usage statistic to the shop
     *
     * @throws \dml_exception
     */
    public function sendUsage() {
        $instance_id = $this->getInstanceId();

        $response = false;

        $course_licenses = $this->getCourseLicenses([
                IVS_LICENCSE_ACTIVE,
                IVS_LICENCSE_OVERBOOKED,
        ]);
        $instance_licenses = $this->getInstanceLicenses([
                IVS_LICENCSE_ACTIVE,
                IVS_LICENCSE_OVERBOOKED,
        ]);
        if (empty($course_licenses) && empty($instance_licenses)) {
            return $response;
        }

        // usage course licenses
        $course_users = [
                'already' => [],
                'users' => 0,
        ];

        $sum_course_users = 0;

        foreach ($course_licenses as $cl) {
            // we are interested in active licenses
            if (empty($cl->course_id)) {
                continue;
            }

            $course_users = $this->getNumCourseMembers($cl->course_id, $course_users);
            $sum_course_users += $course_users['users'];

            $request_data = [
                    "instance_id" => $instance_id,
                    'license_id' => $cl->id,
                    'spots_in_use' => $course_users['users'],
            ];
            $this->sendRequest("usage", $request_data);
        }

        foreach ($instance_licenses as $il) {
            $all_user = $this->getNumInstanceMembers();
            $spots_needed = $all_user - $sum_course_users;
            $request_data = [
                    "instance_id" => $instance_id,
                    'license_id' => $il->id,
                    'spots_in_use' => $spots_needed,
            ];
            $response = $this->sendRequest("usage", $request_data);
        }

        $this->getStatus(true);

        return $response;

    }

    /**
     * Gives the amount of users on a specified course
     *
     * @param $course_id
     * @param array $allready_goten_users
     *
     * @return array
     */
    public function getNumCourseMembers($course_id, $allready_goten_users = []) {
        $users = $this->getUserFromCourse($course_id);

        foreach ($users as $key => $value) {
            $allready_goten_users['already'][$key] = $value;
        }

        $allready_goten_users['users'] = count($users);
        return $allready_goten_users;
    }

    /**
     * Gives the amount of members on the instance
     *
     * @return int
     */
    public function getNumInstanceMembers() {
        return count($this->getAllUserFromInstance());
    }

    public function sendRequest($type, $request_data) {
        $pathAvailable = [
                "usage" => IVS_CORE_API_CALLBACK_USAGE,
                "coreRegister" => IVS_CORE_API_CALLBACK_REGISTER,
                "status" => IVS_CORE_API_CALLBACK_STATUS,
                "callback_cdn" => IVS_CORE_API_CALLBACK_CDN,
                "activate" => IVS_CORE_API_CALLBACK_ACTIVATE,
                "release" => IVS_CORE_API_CALLBACK_RELEASE,
                "instance" => IVS_CORE_API_CALLBACK_INSTANCE,
        ];
        $path = $pathAvailable[$type];
        if (empty($path)) {
            throw new \Exception('Unknown path ' . $type);
        }

        return $this->sendCurlRequest($path, "POST", $request_data);
    }

    public function cronRuntimeTooOld() {
        $lastRun = strtotime('NOW') - strtotime(get_config('mod_ivs', 'ivs_schedule_task'));
        $maxTime = IVS_CORE_CRON_WAITING_TIME;
        if ($lastRun > $maxTime) {
            return true;
        }
        return false;
    }

    public function setLastRuntime() {
        set_config('ivs_schedule_task', date('Y-m-d H:i:s', time()), 'mod_ivs');
    }

    public function setTestsystemInstanceId($testsystem_instance_id) {
        $request_data = [
                'instance_id' => $this->getInstanceId(),
                'testsystem_instance_id' => $testsystem_instance_id,
                'action' => IVS_ACTION_TESTSYSTEM
        ];
        $response = $this->sendRequest("instance", $request_data);
        return $response;
    }

    public function setPlayerVersion($player_version) {
        $request_data = [
                'instance_id' => $this->getInstanceId(),
                'player_version' => $player_version,
                'action' => IVS_ACTION_PLAYERVERSION
        ];
        $response = $this->sendRequest("instance", $request_data);
        return $response;
    }

}
