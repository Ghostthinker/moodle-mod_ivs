<?php
// Standard GPL and phpdocs
namespace mod_ivs\output\license;

use renderable;
use renderer_base;
use templatable;
use stdClass;

class settings_license_none_view implements renderable, templatable {

    public function __construct() {
    }

    public function export_for_template(renderer_base $output) {

        $lc = ivs_get_license_controller();
        return $lc->getSettingsLicenseNoneData();
    }
}
