<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 18.02.2018
 * Time: 17:47
 */

namespace mod_ivs\ivs_match\exception;

class MatchQuestionNotFoundException extends MatchQuestionException {

    /**
     * MatchQuestionNotFoundException constructor.
     */
    public function __construct() {
        parent::__construct([], "Match question not found");
    }
}
