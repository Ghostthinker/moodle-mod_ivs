<?php
/**
 * Created by PhpStorm.
 * User: Ghostthinker
 * Date: 30.07.2018
 * Time: 17:24
 */

namespace mod_ivs;

class IvsHelper {

    static function getUserDataForPlayer($user_id) {

        if ($user_id == null) {


            return array(
                    'name' => "Anonymous",
                    'picture' => (string) new \moodle_url('/user/pix.php')
            );
        }

        $user = IvsHelper::getUser($user_id);

        return array(
                'uid' => $user_id,
                'name' => $user['fullname'],
                'picture' => $user['picture']
        );
    }

    /**
     * Get the moodle user of this annotation
     *
     * @return bool|false|mixed|null|object|\stdClass|string
     */
    public static function getUser($id) {
        global $USER, $DB, $PAGE;;

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

            $user_picture = new \user_picture($user);
            $user_picture_url = $user_picture->get_url($PAGE) . '';

            $usercache[$id]['user'] = $user;
            $usercache[$id]['fullname'] = fullname($user);
            $usercache[$id]['picture'] = $user_picture_url;

        }
        return $usercache[$id];

    }

    /**
     * Check if the current user may view an ivs
     *
     * @param $ivs_id
     * @return bool
     */
    static function accessPlayer($ivs_id) {
        global $DB;

        try {

            $ivs = $DB->get_record('ivs', array('id' => $ivs_id), '*', MUST_EXIST);
            $course = $DB->get_record('course', array('id' => $ivs->course), '*', MUST_EXIST);
            $cm = get_coursemodule_from_instance('ivs', $ivs->id, $course->id, false, MUST_EXIST);

            require_login($course, true, $cm);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

}
