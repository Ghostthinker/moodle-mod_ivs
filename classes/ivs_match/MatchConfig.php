<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 15.08.2018
 * Time: 12:01
 */

namespace mod_ivs\ivs_match;

class MatchConfig {

    public $assessment_type = AssessmentConfig::ASSESSMENT_TYPE_TAKES;

    public $rate = 100; //int
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
    public static function fromArray($ar) {
        $mc = new MatchConfig();

        if (array_key_exists('attempts', $ar)) {
            $mc->attempts = $ar['attempts'];
        }

        if (array_key_exists('rate', $ar)) {
            $mc->rate = $ar['rate'];
        }
        return $mc;
    }

    public function hasUnlimitedAttempts() {
        return $this->attempts == 0;
    }

    public function hasPassed($score) {
        return $score >= $this->rate;
    }
}
