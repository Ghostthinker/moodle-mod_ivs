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
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\upload;

use \tool_opencast\local\api;

class SwitchCastFileVideoHost implements IVideoHost {

    protected $ivs;
    protected $coursemodule;

    /**
     * VideoHostFactory constructor.
     */
    public function __construct($cm, $ivs) {
        $this->ivs = $ivs;
        $this->coursemodule = $cm;
    }

    public function get_video() {

        if (!class_exists('\\tool_opencast\\local\\api')) {
            return;
        }

        $parts = explode("://", $this->ivs->videourl);
        $id = $parts[1];

        $api = new api();

        $query = '/api/events/' . $id . '/publications/';
        $result = $api->oc_get($query);
        $publications = json_decode($result, true);

        foreach ($publications as $publication) {
            if ($publication['channel'] == 'switchcast-api') {

                // Sort array by media height (max -> min).
                usort($publication['media'], function($a, $b) {
                    return strcmp($b['height'], $a['height']);
                });

                if (!empty($publication['media'][0]['url'])) {
                    $url = $publication['media'][0]['url'];
                }

            }
        }

        return $url;
    }

    public function save_video($form_values) {

    }

    public function get_thumbnail() {
        // TODO: Implement getThumbnail() method.
    }
}
