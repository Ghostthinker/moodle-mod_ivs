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

use mod_ivs\MoodleMatchController;

define('AJAX_SCRIPT', true);

require('../../config.php');

$action = optional_param('action', false, PARAM_ALPHA);
$view = optional_param('view', false, PARAM_ALPHA);

// Security.

$PAGE->set_context(context_system::instance());

error_reporting(error_reporting() & ~E_NOTICE);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);

$www_root = $CFG->wwwroot;

$pathendpoint = $www_root . "/mod/ivs/backend.php/";

$requesturi = strtok($_SERVER["REQUEST_URI"], '?');

$actualurl = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$requesturi}";
$url = str_replace($pathendpoint, "", $actualurl);

$args = explode("/", $url);

$endpoint = $args[0];
$videoid = $args[1];

$postdata = array();

if (!\mod_ivs\IvsHelper::access_player($videoid)) {
    ivs_backend_error_exit();
}

switch ($endpoint) {
    case 'comments':
        if ($requestbody = file_get_contents('php://input')) {
            $postdata = json_decode($requestbody);
        }
        ivs_backend_comments($args, $postdata);
        break;
    case 'playbackcommands':
        if ($requestbody = file_get_contents('php://input')) {
            $postdata = json_decode($requestbody);
        }
        ivs_backend_playbackcommands($args, $postdata);
        break;
    case 'match_questions':
    case 'match_answers':
    case "match_context":
        $mc = new MoodleMatchController();
        array_shift($args);
        if ($requestbody = file_get_contents('php://input')) {
            $postdata = json_decode($requestbody, true);
        }
        $mc->handle_request($endpoint, $args, $_SERVER['REQUEST_METHOD'], $postdata);
        break;

}

function ivs_backend_comments($args, $postdata) {
    $videoid = $args[1];

    $parentid = null;

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $annotations = \mod_ivs\annotation::retrieve_from_db_by_video($videoid);

            $data = array();

            /** @var \mod_ivs\annotation $annotation */
            foreach ($annotations as $annotation) {
                $data[] = $annotation->to_player_comment();
            }
            print json_encode($data);

            die();
        case 'POST':
            if (!empty($args[2]) && $args[2] == 'reply') {
                $parentid = $args[3];
                //Todo: check
            }

            //check access
            $annotation = new \mod_ivs\annotation();

            $annotation->from_request_body($postdata, $parentid);

            if (!$annotation->access("create")) {
                ivs_backend_error_exit();
            }

            $annotation->from_request_body($postdata, $parentid);

            print json_encode($annotation->to_player_comment());

            die();

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

                print json_encode($annotation->to_player_comment());
                exit;
            }

            /** @var \mod_ivs\annotation $an */

            if (!empty($args[2]) && $args[2] == 'reply') {
                $annotationid = $args[4];
            }
            $annotation = \mod_ivs\annotation::retrieve_from_db($annotationid, true);

            if (!$annotation->access("edit")) {
                ivs_backend_error_exit();
            }
            $annotation->from_request_body($postdata);

            print json_encode($annotation->to_player_comment());
            die();
        case 'DELETE':
            $annotationid = $args[2];

            if (!empty($args[2]) && $args[2] == 'reply') {
                $annotationid = $args[4];
            }
            /** @var \mod_ivs\annotation $an */
            $an = \mod_ivs\annotation::retrieve_from_db($annotationid);

            if (!$an->access("delete")) {
                print_r($an->get_record());
                ivs_backend_error_exit();
            }
            $an->delete_from_db($an);

            die("ok");
    }
}

function ivs_backend_playbackcommands($args, $postdata) {
    $videonid = $args[1];

    $coursemodule = get_coursemodule_from_instance('ivs', $videonid, 0, false, MUST_EXIST);
    $activity = \context_module::instance($coursemodule->id);
    $playbackcommandService = new \mod_ivs\PlaybackcommandService();
    $activityid = $activity->instanceid;

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            try {
                $playbackcommands = $playbackcommandService->retrieve($activityid);
                print json_encode($playbackcommands);
                exit;
            } catch (Exception $e) {
                ivs_backend_error_exit($e->getMessage());
            }

            break;
        case 'POST':
        case 'PUT':
            try {
                $playbackcommand = $playbackcommandService->save($postdata, $activityid);
                print json_encode($playbackcommand);
                exit;
            } catch (Exception $e) {
                ivs_backend_error_exit($e->getMessage());
            }
            break;
        case 'DELETE':
            $playbackcommandid = $args[2];
            try {
                $playbackcommandService->delete($playbackcommandid, $activityid);
                print "ok";
                exit;
            } catch (Exception $e) {
                ivs_backend_error_exit($e->getMessage());
            }

            break;
    }
}

/**
 * @param string $data
 * @param int $status_code
 */
function ivs_backend_error_exit($data = "access denied", $status_code = 403) {
    http_response_code($status_code);
    json_encode($data);
    exit;
}

