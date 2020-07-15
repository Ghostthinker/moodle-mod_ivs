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
 * Internal library of functions for module ivs
 *
 * All the ivs specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_ivs
 * @copyright 2017 Ghostthinker GmbH <info@ghostthinker.de>
 * @license   All Rights Reserved.
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

function ivs_ep5_get_js_and_css_dependencies() {

    global $DB;

    $css_files = [];
    $js_files = [];

    $id = optional_param('id', 0, PARAM_INT); // Course_module ID, or

    $active_license = null;
    if ($id) {
        $cm = get_coursemodule_from_id('ivs', $id, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

        $lc = ivs_get_license_controller();
        $active_license = $lc->getActiveLicense(['course' => $course]);
    }

    if ($active_license) {
        $license_cdn = $lc->getCDNSource($active_license->id);
        $core_url = $lc->getCoreUrl();
        foreach ($license_cdn->js as $js_item) {
            // $PAGE->requires->js(IVS_CORE_URL . $js_item, TRUE);
            $js_files[] = $core_url . $js_item;
        }
        foreach ($license_cdn->css as $css_item) {
            // $PAGE->requires->css($css_item, TRUE);
            $css_files[] = $core_url . $css_item;
        }

        $lng = current_language();
        $langfile = $core_url . ((array) $license_cdn->lang)[$lng];

        $js_files[] = $langfile;
    }

    return ['js' => $js_files, 'css' => $css_files];

}

/**
 * Returns True if user has permission to edit playbackcommands in the activity context
 *
 * @param $activity_context
 * @return mixed
 */
function ivs_may_edit_playbackcommands($activity_context) {
    return has_capability('mod/ivs:edit_playbackcommands', $activity_context);
}

;

/**
 * Returns True if user has permission to edit match questions in the activity context
 *
 * @param $activity_context
 * @return mixed
 */
function ivs_may_edit_match_questions($activity_context) {
    return has_capability('mod/ivs:edit_match_questions', $activity_context);
}

;

