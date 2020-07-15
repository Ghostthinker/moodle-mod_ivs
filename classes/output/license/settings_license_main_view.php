<?php

namespace mod_ivs\output\license;

use renderable;
use renderer_base;
use templatable;

class settings_license_main_view implements renderable, templatable {

    public function __construct() {
    }

    public function export_for_template(renderer_base $output) {
        $data = new \stdClass;
        $lc = ivs_get_license_controller();
        $data->instance_id_label = get_string('ivs_instance_id_label', 'ivs');
        $data->license_instance_id = $lc->getInstanceId();
        $data->manage_license_label = get_string('ivs_package_button_label', 'ivs');
        $data->manage_license_href = $lc->getCoreUrl();
        $data->shop_info_text = get_string('ivs_package_value', 'ivs');
        return $data;
    }
}
