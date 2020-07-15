<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 15.08.2018
 * Time: 14:00
 */

namespace mod_ivs\ivs_match\exception;

use Throwable;

class MatchNoConfigException extends \Exception {
    public function __construct($message = "", $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}
