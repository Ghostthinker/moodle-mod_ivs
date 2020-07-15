<?php

namespace mod_ivs;

use Exception;

defined('MOODLE_INTERNAL') || die();

//require_once('../../../config.php');

//TODO create interface
class AnnotationService {

    /**
     * Get all annotations by course with filters
     *
     * @param $courseId
     * @param bool $skipAccessCheck
     * @param array $options
     * @param bool $count_total
     * @param null $user_id
     * @return array|mixed
     * @throws \Exception
     * @throws \dml_missing_record_exception
     * @throws \dml_multiple_records_exception
     */
    public function getAnnotationsByCourse($courseId, $skipAccessCheck = false, $options = array(), $count_total = false,
            $user_id = null) {
        if (empty($courseId)) {
            throw new Exception("no course id set");
        }

        //set default options

        $default_options = array(
                'sortkey' => "timecreated", //time_stamp
                'sortorder' => 'DESC',
                'grouping' => 'none', //'user', 'rating', 'video'
                'offset' => 0,
                'limit' => null,
                'filter_users' => null,
                'filter_has_drawing' => null,
                'filter_timecreated_min' => null,
                'groups' => null,
                'rating' => null
        );

        $options = array_replace_recursive($default_options, $options);

        global $DB;

        if ($user_id == null) {
            global $USER;
            $user_id = $USER->id;
        };

        $annotations = array();

        //build the  base query
        //$query = "SELECT DISTINCT vc.id, vc.* FROM {ivs_videocomment} vc WHERE parent_id IS NULL";

        //if we wanr the count only
        if ($count_total) {
            $query = "SELECT DISTINCT COUNT(vc.id) as total";
        } else {
            $query = "SELECT DISTINCT
                vc.id,
                vc.*
              ";
        }

        $query .= " FROM {ivs_videocomment} vc
                INNER JOIN {course_modules} cm
                  ON vc.video_id = cm.instance
                INNER JOIN {modules} m
                  ON cm.module = m.id
                     AND m.name = 'ivs'
              WHERE parent_id IS NULL AND cm.course=? ";
        //$query = "SELECT DISTINCT vc.id, vc.* FROM {ivs_videocomment} vc INNER JOIN {ivs} eb ON vc.video_id = eb.id WHERE parent_id IS NULL";
        $parameters = array($courseId);

        //ADD FILTER QUERY
        list($filter_query, $filter_parameters) = $this->createFilterQuery($courseId, $options);

        if (!empty($filter_query)) {
            $query .= " AND " . $filter_query;
            $parameters = array_merge($parameters, $filter_parameters);
        }

        //ADD ACCESS QUERY
        if (!$skipAccessCheck) {
            $grants = \mod_ivs\annotation::get_user_grants($user_id, $courseId);

            list($access_query, $access_parameters) =
                    \mod_ivs\annotation::get_user_grants_query($grants['user'], $grants['course'], $grants['group'],
                            $grants['role']);

            if (!empty($access_query)) {
                $query .= " AND " . $access_query;
                $parameters = array_merge($parameters, $access_parameters);
            }
        }

        /*
        //GROUPING
        if (!$count_total) {
          if ($options['grouping'] == "user") {
            $query .= " GROUP BY vc.user_id ";
          }
          elseif ($options['grouping'] == "video") {
            $query .= " GROUP BY vc.video_id ";
          }
        }
        */

        $group_sort = "";
        if (!$count_total) {
            if ($options['grouping'] == "user") {
                $group_sort = " vc.user_id, ";
            } else if ($options['grouping'] == "video") {
                $group_sort = " vc.video_id, ";
            }
        }

        if (!$count_total) {
            //SORTING
            $sortOrder = $options['sortorder'] == "DESC" ? "DESC" : "ASC";

            if ($options['sortkey'] == 'timestamp') {
                $query .= " Order by $group_sort vc.time_stamp $sortOrder, vc.timecreated $sortOrder ";
            } else {
                $query .= " Order by $group_sort vc.timecreated $sortOrder, vc.time_stamp $sortOrder";
            }
        }

        $offset = 0;
        $perpage = 0;

        //limit
        if (!empty($options['limit']) && !$count_total) {
            $offset = $options['offset'];
            $perpage = $options['limit'];
            //   $query .= " LIMIT $offset, $perpage";
        }

        //END QUERY BUILDING

        if ($count_total) {
            return $DB->get_record_sql($query, $parameters);

        } else {

            $data = $DB->get_records_sql($query, $parameters, $offset, $perpage);

            foreach ($data as $record) {
                $annotations[$record->id] = new \mod_ivs\annotation($record);
            }

            \mod_ivs\annotation::load_replies($annotations);

            return $annotations;
        }

    }

    protected function createFilterQuery($courseID, $options) {

        $query_parts = array();
        $parameters = array();

        //USER
        if (!empty($options['filter_users'])) {
            $uid = $options['filter_users'];
            $query_parts[] = " vc.user_id = ? ";
            $parameters = array($uid);
        }

        //DRAWING_DATA
        //This field is serialized, so we not to do some magic here...
        if ($options['filter_has_drawing'] === 'no') {
            $query_parts[] = " vc.additional_data LIKE '%drawing_data\";O:8:\"stdClass\":2:{s:4:\"json\";s:58:\"{\"objects\":[]%' ";
        } else if ($options['filter_has_drawing'] === 'yes') {
            $query_parts[] =
                    " vc.additional_data NOT LIKE '%drawing_data\";O:8:\"stdClass\":2:{s:4:\"json\";s:58:\"{\"objects\":[]%' ";
        }

        //RATING
        //rating";i:100
        if (!empty($options['filter_rating'])) {

            switch ($options['filter_rating']) {
                case "red":
                    $rating = 34;
                    break;
                case "yellow":
                    $rating = 67;
                    break;
                case "green":
                    $rating = 100;
                    break;
            }
            $query_parts[] = " vc.additional_data LIKE '%rating\";i:$rating;%'";
        }

        //ACCESS
        //This field is serialized, so we not to do some magic here...
        //s:5:"realm";s:6:"member"
        if (!empty($options['filter_access'])) {

            $realm = $options['filter_access'];
            $realm_length = strlen($realm);
            $query_parts[] = " vc.additional_data LIKE '%s:5:\"realm\";s:$realm_length:\"$realm\"%' ";
        }

        //CREATed DATE
        if ($options['filter_timecreated_min'] !== null) {

            $filter_timecreated_min = $options['filter_timecreated_min'];
            $query_parts[] = " vc.timecreated > ? ";
            $parameters[] = $filter_timecreated_min;
        }

        $query = implode(" AND ", $query_parts);

        return array("$query", $parameters);
    }
}
