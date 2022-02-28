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
 * TestsystemForm.php
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
 * Class TestsystemForm
 *
 */
class TestsystemForm extends moodleform {

    /**
     * Definition for the form
     */
    public function definition() {

        $mform = $this->_form;
        $mform->setType('ivs_testsystem', PARAM_RAW);
        $lc = ivs_get_license_controller();

        $status = $lc->get_status();
        if(!empty($status)) {
            $group = [];
            $mform->addElement('header', 'section_testsystem', get_string('ivs_set_testsystem', 'ivs'));
            $group[] = &$mform->createElement('text', '', '',
                    ['size' => 40, 'style' => 'margin-left:-20px;', 'value' => $status->testsystem]);
            $group[] = &$mform->createElement('submit', 'submitbutton', get_string('ivs_set_testsystem', 'ivs'));
            $mform->addGroup($group, 'ivs_testsystem', get_string('ivs_testsystem', 'ivs'));
            $mform->setExpanded("section_testsystem", false);
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
