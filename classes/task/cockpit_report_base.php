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
 * The base class for creating the cockpit reports
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\task;

use mod_ivs\AnnotationService;
use mod_ivs\Report;
use mod_ivs\ReportService;

/**
 * Class cockpit_report_base
 */
abstract class cockpit_report_base extends \core\task\scheduled_task {

    /**
     * @var mixed
     */
    protected $reporting;

    /**
     * @var mixed
     */
    protected $mailbodyrotation;

    /**
     * @var mixed
     */
    protected $mailsubject;

    /**
     * @var mixed
     */
    protected $cockpitreport;

    /**
     * // Send Email Report.
     * @throws \Exception
     */
    public function execute() {

        global $DB;

        $reportsservice = new ReportService();
        $annotationservice = new AnnotationService();
        $reports = $reportsservice->get_reports_by_rotation($this->reporting, time());

        foreach ($reports as $report) {

            // Get Support User as userFrom.
            $userfrom = \core_user::get_support_user();

            $userto = $DB->get_record('user', array('id' => $report->get_userid()), '*', MUST_EXIST);

            try {
                $course = $DB->get_record('course', array('id' => $report->get_courseid()), '*', MUST_EXIST);
            } catch (\dml_exception $e) {
                return;
            }
            $url = new \moodle_url('/mod/ivs/cockpit.php', array('id' => $report->get_courseid()));

            $body = get_string_manager()->get_string('cockpit_report_mail_body_header', 'mod_ivs', [
                            'fullname' => fullname($userto)
                    ], $userto->lang) . '<br/><br/>';

            $body .= get_string_manager()->get_string('cockpit_report_mail_body', 'mod_ivs', [
                    'rotation' => get_string_manager()->get_string($this->mailbodyrotation, 'mod_ivs', null, $userto->lang),
                    'course' => $course->fullname
            ], $userto->lang);
            $body .= $reportsservice->render_mail_report($report, $annotationservice, $userto) . '<br/>' .
                    get_string_manager()->get_string('cockpit_report_mail_body_footer_separator', 'mod_ivs') . '<br/>' .
                    get_string_manager()->get_string('cockpit_report_mail_body_footer', 'mod_ivs', null, $userto->lang) . '<br/>';
            $body .= $url;

            $message = new \core\message\message();
            $message->component = 'mod_ivs';
            $message->name = 'ivs_annotation_report';
            $message->userfrom = $userfrom;
            $message->userto = $userto;
            $message->subject = get_string_manager()->get_string($this->mailsubject, 'mod_ivs', null, $userto->lang);
            $message->fullmessage = '';
            $message->fullmessageformat = FORMAT_HTML;
            $message->fullmessagehtml = $body;
            $message->smallmessage = get_string_manager()->get_string($this->cockpitreport, 'mod_ivs', null, $userto->lang);
            $message->notification = '1';
            $message->contexturl = '';
            $message->contexturlname = '';

            message_send($message);

        }
    }
}
