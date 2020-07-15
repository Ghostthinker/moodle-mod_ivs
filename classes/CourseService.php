<?php

namespace mod_ivs;

defined('MOODLE_INTERNAL') || die();

//require_once('../../../config.php');

class CourseService {

    public static function getCourseContext($courseId) {
        if (empty($courseId)) {
            // Get the course module id from a post or get request.
            $courseId = required_param('id', PARAM_INT);
        }

        return \context_course::instance($courseId);
    }

    public function getCourseModule($courseId, $moduleName = MOD_ivs_COMMENT) {
        if (empty($courseId)) {
            // Get the course module id from a post or get request.
            $courseId = required_param('id', PARAM_INT);
        }

        // Get the course module.
        return get_coursemodule_from_id($moduleName, $courseId, 0, false, MUST_EXIST);
    }

    public function getUserActivityGroups($courseId) {
        // Get the course module.
        $cm = $this->getCourseModule($courseId);

        return groups_get_activity_allowed_groups($cm);
    }

    public function getAllCourseGroups($courseId) {
        return groups_get_all_groups($courseId);
    }

    public static function getUserCourseGroups($courseId, $userId) {
        return groups_get_all_groups($courseId, $userId);
    }

    public function getCourseMembers($courseId) {
        global $DB;
        $direction = 'DESC';

        $sql = "SELECT DISTINCT u.id, u.firstname,u.lastname
        FROM {user} u 
        JOIN {user_enrolments} ue ON (ue.userid = u.id ) 
        JOIN {enrol} e ON (ue.enrolid = e.id ) 
        Where e.courseid=$courseId
         ORDER BY u.lastname, u.firstname DESC";

        $users = $DB->get_records_sql($sql);

        return $users;

        $groups = $this->getAllCourseGroups($courseId);

        $members = array();
        foreach ($groups as $group) {
            $gm = groups_get_members($group->id);
            if (sizeof($gm) > 0) {
                $members[] = $gm;
            }
            //$members[] = \core_user::get_user($member->userid);
        }
        return $members;
    }

    public function getRoleNames($courseId) {
        $courseContext = $this->getCourseContext($courseId);
        return role_fix_names(get_roles_used_in_context($courseContext));
        //return role_get_names($courseContext);
    }

    public function getRolesInContext($courseId) {
        $courseContext = $this->getCourseContext($courseId);
        return get_roles_used_in_context($courseContext);
    }

    public function getCourseMembersbyRole($courseId, $role_id) {
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

        $users = $DB->get_records_sql($sql, array($role_id, $courseId, time()));
        return $users;

    }

    /*
     * @return array of objects with fields ->userid, ->contextid and ->roleid.
     */
    public static function getUserCourseRoleAssignments($courseId, $userId) {


        $courseContext = self::getCourseContext($courseId);
        return get_user_roles_with_special($courseContext, $userId);
    }

    public function getCurrentUserGroups() {
        return groups_get_my_groups();
    }

    public function getCurrentUserCourses() {
        $groups = $this->getCurrentUserGroups();

        $courses = array();
        foreach ($groups as $group) {
            $course = get_course($group->courseid);
            /*
            if (!in_array($course, $courses)) {
                $courses[] = $course;
            }
            */
            if (!array_key_exists($course->id, $courses)) {
                $courses[$course->id] = $course;
            }
        }
        return $courses;
    }

    public function getUserGroups($userId) {
        global $DB;
        $sql = "SELECT * FROM {groups_members} gm JOIN {groups} g
              ON g.id = gm.groupid WHERE gm.userid = ? ORDER BY name ASC";
        return $DB->get_records_sql($sql, array($userId));
    }

    public function getUserCourses($userId) {
        $groups = $this->getUserGroups($userId);

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
     * @param $courseId
     * @return array
     * @throws \dml_exception
     */
    public function getCourseStudents($courseId) {
        global $DB;
        $roleStudent = $DB->get_record('role', array('shortname' => 'student'));
        return $this->getCourseMembersbyRole($courseId, $roleStudent->id);
    }
}
