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
 * Define all the backup steps that will be used by the backup_ivs_activity_task
 *
 * @package   mod_ivs
 * @category  backup
 * @copyright 2017 Ghostthinker GmbH <info@ghostthinker.de>
 * @license   All Rights Reserved.
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define the complete ivs structure for backup, with file and id annotations
 *
 * @package   mod_ivs
 * @category  backup
 * @copyright 2017 Ghostthinker GmbH <info@ghostthinker.de>
 * @license   All Rights Reserved.
 */
class backup_course_ivs_settings_step extends backup_activity_structure_step {

    /**
     * Defines the backup structure of the module
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        // Get know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Get anonymize setting
        $anonymize = $this->get_setting_value('anonymize');

        //Backup Player Settings
        $course_settings = new backup_nested_element('settings');

        $course_setting = new backup_nested_element('setting', array('id'), array(
                'target_id',
                'target_type',
                'name',
                'value',
                'locked'
        ));

        $course_settings->add_child($course_setting);

        //Player settings activity
        $course_setting->set_source_sql('
            SELECT *
              FROM {ivs_settings}
             WHERE target_id = ? AND target_type = \'course\'',
                array(backup::VAR_COURSEID));

        return $this->prepare_activity_structure($course_settings);
    }
}
