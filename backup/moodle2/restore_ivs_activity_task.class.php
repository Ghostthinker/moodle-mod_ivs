<?php
/*************************************************************************
 *
 * GHOSTTHINKER CONFIDENTIAL
 * __________________
 *
 *  2006 - 2017 Ghostthinker GmbH
 *  All Rights Reserved.
 *
 * NOTICE:  All information contained herein is, and remains
 * the property of Ghostthinker GmbH and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Ghostthinker GmbH
 * and its suppliers and may be covered by German and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Ghostthinker GmbH.
 */

/**
 * Provides the restore activity task class
 *
 * @package   mod_ivs
 * @category  backup
 * @copyright 2017 Ghostthinker GmbH <info@ghostthinker.de>
 * @license   All Rights Reserved.
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/ivs/backup/moodle2/restore_ivs_stepslib.php');
require_once($CFG->dirroot . '/mod/ivs/backup/moodle2/restore_ivs_settingslib.php');

/**
 * Restore task for the ivs activity module
 *
 * Provides all the settings and steps to perform complete restore of the activity.
 *
 * @package   mod_ivs
 * @category  backup
 * @copyright 2017 Ghostthinker GmbH <info@ghostthinker.de>
 * @license   All Rights Reserved.
 */
class restore_ivs_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        //Add Match Answer Backup Setting
        $setting_match_answer = new restore_match_answer_setting('ivs_' . $this->oldmoduleid . '_include_match');
        $this->add_setting($setting_match_answer);
        $this->plan->get_setting('users')->add_dependency($setting_match_answer);

        $setting_videocomments = new restore_videocomments_setting('ivs_' . $this->oldmoduleid . '_include_videocomments');
        $this->add_setting($setting_videocomments);
        $this->plan->get_setting('users')->add_dependency($setting_videocomments);
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // We have just one structure step here.
        $this->add_step(new restore_ivs_activity_structure_step('ivs_structure', 'ivs.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('ivs', array('intro'), 'ivs');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('IVSVIEWBYID', '/mod/ivs/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('IVSINDEX', '/mod/ivs/index.php?id=$1', 'course');

        return $rules;

    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * ivs logs. It must return one array
     * of {@link restore_log_rule} objects
     */
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('ivs', 'add', 'view.php?id={course_module}', '{ivs}');
        $rules[] = new restore_log_rule('ivs', 'update', 'view.php?id={course_module}', '{ivs}');
        $rules[] = new restore_log_rule('ivs', 'view', 'view.php?id={course_module}', '{ivs}');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('ivs', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
