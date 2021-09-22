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
 * Locallib file
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

defined('MOODLE_INTERNAL') || die();

/*
 * Does something really useful with the passed things
 *
 * @param array $things
 * @return object
 *function ivs_do_something_useful(array $things) {
 *    return new stdClass();
 *}
 */

/**
 * Load js and css dependencies
 * @return array[]
 * @throws \Exception
 */
function ivs_ep5_get_js_and_css_dependencies() {

    global $DB;

    $cssfiles = [];
    $jsfiles = [];

    $id = optional_param('id', 0, PARAM_INT); // Course_module ID, or.

    $activelicense = null;
    if ($id) {
        $cm = get_coursemodule_from_id('ivs', $id, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

        $lc = ivs_get_license_controller();
        $activelicense = $lc->get_active_license(['course' => $course]);
    }

    if ($activelicense) {
        $licensecdn = $lc->get_cdn_source($activelicense->id);
        $coreurl = $lc->get_core_url();
        foreach ($licensecdn->js as $jsitem) {
            $jsfiles[] = $coreurl . $jsitem;
        }
        foreach ($licensecdn->css as $cssitem) {
            $cssfiles[] = $coreurl . $cssitem;
        }

        $currentlanguage  = substr(current_language(), 0, 2);
        $langfiles = (array) $licensecdn->lang;
        $lng = !empty($langfiles[$currentlanguage ]) ? $currentlanguage : 'en';

        $langfile = $coreurl . $langfiles[$lng];

        $jsfiles[] = $langfile;
    }

    return ['js' => $jsfiles, 'css' => $cssfiles];

}

/**
 * Returns True if user has permission to edit playbackcommands in the activity context
 *
 * @param mixed $activitycontext
 * @return mixed
 */
function ivs_may_edit_playbackcommands($activitycontext) {
    return has_capability('mod/ivs:edit_playbackcommands', $activitycontext);
}


/**
 * Returns True if user has permission to edit match questions in the activity context
 *
 * @param mixed $activitycontext
 * @return mixed
 */
function ivs_may_edit_match_questions($activitycontext) {
    return has_capability('mod/ivs:edit_match_questions', $activitycontext);
}