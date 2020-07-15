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

class PlayerVersionForm extends moodleform {

    public function definition() {

        $mform = $this->_form;
        $mform->setType('ivs_player_version', PARAM_RAW);
        $lc = ivs_get_license_controller();

        $status = $lc->getStatus();
        $group = [];
        $player_version = json_decode(json_encode($status->player_versions), true);

        $mform->addElement('header', 'section_playerversion', get_string('ivs_set_player_version', 'ivs'));
        $group[] = &$mform->createElement('select', 'player_version', "", $player_version);
        $mform->setDefault('ivs_player_version[player_version]', $status->used_player_version);
        $group[] = &$mform->createElement('submit', '', get_string('ivs_set_player_version', 'ivs'));
        $mform->addGroup($group, 'ivs_player_version',
                get_string('ivs_actual_player_version', 'mod_ivs') . $status->used_player_version);
        $mform->setExpanded("section_playerversion", false);
    }

    //Custom validation should be added here
    function validation($data, $files) {
    }

}
