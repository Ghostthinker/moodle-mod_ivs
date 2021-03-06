<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

// Standard GPL and phpdocs.
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
            $renderable->question['question_body'] = str_replace('\[','$$',$renderable->question['question_body']);
            $renderable->question['question_body'] = str_replace('\]','$$',$renderable->question['question_body']);
            $renderable->question['question_body'] = str_replace('\(','$',$renderable->question['question_body']);
            $renderable->question['question_body'] = str_replace('\)','$',$renderable->question['question_body']);
            $renderable->question['title'] = str_replace('$$','$',$renderable->question['title']);
            $renderable->question['title'] = str_replace('$','$$',$renderable->question['title']);
            $renderable->question['title'] = str_replace('\[','$$',$renderable->question['title']);
            $renderable->question['title'] = str_replace('\]','$$',$renderable->question['title']);
            $renderable->question['title'] = str_replace('\(','$$',$renderable->question['title']);
            $renderable->question['title'] = str_replace('\)','$$',$renderable->question['title']);
            // We need this, because he dont apply mathjax when no $$ exists.
            if(!strpos($renderable->question['question_body'],'$$')){
                $renderable->question['question_body'] .= ' $$ $$';
            }
            if(!strpos($renderable->question['title'],'$$')){
                $renderable->question['title'] .= ' $$ $$';
            }
            $renderable->question['question_body'] = format_text($renderable->question['question_body'], FORMAT_MARKDOWN);
            $renderable->question['title'] = format_text($renderable->question['title'], FORMAT_MARKDOWN);
            $data->questions[] = $output->render($renderable);
        }



        // Render Pager Options in Dropdown.
        $pagerurl = new moodle_url('/mod/ivs/questions.php?id=' . $this->module->id . '&question-summary');

        if (optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT) == 10) {
            $data->pager_options[] = '<option selected value="' . $pagerurl . '&perpage=10">10</option>';
            $data->pager_options[] = '<option value="' . $pagerurl . '&perpage=100">100</option>';
        } else {
            $data->pager_options[] = '<option value="' . $pagerurl . '&perpage=10">10</option>';
            $data->pager_options[] = '<option selected value="' . $pagerurl . '&perpage=100">100</option>';
        }

        $data->elements = get_string("ivs_match_question_answer_menu_label_elements_per_page", 'ivs');

        $data->download_options = $output->download_dataformat_selector(get_string('ivs_match_download_summary_label', 'ivs'),
                'question_overview_download.php', 'download', array('player_id' => $this->ivs->id, 'cmid' => $this->module->id));
        return $data;
    }
}
