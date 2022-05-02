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
 * This class is a helper class for the backend service
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs;

defined('MOODLE_INTERNAL') || die();

class BackendService {

    public function ivs_backend_comments($args, $postdata, $requestmethod) {
        $videoid = $args[1];

        $parentid = null;

        switch ($requestmethod) {
            case 'GET':
                $annotations = \mod_ivs\annotation::retrieve_from_db_by_video($videoid);

                $data = array();

                /** @var \mod_ivs\annotation $annotation */
                foreach ($annotations as $annotation) {
                    $data[] = $annotation->to_player_comment();
                }
                $this->ivs_backend_exit($data);

            case 'POST':
                if (!empty($args[2]) && $args[2] == 'reply') {
                    $parentid = $args[3];
                    // Todo: check.
                }

                // Check access.
                $annotation = new \mod_ivs\annotation();

                $annotation->from_request_body($postdata, $parentid);

                if (!$annotation->access("create")) {
                    $this->ivs_backend_error_exit('No create access');
                }

                $playercomment = $annotation->to_player_comment();
                $this->ivs_backend_exit($playercomment);
            case 'PUT':
                $annotationid = $args[2];

                if (!empty($args[2]) && $args[2] == 'field') {

                    $annotationid = $args[3];
                    $annotation = \mod_ivs\annotation::retrieve_from_db($annotationid, true);

                    foreach ($postdata as $fieldset) {

                        $key = $fieldset->key;
                        $value = $fieldset->value;

                        // Check access lock.
                        if ($key == "access_settings" && $annotation->access("lock_access")) {
                            $annotation->lock_access($value);
                        }
                    }
                    $playercomment = $annotation->to_player_comment();
                    $this->ivs_backend_exit($playercomment);
                }

                if (!empty($args[2]) && $args[2] == 'reply') {
                    $annotationid = $args[4];
                }
                $annotation = \mod_ivs\annotation::retrieve_from_db($annotationid, true);

                if (!$annotation->access("edit")) {
                    $this->ivs_backend_error_exit('No edit access');
                }
                $annotation->from_request_body($postdata);

                $playercomment = $annotation->to_player_comment();
                $this->ivs_backend_exit($playercomment);
            case 'DELETE':
                $annotationid = $args[2];

                if (!empty($args[2]) && $args[2] == 'reply') {
                    $annotationid = $args[4];
                }
                /** @var \mod_ivs\annotation $an */
                $an = \mod_ivs\annotation::retrieve_from_db($annotationid);

                if (!$an->access("delete")) {
                    $this->ivs_backend_error_exit('No delete access');
                }
                $an->delete_from_db();

                if (!empty($an->load_audio_annotation())) {
                    $an->delete_audio();
                }

                $this->ivs_backend_exit('ok');
        }
    }

    public function ivs_backend_playbackcommands( $args, $postdata, $requestmethod) {
        $videonid = $args[1];

        $coursemodule = get_coursemodule_from_instance('ivs', $videonid, 0, false, MUST_EXIST);
        $activity = \context_module::instance($coursemodule->id);
        $playbackcommandservice = new \mod_ivs\PlaybackcommandService();
        $activityid = $activity->instanceid;

        switch ($requestmethod) {
            case 'GET':
                try {
                    $playbackcommands = $playbackcommandservice->retrieve($activityid);
                    $this->ivs_backend_exit($playbackcommands);
                } catch (Exception $e) {
                    $this->ivs_backend_error_exit($e->getMessage());
                }

                break;
            case 'POST':
            case 'PUT':
                try {
                    $playbackcommand = $playbackcommandservice->save($postdata, $activityid);
                    $this->ivs_backend_exit($playbackcommand);
                } catch (Exception $e) {
                    $this->ivs_backend_error_exit($e->getMessage());
                }
                break;
            case 'DELETE':
                $playbackcommandid = $args[2];
                try {
                    $playbackcommandservice->delete($playbackcommandid, $activityid);
                    $this->ivs_backend_exit('ok');
                } catch (Exception $e) {
                    $this->ivs_backend_error_exit($e->getMessage());
                }

                break;
        }
    }

    /**
     * Exit call when successfully ended tasks
     *
     * @param string|array $data
     * @param int $statuscode
     */
    function ivs_backend_exit($data, $statuscode = 200) {
        http_response_code($statuscode);
        if (is_string($data)) {
            print $data;
        } else {
            header("Content-type: application/json; charset=utf-8");
            print json_encode($data);
        }
        exit;
    }

    /**
     * Exit call when errors appear
     *
     * @param string $data
     * @param int $statuscode
     */
    function ivs_backend_error_exit($data = "access denied", $statuscode = 403) {
        http_response_code($statuscode);
        json_encode($data);
        exit;
    }
}