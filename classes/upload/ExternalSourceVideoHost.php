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

use mod_ivs\ivs_match\question\QuestionSummary;
use mod_ivs\video;
use function GuzzleHttp\Psr7\_caseless_remove;

/**
 * Class PanoptoFileVideoHost
 */
class ExternalSourceVideoHost implements IVideoHost {

    /**
     * @var \stdClass
     */
    protected $ivs;

    /**
     * @var \stdClass
     */
    protected $coursemodule;

    const TYPE_YOUTUBE = 'youtube';
    const TYPE_VIMEO = 'vimeo';
    const TYPE_EXTERNAL = 'external';
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

    public static function parseExternalVideoSourceUrl($url) {
        if (empty($url)) {
            return [
                    'type' => ExternalSourceVideoHost::TYPE_UNSUPPORTED,
                    'details' => [],
                    'idstring' => null,
                    'originalstring' => null
            ];
        }

        //Check Youtube
        if ($details = self::externalVideoHostGetVideoJsonYoutube($url)) {
            return [
                    'type' => ExternalSourceVideoHost::TYPE_YOUTUBE,
                    'details' => $details,
                    'idstring' => "ExternalSourceVideoHost://" . json_encode($details),
                    'originalstring' => $url
            ];
        }

        //Check Vimeo
        if ($details = self::externalVideoHostGetVideoJsonVimeo($url)) {
            return [
                    'type' => ExternalSourceVideoHost::TYPE_VIMEO,
                    'details' => $details,
                    'idstring' => "ExternalSourceVideoHost://" . json_encode($details),
                    'originalstring' => $url
            ];
        }

        //Check External
        if ($details = self::externalVideoHostGetVideoJsonExternal($url)) {
            return [
                    'type' => ExternalSourceVideoHost::TYPE_EXTERNAL,
                    'details' => $details,
                    'idstring' => "ExternalSourceVideoHost://" . json_encode($details),
                    'originalstring' => $url
            ];
        }

        return [
                'type' => ExternalSourceVideoHost::TYPE_UNSUPPORTED,
                'details' => [],
        ];
    }

    private static function externalVideoHostGetVideoJsonYoutube($externalvideosource) {
        preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user|shorts)\/))([^\?&\"'>]+)/",
                $externalvideosource, $matches);

        if (empty($matches[1])) {
            return null;
        }

        return [
                'type' => ExternalSourceVideoHost::TYPE_YOUTUBE,
                'id' => $matches[1],
                'originalstring' => $externalvideosource
        ];
    }

    private static function externalVideoHostGetVideoJsonVimeo($externalvideosource) {
        preg_match("/^(?:http:|https:|)\/\/(?:player.|www.)?vimeo\.com\/(?:video\/|embed\/|watch\?\S*v=|v\/)?(\d*)/",
                $externalvideosource, $matches);

        if (empty($matches[1])) {
            return null;
        }

        return [
                'type' => ExternalSourceVideoHost::TYPE_VIMEO,
                'id' => $matches[1],
                'originalstring' => $externalvideosource
        ];
    }

    private static function externalVideoHostGetVideoJsonExternal($externalvideosource) {
        $source = $externalvideosource;

        $sourceavailable = false;
        $primaryresponse = @get_headers($source, 1);

        // todo handle 3xx redirects properly
        if (
                stripos($primaryresponse[0], "200") !== false ||
                stripos($primaryresponse[0], "206") !== false
        ) {
            // success
            if (stripos($primaryresponse["Content-Type"], "video") !== false) {
                $sourceavailable = true;
            }
        }

        if ($sourceavailable) {
            return [
                    'type' => ExternalSourceVideoHost::TYPE_EXTERNAL,
                    'source' => $source,
                    'originalstring' => $externalvideosource
            ];
        } else {
            return null;
        }

    }

    /**
     * Get the video
     *
     * @return string
     */
    public function get_video() {

        return null;
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

        $sourceinfo = $this->getexternalsourceinfo();

        switch ($sourceinfo['type']) {
            case ExternalSourceVideoHost::TYPE_YOUTUBE:
                $renderable = new \mod_ivs\output\mediacontainer\youtube_video_view($this->getexternalsourceinfo()['id']);
                return $renderer->render($renderable);
            case ExternalSourceVideoHost::TYPE_VIMEO:
                $renderable = new \mod_ivs\output\mediacontainer\vimeo_video_view($this->getexternalsourceinfo()['id']);
                return $renderer->render($renderable);
            case ExternalSourceVideoHost::TYPE_EXTERNAL:
                $renderable = new \mod_ivs\output\mediacontainer\html5_video_view($this->getexternalsourceinfo()['source']);
                return $renderer->render($renderable);
            default:
                break;
        }

        return 'unsupported external video';

    }

    public static function getexternalsourceinfobyvideourl($videourl) {

        $parts = explode("://", $videourl);
        $data = $parts[1];

        return json_decode($data, true);
    }

    private function getexternalsourceinfo() {
        return ExternalSourceVideoHost::getexternalsourceinfobyvideourl($this->ivs->videourl);
    }

}
