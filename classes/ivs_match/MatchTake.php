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

/**
 * Class MatchTake
 *
 * @package all\modules\features\ivs_match\inc\ivs_match\exception
 */
class MatchTake {

    const STATUS_NEW = 'new';
    const STATUS_PROGRESS = 'progress';
    const STATUS_PASSED = 'passed';
    const STATUS_FAILED = 'failed';

    public $id;
    public $userid;
    public $contextid;
    public $videoid;
    public $created;
    public $changed;
    public $completed;
    public $score;
    public $status = MatchTake::STATUS_NEW;
    public $evaluated = false;

}
