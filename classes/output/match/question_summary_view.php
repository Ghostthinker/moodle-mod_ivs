<?php
// Standard GPL and phpdocs
namespace mod_ivs\output\match;

use mod_ivs\IvsHelper;
use mod_ivs\MoodleMatchController;
use renderable;
use renderer_base;
use templatable;
use stdClass;

class question_summary_view implements renderable, templatable {

    var $question = null;
    var $course_students = null;

    public function __construct($question, $course_students) {
        $this->question = $question;
        $this->course_students = $course_students;
    }

    public function export_for_template(renderer_base $output) {

        $controller = new MoodleMatchController();

        return $controller->getQuestionSummaryFormated($this->question, $this->course_students);

    }
}
