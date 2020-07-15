<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 18.02.2018
 * Time: 17:47
 */

namespace mod_ivs\ivs_match\exception;

class MatchQuestionAccessDeniedException extends MatchQuestionException {

    /**
     * MatchQuestionAccessDeniedException constructor.
     */
    public function __construct($object, $message) {
        parent::__construct($object, $message);
    }
}
