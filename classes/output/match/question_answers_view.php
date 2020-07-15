<?php
// Standard GPL and phpdocs
namespace mod_ivs\output\match;

use mod_ivs\MoodleMatchController;
use moodle_url;
use renderable;
use renderer_base;
use templatable;
use stdClass;
use tool_langimport\controller;

class question_answers_view implements renderable, templatable {

    var $detailArray = null;
    var $questions = null;
    var $cm_id = null;
    protected $video_id;
    protected $course_users;
    protected $totalcount;

    public function __construct($array, $questions, $cm_id, $video_id, $course_users, $totalcount) {
        $this->detailArray = $array;
        $this->questions = $questions;
        $this->cm_id = $cm_id;
        $this->video_id = $video_id;
        $this->course_users = $course_users;
        $this->totalcount = $totalcount;
    }

    public function export_for_template(renderer_base $output) {

        $instance = $this->video_id; //$cm->instance

        $controller = new MoodleMatchController();
        $data = $controller->getQuestionAnswersData($this->detailArray, $this->questions, $this->cm_id, $this->video_id,
                $this->course_users, $this->totalcount, $output);

        $qid = $data->id;
        $data->download_options = $output->download_dataformat_selector(get_string('ivs_match_download_summary_label', 'ivs'),
                'question_answers_download.php', 'download',
                array('question_id' => $qid, 'cmid' => $this->cm_id, 'instance_id' => $instance,
                        'total_count' => $this->totalcount));
        return $data;
    }
}
