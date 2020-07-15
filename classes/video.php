<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 17.01.2017
 * Time: 15:28
 * based on https://github.com/lemonad/moodle-mod_videofile/blob/master/locallib.php
 */

namespace mod_ivs;

defined('MOODLE_INTERNAL') || die();

class video {

    private $instance;

    /**
     * @var context The context of the course module
     */
    private $context;

    private $course;

    private $coursemodule;

    public function __construct($coursemodulecontext, $coursemodule, $course) {
        global $PAGE;
        $this->context = $coursemodulecontext;
        $this->coursemodule = $coursemodule;
        $this->course = $course;
    }

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
