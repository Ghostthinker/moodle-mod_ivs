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
 * File for all events
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

defined('MOODLE_INTERNAL') || die();

$observers = array(

    // Annotation created.
        array(
                'eventname' => '\mod_ivs\event\annotation_created',
                'callback' => 'ivs_annotation_event_created',
                'includefile' => '/mod/ivs/lib.php'
        ),

    // Annotation updated.
        array(
                'eventname' => '\mod_ivs\event\annotation_updated',
                'callback' => 'annotation_event_updated',
                'includefile' => '/mod/ivs/lib.php'
        ),

    // Annotation deleted.
        array(
                'eventname' => '\mod_ivs\event\annotation_deleted',
                'callback' => 'ivs_annotation_event_deleted',
                'includefile' => '/mod/ivs/lib.php'
        )
);
