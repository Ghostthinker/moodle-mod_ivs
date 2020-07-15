<?php
/**
 * Created by PhpStorm.
 * User: bhoer
 * Date: 25.04.2019
 * Time: 15:50
 */

namespace mod_ivs\ivs_match\question;

class QuestionSummary {

    public $question_id;
    public $question_title;
    public $question_body;
    public $first_attempt_correct;
    public $last_attempt_correct;
    public $num_students_participation; //number of course members with role student that have answered the question at least one time
    public $num_students_total;

}
