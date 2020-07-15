<?php

namespace mod_ivs\task;

use mod_ivs\Report;

class cockpit_report_weekly extends cockpit_report_base {

    public function __construct() {

        $this->reporting = Report::ROTATION_WEEK;
        $this->mail_body_rotation = 'cockpit_report_mail_body_rotation_weekly';
        $this->mail_subject = 'cockpit_report_mail_subject_weekly';
        $this->cockpit_report = 'cockpit_report_weekly';

    }

    public function get_name() {
        // Shown in admin screens
        return $this->cockpit_report;
    }

}
