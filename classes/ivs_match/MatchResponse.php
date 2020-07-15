<?php

namespace mod_ivs\ivs_match;

class MatchResponse {

    private $status;
    private $data;

    /**
     * MatchResponse constructor.
     *
     * @param $status
     * @param $data
     */
    public function __construct($data = array(), $status = 200) {
        $this->status = $status;
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data) {
        $this->data = $data;
    }

}
