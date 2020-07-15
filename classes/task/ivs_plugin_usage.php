<?php

namespace mod_ivs\task;

use mod_ivs\license\MoodleLicenseController;

class ivs_plugin_usage extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('ivs_plugin', 'mod_ivs');
    }

    public function execute() {
        $controller = new MoodleLicenseController();
        $controller->sendUsage();
    }

}
