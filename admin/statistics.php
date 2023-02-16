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
 * This file is used to render the admin statistics page
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */


require(__DIR__ . '/../../../config.php');

global $DB;

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/outputcomponents.php');

require_login(null, false);
admin_externalpage_setup('statistics');

$heading = get_string('modulecategory', 'ivs') . ' ' . get_string('ivs_statistics', 'ivs');

$PAGE->set_title($heading);
$PAGE->set_heading($heading);
$PAGE->requires->css(new moodle_url($CFG->httpswwwroot . '/mod/ivs/templates/statistics.css'));
echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('ivs');

$renderable = new \mod_ivs\output\statistics_view();
echo $renderer->render($renderable);


echo $renderer->footer();

