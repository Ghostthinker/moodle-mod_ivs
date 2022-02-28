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
 * PlayerVersionForm.php
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\license;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once("$CFG->libdir/formslib.php");

use mod_ivs\CourseService;
use moodleform;

/**
 * Class PlayerVersionForm
 *
 */
class PlayerVersionForm extends moodleform {

    /**
     * Definition for the form
     */
    public function definition() {

        $mform = $this->_form;
        $mform->setType('ivs_player_version', PARAM_RAW);
        $lc = ivs_get_license_controller();

        $status = $lc->get_status();
        if (!empty($status)) {
            $group = [];
            $playerversion = json_decode(json_encode($status->player_versions), true);

            $mform->addElement('header', 'section_playerversion', get_string('ivs_set_player_version', 'ivs'));
            $group[] = &$mform->createElement('select', 'player_version', "", $playerversion);
            $mform->setDefault('ivs_player_version[player_version]', $status->used_player_version);
            $group[] = &$mform->createElement('submit', '', get_string('ivs_set_player_version', 'ivs'));
            $mform->addGroup($group, 'ivs_player_version',
                    get_string('ivs_actual_player_version', 'mod_ivs') . $status->used_player_version);
            $mform->setExpanded("section_playerversion", false);
        }
    }

    /**
     * Custom validation should be added here.
     * @param \stdClass $data
     * @param \stdClass $files
     */
    public function validation($data, $files) {
    }

}
