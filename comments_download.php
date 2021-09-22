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
 * Download all comments from the Interactive video suite activity.
 *
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

require_once('../../config.php');
require_once($CFG->libdir . '/dataformatlib.php');

use mod_ivs\MoodleMatchController;
use mod_ivs\CourseService;

define('DEFAULT_PAGE_SIZE', 10);
define('SHOW_ALL_PAGE_SIZE', 5000);

$dataformat = optional_param('download', '', PARAM_ALPHA);
$cmid = required_param('cmid', PARAM_INT);

$controller = new MoodleMatchController();

$cm = get_coursemodule_from_id('ivs', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$ivs = $DB->get_record('ivs', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
$context = \context_module::instance($cmid);
if (!has_capability('mod/ivs:download_annotations', $context)) {
    return;
}

$columns = array(
    'col_1' => get_string("ivs_videocomment_header_id_label", 'ivs'),
    'col_2' => get_string("ivs_videocomment_header_title_label", 'ivs'),
    'col_3' => get_string("ivs_videocomment_header_author_name_label", 'ivs'),
    'col_4' => get_string("ivs_videocomment_header_timecode_label", 'ivs'),
    'col_5' => get_string("ivs_videocomment_header_textcontent_label", 'ivs'),
    'col_6' => get_string("ivs_videocomment_header_stoplightrating_label", 'ivs'),
    'col_7' => get_string("ivs_videocomment_header_creationdate_label", 'ivs'),
    'col_8' => get_string("ivs_videocomment_header_question_id_label", 'ivs'),
    'col_9' => get_string("ivs_videocomment_header_link_to_videotimecode_label", 'ivs'),
);

$comments = \mod_ivs\annotation::retrieve_from_db_by_video($ivs->id, null);

foreach ($comments as $comment) {
    if (count($comment->get_replies()) > 0) {
        foreach ($comment->get_replies() as $replycomment) {
            $replycomment->set_timestamp($comment->get_timestamp());
            $comments[] = $replycomment;
        }
    }
}

foreach ($comments as $comment) {

    $username = $comment->get_player_user_data()['name'];
    $additionaldata = $comment->get_additionaldata();

    $data[] = array(
        'col_1' => $comment->get_id(),
        'col_2' => $ivs->name,
        'col_3' => $username,
        'col_4' => $comment->get_timecode(true),
        'col_5' => $comment->get_body(),
        'col_6' => $comment->get_rating_text(),
        'col_7' => date('l, d F Y, G:i', $comment->get_timecreated()),
        'col_8' => $comment->get_parentid(),
        'col_9' => $comment->get_annotation_player_url()->out(false)
    );
}

$filename = clean_filename($course->shortname .'-'.$ivs->name.'-'. get_string('ivs_videocomment_export_filename', 'ivs'));

if (class_exists ( '\core\dataformat' )) {
    \core\dataformat::download_data($filename, $dataformat, $columns, $data);
} else {
    download_as_dataformat($filename, $dataformat, $columns, $data);
}
