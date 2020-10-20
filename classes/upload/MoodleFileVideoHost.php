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

use context_module;

class MoodleFileVideoHost implements IVideoHost {

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


        $cm = get_coursemodule_from_instance('ivs', $this->ivs->id, $this->ivs->course, false, MUST_EXIST);

        $context = context_module::instance($cm->id);

        $fs = get_file_storage();
        $videos = $fs->get_area_files($context->id,
                'mod_ivs',
                'videos',
                0,
                'itemid, filepath, filename',
                false);

        if (!empty($videos)) {
            $file = end($videos);
        } else {
            return null;
        }

        $url = \moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename(),
                false);

        return $url;
    }

    public function save_video($form_values) {

        if (empty($this->ivs->video_file)) {
            return;
        }

        $contextid = "{$this->ivs->id}";

        // Storage of files from the filemanager (videos).
        $draftitemid = $this->ivs->video_file;

        $context = context_module::instance($this->ivs->coursemodule);

        if ($draftitemid) {
            file_save_draft_area_files(
                    $draftitemid,
                    $context->id,
                    'mod_ivs',
                    'videos',
                    0
            );
        }

    }

    public function get_thumbnail() {
        // TODO: Implement getThumbnail() method.
    }
}
