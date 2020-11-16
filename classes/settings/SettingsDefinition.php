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
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\settings;

class SettingsDefinition {

    const SETTING_PLAYBACKCOMMANDS_ENABLED = 'playbackcommands_enabled';
    const SETTING_MATCH_QUESTION_ENABLED = 'match_question_enabled';
    const SETTING_BUTTONS_HOVER_ENABLED = 'list_item_buttons_hover_enabled';
    const SETTING_ANNOTATION_READMORE_ENABLED = 'annotations_readmore_enabled';
    const SETTING_ANNOTATION_REALM_DEFAULT_ENABLED = 'annotation_realm_default_enabled';
    const SETTING_MATCH_SINGLE_CHOICE_QUESTION_RANDOM_DEFAULT = 'default_random_question';
    const SETTING_PLAYER_AUTOHIDE_CONTROLBAR = 'hide_when_inactive';
    const SETTING_PLAYER_ACCESSIBILITY = 'accessibility_enabled';
    const SETTING_PLAYER_PLAYBACKRATE = 'playbackrate_enabled';

    public $name;
    public $title;
    public $description;
    public $type;
    public $default;
    public $lockedcourse;
    public $lockedsite;

    /**
     * SettingsDefinition constructor.
     *
     * @param $name
     * @param $title
     * @param $description
     * @param $type
     * @param $default
     */
    public function __construct($name, $title, $description, $type, $default, $lockedcourse, $locked_site) {
        $this->name = $name;
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->default = $default;
        $this->lockedcourse = $lockedcourse;
        $this->lockedsite = $locked_site;
    }

}
