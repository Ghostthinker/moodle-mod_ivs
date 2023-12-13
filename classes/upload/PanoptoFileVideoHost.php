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
 * This class is for the panopto video file host
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\upload;

/**
 * Class PanoptoFileVideoHost
 */
class PanoptoFileVideoHost implements IVideoHost {

    /**
     * @var \stdClass
     */
    protected $ivs;

    /**
     * @var \stdClass
     */
    protected $coursemodule;

    /**
     * @var \stdClass
     */
    protected $course;

    /**
     * PanoptoFileVideoHost constructor.
     *
     * @param \stdClass $cm
     * @param \stdClass $ivs
     * @param \stdClass $course
     */
    public function __construct($cm, $ivs, $course) {
        $this->ivs = $ivs;
        $this->coursemodule = $cm;
        $this->course = $course;
    }

    /**
     * Get the video
     * @return string
     */
    public function get_video() {
        global $DB;
        $parts = explode("://", $this->ivs->videourl);
        $data = $parts[1];

        $decodeddata = json_decode($data ?? '', true);
        $result = [];
        $result['servername'] = $decodeddata['servername'];
        $result['sessionid'] = current($decodeddata['sessionId']);

        return $result;
    }

    /**
     * Save video data
     * @param \stdClass $data
     */
    public function save_video($data) {
        // TODO: Implement saveVideo() method.
    }

    /**
     * Get the thumbnail
     */
    public function get_thumbnail() {
        // TODO: Implement getThumbnail() method.
    }

    /**
     * Prerender function
     */
    public function prerender(&$urliframe) {

    }

    /**
     * Get the cross origin tag
     * @return string
     */
    public function getcrossorigintag() {
        return '';
    }

    public function rendermediacontainer($PAGE) {

        $renderer = $PAGE->get_renderer('ivs');

        $video = $this->get_video();
        $renderable = new \mod_ivs\output\mediacontainer\panopto_video_view($video['servername'], $video['sessionid']);
        return $renderer->render($renderable);

    }



}
