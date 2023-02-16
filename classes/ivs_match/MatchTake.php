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
 * Class for the match takes
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\ivs_match;

/**
 * Class MatchTake
 */
class MatchTake {

    /**
     * @var string
     */
    const STATUS_NEW = 'new';

    /**
     * @var string
     */
    const STATUS_PROGRESS = 'progress';

    /**
     * @var string
     */
    const STATUS_PASSED = 'passed';

    /**
     * @var string
     */
    const STATUS_FAILED = 'failed';

    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $userid;

    /**
     * @var int
     */
    public $contextid;

    /**
     * @var int
     */
    public $videoid;

    /**
     * @var int
     */
    public $created;

    /**
     * @var int
     */
    public $changed;

    /**
     * @var bool
     */
    public $completed;

    /**
     * @var int
     */
    public $score;

    /**
     * @var string
     */
    public $status = self::STATUS_NEW;

    /**
     * @var bool
     */
    public $evaluated = false;

    /**
     * @var bool
     */
    public function is_completed(){
        return $this->completed > 0;
    }

}
