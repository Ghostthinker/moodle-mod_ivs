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
 * Renderer class for the mustache files
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;

/**
 * Class renderer
 *
 */
class renderer extends plugin_renderer_base {
    /**
     * Defer to template.
     *
     * @param index_page $page
     *
     * @return string html for the page
     */
    public function render_annotation_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/annotation_view', $data);
    }

    /**
     * Define renderer for template
     * @param index_page $page
     *
     * @return mixed
     */
    public function render_annotation_reply_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/annotation_reply_view', $data);
    }

    /**
     * Define renderer for template
     * @param index_page $page
     *
     * @return mixed
     */
    public function render_annotation_report_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/annotation_report_view', $data);
    }

    /**
     * Define renderer for template
     * @param index_page $page
     *
     * @return mixed
     */
    public function render_question_overview($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/question_view', $data);
    }

    /**
     * Define renderer for template
     * @param index_page $page
     *
     * @return mixed
     */
    public function render_question_answers_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/question_answers_view', $data);
    }

    /**
     * Define renderer for template
     * @param index_page $page
     *
     * @return mixed
     */
    public function render_question_text_answer_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/question_text_answer_view', $data);
    }

    /**
     * Define renderer for template
     * @param index_page $page
     *
     * @return mixed
     */
    public function render_question_click_answer_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/question_click_answer_view', $data);
    }

    /**
     * Define renderer for template
     * @param index_page $page
     *
     * @return mixed
     */
    public function render_question_single_choice_answer_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/question_single_choice_answer_view', $data);
    }

    /**
     * Define renderer for template
     * @param index_page $page
     *
     * @return mixed
     */
    public function render_question_summary($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/question_summary', $data);
    }

    /**
     * Define renderer for template
     * @param index_page $page
     *
     * @return mixed
     */
    public function render_question_summary_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/question_summary_view', $data);
    }

    /**
     * Define renderer for template
     * @param index_page $page
     *
     * @return mixed
     */
    public function settings_license_none_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/settings_license_none_view', $data);
    }

    /**
     * Define renderer for template
     * @param index_page $page
     *
     * @return mixed
     */
    public function render_settings_license_course_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/settings_license_course_view', $data);
    }

    /**
     * Define renderer for template
     * @param index_page $page
     *
     * @return mixed
     */
    public function render_settings_license_instance_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/settings_license_instance_view', $data);
    }

}
