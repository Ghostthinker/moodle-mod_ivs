<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 17.01.2017
 * Time: 17:21
 */

namespace mod_ivs\upload;

class VideoHostFactory {

    /**
     * @param $ivs
     * @return \mod_ivs\upload\IVideoHost
     */
    public static function create($cm, $ivs) {

        $parts = explode("://", $ivs->videourl);

        switch ($parts[0]) {
            case 'MoodleFileVideoHost':
                return new MoodleFileVideoHost($cm, $ivs);
            case 'SwitchCastFileVideoHost':
                return new SwitchCastFileVideoHost($cm, $ivs);
            case 'TestingFileVideoHost':
                return new TestingFileVideoHost($cm, $ivs);
        }
    }
}
