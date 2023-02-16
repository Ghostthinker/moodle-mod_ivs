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
 * IvsHelper class to communicate with the player
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs;

/**
 * Class IvsHelper
 */
class IvsHelper {

    /**
     * Get the user date for the player
     * @param int $userid
     *
     * @return array|string[]
     */
    public static function get_user_data_for_player($userid) {

        if ($userid == null) {

            return array(
                    'name' => "Anonymous",
                    'picture' => (string) new \moodle_url('/user/pix.php')
            );
        }

        $user = self::get_user($userid);

        return array(
                'uid' => $userid,
                'name' => $user['fullname'],
                'picture' => $user['picture']
        );
    }

    /**
     * Get the moodle user of this annotation
     * @param int $id
     *
     * @return mixed|null
     */
    public static function get_user($id) {
        global $USER, $DB, $PAGE;

        if (empty($id)) {
            return null;
        }

        static $usercache;

        if (!isset($usercache[$id])) {

            if ($USER->id == $id) {
                $user = clone($USER);
            } else {
                $user = $DB->get_record(
                        'user', array('id' => $id));
            }

            if (!empty($user)) {
                $userpicture = new \user_picture($user);
                $userpictureurl = (string) $userpicture->get_url($PAGE);

                $usercache[$id]['user'] = $user;
                $usercache[$id]['fullname'] = fullname($user);
                $usercache[$id]['picture'] = $userpictureurl;
                $usercache[$id]['pictureObject'] = $userpicture;
            } else {
                $userpicture = new \moodle_url('/user/pix.php');

                $usercache[$id]['user'] = null;
                $usercache[$id]['fullname'] = "Anonymous";
                $usercache[$id]['picture'] = (string) $userpicture;

            }
        }
        return $usercache[$id];
    }

    /**
     * Check if the current user may view an ivs
     *
     * @param int $ivsid
     * @return bool
     */
    public static function access_player($ivsid) {
        global $DB;

        try {

            $ivs = $DB->get_record('ivs', array('id' => $ivsid), '*', MUST_EXIST);
            $course = $DB->get_record('course', array('id' => $ivs->course), '*', MUST_EXIST);
            $cm = get_coursemodule_from_instance('ivs', $ivs->id, $course->id, false, MUST_EXIST);

            require_login($course, true, $cm);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * get language
     *
     * @param int $ivsid
     * @return bool
     */
    public static function get_language() {
        $currentlanguage = current_language();
        $lang = $currentlanguage;
        return stripos($lang, '_') ? substr($lang, 0, stripos($lang, '_')) : $lang;
    }

    public static function get_ivs_activities_by_course_and_type($courseId) {
        global $DB;
        $activityType = $DB->get_record('modules', array('name' => 'ivs'));
        $records = $DB->get_records('course_modules', array('course' => $courseId, 'module' => $activityType->id));
        $activities = [];
        foreach($records as $cm){
            $activities[] = $DB->get_record('ivs', array('id' => $cm->instance), '*', MUST_EXIST);
        }

        return $activities;
    }

    public static function get_ivs_activities_by_instance_and_type() {
        global $DB;
        $activityType = $DB->get_record('modules', array('name' => 'ivs'));
        $records = $DB->get_records('course_modules', array('module' => $activityType->id));
        $activities = [];
        foreach($records as $cm){
            $activities[] = $DB->get_record('ivs', array('id' => $cm->instance), '*', MUST_EXIST);
        }
        return $activities;
    }
}
