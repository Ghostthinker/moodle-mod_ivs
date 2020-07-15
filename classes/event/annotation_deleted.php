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
class annotation_deleted extends annotation_base {

    public static function get_name() {
        return get_string('eventannotationdeleted', 'mod_ivs');
    }

    public function get_description() {
        return "The user with id {$this->userid} deleted an annotation with id {$this->objectid}.";
    }
}
