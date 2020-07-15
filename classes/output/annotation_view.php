<?php
// Standard GPL and phpdocs
namespace mod_ivs\output;

use html_writer;
use mod_ivs\IvsHelper;
use renderable;
use renderer_base;
use templatable;
use stdClass;
use user_picture;

class annotation_view implements renderable, templatable {

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
    public function __construct(\mod_ivs\annotation $annotation, $ivs, $module) {
        $this->annotation = $annotation;
        $this->ivs = $ivs;
        $this->module = $module;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {

        global $PAGE;
        $data = new stdClass();// $data = (object)$this->annotation->getRecord();

        ;
        $user = IvsHelper::getUser($this->annotation->getUserId());

        if ($user['fullname']) {
            $userpicture = new user_picture($user['user']);
            $userpicture->size = 1; // Size f1.

            $data->comment_author_link = $user['fullname'];
            $data->user_picture = $userpicture->get_url($PAGE)->out(false);
        } else {
            $data->comment_author_link = 'Anonymous';
            $data->user_picture = (string) new \moodle_url('/user/pix.php');
        }

        $data->comment_body = $this->annotation->getBody();
        $data->id = $this->annotation->getId();

        $data->comment_created = userdate($this->annotation->getTimecreated());
        $data->comment_timestamp = $this->annotation->getTimestamp() / 1000;
        $data->timecode = $this->annotation->getTimecode(true);

        $data->svg = "";

        $additional_data = $this->annotation->getAdditionalData();

        if (isset($additional_data['drawing_data'])) {
            $data->svg = $additional_data['drawing_data']->svg;
        }

        $data->player_link =
                new \moodle_url('/mod/ivs/view.php', array('id' => $this->module->id, 'cid' => $this->annotation->getId()));

        $data->preview_image = $this->annotation->getPreviewURL();

        if ($data->preview_image) {
            $data->preview_img_available = true;
        } else {
            $data->preview_img_available = false;
        }

        //$data->replies = print_r($this->annotation->getReplies(), TRUE);
        $data->replies = '';
        $replies = $this->annotation->getReplies();

        $renderer = $PAGE->get_renderer('ivs');

        //Render Replies
        /** @var \mod_ivs\annotation $comment */
        foreach ($replies as $reply) {
            $renderable = new \mod_ivs\output\annotation_reply_view($reply);
            $data->replies .= $renderer->render($renderable);
        }

        $video_host = \mod_ivs\upload\VideoHostFactory::create($this->module, $this->ivs);

        $data->video_url = $video_host->getVideo();
        // $data->user_picture = ""
        return $data;
    }
}
