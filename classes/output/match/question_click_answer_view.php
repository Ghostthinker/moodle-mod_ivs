<?php
// Standard GPL and phpdocs
namespace mod_ivs\output\match;

use mod_ivs\IvsHelper;
use mod_ivs\MoodleMatchController;
use renderable;
use renderer_base;
use templatable;
use stdClass;
use tool_langimport\controller;

class question_click_answer_view implements renderable, templatable {

    var $answer = null;

    public function __construct($answer, $course_user) {
        $this->answer = $answer;
        $this->course_user = $course_user;
    }

    public function export_for_template(renderer_base $output) {

        $controller = new MoodleMatchController();

        return $controller->getQuestionAnswersDataClickQuestion($this->answer, $this->course_user);
    }
}
