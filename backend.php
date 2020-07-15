<?php

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

$path_endpoint = $www_root . "/mod/ivs/backend.php/";

$request_uri = strtok($_SERVER["REQUEST_URI"], '?');

$actual_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$request_uri}";
$url = str_replace($path_endpoint, "", $actual_url);

$args = explode("/", $url);

$endpoint = $args[0];
$video_id = $args[1];

$post_data = array();

if (!\mod_ivs\IvsHelper::accessPlayer($video_id)) {
    ivs_backend_error_exit();
}

switch ($endpoint) {
    case 'comments':
        if ($request_body = file_get_contents('php://input')) {
            $post_data = json_decode($request_body);
        }
        ivs_backend_comments($args, $post_data);
        break;
    case 'playbackcommands':
        if ($request_body = file_get_contents('php://input')) {
            $post_data = json_decode($request_body);
        }
        ivs_backend_playbackcommands($args, $post_data);
        break;
    case 'match_questions':
    case 'match_answers':
    case "match_context":
        $mc = new MoodleMatchController();
        array_shift($args);
        if ($request_body = file_get_contents('php://input')) {
            $post_data = json_decode($request_body, true);
        }
        $mc->handleRequest($endpoint, $args, $_SERVER['REQUEST_METHOD'], $post_data);
        break;

}

function ivs_backend_comments($args, $post_data) {
    $video_id = $args[1];
    $parent_id = null;

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':

            $annotations = \mod_ivs\annotation::retrieve_from_db_by_video($video_id);

            $data = array();

            /** @var \mod_ivs\annotation $annotation */
            foreach ($annotations as $annotation) {
                $data[] = $annotation->toPlayerComment();
            }
            print json_encode($data);
            die();
            break;
        case 'POST':

            if (!empty($args[2]) && $args[2] == 'reply') {
                $parent_id = $args[3];
                //Todo: check
            }

            $annotation = new \mod_ivs\annotation();
            $annotation->from_request_body($post_data, $parent_id);

            print json_encode($annotation->toPlayerComment());
            die();

            break;
        case 'PUT':
            $annotation_id = $args[2];

            if (!empty($args[2]) && $args[2] == 'field') {

                $annotation_id = $args[3];
                $annotation = \mod_ivs\annotation::retrieve_from_db($annotation_id, true);

                foreach ($post_data as $fieldset) {

                    $key = $fieldset->key;
                    $value = $fieldset->value;

                    //check access lock
                    if ($key == "access_settings" && $annotation->access("lock_access")) {
                        $annotation->lockAccess($value);
                    }
                }

                print json_encode($annotation->toPlayerComment());
                die();
            }

            /** @var \mod_ivs\annotation $an */

            if (!empty($args[2]) && $args[2] == 'reply') {
                $annotation_id = $args[4];
                //$parent_id = $args[3];
            }
            $annotation = \mod_ivs\annotation::retrieve_from_db($annotation_id, true);

            if (!$annotation->access("edit")) {
                ivs_backend_error_exit();
            }
            $annotation->from_request_body($post_data);

            print json_encode($annotation->toPlayerComment());
            die();

            break;
        case 'DELETE':
            $annotation_id = $args[2];

            if (!empty($args[2]) && $args[2] == 'reply') {
                $annotation_id = $args[4];
                //$parent_id = $args[3];
            }
            /** @var \mod_ivs\annotation $an */
            $an = \mod_ivs\annotation::retrieve_from_db($annotation_id);

            if (!$an->access("delete")) {
                print_r($an->getRecord());
                ivs_backend_error_exit();
            }
            $an->delete_from_db($an);

            die("ok");
            break;
    }
}

function ivs_backend_playbackcommands($args, $post_data) {
    $video_nid = $args[1];

    $course_module = get_coursemodule_from_instance('ivs', $video_nid, 0, false, MUST_EXIST);
    $activity = \context_module::instance($course_module->id);
    $playbackcommandService = new \mod_ivs\PlaybackcommandService();
    $activity_id = $activity->instanceid;

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            try {
                $playbackcommands = $playbackcommandService->retrieve($activity_id);
                print json_encode($playbackcommands);
                exit;
            } catch (Exception $e) {
                ivs_backend_error_exit($e->getMessage());
            }

            break;
        case 'POST':
        case 'PUT':
            try {
                $playbackcommand = $playbackcommandService->save($post_data, $activity_id);
                print json_encode($playbackcommand);
                exit;
            } catch (Exception $e) {
                ivs_backend_error_exit($e->getMessage());
            }
            break;
        case 'DELETE':
            $playback_command_id = $args[2];
            try {
                $playbackcommandService->delete($playback_command_id, $activity_id);
                print "ok";
                exit;
            } catch (Exception $e) {
                ivs_backend_error_exit($e->getMessage());
            }

            break;
    }
}

function ivs_backend_error_exit($data = "access denied", $status_code = 403) {
    http_response_code($status_code);
    json_encode($data);
    exit;
}

