<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 15.08.2018
 * Time: 14:50
 */

namespace mod_ivs\ivs_match\exception;

class MatchAlreadyAnsweredException extends \Exception {

    public function __construct($message = "", $code = 0, \Throwable $previous = null) {
        parent::__construct("Answer already exits for this user and question: " . $message, $code, $previous);
    }
}
