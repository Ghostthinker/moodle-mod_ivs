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
 * Class for the Report Service
 *
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs;

use Exception;
use mod_ivs\license\MoodleLicenseController;

defined('MOODLE_INTERNAL') || die();

/**
 * Class ReportService
 */
class StatisticsService {

    private \moodle_database $database;

    /**
     * UpdateService constructor.
     */
    public function __construct() {
        global $DB;
        $this->database = $DB;
    }

    /**
     * Triggered when deinstalling the plugin
     * @return void
     */
    public function triggerDeinstallationRequest() {

        $data = [
                'deinstallation_date' => time()
        ];

        $this->sendStatisticData($data);
    }

    /**
     * Callback to check if statistics can be sended
     * @return void
     * @throws \dml_exception
     */
    public function statisticChanged(){
        $sendstatisticenabled = get_config('mod_ivs', 'ivs_statistics');
        if(!(int) $sendstatisticenabled){
            return;
        }
        $statisticData = $this->collectStatisticData();
        $this->sendStatisticData($statisticData);
    }

    /**
     * Callback for sending the statistics
     * @param $data
     * @return void
     * @throws \dml_exception
     */
    private function sendStatisticData($data) {
        $data['instance_id'] = get_config('mod_ivs', 'ivs_instance_id');
        $moodlelicencecontroller = new MoodleLicenseController();
        $moodlelicencecontroller->send_request('statistic', $data);
    }

    /**
     * Collect all relevant IVS statistics data
     * @param $reset
     * @return array|mixed
     * @throws \dml_exception
     */
    public function collectStatisticData($reset = FALSE) {

        static $cache = NULL;

        if(!empty($cache) && !$reset){
            return $cache;
        }

        $values['num_ivs_activities'] = $this->numIVSActivities();
        $values['num_courses_with_ivs_activities'] = $this->numCoursesWithIVSActivities();
        $values['num_ivs_comments'] = $this->numIVSComments();
        $values['num_ivs_audio_comments'] = $this->numIVSAudioComments();
        $values['num_ivs_match_questions'] = $this->numIVSMatchQuestions();
        $values['num_ivs_match_questions_types'] = $this->numIVSMatchQuestionsTypes();
        $values['num_ivs_match_takes'] = $this->numIVSMatchTakes();
        $values['num_ivs_videohosts'] = $this->numIVSVideoHosts();
        $values['ivs_settings'] = get_config('mod_ivs');
        $cache = $values;
        return $values;

    }

    /**
     * Returns the number of all ivs activites
     * @return int
     * @throws \dml_exception
     */
    private function numIVSActivities() {
        return $this->database->count_records_sql("SELECT COUNT(id) FROM {ivs} ");
    }

    /**
     * Returns the number of all courses which uses atleast one ivs activitie
     * @return int
     * @throws \dml_exception
     */
    private function numCoursesWithIVSActivities() {
        return $this->database->count_records_sql("SELECT COUNT(DISTINCT(course)) FROM {ivs} ");
    }

    /**
     * Returns the number of all ivs comments
     * @return int
     * @throws \dml_exception
     */
    private function numIVSComments() {
        return $this->database->count_records_sql("SELECT COUNT(id) FROM {ivs_videocomment}");
    }

    /**
     * Return the number of all audio comments
     * @return int
     * @throws \dml_exception
     */
    private function numIVSAudioComments() {
       return $this->database->count_records_sql("SELECT COUNT(id) FROM {ivs_videocomment} WHERE comment_type = 'audio_comment'");
    }

    /**
     * Return the number of all match questions
     * @return int
     * @throws \dml_exception
     */
    private function numIVSMatchQuestions() {
        return $this->database->count_records_sql("SELECT COUNT(id) FROM {ivs_matchquestion}");
    }

    /**
     * Return the number of different match questions
     * @return array
     * @throws \dml_exception
     */
    private function numIVSMatchQuestionsTypes() {
        $questiontypes = [];
        $questiontypes['single_choice_question'] =
                $this->database->count_records_sql("SELECT COUNT(id) FROM {ivs_matchquestion} WHERE type='single_choice_question'");
        $questiontypes['click_question'] =
                $this->database->count_records_sql("SELECT COUNT(id) FROM {ivs_matchquestion} WHERE type='click_question'");
        $questiontypes['text_question'] =
                $this->database->count_records_sql("SELECT COUNT(id) FROM {ivs_matchquestion} WHERE type='text_question'");
        return $questiontypes;
    }

    /**
     * Returns the number of all completed match takes
     * @return int
     * @throws \dml_exception
     */
    private function numIVSMatchTakes() {
        return $this->database->count_records_sql("SELECT COUNT(id) FROM {ivs_matchtake}");
    }

    /**
     * Returns all different numbers for used video hosts
     * @return array
     * @throws \dml_exception
     */
    private function numIVSVideoHosts() {
        $videohosts = [];
        $videohosts['panopto'] =
                $this->database->count_records_sql("SELECT COUNT(id) FROM {ivs} WHERE videourl LIKE 'PanoptoFileVideoHost:%'");
        $videohosts['kaltura'] =
                $this->database->count_records_sql("SELECT COUNT(id) FROM {ivs} WHERE videourl LIKE 'KalturaFileVideoHost:%'");
        $videohosts['opencast'] =
                $this->database->count_records_sql("SELECT COUNT(id) FROM {ivs} WHERE videourl LIKE 'OpenCastFileVideoHost:%'");
        $videohosts['internal'] =
                $this->database->count_records_sql("SELECT COUNT(id) FROM {ivs} WHERE videourl LIKE 'MoodleFileVideoHost:%'");
        $videohosts['external'] =
                $this->database->count_records_sql("SELECT COUNT(id) FROM {ivs} WHERE videourl LIKE 'ExternalSourceVideoHost:%'");
        return $videohosts;
    }

}
