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

        $decodeddata = json_decode($data, true);

        $servername = $decodeddata['servername'];
        $videoid = $decodeddata['sessionId'];

        $videourl = 'https://' . $servername . '/panopto/podcast/download/' . $videoid[0] . '.mp4?mediaTargetType=videoPodcast';
        return $videourl;
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
        if (!file_exists(dirname(__FILE__) . '/../../../../blocks/panopto/lib/panopto_data.php')) {
            return;
        }

        global $USER, $CFG;

        require_once(dirname(__FILE__) . '/../../../../blocks/panopto/lib/panopto_data.php');
        require_once($CFG->dirroot . '/blocks/panopto/lib/block_panopto_lib.php');
        $configuredserverarray = panopto_get_configured_panopto_servers();

        $panoptodata = new \panopto_data($this->course->id);
        if (!empty($panoptodata->servername) && !empty($panoptodata->applicationkey)) {
            $panoptodata->sync_external_user($USER->id);
        } else {
            return;
        }
        $instancestring = '&instance=' .$panoptodata->instancename;
        $sessiongroupid = $panoptodata->sessiongroupid;
        $iframeurl = 'https://' .$panoptodata->servername.
          '/Panopto/Pages/Sessions/EmbeddedUpload.aspx?playlistsEnabled=true'.$instancestring.'&folderID='.$sessiongroupid;

        // Load the iframe for the session - reload the  player afterward and remove it. This is required to get the cookie .
        echo '<iframe width="100px" height="100px" src="'.$iframeurl.'" style="display:none" allowfullscreen="allowfullscreen"
        mozallowfullscreen="mozallowfullscreen" 
        msallowfullscreen="msallowfullscreen" 
        oallowfullscreen="oallowfullscreen" 
        webkitallowfullscreen="webkitallowfullscreen"
        onload="setTimeout(() => {$(\'iframe.edubreak-responsive-iframe\')[0].src=\''.$urliframe.'\';},2000);"></iframe>';
        $urliframe = '';
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

    $renderable = new \mod_ivs\output\mediacontainer\html5_video_view($this->get_video(), $this->getcrossorigintag());
    return $renderer->render($renderable);

  }



}
