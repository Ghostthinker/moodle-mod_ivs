<?php
/**
 * Created by PhpStorm.
 * User: Ghostthinker
 * Date: 20.11.2018
 * Time: 13:29
 */

namespace mod_ivs\license;

global $CFG;
require_once("$CFG->libdir/formslib.php");

use mod_ivs\CourseService;
use moodleform;

class TestsystemForm extends moodleform {

    public function definition() {

        $mform = $this->_form;
        $mform->setType('ivs_testsystem', PARAM_RAW);
        $lc = ivs_get_license_controller();

        $status = $lc->getStatus();
        $group = [];
        $mform->addElement('header', 'section_testsystem', get_string('ivs_set_testsystem', 'ivs'));
        $group[] = &$mform->createElement('text', '', '',
                ['size' => 40, 'style' => 'margin-left:-20px;', 'value' => $status->testsystem]);
        $group[] = &$mform->createElement('submit', 'submitbutton', get_string('ivs_set_testsystem', 'ivs'));
        $mform->addGroup($group, 'ivs_testsystem', get_string('ivs_testsystem', 'ivs'));
        $mform->setExpanded("section_testsystem", false);
    }

    //Custom validation should be added here
    function validation($data, $files) {
    }

}
