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
 * Class for storing the assessment config
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\ivs_match;

/**
 * Class AssessmentConfig
 */
class AssessmentConfig {

    /**
     * @var string
     */
    const TAKES_SIMULATE = 'TAKES_SIMULATE';

    /**
     * @var string
     */
    const TAKES_LEFT_NEW = 'TAKES_LEFT_NEW';

    /**
     * @var string
     */
    const TAKES_LEFT_PROGRESS = 'TAKES_LEFT_PROGRESS';

    /**
     * @var string
     */
    const TAKES_LEFT_COMPLETED_SUCCESS = 'TAKES_LEFT_COMPLETED';

    /**
     * @var string
     */
    const NO_TAKES_LEFT_COMPLETED_SUCCESS = 'NO_TAKES_LEFT_COMPLETED_SUCCESS';

    /**
     * @var string
     */
    const NO_TAKES_LEFT_COMPLETED_FAILED = 'NO_TAKES_LEFT_COMPLETED_FAILED';

    /**
     * @var string
     */
    const ASSESSMENT_TYPE_FORMATIVE = 'FORMATIVE'; // No takes, a formative training.


    /**
     * @var string
     */
    const ASSESSMENT_TYPE_NONE = 0;      // Multiple takes, takes count as score unit, one take at a time.

    /**
     * @var string
     */
    const ASSESSMENT_TYPE_QUIZ = 1;      // Multiple takes, takes count as score unit, one take at a time.

    /**
     * @var string
     */
    const ASSESSMENT_TYPE_TIMING = 2; //

    /**
     * @var string
     */
    const NO_TAKES_LEFT_COMPLETED_SUCCESS_NO_SUMMARY = 'NO_TAKES_LEFT_COMPLETED_SUCCESS_NO_SUMMARY'; //

    /**
     * @var int
     */
    public $context_id;

    /**
     * @var string
     */
    public $context_label;

    /**
     * @var int
     */
    public $takes_left;

    /**
     * @var string
     */
    public $status;

    /** @var MatchConfig */
    public $matchConfig;

    /** @var \mod_ivs\ivs_match\MatchTake[] */
    public $takes;

    /**
     * @var string
     */
    public $status_description;

}
