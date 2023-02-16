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
 * Class to create a match config
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\ivs_match;

/**
 * Class MatchConfig
 */
class MatchConfig {

    /**
     * @var string
     */
    public $assessment_type = AssessmentConfig::ASSESSMENT_TYPE_QUIZ;

    /**
     * @var int
     */
    public $rate = 100;

    /**
     * @var int
     */
    public $attempts = 0;

    /**
     * @var bool
     */
    public $allow_repeat_answers = false;

    /**
     * @var bool
     */
    public $player_controls_enabled = false;

    /**
     * @var bool
     */
    public $show_solution = true;

    /**
     * @var bool
     */
    public $show_feedback = false;

    /**
     * Check if the attempts are limited
     * @return bool
     */
    public function has_unlimited_attempts() {
        return $this->attempts == 0;
    }

    /**
     * Check if the user has passed
     * @param int $score
     *
     * @return bool
     */
    public function hasPassed(float $score) {
        return $score >= $this->rate;
    }
}
