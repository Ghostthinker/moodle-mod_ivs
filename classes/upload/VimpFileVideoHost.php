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
 *
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\upload;


/**
 * Class VimpFileVideoHost
 */
class VimpFileVideoHost implements IVideoHost {

    /**
     * @var \stdClass
     */
    protected $ivs;

    /**
     * @var \stdClass
     */
    protected $coursemodule;
    const TYPE_VIMP = 'vimp';
    const TYPE_UNSUPPORTED = 'unsupported';

    /**
     * ExternalSourceVideoHost constructor.
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
     *
     * @return string
     */
    public function get_video() {

        $config = $this->getVimpConfiguration();
        if(empty($config)) {
            return null;
        }

        $sourceinfo = $this->getexternalsourceinfo();

        $response = file_get_contents($config->masterurl . 'getMedium?apikey=' . $config->skey . '&mediumid=' . $sourceinfo['mediumid']);

        if(empty($response)) {
            return null;
        }
        $xml = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json = json_encode($xml);
        $response_array = json_decode($json ?? '', true);
        $medium = current($response_array['medium']['medium']);

        return $medium;
    }

    /**
     * Save video data
     *
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
        // TODO: Implement prerender() method.
    }

    /**
     * Get the cross origin tag
     *
     * @return string
     */
    public function getcrossorigintag() {
        return '';
    }

    public function rendermediacontainer($PAGE) {
        $renderer = $PAGE->get_renderer('ivs');

        $renderable = new \mod_ivs\output\mediacontainer\vimp_video_view($this->get_video());
        return $renderer->render($renderable);
    }

    public static function getexternalsourceinfobyvideourl($videourl) {
        $result = [];
        $parts = explode("://", $videourl);
        $result['type'] = $parts[0];
        $result['mediumid'] = $parts[1];
        return $result;
    }

    private function getexternalsourceinfo() {
        return VimpFileVideoHost::getexternalsourceinfobyvideourl($this->ivs->videourl);
    }

    public static function getVimpConfiguration() {
        $config = get_config('auth_vimpsso');
        if (empty($config) || empty($config->skey) || empty($config->masterurl)) {
            return null;
        }

        return $config;
    }


}
