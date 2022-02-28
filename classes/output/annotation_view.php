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
 * Output class for rendering annotations
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

// Standard GPL and phpdocs.
namespace mod_ivs\output;

use html_writer;
use mod_ivs\IvsHelper;
use renderable;
use renderer_base;
use templatable;
use stdClass;
use user_picture;

/**
 * Class annotation_view
 *
 */
class annotation_view implements renderable, templatable {

    /**
     * @var \mod_ivs\annotation|null
     */
    public $annotation = null;

    /**
     * @var null|stdClass
     */
    public $ivs = null;

    /**
     * @var stdClass
     */
    public $module;

    /**
     * annotation_view constructor.
     *
     * @param \mod_ivs\annotation $annotation
     * @param stdClass $ivs
     * @param stdClass $module
     */
    public function __construct(\mod_ivs\annotation $annotation, $ivs, $module) {
        $this->annotation = $annotation;
        $this->ivs = $ivs;
        $this->module = $module;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     * @param \renderer_base $output
     *
     * @return \stdClass
     */
    public function export_for_template(renderer_base $output) {

        global $PAGE;
        $data = new stdClass();

        $user = IvsHelper::get_user($this->annotation->get_userid());

        if (isset($user['pictureObject'])) {
            $userpictureobject = $user['pictureObject'];
            $userpictureobject->size = 1; // Size f1.
            $userpicture = $userpictureobject->get_url($PAGE)->out(false);
        } else {
            $userpicture = $user['picture'];
        }

        $data->comment_author_link = $user['fullname'];
        $data->user_picture = $userpicture;

        $data->comment_body = $this->annotation->get_rendered_body();
        $data->id = $this->annotation->get_id();

        $data->comment_created = userdate($this->annotation->get_timecreated());
        $data->comment_timestamp = $this->annotation->get_timestamp() / 1000;
        $data->timecode = $this->annotation->get_timecode(true);

        $data->svg = "";

        $additionaldata = $this->annotation->get_additionaldata();

        if (isset($additionaldata['drawing_data'])) {
            $data->svg = $additionaldata['drawing_data']->svg;
        }

        $data->player_link =
                new \moodle_url('/mod/ivs/view.php', array('id' => $this->module->id, 'cid' => $this->annotation->get_id()));

        $data->preview_image = $this->annotation->get_preview_url();

        if ($data->preview_image) {
            $data->preview_img_available = true;
        } else {
            $data->preview_img_available = false;
        }

        $data->replies = '';
        $replies = $this->annotation->get_replies();

        $renderer = $PAGE->get_renderer('ivs');

        // Render Replies.
        foreach ($replies as $reply) {
            $renderable = new \mod_ivs\output\annotation_reply_view($reply);
            $data->replies .= $renderer->render($renderable);
        }

        $videohost = \mod_ivs\upload\VideoHostFactory::create($this->module, $this->ivs);

        $data->video_url = $videohost->get_video();

        // Render audio player.
        $renderable = new \mod_ivs\output\annotation_audio_player_view($this->annotation);
        $data->render_audio_player = $renderer->render($renderable);

        return $data;
    }
}
