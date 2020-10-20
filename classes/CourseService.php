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
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs;

defined('MOODLE_INTERNAL') || die();

//require_once('../../../config.php');

class CourseService {

    public static function get_course_context($courseid) {
        if (empty($courseid)) {
            // Get the course module id from a post or get request.
            $courseid = required_param('id', PARAM_INT);
        }

        return \context_course::instance($courseid);
    }

    public function get_course_module($courseid, $modulename = MOD_ivs_COMMENT) {
        if (empty($courseid)) {
            // Get the course module id from a post or get request.
            $courseid = required_param('id', PARAM_INT);
        }

        // Get the course module.
        return get_coursemodule_from_id($modulename, $courseid, 0, false, MUST_EXIST);
    }

    public function get_user_activity_groups($courseid) {
        // Get the course module.
        $cm = $this->get_course_module($courseid);

        return groups_get_activity_allowed_groups($cm);
    }

    public function get_all_course_groups($coursed) {
        return groups_get_all_groups($coursed);
    }

    public static function get_user_course_groups($courseid, $userid) {
        return groups_get_all_groups($courseid, $userid);
    }

    public function get_course_members($courseid) {
        global $DB;
        $direction = 'DESC';

        $sql = "SELECT DISTINCT u.id, u.firstname,u.lastname
        FROM {user} u 
        JOIN {user_enrolments} ue ON (ue.userid = u.id ) 
        JOIN {enrol} e ON (ue.enrolid = e.id ) 
        Where e.courseid= ?
         ORDER BY u.lastname, u.firstname DESC";

        $users = $DB->get_records_sql($sql,[$courseid]);

        return $users;

        $groups = $this->get_all_course_groups($courseid);

        $members = array();
        foreach ($groups as $group) {
            $gm = groups_get_members($group->id);
            if (sizeof($gm) > 0) {
                $members[] = $gm;
            }
        }
        return $members;
    }

    public function get_role_names($courseid) {
        $coursecontext = $this->get_course_context($courseid);
        return role_fix_names(get_roles_used_in_context($coursecontext));

    }

    public function get_roles_in_context($courseid) {
        $coursecontext = $this->get_course_context($courseid);
        return get_roles_used_in_context($coursecontext);
    }

    public function get_course_membersby_role($courseid, $roleid) {
        global $DB;

        $sql = "SELECT DISTINCT u.*
                FROM {user} u
                  JOIN {user_enrolments} ue ON ue.userid = u.id
                  JOIN {enrol} e ON e.id = ue.enrolid
                  JOIN {role_assignments} ra ON ra.userid = u.id AND ra.roleid = ?
                  JOIN {context} ct ON ct.id = ra.contextid AND ct.contextlevel = 50
                  JOIN {course} c ON c.id = ct.instanceid AND e.courseid = c.id
                WHERE c.id = ? AND e.status = 0 AND u.suspended = 0 AND u.deleted = 0
                AND (ue.timeend = 0 OR ue.timeend > ?) AND ue.status = 0 ORDER BY u.lastname";

        $users = $DB->get_records_sql($sql, array($roleid, $courseid, time()));
        return $users;

    }

    /*
     * @return array of objects with fields ->userid, ->contextid and ->roleid.
     */
    public static function get_user_course_role_assignments($courseid, $userid) {


        $coursecontext = self::get_course_context($courseid);
        return get_user_roles_with_special($coursecontext, $userid);
    }

    public function get_current_user_groups() {
        return groups_get_my_groups();
    }

    public function get_current_user_courses() {
        $groups = $this->get_current_user_groups();

        $courses = array();
        foreach ($groups as $group) {
            $course = get_course($group->courseid);

            if (!array_key_exists($course->id, $courses)) {
                $courses[$course->id] = $course;
            }
        }
        return $courses;
    }

    public function get_user_groups($userid) {
        global $DB;
        $sql = "SELECT * FROM {groups_members} gm JOIN {groups} g
              ON g.id = gm.groupid WHERE gm.userid = ? ORDER BY name ASC";
        return $DB->get_records_sql($sql, array($userid));
    }

    public function get_user_courses($userid) {
        $groups = $this->get_user_groups($userid);

        $courses = array();
        foreach ($groups as $group) {
            $course = get_course($group->courseid);
            if (!array_key_exists($course->id, $courses)) {
                $courses[$course->id] = $course;
            }
        }
        return $courses;
    }

    /**
     * Get all students by course
     *
     * @param $courseid
     * @return array
     * @throws \dml_exception
     */
    public function get_course_students($courseid) {
        global $DB;
        $rolestudent = $DB->get_record('role', array('shortname' => 'student'));
        return $this->get_course_membersby_role($courseid, $rolestudent->id);
    }
}
