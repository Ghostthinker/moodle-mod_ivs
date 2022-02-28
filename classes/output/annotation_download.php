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
 * Output class for rendering downloading annotations
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

// Standard GPL and phpdocs.
namespace mod_ivs\output;

use html_writer;
use mod_ivs\IvsHelper;
use moodle_url;
use renderable;
use renderer_base;
use templatable;
use stdClass;
use user_picture;

/**
 * Class annotation_download
 */
class annotation_download implements renderable, templatable {

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
     * annotation_download constructor.
     *
     * @param array $allcomments
     * @param stdClass $ivs
     * @param stdClass $cm
     */
    public function __construct($allcomments, $ivs, $cm) {
        $this->all_comments = $allcomments;
        $this->ivs = $ivs;
        $this->module = $cm;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     * @param \renderer_base $output
     *
     * @return \stdClass
     */
    public function export_for_template(renderer_base $output) {

        $data = new stdClass();

        // Render Pager Options in Dropdown.
        $pagerurl = new moodle_url('/mod/ivs/annotation_overview.php?id=' . $this->module->id );

        if (optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT) == 10) {
            $data->pager_options[] = '<option selected value="' . $pagerurl . '&perpage=10">10</option>';
            $data->pager_options[] = '<option value="' . $pagerurl . '&perpage=100">100</option>';
        } else {
            $data->pager_options[] = '<option value="' . $pagerurl . '&perpage=10">10</option>';
            $data->pager_options[] = '<option selected value="' . $pagerurl . '&perpage=100">100</option>';
        }

        $data->elements = get_string("ivs_videocomment_menu_label_elements_per_page", 'ivs');

        $context = \context_module::instance($this->module->id);
        if (has_capability('mod/ivs:download_annotations', $context)) {
            $data->download_options = $output->download_dataformat_selector(get_string('ivs_match_download_summary_label',
                'ivs'),
                'comments_download.php', 'download',
                [
                    'cmid' => $this->module->id,
                ]
            );
        }
        return $data;
    }
}
