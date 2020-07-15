<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 17.01.2017
 * Time: 15:30
 */

namespace mod_ivs\upload;

use \tool_opencast\local\api;

class SwitchCastFileVideoHost implements IVideoHost {

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

        if (!class_exists('\\tool_opencast\\local\\api')) {
            return;
        }

        $parts = explode("://", $this->ivs->videourl);
        $id = $parts[1];

        $api = new api();

        $query = '/api/events/' . $id . '/publications/';
        $result = $api->oc_get($query);
        $publications = json_decode($result, true);

        foreach ($publications as $publication) {
            if ($publication['channel'] == 'switchcast-api') {

                // sort array by media height (max -> min)
                usort($publication['media'], function($a, $b) {
                    return strcmp($b['height'], $a['height']);
                });

                if (!empty($publication['media'][0]['url'])) {
                    $url = $publication['media'][0]['url'];
                }

            }
        }

        return $url;
    }

    public function saveVideo($form_values) {

    }

    public function getThumbnail() {
        // TODO: Implement getThumbnail() method.
    }
}
