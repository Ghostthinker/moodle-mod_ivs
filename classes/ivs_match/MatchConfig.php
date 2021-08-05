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

namespace mod_ivs\ivs_match;

class MatchConfig {

    public $assessmenttype = AssessmentConfig::ASSESSMENT_TYPE_TAKES;

    public $rate = 100;
    public $attempts = 0;
    public $allow_repeat_answers = false;
    public $player_controls_enabled = false;
    public $show_solution = true;
    public $show_feedback = false;

    /**
     * Create match config object from array
     *
     * @param $ar
     * @return MatchConfig
     */
    public static function from_array($ar) {
        $mc = new MatchConfig();

        if (array_key_exists('attempts', $ar)) {
            $mc->attempts = $ar['attempts'];
        }

        if (array_key_exists('rate', $ar)) {
            $mc->rate = $ar['rate'];
        }
        return $mc;
    }

    public function has_unlimited_attempts() {
        return $this->attempts == 0;
    }

    public function has_passed($score) {
        return $score >= $this->rate;
    }
}
