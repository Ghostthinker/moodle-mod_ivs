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
 * The controller manages the Matchquestions
 *
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs;

/**
 * Class MediaController
 *
 */
class MediaController {

    private $backendService;

    public function __construct(){
        $this->backendService = new BackendService();
    }

    public function handle_request($patharguments, $method, $files) {
        switch ($method) {
            case 'POST':
                $this->handle_post($patharguments, $files);
                break;
            case 'DELETE':
                $this->handle_delete($patharguments);
                break;
        }

        $this->backendService->ivs_backend_error_exit('Method not found', 405);
    }

    private function handle_post($patharguments, $files) {
        global $DB;

        $videoid = $patharguments[0];
        $annotationid = $patharguments[1];

        $annotation = annotation::retrieve_from_db($annotationid);

        if (!$annotation->access("edit")) {
            $this->backendService->ivs_backend_error_exit('No edit access');
        }

        try {
            $file = $annotation->save_audio($files['media']['tmp_name']);
        } catch (\Exception $e) {
            $this->backendService->ivs_backend_error_exit('Failed to save media', 500);
        }

        $response = $annotation->to_player_comment();

        $DB->execute("UPDATE {ivs_videocomment} SET comment_type = :comment_type WHERE id = :id",
                ['comment_type' => 'audio_comment', 'id' => $annotation->get_id()]);

        $this->backendService->ivs_backend_exit($response, 200);

    }

    private function handle_delete($patharguments) {
        $annotationid = $patharguments[1];

        $annotation = annotation::retrieve_from_db($annotationid);
        if (empty($annotation)) {
            $this->backendService->ivs_backend_error_exit('Annotation not found', 404);
        }
        if (!$annotation->access("delete")) {
            $this->backendService->ivs_backend_error_exit('Access denied', 403);
        }

        try {
            $annotation->delete_audio();
        } catch (\Exception $e) {
            $this->backendService->ivs_backend_error_exit('Failed to delete media', 500);
        }

        $this->backendService->ivs_backend_exit('success', 200);
        exit;
    }

}
