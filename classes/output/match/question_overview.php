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
 * Output class for rendering the question overview
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

// Standard GPL and phpdocs.
namespace mod_ivs\output\match;

use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Class question_overview
 *
 */
class question_overview implements renderable, templatable {

    /**
     * @var null
     */
    public $question = null;

    /**
     * @var null
     */
    public $module = null;

    /**
     * question_overview constructor.
     *
     * @param array $question
     * @param stdClass $module
     */
    public function __construct($question, $module) {
        $this->question = $question;
        $this->module = $module;
    }

    /**
     * Render mustache template
     * @param \renderer_base $output
     *
     * @return \stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->id = $this->question['nid'];
        $this->question['question_body'] = str_replace('\[', '$$', $this->question['question_body']);
        $this->question['question_body'] = str_replace('\]', '$$', $this->question['question_body']);
        $this->question['question_body'] = str_replace('\(', '$', $this->question['question_body']);
        $this->question['question_body'] = str_replace('\)', '$', $this->question['question_body']);
        $this->question['title'] = str_replace('\[', '$$', $this->question['title']);
        $this->question['title'] = str_replace('\]', '$$', $this->question['title']);
        $this->question['title'] = str_replace('\(', '$', $this->question['title']);
        $this->question['title'] = str_replace('\)', '$', $this->question['title']);
        // We need this, because he dont apply mathjax when no $$ exists.
        if (!strpos($this->question['question_body'], '$$')) {
            $this->question['question_body'] .= ' $$ $$';
        }
        if (!strpos($this->question['title'], '$$')) {
            $this->question['title'] .= ' $$ $$';
        }
        $data->question = format_text($this->question['question_body'], FORMAT_MARKDOWN);
        if (strlen($this->question['title']) > 0) {
            $data->question = '<div style="display:flex;    align-items: baseline;">'.format_text($this->question['title'],
                FORMAT_MARKDOWN) . ': &nbsp;&nbsp;' . format_text($this->question['question_body'], FORMAT_MARKDOWN) . '</div>';
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
