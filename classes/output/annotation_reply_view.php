<?php
// Standard GPL and phpdocs
namespace mod_ivs\output;

use mod_ivs\IvsHelper;
use renderable;
use renderer_base;
use templatable;
use stdClass;

class annotation_reply_view implements renderable, templatable {

    /** @var \mod_ivs\annotation */
    var $annotation = null;
    var $ivs = null;
    var $module;

    /**
     * annotation_reply_view constructor.
     *
     * @param \mod_ivs\annotation $annotation
     * @param null $ivs
     */
    public function __construct(\mod_ivs\annotation $annotation) {
        $this->annotation = $annotation;
        # $this->ivs = $ivs;
        # $this->module = $module;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

        $user = IvsHelper::getUser($this->annotation->getUserId());

        $data->comment_body = $this->annotation->getBody();
        $data->id = $this->annotation->getId();
        $data->user_picture = $user['picture'];
        $data->comment_author_link = $user['fullname'];
        $data->comment_created = userdate($this->annotation->getTimecreated());

        return $data;
    }
}
