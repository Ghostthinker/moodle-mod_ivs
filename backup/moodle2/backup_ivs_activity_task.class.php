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
 * Defines backup_ivs_activity_task class
 *
 * @package   mod_ivs
 * @category  backup
 * @copyright 2017 Ghostthinker GmbH <info@ghostthinker.de>
 * @license   All Rights Reserved.
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/ivs/backup/moodle2/backup_ivs_stepslib.php');
require_once($CFG->dirroot . '/mod/ivs/backup/moodle2/backup_course_ivs_settings_step.class.php');
//require_once($CFG->dirroot . '/mod/ivs/backup/moodle2/backup_ivs_settingslib.php');

/**
 * Provides the steps to perform one complete backup of the ivs instance
 *
 * @package   mod_ivs
 * @category  backup
 * @copyright 2017 Ghostthinker GmbH <info@ghostthinker.de>
 * @license   All Rights Reserved.
 */
class backup_ivs_activity_task extends backup_activity_task {

    /**
     * No specific settings for this activity
     */
    protected function define_my_settings() {

    }

    /**
     * Defines a backup step to store the instance data in the ivs.xml file
     */

    //Todo: Wait for answer from moodle question at https://moodle.org/mod/forum/discuss.php?d=378747#p1528693
    protected function define_my_steps() {
        $this->add_step(new backup_ivs_activity_structure_step('ivs_structure', 'ivs.xml'));
    }

    /**
     * Encodes URLs to the index.php and view.php scripts
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string the content with the URLs encoded
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, '/');

        // Link to the list of ivss.
        $search = '/(' . $base . '\/mod\/ivs\/index.php\?id\=)([0-9]+)/';
        $content = preg_replace($search, '$@IVSINDEX*$2@$', $content);

        // Link to ivs view by moduleid.
        $search = '/(' . $base . '\/mod\/ivs\/view.php\?id\=)([0-9]+)/';
        $content = preg_replace($search, '$@IVSVIEWBYID*$2@$', $content);

        return $content;
    }
}
