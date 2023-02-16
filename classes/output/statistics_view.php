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
 * Output class for rendering no licenses for courses
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

// Standard GPL and phpdocs.
namespace mod_ivs\output;

use mod_ivs\StatisticsService;
use renderable;
use renderer_base;
use templatable;

/**
 * Class settings_license_none_view
 *
 */
class statistics_view implements renderable, templatable {

    /**
     * settings_license_none_view constructor.
     */
    public function __construct() {
    }

    /**
     * Render mustache template
     * @param \renderer_base $output
     *
     * @return \stdClass
     */
    public function export_for_template(renderer_base $output) {
        $statisticsservice = new StatisticsService();
        $statisticdata = $statisticsservice->collectStatisticData();

        $statisticdataobject = new \stdClass();
        $statisticdataobject->statistic_heading = get_string('ivs_statistics', 'ivs');;
        $statisticdataobject->statistic_info_general = get_string('statistic_info_general', 'ivs');;
        $statisticdataobject->statistic_info = get_string('statistic_info_text', 'ivs');
        $statisticdataobject->num_ivs_activities_label = get_string('ivs_activities_label', 'ivs');
        $statisticdataobject->num_ivs_activities = $statisticdata['num_ivs_activities'];
        $statisticdataobject->num_courses_with_ivs_activities_label = get_string('ivs_activities_courses_label', 'ivs');
        $statisticdataobject->num_courses_with_ivs_activities = $statisticdata['num_courses_with_ivs_activities'];
        $statisticdataobject->num_ivs_comments_label = get_string('ivs_comments_label', 'ivs');
        $statisticdataobject->num_ivs_comments = $statisticdata['num_ivs_comments'];
        $statisticdataobject->num_ivs_audio_comments_label = get_string('ivs_audio_comments_label', 'ivs');
        $statisticdataobject->num_ivs_audio_comments = $statisticdata['num_ivs_audio_comments'];
        $statisticdataobject->num_ivs_match_questions_label = get_string('ivs_match_question_label', 'ivs');
        $statisticdataobject->num_ivs_match_questions = $statisticdata['num_ivs_match_questions'];
        $statisticdataobject->num_ivs_match_questions_types_label = get_string('ivs_match_question_types_label', 'ivs');;
        $statisticdataobject->num_ivs_match_questions_types_single_choice_question_label = 'Single-Choice';
        $statisticdataobject->num_ivs_match_questions_types_single_choice_question = $statisticdata['num_ivs_match_questions_types']['single_choice_question'];
        $statisticdataobject->num_ivs_match_questions_types_click_question_label = 'Click';
        $statisticdataobject->num_ivs_match_questions_types_click_question = $statisticdata['num_ivs_match_questions_types']['click_question'];
        $statisticdataobject->num_ivs_match_questions_types_text_question_label = 'Text';
        $statisticdataobject->num_ivs_match_questions_types_text_question = $statisticdata['num_ivs_match_questions_types']['text_question'];
        $statisticdataobject->num_ivs_match_takes_label = get_string('ivs_match_takes_label', 'ivs');
        $statisticdataobject->num_ivs_match_takes = $statisticdata['num_ivs_match_takes'];
        $statisticdataobject->num_ivs_videohosts_label = get_string('ivs_videohosts_label', 'ivs');
        $statisticdataobject->num_ivs_videohosts_panopto = $statisticdata['num_ivs_videohosts']['panopto'];
        $statisticdataobject->num_ivs_videohosts_panopto_label = 'Panopto';
        $statisticdataobject->num_ivs_videohosts_kaltura = $statisticdata['num_ivs_videohosts']['kaltura'];
        $statisticdataobject->num_ivs_videohosts_kaltura_label = 'Kaltura';
        $statisticdataobject->num_ivs_videohosts_opencast = $statisticdata['num_ivs_videohosts']['opencast'];
        $statisticdataobject->num_ivs_videohosts_opencast_label = 'Opencast';
        $statisticdataobject->num_ivs_videohosts_internal = $statisticdata['num_ivs_videohosts']['internal'];
        $statisticdataobject->num_ivs_videohosts_internal_label = 'Intern';
        $statisticdataobject->num_ivs_videohosts_external = $statisticdata['num_ivs_videohosts']['external'];
        $statisticdataobject->num_ivs_videohosts_external_label = 'Extern';


        return $statisticdataobject;
    }
}
