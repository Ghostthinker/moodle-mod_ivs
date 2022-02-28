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
 * File for the backend
 *
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

use mod_ivs\MediaController;
use mod_ivs\MoodleMatchController;

define('AJAX_SCRIPT', true);

require('../../config.php');
require_login(null, false);

$action = optional_param('action', false, PARAM_ALPHA);
$view = optional_param('view', false, PARAM_ALPHA);

// Security.

$PAGE->set_context(context_system::instance());

error_reporting(error_reporting() & ~E_NOTICE);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);

$wwwroot = $CFG->wwwroot;

$pathendpoint = $wwwroot . "/mod/ivs/backend.php/";

$httpschema = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';

if (!isset($_SERVER['HTTP_HOST'])) {
    ivs_backend_error_exit();
}
$httphost = $_SERVER['HTTP_HOST'];

if (!isset($_SERVER['REQUEST_URI'])) {
    ivs_backend_error_exit();
}
$requesturi = strtok($_SERVER["REQUEST_URI"], '?');

$actualurl = $httpschema . '://' . $httphost . $requesturi;

$url = str_replace($pathendpoint, "", $actualurl);

$args = explode("/", $url);

$endpoint = $args[0];
$videoid = $args[1];

$postdata = array();

if (!\mod_ivs\IvsHelper::access_player($videoid)) {
    ivs_backend_error_exit();
}

if (!isset($_SERVER['REQUEST_METHOD'])) {
    ivs_backend_error_exit();
}

$requestmethod = $_SERVER['REQUEST_METHOD'];

switch ($endpoint) {
    case 'comments':
        if ($requestbody = file_get_contents('php://input')) {
            $postdata = json_decode($requestbody);
        }
        ivs_backend_comments($args, $postdata, $requestmethod);
        break;
    case 'playbackcommands':
        if ($requestbody = file_get_contents('php://input')) {
            $postdata = json_decode($requestbody);
        }
        ivs_backend_playbackcommands($args, $postdata, $requestmethod);
        break;
    case 'match_questions':
    case 'match_answers':
    case "match_context":
        $mc = new MoodleMatchController();
        array_shift($args);
        if ($requestbody = file_get_contents('php://input')) {
            $postdata = json_decode($requestbody, true);
        }
        $mc->handle_request($endpoint, $args, $requestmethod, $postdata);
        break;
    case "media":

        $mc = new MediaController();
        array_shift($args);
        if ($requestbody = file_get_contents('php://input')) {
            $postdata = json_decode($requestbody, true);
        }

        $mc->handle_request($args, $requestmethod, $_FILES);

}

/**
 * Callback for comments
 *
 * @param array $args
 * @param array $postdata
 * @param string $requestmethod
 */
function ivs_backend_comments($args, $postdata, $requestmethod) {
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
            ivs_backend_exit($data);

        case 'POST':
            if (!empty($args[2]) && $args[2] == 'reply') {
                $parentid = $args[3];
                // Todo: check.
            }

            // Check access.
            $annotation = new \mod_ivs\annotation();

            $annotation->from_request_body($postdata, $parentid);

            if (!$annotation->access("create")) {
                ivs_backend_error_exit();
            }

            $playercomment = $annotation->to_player_comment();
            ivs_backend_exit($playercomment);
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
                ivs_backend_exit($playercomment);
            }

            if (!empty($args[2]) && $args[2] == 'reply') {
                $annotationid = $args[4];
            }
            $annotation = \mod_ivs\annotation::retrieve_from_db($annotationid, true);

            if (!$annotation->access("edit")) {
                ivs_backend_error_exit();
            }
            $annotation->from_request_body($postdata);

            $playercomment = $annotation->to_player_comment();
            ivs_backend_exit($playercomment);
        case 'DELETE':
            $annotationid = $args[2];

            if (!empty($args[2]) && $args[2] == 'reply') {
                $annotationid = $args[4];
            }
            /** @var \mod_ivs\annotation $an */
            $an = \mod_ivs\annotation::retrieve_from_db($annotationid);

            if (!$an->access("delete")) {
                ivs_backend_error_exit();
            }
            $an->delete_from_db();

            if (!empty($an->load_audio_annotation())) {
                $an->delete_audio();
            }

            ivs_backend_exit('ok');
    }
}

/**
 * Callback for playbackcommands
 *
 * @param array $args
 * @param array $postdata
 * @param string $requestmethod
 */
function ivs_backend_playbackcommands($args, $postdata, $requestmethod) {
    $videonid = $args[1];

    $coursemodule = get_coursemodule_from_instance('ivs', $videonid, 0, false, MUST_EXIST);
    $activity = \context_module::instance($coursemodule->id);
    $playbackcommandservice = new \mod_ivs\PlaybackcommandService();
    $activityid = $activity->instanceid;

    switch ($requestmethod) {
        case 'GET':
            try {
                $playbackcommands = $playbackcommandservice->retrieve($activityid);
                ivs_backend_exit($playbackcommands);
            } catch (Exception $e) {
                ivs_backend_error_exit($e->getMessage());
            }

            break;
        case 'POST':
        case 'PUT':
            try {
                $playbackcommand = $playbackcommandservice->save($postdata, $activityid);
                ivs_backend_exit($playbackcommand);
            } catch (Exception $e) {
                ivs_backend_error_exit($e->getMessage());
            }
            break;
        case 'DELETE':
            $playbackcommandid = $args[2];
            try {
                $playbackcommandservice->delete($playbackcommandid, $activityid);
                ivs_backend_exit('ok');
            } catch (Exception $e) {
                ivs_backend_error_exit($e->getMessage());
            }

            break;
    }
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

