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
 * Output class for rendering main instances for courses
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */


namespace mod_ivs\output\license;

use renderable;
use renderer_base;
use templatable;

/**
 * Class settings_license_main_view
 *
 */
class settings_license_main_view implements renderable, templatable {

    /**
     * settings_license_main_view constructor.
     */
    public function __construct() {
    }

    /**
     * Render mustache template
     * @param \renderer_base $output
     *
     * @return \stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new \stdClass;
        $lc = ivs_get_license_controller();
        $data->instance_id_label = get_string('ivs_instance_id_label', 'ivs');
        $data->license_instance_id = $lc->get_instance_id();
        $data->manage_license_label = get_string('ivs_package_button_label', 'ivs');
        $data->manage_license_href = $lc->get_core_url();
        $data->shop_info_text = get_string('ivs_package_value', 'ivs');
        return $data;
    }
}
