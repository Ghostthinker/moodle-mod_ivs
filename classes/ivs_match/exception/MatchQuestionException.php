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
 * Class for custom exception
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\ivs_match\exception;

/**
 * Class MatchQuestionException
 */
class MatchQuestionException extends \Exception {

    /**
     * @var \stdClass
     */
    private $questionnode;

    /**
     * Get the question node
     * @return \stdClass
     */
    public function get_questionnode() {
        return $this->questionnode;
    }

    /**
     * MatchQuestionException constructor.
     *
     * @param \stdClass $questionnode
     * @param string $message
     * @param int $code
     * @param \mod_ivs\ivs_match\exception\Throwable|null $previous
     */
    public function __construct($questionnode, $message = "", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->questionnode = $questionnode;

    }

}
