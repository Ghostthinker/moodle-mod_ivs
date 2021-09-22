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
 * Service class for handling annotations
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs;

use Exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Class AnnotationService
 *
 */
class AnnotationService {

    /**
     * Get all annotations by course with filters
     *
     * @param int $courseid
     * @param bool $skipaccesscheck
     * @param array $options
     * @param bool $counttotal
     * @param null $userid
     * @return array|mixed
     * @throws \Exception
     * @throws \dml_missing_record_exception
     * @throws \dml_multiple_records_exception
     */
    public function get_annotations_by_course($courseid, $skipaccesscheck = false, $options = array(), $counttotal = false,
            $userid = null) {
        if (empty($courseid)) {
            throw new Exception("no course id set");
        }

        // Set default options.

        $defaultoptions = array(
                'sortkey' => "timecreated", // Time_stamp.
                'sortorder' => 'DESC',
                'grouping' => 'none', // ... grouping option are 'user', 'rating', 'video'.
                'offset' => 0,
                'limit' => null,
                'filter_users' => null,
                'filter_has_drawing' => null,
                'filter_timecreated_min' => null,
                'groups' => null,
                'rating' => null
        );

        $options = array_replace_recursive($defaultoptions, $options);

        global $DB;

        if ($userid == null) {
            global $USER;
            $userid = $USER->id;
        };

        $annotations = array();

        if ($counttotal) {
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
        $parameters = array($courseid);

        // ADD FILTER QUERY.
        list($filterquery, $filterparameters) = $this->create_filter_query($courseid, $options);

        if (!empty($filterquery)) {
            $query .= " AND " . $filterquery;
            $parameters = array_merge($parameters, $filterparameters);
        }

        // ADD ACCESS QUERY.
        if (!$skipaccesscheck) {
            $grants = \mod_ivs\annotation::get_user_grants($userid, $courseid);

            list($accessquery, $accessparameters) =
                    \mod_ivs\annotation::get_user_grants_query($grants['user'], $grants['course'], $grants['group'],
                            $grants['role']);

            if (!empty($accessquery)) {
                $query .= " AND " . $accessquery;
                $parameters = array_merge($parameters, $accessparameters);
            }
        }

        $groupsort = "";
        if (!$counttotal) {
            if ($options['grouping'] == "user") {
                $groupsort = " vc.user_id, ";
            } else if ($options['grouping'] == "video") {
                $groupsort = " vc.video_id, ";
            }
        }

        if (!$counttotal) {
            // SORTING.
            $sortorder = $options['sortorder'] == "DESC" ? "DESC" : "ASC";

            if ($options['sortkey'] == 'timestamp') {
                $query .= " Order by $groupsort vc.time_stamp $sortorder, vc.timecreated $sortorder ";
            } else {
                $query .= " Order by $groupsort vc.timecreated $sortorder, vc.time_stamp $sortorder";
            }
        }

        $offset = 0;
        $perpage = 0;

        // Limit.
        if (!empty($options['limit']) && !$counttotal) {
            $offset = $options['offset'];
            $perpage = $options['limit'];

        }

        // END QUERY BUILDING.

        if ($counttotal) {
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

    /**
     * Create query to filter annotations
     * @param int $courseid
     * @param array $options
     *
     * @return array
     */
    protected function create_filter_query($courseid, $options) {

        $queryparts = array();
        $parameters = array();

        // USER.
        if (!empty($options['filter_users'])) {
            $uid = $options['filter_users'];
            $queryparts[] = " vc.user_id = ? ";
            $parameters = array($uid);
        }

        // DRAWING_DATA.
        // This field is serialized, so we not to do some magic here...
        if ($options['filter_has_drawing'] === 'no') {
            $queryparts[] = " vc.additional_data LIKE '%drawing_data\";O:8:\"stdClass\":2:{s:4:\"json\";s:58:\"{\"objects\":[]%' ";
        } else if ($options['filter_has_drawing'] === 'yes') {
            $queryparts[] =
                    " vc.additional_data NOT LIKE '%drawing_data\";O:8:\"stdClass\":2:{s:4:\"json\";s:58:\"{\"objects\":[]%' ";
        }

        // RATING.
        // Rating";i:100.
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
            $queryparts[] = " vc.additional_data LIKE '%rating\";i:$rating;%'";
        }

        // ACCESS.
        // This field is serialized, so we not to do some magic here...
        // S:5:"realm";s:6:"member".
        if (!empty($options['filter_access'])) {

            $realm = $options['filter_access'];
            $realmlength = strlen($realm);
            $queryparts[] = " vc.additional_data LIKE '%s:5:\"realm\";s:$realmlength:\"$realm\"%' ";
        }

        // CREATed DATE.
        if ($options['filter_timecreated_min'] !== null) {

            $filtertimecreatedmin = $options['filter_timecreated_min'];
            $queryparts[] = " vc.timecreated > ? ";
            $parameters[] = $filtertimecreatedmin;
        }

        $query = implode(" AND ", $queryparts);

        return array("$query", $parameters);
    }
}
