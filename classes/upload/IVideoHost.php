<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 17.01.2017
 * Time: 15:27
 */

namespace mod_ivs\upload;

interface IVideoHost {
    public function getVideo();

    public function saveVideo($data);

    public function getThumbnail();

}
