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
 * Output class for rendering the question summary view
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

/**
 * Class question_summary_view
 *
 */
class question_summary_view implements renderable, templatable {

    /**
     * @var array|null
     */
    public $question = null;

    /**
     * @var \stdClass|null
     */
    public $coursestudents = null;

    /**
     * question_summary_view constructor.
     *
     * @param array $question
     * @param stdClass $coursestudents
     */
    public function __construct($question, $coursestudents) {
        $this->question = $question;
        $this->coursestudents = $coursestudents;
    }

    /**
     * Render mustache template
     * @param \renderer_base $output
     *
     * @return \mod_ivs\ivs_match\question\QuestionSummary|\stdClass
     */
    public function export_for_template(renderer_base $output) {

        $controller = new MoodleMatchController();

        return $controller->get_question_summary_formated($this->question, $this->coursestudents);

    }
}
