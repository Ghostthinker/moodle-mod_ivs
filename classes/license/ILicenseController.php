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
 * ILicenseController
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\license;

/**
 * Interface ILicenseController
 *
 */
interface ILicenseController {

    /**
     * Interface function
     * @return mixed
     */
    public function generate_instance_id();

    /**
     * Interface function
     * @return mixed
     */
    public function get_instance_id();

    /**
     * Interface function
     * @param null $context
     *
     * @return mixed
     */
    public function has_active_license($context = null);

    /**
     * Interface function
     * @param null|\stdClass $context
     *
     * @return mixed
     */
    public function get_active_license($context = null);

    /**
     * Interface function
     * @param int $instanceid
     *
     * @return mixed
     */
    public function core_register($instanceid);

    /**
     * Interface function
     * @param int $courseid
     * @param int $licenseid
     *
     * @return mixed
     */
    public function activate_course_license($courseid, $licenseid);

    /**
     * Interface function
     * @return mixed
     */
    public function get_status();

    /**
     * Interface function
     * @param \stdClass $license
     *
     * @return mixed
     */
    public function get_license_type($license);

    /**
     * Interface function
     * @param int $licenseid
     *
     * @return mixed
     */
    public function get_cdn_source($licenseid);

    /**
     * Interface function
     * @param int $courseid
     * @param int $licenseid
     *
     * @return mixed
     */
    public function release_course_license($courseid, $licenseid);

    /**
     * Interface function
     * @param \stdClass $status
     * @param false $reset
     *
     * @return mixed
     */
    public function get_course_licenses($status, $reset = false);

    /**
     * Interface function
     * @param \stdClass $status
     * @param false $reset
     *
     * @return mixed
     */
    public function get_instance_licenses($status, $reset = false);

    /**
     * Interface function
     * @param array $courselicenses
     *
     * @return mixed
     */
    public function get_course_license_options($courselicenses);

    /**
     * Interface function
     * @return mixed
     */
    public function send_usage();

}
