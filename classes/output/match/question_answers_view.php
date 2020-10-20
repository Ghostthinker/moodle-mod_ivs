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
    var $cmid = null;
    protected $videoid;
    protected $courseusers;
    protected $totalcount;

    public function __construct($array, $questions, $cmid, $videoid, $courseusers, $totalcount) {
        $this->detailArray = $array;
        $this->questions = $questions;
        $this->cmid = $cmid;
        $this->videoid = $videoid;
        $this->courseusers = $courseusers;
        $this->totalcount = $totalcount;
    }

    public function export_for_template(renderer_base $output) {

        $instance = $this->videoid;

        $controller = new MoodleMatchController();
        $data = $controller->get_question_answers_data($this->detailArray, $this->questions, $this->cmid, $this->videoid,
                $this->courseusers, $this->totalcount, $output);

        $qid = $data->id;
        $data->download_options = $output->download_dataformat_selector(get_string('ivs_match_download_summary_label', 'ivs'),
                'question_answers_download.php', 'download',
                array('question_id' => $qid, 'cmid' => $this->cmid, 'instance_id' => $instance,
                        'total_count' => $this->totalcount));
        return $data;
    }
}
