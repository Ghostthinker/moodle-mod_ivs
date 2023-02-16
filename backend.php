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

// $_SERVER['HTTPS'] == 'on' could be not available in all webserver versions
if ((!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') ||
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
        (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443')) {
    $httpschema = 'https';
} else {
    $httpschema = 'http';
}

$backendservice = new \mod_ivs\BackendService();
if (!isset($_SERVER['HTTP_HOST'])) {
    $backendservice->ivs_backend_error_exit('No http host found');
}
$httphost = $_SERVER['HTTP_HOST'];

if (!isset($_SERVER['REQUEST_URI'])) {
    $backendservice->ivs_backend_error_exit('No Request uri found');
}
$requesturi = strtok($_SERVER["REQUEST_URI"], '?');

$actualurl = $httpschema . '://' . $httphost . $requesturi;

$url = str_replace($pathendpoint, "", $actualurl);

$args = explode("/", $url);

$endpoint = $args[0];
$videoid = $args[1];

$postdata = array();

if (!\mod_ivs\IvsHelper::access_player($videoid)) {
    $backendservice->ivs_backend_error_exit('No access to the player');
}

if (!isset($_SERVER['REQUEST_METHOD'])) {
    $backendservice->ivs_backend_error_exit('No Request method found');
}

$requestmethod = $_SERVER['REQUEST_METHOD'];

switch ($endpoint) {
    case 'comments':
        if ($requestbody = file_get_contents('php://input')) {
            $postdata = json_decode($requestbody);
        }
        $backendservice->ivs_backend_comments($args, $postdata, $requestmethod);
        break;
    case 'playbackcommands':
        if ($requestbody = file_get_contents('php://input')) {
            $postdata = json_decode($requestbody);
        }
        $backendservice->ivs_backend_playbackcommands($args, $postdata, $requestmethod);
        break;
    case 'match_questions':
    case 'match_answers':
    case "match_context":
    case "timing-types":
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

