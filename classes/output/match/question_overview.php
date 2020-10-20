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

// Standard GPL and phpdocs.
namespace mod_ivs\output\match;

use renderable;
use renderer_base;
use templatable;
use stdClass;

class question_overview implements renderable, templatable {

    var $question = null;
    var $module = null;

    public function __construct($question, $module) {
        $this->question = $question;
        $this->module = $module;
    }

    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

        $data->id = $this->question['nid'];
        $data->question = $this->question['question_body'];
        if (strlen($this->question['title']) > 0) {
            $data->question = $this->question['title'] . ': ' . $data->question;
        }

        $data->link = new \moodle_url('/mod/ivs/question_answers.php',
                array(
                        'id' => $this->module->id,
                        'vid' => $this->question['video'],
                        'qid' => $data->id,
                        'perpage' => 10
                )
        );

        return $data;
    }
}
