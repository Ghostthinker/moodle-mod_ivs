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
 * Class to add a video to a instance
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs;

defined('MOODLE_INTERNAL') || die();

/**
 * Class video
 */
class video {

    /**
     * @var \stdClass
     */
    private $instance;

    /**
     * @var context The context of the course module
     */
    private $context;

    /**
     * @var \stdClass
     */
    private $course;

    /**
     * @var \stdClass
     */
    private $coursemodule;

    /**
     * video constructor.
     *
     * @param \stdClass $coursemodulecontext
     * @param \stdClass $coursemodule
     * @param \stdClass $course
     */
    public function __construct($coursemodulecontext, $coursemodule, $course) {
        global $PAGE;
        $this->context = $coursemodulecontext;
        $this->coursemodule = $coursemodule;
        $this->course = $course;
    }

    /**
     * Add a video instance
     * @param \mod_ivs\stdClass $formdata
     *
     * @return mixed
     */
    public function add_instance(stdClass $formdata) {
        global $DB;
        // Add the database record.
        $add = new stdClass();
        $add->name = $formdata->name;
        $add->timemodified = time();
        $add->timecreated = time();
        $add->course = $formdata->course;
        $add->courseid = $formdata->course;
        $add->intro = $formdata->intro;
        $add->introformat = $formdata->introformat;
        $add->width = $formdata->width;
        $add->height = $formdata->height;
        $add->responsive = $formdata->responsive;

        $returnid = $DB->insert_record('videofile', $add);

        $this->instance = $DB->get_record('videofile',
                array('id' => $returnid),
                '*',
                MUST_EXIST);
        $this->save_files($formdata);
        // Cache the course record.
        $this->course = $DB->get_record('course',
                array('id' => $formdata->course),
                '*',
                MUST_EXIST);
        return $returnid;
    }



}
