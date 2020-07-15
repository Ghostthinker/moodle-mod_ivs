<?php

namespace mod_ivs\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;

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

    public function render_annotation_reply_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/annotation_reply_view', $data);
    }

    public function render_annotation_report_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/annotation_report_view', $data);
    }

    public function render_question_overview($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/question_view', $data);
    }

    public function render_question_answers_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/question_answers_view', $data);
    }

    public function render_question_text_answer_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/question_text_answer_view', $data);
    }

    public function render_question_click_answer_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/question_click_answer_view', $data);
    }

    public function render_question_single_choice_answer_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/question_single_choice_answer_view', $data);
    }

    public function render_question_summary($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/question_summary', $data);
    }

    public function render_question_summary_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/question_summary_view', $data);
    }

    public function settings_license_none_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/settings_license_none_view', $data);
    }

    public function render_settings_license_course_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/settings_license_course_view', $data);
    }

    public function render_settings_license_instance_view($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('ivs/settings_license_instance_view', $data);
    }

}
