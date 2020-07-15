<?php

namespace mod_ivs;

use Exception;

defined('MOODLE_INTERNAL') || die();

//require_once('../../../config.php');

//TODO create interface
class PlaybackcommandService {
    public function fromJson() {

    }

    /**
     * Save Playbackcommand
     *
     * @param $post_data
     * @param $activity_id
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function save($post_data, $activity_id) {

        if (!$this->hasEditAccess($activity_id)) {
            throw new Exception("Access denied");
        }

        $commands = $this->retrieve($activity_id);
        $is_new = true;

        if (empty($post_data->id)) {
            $is_new = false;
            $id = uniqid("", true);
        } else {
            $id = clean_param($post_data->id, PARAM_TEXT);
        }

        $command = [];

        $command['id'] = $id;
        $command['type'] = clean_param($post_data->type, PARAM_ALPHANUM);
        $command['timestamp'] = clean_param($post_data->timestamp, PARAM_INT);
        $command['duration'] = clean_param($post_data->duration, PARAM_INT);
        $command['title'] = clean_param($post_data->title, PARAM_TEXT);

        //special fields
        switch ($command['type']) {
            case "pause":
                $command['wait_time'] = clean_param($post_data->wait_time, PARAM_INT);
                break;
            case "playbackrate":
                $command['playbackrate'] = clean_param($post_data->playbackrate, PARAM_FLOAT);
                break;
            case "zoom":
                $command['zoom'] = clean_param($post_data->zoom, PARAM_FLOAT);
                $command['zoom_transform'] = $post_data->zoomdata;
                break;
            case "audio":
                //TODO
                //$command['audio'] = clean_param($post_data->audio);
                break;
            case "drawing":
                $command['drawing_data'] = $post_data->drawing_data;
                break;
            case "text":
                $command['drawing_data'] = $post_data->drawing_data;
                break;
            case "textoverlay":
                //not used 19.12.2016 - 17:00 - SH - but kinda working
                $command['body'] = clean_param($post_data->body, PARAM_TEXT);
                $command['position'] = $post_data->position;
                break;
        }

        //check existing id
        $is_new = true;
        if (count($commands) > 0) {
            foreach ($commands as $k => $c) {
                if ($c['id'] === $id) {
                    $commands[$k] = $command;
                    $is_new = false;
                }
            }
        }
        if ($is_new) {
            $commands[] = $command;
        }

        global $DB;
        $ivs = $this->loadVideoByActivityId($activity_id);
        $ivs->playbackcommands = json_encode($commands);

        $DB->update_record('ivs', $ivs);

        return $command;
    }

    /**
     * Retrieve Playbackcommand
     *
     * @param $activity_id
     * @return mixed
     * @throws \Exception
     */
    public function retrieve($activity_id) {

        $ivs = $this->loadVideoByActivityId($activity_id);

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
     * @param $playback_command_id
     * @param $activity_id
     * @throws \dml_exception
     */
    public function delete($playback_command_id, $activity_id) {


        if (!$this->hasEditAccess($activity_id)) {
            throw new Exception("Access denied");
        }

        $commands = $this->retrieve($activity_id);

        //check if command id exists
        foreach ($commands as $k => $c) {
            if ($c['id'] === $playback_command_id) {
                unset($commands[$k]);
            }
        }

        global $DB;
        $ivs = $this->loadVideoByActivityId($activity_id);
        $ivs->playbackcommands = json_encode(array_values($commands));

        //print_r($ivs->playbackcommands);die();

        $DB->update_record('ivs', $ivs);
    }

    /**
     * Load Video by activity id
     *
     * @param $activity_id
     * @return mixed
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function loadVideoByActivityId($activity_id) {
        global $DB;

        $cm = $this->getActivity($activity_id);
        // $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $ivs = $DB->get_record('ivs', array('id' => $cm->instance), '*', MUST_EXIST);

        return $ivs;

    }

    private function getActivity($activity_id) {
        return get_coursemodule_from_id('ivs', $activity_id, 0, false, MUST_EXIST);
    }

    private function hasEditAccess($activity_id) {

        $activity_context = \context_module::instance($activity_id);
        return has_capability('mod/ivs:edit_playbackcommands', $activity_context);

    }

    /**
     * Special behavior playbackcommand sequence
     *
     * @param $activity_id
     * @return array
     * @throws \Exception
     */
    public function hasSequence($activity_id) {

        $commands = $this->retrieve($activity_id);

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
