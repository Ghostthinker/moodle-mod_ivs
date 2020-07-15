<?php

namespace mod_ivs\output\license;

use renderable;
use renderer_base;
use templatable;

class settings_license_data_policy_view implements renderable, templatable {

    public function __construct() {
    }

    public function export_for_template(renderer_base $output) {
        $data = new \stdClass;
        $data->data_policy_text = get_string('ivs_license_data_policy', 'ivs');
        return $data;
    }
}
