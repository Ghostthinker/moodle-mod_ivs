<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 17.01.2017
 * Time: 15:30
 */

namespace mod_ivs\upload;

use context_module;

class MoodleFileVideoHost implements IVideoHost {

    protected $ivs;
    protected $course_module;

    /**
     * VideoHostFactory constructor.
     */
    public function __construct($cm, $ivs) {
        $this->ivs = $ivs;
        $this->course_module = $cm;
    }

    public function getVideo() {


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

    public function saveVideo($form_values) {

        if (empty($this->ivs->video_file)) {
            return;
        }

        $context_id = "{$this->ivs->id}";

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

    public function getThumbnail() {
        // TODO: Implement getThumbnail() method.
    }
}
