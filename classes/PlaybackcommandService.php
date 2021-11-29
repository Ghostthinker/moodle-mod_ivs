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
 * PlaybackcommandService.php
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs;

use Exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Class PlaybackcommandService
 *
 * @package mod_ivs
 */
class PlaybackcommandService {

    /**
     * Convert json response
     */
    public function from_json() {

    }

    /**
     * Save Playbackcommand
     *
     * @param \stdClass $postdata
     * @param int $activityid
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function save($postdata, $activityid) {

        if (!$this->has_edit_access($activityid)) {
            throw new Exception("Access denied");
        }

        $commands = $this->retrieve($activityid);
        $isnew = true;

        if (empty($postdata->id)) {
            $isnew = false;
            $id = uniqid("", true);
        } else {
            $id = clean_param($postdata->id, PARAM_TEXT);
        }

        $command = [];

        $command['id'] = $id;
        $command['type'] = clean_param($postdata->type, PARAM_ALPHANUM);
        $command['timestamp'] = clean_param($postdata->timestamp, PARAM_INT);
        $command['duration'] = clean_param($postdata->duration, PARAM_INT);
        $command['title'] = clean_param($postdata->title, PARAM_TEXT);

        // Special fields.
        switch ($command['type']) {
            case "pause":
                $command['wait_time'] = clean_param($postdata->wait_time, PARAM_INT);
                break;
            case "playbackrate":
                $command['playbackrate'] = clean_param($postdata->playbackrate, PARAM_FLOAT);
                break;
            case "zoom":
                $command['zoom'] = clean_param($postdata->zoom, PARAM_FLOAT);
                $command['zoom_transform'] = $postdata->zoomdata;
                break;
            case "audio":
                break;
            case "drawing":
                $command['drawing_data'] = $postdata->drawing_data;
                break;
            case "text":
                $command['drawing_data'] = $postdata->drawing_data;
                break;
            case "textoverlay":
                // Not used 19.12.2016 - 17:00 - SH - but kinda working.
                $command['body'] = clean_param($postdata->body, PARAM_TEXT);
                $command['position'] = $postdata->position;
                break;
        }

        // Check existing id.
        $isnew = true;
        if (count($commands) > 0) {
            foreach ($commands as $k => $c) {
                if ($c['id'] === $id) {
                    $commands[$k] = $command;
                    $isnew = false;
                }
            }
        }
        if ($isnew) {
            $commands[] = $command;
        }

        global $DB;
        $ivs = $this->load_video_by_activity_id($activityid);
        $ivs->playbackcommands = json_encode($commands);

        $DB->update_record('ivs', $ivs);

        return $command;
    }

    /**
     * Retrieve Playbackcommand
     *
     * @param int $activityid
     * @return mixed
     * @throws \Exception
     */
    public function retrieve($activityid) {

        $ivs = $this->load_video_by_activity_id($activityid);

        if (empty($ivs)) {
            throw new Exception("video not found");
        }

        $playbackcommands = json_decode($ivs->playbackcommands, true);

        if ($playbackcommands === null && json_last_error() !== JSON_ERROR_NONE) {
            return array();
        }

        return $playbackcommands;

    }

    /**
     * Delete Playbackcommand
     *
     * @param int $playbackcommandid
     * @param int $activityid
     * @throws \dml_exception
     */
    public function delete($playbackcommandid, $activityid) {

        if (!$this->has_edit_access($activityid)) {
            throw new Exception("Access denied");
        }

        $commands = $this->retrieve($activityid);

        // Check if command id exists.
        foreach ($commands as $k => $c) {
            if ($c['id'] === $playbackcommandid) {
                unset($commands[$k]);
            }
        }

        global $DB;
        $ivs = $this->load_video_by_activity_id($activityid);
        $ivs->playbackcommands = json_encode(array_values($commands));

        $DB->update_record('ivs', $ivs);
    }

    /**
     * Load Video by activity id
     *
     * @param int $activityid
     * @return mixed
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function load_video_by_activity_id($activityid) {
        global $DB;

        $cm = $this->get_activity($activityid);
        $ivs = $DB->get_record('ivs', array('id' => $cm->instance), '*', MUST_EXIST);

        return $ivs;

    }

    /**
     * Returns the ivs activity
     *
     * @param int $activityid
     *
     * @return mixed
     */
    private function get_activity($activityid) {
        return get_coursemodule_from_id('ivs', $activityid, 0, false, MUST_EXIST);
    }

    /**
     * Check the access for an ivs activity
     *
     * @param int $activityid
     *
     * @return mixed
     */
    private function has_edit_access($activityid) {

        $activitycontext = \context_module::instance($activityid);
        return has_capability('mod/ivs:edit_playbackcommands', $activitycontext);

    }

    /**
     * Special behavior playbackcommand sequence
     *
     * @param int $activityid
     * @return array
     * @throws \Exception
     */
    public function has_sequence($activityid) {

        $commands = $this->retrieve($activityid);

        if (count($commands) > 0) {
            foreach ($commands as $pc) {
                if ($pc['type'] == "sequence") {
                    $start = $pc['timestamp'] / 1000;
                    return array(
                            'start' => $start,
                            'end' => $start + $pc['duration'] / 1000,
                    );
                }
            }
        }
    }
}
