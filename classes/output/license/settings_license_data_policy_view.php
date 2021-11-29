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
 * Output class for rendering the policy view
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
 * Class settings_license_data_policy_view
 *
 */
class settings_license_data_policy_view implements renderable, templatable {

    /**
     * settings_license_data_policy_view constructor.
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
        $data->data_policy_text = get_string('ivs_license_data_policy', 'ivs');
        return $data;
    }
}
