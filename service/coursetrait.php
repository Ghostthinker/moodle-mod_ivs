<?php

namespace mod_ivs\service;

trait CourseTrait {

    private $courseService;

    /**
     * @return mixed
     */
    public function getCourseService() {
        if ($this->courseService == null) {
            $this->courseService = new courseservice();
        }
        return $this->courseService;
    }

    /**
     * @param mixed $courseService
     */
    public function setCourseService($courseService) {
        $this->courseService = $courseService;
    }

}
