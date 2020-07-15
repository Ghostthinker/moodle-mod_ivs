<?php

namespace mod_ivs\task;

use mod_ivs\AnnotationService;
use mod_ivs\Report;
use mod_ivs\ReportService;

abstract class cockpit_report_base extends \core\task\scheduled_task {

    protected $reporting;
    protected $mail_body_rotation;
    protected $mail_subject;
    protected $cockpit_report;

    //Send Email Report
    public function execute() {

        global $DB;

        $reports_service = new ReportService();
        $annotation_service = new AnnotationService();
        $reports = $reports_service->getReportsByRotation($this->reporting, time());

        foreach ($reports as $report) {

            /*Enable this to debug Reports
            $annotations = $reports_service->getAnnotationsByReport($report, $annotation_service);
            $count = count($annotations);
            print "Report {$report->getId()}: $count \n";
            */

            //Get Support User as userFrom
            $userFrom = \core_user::get_support_user();

            $userTo = $DB->get_record('user', array('id' => $report->getUserId()), '*', MUST_EXIST);

            try {
                $course = $DB->get_record('course', array('id' => $report->getCourseId()), '*', MUST_EXIST);
            } catch (\dml_exception $e) {
                return;
            }
            $url = new \moodle_url('/mod/ivs/cockpit.php', array('id' => $report->getCourseId()));

            $body = get_string_manager()->get_string('cockpit_report_mail_body_header', 'mod_ivs', [
                            'usertofirstname' => $userTo->firstname,
                            'usertolastname' => $userTo->lastname,
                    ], $userTo->lang) . '<br/><br/>';

            $body .= get_string_manager()->get_string('cockpit_report_mail_body', 'mod_ivs', [
                    'rotation' => get_string_manager()->get_string($this->mail_body_rotation, 'mod_ivs', null, $userTo->lang),
                    'course' => $course->fullname
            ], $userTo->lang);
            $body .= $reports_service->renderMailReport($report, $annotation_service, $userTo) . '<br/>' .
                    get_string_manager()->get_string('cockpit_report_mail_body_footer_separator', 'mod_ivs') . '<br/>' .
                    get_string_manager()->get_string('cockpit_report_mail_body_footer', 'mod_ivs', null, $userTo->lang) . '<br/>';
            $body .= $url;

            $message = new \core\message\message();
            $message->component = 'mod_ivs';
            $message->name = 'ivs_annotation_report';
            $message->userfrom = $userFrom;
            $message->userto = $userTo;
            $message->subject = get_string_manager()->get_string($this->mail_subject, 'mod_ivs', null, $userTo->lang);
            $message->fullmessage = '';
            $message->fullmessageformat = FORMAT_HTML;
            $message->fullmessagehtml = $body;
            $message->smallmessage = get_string_manager()->get_string($this->cockpit_report, 'mod_ivs', null, $userTo->lang);
            $message->notification = '1';
            $message->contexturl = '';
            $message->contexturlname = '';

            message_send($message);

        }
    }
}
