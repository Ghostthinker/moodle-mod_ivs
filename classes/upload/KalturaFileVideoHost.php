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
 * This class is for the Kaltura video file host
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\upload;

/**
 * Class KalturaFileVideoHost
 */
class KalturaFileVideoHost implements IVideoHost {

    /**
     * @var \stdClass
     */
    protected $ivs;

    /**
     * @var \stdClass
     */
    protected $coursemodule;

    /**
     * KalturaFileVideoHost constructor.
     *
     * @param \stdClass $cm
     * @param \stdClass $ivs
     */
    public function __construct($cm, $ivs) {
        $this->ivs = $ivs;
        $this->coursemodule = $cm;
    }

    /**
     * Get the video
     * @return mixed|string|void
     */
    public function get_video() {
        global $CFG;

        if (!file_exists($CFG->dirroot . '/local/kaltura/API/KalturaClient.php')) {
            return;
        }
        require_once($CFG->dirroot . '/mod/ivs/classes/KalturaService.php');

        $parts = explode("://", $this->ivs->videourl);
        $id = $parts[1];

        $kalturaservice = new \KalturaService();

        $url = $kalturaservice->getMediaDataUrl($id);

        return $url;
    }

    /**
     * Save video data
     * @param \stdClass $formvalues
     */
    public function save_video($formvalues) {

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
        // TODO: Implement prerender() method.
    }

    /**
     * Get the cross origin tag
     * @return string
     */
    public function getcrossorigintag() {
        return 'crossorigin="anonymous"';
    }


  public function rendermediacontainer($PAGE) {

    $renderer = $PAGE->get_renderer('ivs');

    $renderable = new \mod_ivs\output\mediacontainer\html5_video_view($this->get_video(), $this->getcrossorigintag());
    return $renderer->render($renderable);

  }


}
