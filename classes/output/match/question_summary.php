<?php
// Standard GPL and phpdocs
namespace mod_ivs\output\match;

use moodle_url;
use renderable;
use renderer_base;
use templatable;
use stdClass;

class question_summary implements renderable, templatable {

    var $question = null;
    var $module = null;
    private $ivs;

    public function __construct($ivs, $questions, $module, $offset, $perpage, $courseStudents) {
        $this->questions = $questions;
        $this->module = $module;
        $this->offset = $offset;
        $this->perpage = $perpage;
        $this->course_students = $courseStudents;
        $this->ivs = $ivs;
    }

    public function export_for_template(renderer_base $output) {

        $data = new stdClass();
        $data->title = get_string("ivs_match_question_summary_title", 'ivs');

        $data->question_id = get_string("ivs_match_question_summary_question_id", 'ivs');#'Question ID';
        $data->question_title = get_string("ivs_match_question_summary_question_title", 'ivs'); #'Title';
        $data->question_body = get_string("ivs_match_question_summary_question_body", 'ivs'); #'Question';
        $data->question_type = get_string("ivs_match_question_summary_question_type", 'ivs'); #'Question Type';
        $data->question_first_try = get_string("ivs_match_question_summary_question_first_try", 'ivs'); #'First Try: correct';
        $data->question_last_try = get_string("ivs_match_question_summary_question_last_try", 'ivs'); #'Last Try: correct';
        $data->question_answered = get_string("ivs_match_question_summary_question_answered", 'ivs'); #'Participation';

        $totalcount = count($this->questions);

        for ($i = $this->offset; $i < $this->offset + $this->perpage; $i++) {
            if ($i == $totalcount) {
                break;
            }
            $renderable = new question_summary_view($this->questions[$i], $this->course_students);
            $data->questions[] = $output->render($renderable);
        }

        //Render Pager Options in Dropdown
        $pager_url = new moodle_url('/mod/ivs/questions.php?id=' . $this->module->id . '&question-summary');

        if (optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT) == 10) {
            $data->pager_options[] = '<option selected value="' . $pager_url . '&perpage=10">10</option>';
            $data->pager_options[] = '<option value="' . $pager_url . '&perpage=100">100</option>';
        } else {
            $data->pager_options[] = '<option value="' . $pager_url . '&perpage=10">10</option>';
            $data->pager_options[] = '<option selected value="' . $pager_url . '&perpage=100">100</option>';
        }

        $data->elements = get_string("ivs_match_question_answer_menu_label_elements_per_page", 'ivs');

        $data->download_options = $output->download_dataformat_selector(get_string('ivs_match_download_summary_label', 'ivs'),
                'question_overview_download.php', 'download', array('player_id' => $this->ivs->id, 'cmid' => $this->module->id));
        return $data;
    }
}
