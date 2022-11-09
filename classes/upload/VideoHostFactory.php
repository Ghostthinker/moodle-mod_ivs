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
 * This class includes all video hosts
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\upload;

/**
 * Class VideoHostFactory
 */
class VideoHostFactory {

    /**
     * Returns the filehost for an activity
     * @param \stdClass $cm
     * @param \stdClass $ivs
     * @param null $course
     *
     * @return \mod_ivs\upload\MoodleFileVideoHost|\mod_ivs\upload\OpenCastFileVideoHost|\mod_ivs\upload\KalturaFileVideoHost|\mod_ivs\upload\PanoptoFileVideoHost|\mod_ivs\upload\TestingFileVideoHost|\mod_ivs\upload\ExternalSourceVideoHost
     */
    public static function create($cm, $ivs, $course = null) {

        $parts = explode("://", $ivs->videourl);

        switch ($parts[0]) {
            case 'MoodleFileVideoHost':
                return new MoodleFileVideoHost($cm, $ivs);
            case 'OpenCastFileVideoHost':
            case 'SwitchCastFileVideoHost':
                return new OpenCastFileVideoHost($cm, $ivs);
            case 'TestingFileVideoHost':
                return new TestingFileVideoHost($cm, $ivs);
            case 'PanoptoFileVideoHost':
                return new PanoptoFileVideoHost($cm, $ivs, $course);
            case 'KalturaFileVideoHost':
                return new KalturaFileVideoHost($cm, $ivs);
            case 'ExternalSourceVideoHost':
                return new ExternalSourceVideoHost($cm, $ivs);
        }
    }
}
