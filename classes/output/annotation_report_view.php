<?php
// Standard GPL and phpdocs
namespace mod_ivs\output;

use mod_ivs\IvsHelper;
use renderable;
use renderer_base;
use templatable;
use stdClass;

class annotation_report_view implements renderable, templatable {

    /** @var \mod_ivs\annotation */
    var $annotation = null;
    var $ivs = null;
    var $module;

    /**
     * annotation_view constructor.
     *
     * @param \mod_ivs\annotation $annotation
     * @param null $ivs
     */
    public function __construct(\mod_ivs\annotation $annotation, $ivs, $module, $userTo) {
        $this->annotation = $annotation;
        $this->ivs = $ivs;
        $this->module = $module;
        $this->userTo = $userTo;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {

        $data = new stdClass();

        $user = IvsHelper::getUser($this->annotation->getUserId());
        $userTo = $this->userTo;

        $data->comment_body = $this->annotation->getBody();
        $data->comment_author_link = $user['fullname'];
        $data->comment_created = userdate($this->annotation->getTimecreated());
        $data->comment_timestamp = $this->annotation->getTimestamp() / 1000;
        $data->timecode = $this->annotation->getTimecode(true);
        $data->cockpit_report_mail_annotation_header_part_1 =
                get_string_manager()->get_string('cockpit_report_mail_annotation_header_part_1', 'ivs', null, $userTo->lang);
        $data->cockpit_report_mail_annotation_header_part_2 =
                get_string_manager()->get_string('cockpit_report_mail_annotation_header_part_2', 'ivs', null, $userTo->lang);
        $data->cockpit_report_mail_annotation_header_part_3 =
                get_string_manager()->get_string('cockpit_report_mail_annotation_header_part_3', 'ivs', null, $userTo->lang);

        $data->player_link =
                new \moodle_url('/mod/ivs/view.php', array('id' => $this->module->id, 'cid' => $this->annotation->getId()));

        return $data;
    }
}
