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
 * Created by PhpStorm.
 * User: Ghostthinker
 * Date: 10.11.2017
 * Time: 15:31
 */

/**
 * Annotation created event
 *
 * @package    mod_ivs
 * @copyright  13.11.2017 - 09:42 - BH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_ivs\event;
defined('MOODLE_INTERNAL') || die();

/**
 * The mod_ivs instance to send messages by creating an annotation.
 *
 * @package    mod_ivs
 * @copyright 2017 Ghostthinker GmbH <info@ghostthinker.de>
 * @license   All Rights Reserved.
 */
abstract class annotation_base extends \core\event\base {

    protected function init() {
        $this->data['crud'] = 'c'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'ivs_videocomment';
    }

    public static function get_name() {
        throw new \Error("TBI");
    }

    public function get_description() {
        throw new \Error("TBI");
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/ivs/view.php', array('id' => $this->contextinstanceid));
    }

    public function get_legacy_logdata() {
        // Override if you are migrating an add_to_log() call.
        return array($this->courseid, 'ivs', 'LOGACTION',
                '...........',
                $this->objectid, $this->contextinstanceid);
    }

    protected function get_legacy_eventdata() {
        // Override if you migrating events_trigger() call.
        $data = new \stdClass();
        $data->id = $this->objectid;
        $data->userid = $this->relateduserid;
        return $data;
    }
}
