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
 * This template class is used for course service
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\service;

trait CourseTrait {

    private $courseservice;

    /**
     * Get the course service
     * @return mixed
     */
    public function get_courseservice() {
        if ($this->courseservice == null) {
            $this->courseservice = new courseservice();
        }
        return $this->courseservice;
    }

    /**
     * Set the course service
     * @param mixed $courseservice
     */
    public function set_courseservice($courseservice) {
        $this->courseservice = $courseservice;
    }

}
