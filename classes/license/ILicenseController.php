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
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\license;

interface ILicenseController {

    public function generate_instance_id();

    public function get_instance_id();

    public function has_active_license($context = null);

    public function get_active_license($context = null);

    public function core_register($instanceid);

    public function activate_course_license($courseid, $licenseid);

    public function get_status();

    public function get_license_type($license);

    public function get_cdn_source($licenseid);

    public function release_course_license($courseid, $licenseid);

    public function get_course_licenses($status, $reset = false);

    public function get_instance_licenses($status, $reset = false);

    public function get_course_license_options($courselicenses);

    public function send_usage();

}
