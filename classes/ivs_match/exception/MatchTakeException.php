<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 15.08.2018
 * Time: 14:50
 */

namespace mod_ivs\ivs_match\exception;

class MatchTakeException extends \Exception {

    public function __construct($message = "", $code = 0, \Throwable $previous = null) {
        parent::__construct("MatchTakeException: " . $message, $code, $previous);
    }
}
