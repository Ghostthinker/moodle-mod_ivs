<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 23.04.2019
 * Time: 17:03
 */

namespace mod_ivs\upload;

class TestingFileVideoHost implements IVideoHost {

    protected $ivs;
    protected $course_module;

    /**
     * TestingFileVideoHost constructor.
     *
     * @param $cm
     * @param $ivs
     */
    public function __construct($cm, $ivs) {
    }

    public function getVideo() {
        // TODO: Implement getVideo() method.
        return new \moodle_url('/mod/ivs/tests/codeception/tests/_data/sample.mp4');
    }

    public function saveVideo($data) {
        // TODO: Implement saveVideo() method.
    }

    public function getThumbnail() {
        // TODO: Implement getThumbnail() method.
    }
}
