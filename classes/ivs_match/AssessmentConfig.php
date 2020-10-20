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

class AssessmentConfig {

    const TAKES_SIMULATE = 'TAKES_SIMULATE';
    const TAKES_LEFT_NEW = 'TAKES_LEFT_NEW';
    const TAKES_LEFT_PROGRESS = 'TAKES_LEFT_PROGRESS';
    const TAKES_LEFT_COMPLETED_SUCCESS = 'TAKES_LEFT_COMPLETED';
    const NO_TAKES_LEFT_COMPLETED_SUCCESS = 'NO_TAKES_LEFT_COMPLETED_SUCCESS';
    const NO_TAKES_LEFT_COMPLETED_FAILED = 'NO_TAKES_LEFT_COMPLETED_FAILED';

    const ASSESSMENT_TYPE_TAKES = 'TAKES';   //multiple takes, takes count as score unit, one take at a time
    const ASSESSMENT_TYPE_FORMATIVE = 'FORMATIVE'; //no takes, a formative training

    public $context_id;
    public $context_label;
    public $takes_left;
    public $status;

    /** @var MatchConfig */
    public $matchConfig;

    /** @var \mod_ivs\ivs_match\MatchTake[] */
    public $takes;
    public $status_description;

}
