<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 18.02.2018
 * Time: 17:41
 */

namespace mod_ivs\ivs_match\exception;

class MatchQuestionException extends \Exception {

    private $question_node;

    /**
     * @return mixed
     */
    public function getQuestionNode() {
        return $this->question_node;
    }

    /**
     * MatchQuestionException constructor.
     *
     * @param $node
     */
    public function __construct($question_node, $message = "", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->question_node = $question_node;

    }

}
