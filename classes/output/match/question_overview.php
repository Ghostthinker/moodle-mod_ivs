<?php
// Standard GPL and phpdocs
namespace mod_ivs\output\match;

use renderable;
use renderer_base;
use templatable;
use stdClass;

class question_overview implements renderable, templatable {

    var $question = null;
    var $module = null;

    public function __construct($question, $module) {
        $this->question = $question;
        $this->module = $module;
    }

    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

        $data->id = $this->question['nid'];
        $data->question = $this->question['question_body'];
        if (strlen($this->question['title']) > 0) {
            $data->question = $this->question['title'] . ': ' . $data->question;
        }

        $data->link = new \moodle_url('/mod/ivs/question_answers.php',
                array(
                        'id' => $this->module->id,
                        'vid' => $this->question['video'],
                        'qid' => $data->id,
                        'perpage' => 10
                )
        );

        return $data;
    }
}
