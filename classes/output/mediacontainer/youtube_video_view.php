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
 * Output class for rendering annotation audio player
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

// Standard GPL and phpdocs.
namespace mod_ivs\output\mediacontainer;

use mod_ivs\IvsHelper;
use mod_ivs\upload\IVideoHost;
use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Class youtube_video_view
 */
class youtube_video_view implements renderable, templatable {

    /**
     * @var \mod_ivs\annotation|null
     */
    public $youtubeid = null;

    /**
     * annotation_audio_player_view constructor.
     *
     * @param \mod_ivs\annotation $annotation
     */
    public function __construct($youtubeid) {
        $this->youtubeid = $youtubeid;
    }

    /**
     * @param \renderer_base $output
     *
     * @return \stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

        $data->youtubeid = $this->youtubeid;
        return $data;
    }
}
