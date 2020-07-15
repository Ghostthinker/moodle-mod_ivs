<?php
// Standard GPL and phpdocs
namespace mod_ivs\output\license;

use renderable;
use renderer_base;
use templatable;
use stdClass;

class settings_license_course_view implements renderable, templatable {

    public function __construct($course_licenses, $instance_licenses) {
        $this->course_licenses = $course_licenses;
        $this->instance_licenses = $instance_licenses;
    }

    public function export_for_template(renderer_base $output) {

        $lc = ivs_get_license_controller();
        return $lc->getSettingsLicenseCourseData($this->course_licenses, $this->instance_licenses, $output);
    }
}
