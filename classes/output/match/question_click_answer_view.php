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
 * Output class for rendering the click answers
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

// Standard GPL and phpdocs.
namespace mod_ivs\output\match;

use mod_ivs\IvsHelper;
use mod_ivs\MoodleMatchController;
use renderable;
use renderer_base;
use templatable;
use stdClass;
use tool_langimport\controller;

/**
 * Class question_click_answer_view
 *
 */
class question_click_answer_view implements renderable, templatable {

    /**
     * @var array|null
     */
    public $answer = null;

    /**
     * question_click_answer_view constructor.
     *
     * @param array $answer
     * @param stdClass $courseuser
     */
    public function __construct($answer, $courseuser) {
        $this->answer = $answer;
        $this->course_user = $courseuser;
    }

    /**
     * Render mustache template
     * @param \renderer_base $output
     *
     * @return \stdClass
     */
    public function export_for_template(renderer_base $output) {

        $controller = new MoodleMatchController();

        return $controller->get_question_answers_data_click_question($this->answer, $this->course_user);
    }
}
