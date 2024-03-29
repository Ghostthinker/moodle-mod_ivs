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
 * Class for creating a question summary
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\ivs_match\question;

/**
 * Class QuestionSummary
 */
class QuestionSummary {

    /**
     * @var int
     */
    public $question_id;

    /**
     * @var string
     */
    public $question_title;

    /**
     * @var string
     */
    public $question_body;

    /**
     * @var string
     */
    public $question_type;

    /**
     * @var bool
     */
    public $first_attempt_correct;

    /**
     * @var bool
     */
    public $last_attempt_correct;

    /**
     * @var int
     */
    public $num_students_participation; // Number of course members with role student that answered the question at least one time.

    /**
     * @var int
     */
    public $num_students_total;

}
