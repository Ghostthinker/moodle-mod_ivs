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
 * Class witch create the weekly cockpit report
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\task;

use mod_ivs\Report;

/**
 * Class cockpit_report_weekly
 */
class cockpit_report_weekly extends cockpit_report_base {

    /**
     * cockpit_report_weekly constructor.
     */
    public function __construct() {

        $this->reporting = Report::ROTATION_WEEK;
        $this->mailbodyrotation = 'cockpit_report_mail_body_rotation_weekly';
        $this->mailsubject = 'cockpit_report_mail_subject_weekly';
        $this->cockpitreport = 'cockpit_report_weekly';

    }

    /**
     * Get the name of the report
     * @return string
     */
    public function get_name() {
        // Shown in admin screens.
        return $this->cockpitreport;
    }

}
