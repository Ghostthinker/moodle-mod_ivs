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
 * Define here all player settings
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\settings;

/**
 * Class SettingsDefinition
 */
class SettingsDefinition {

    /**
     * @var string
     */
    const SETTING_ANNOTATIONS_ENABLED = 'annotations_enabled';

    /**
     * @var string
     */
    const SETTING_PLAYBACKCOMMANDS_ENABLED = 'playbackcommands_enabled';

    /**
     * @var string
     */
    const SETTING_MATCH_QUESTION_ENABLED = 'match_question_enabled';

    /**
     * @var string
     */
    const SETTING_BUTTONS_HOVER_ENABLED = 'list_item_buttons_hover_enabled';

    /**
     * @var string
     */
    const SETTING_ANNOTATION_READMORE_ENABLED = 'annotations_readmore_enabled';

    /**
     * @var string
     */
    const SETTING_ANNOTATION_REALM_DEFAULT_ENABLED = 'annotation_realm_default_enabled';

    /**
     * @var string
     */
    const SETTING_MATCH_SINGLE_CHOICE_QUESTION_RANDOM_DEFAULT = 'default_random_question';

    /**
     * @var string
     */
    const SETTING_PLAYER_AUTOHIDE_CONTROLBAR = 'hide_when_inactive';

    /**
     * @var string
     */
    const SETTING_PLAYER_ACCESSIBILITY = 'accessibility_enabled';

    /**
     * @var string
     */

    const SETTING_PLAYER_EXAM_ENABLED = 'exam_mode_enabled';

    /**
     * @var string
     */

    const SETTING_PLAYER_CONTROLS_ENABLED = 'player_controls_enabled';

    /**
     * @var string
     */

    const SETTING_PLAYER_SHOW_VIDEOTEST_FEEDBACK = 'show_videotest_feedback';
    /**
     * @var string
     */

    const SETTING_PLAYER_SHOW_VIDEOTEST_SOLUTION = 'show_videotest_solution';

    /**
     * @var string
     */

    const SETTING_PLAYER_VIDEOTEST_GRADE_TO_PASS = 'videotest_grade_to_pass';

    /**
     * @var string
     */

    const SETTING_PLAYER_VIDEOTEST_ATTEMPTS = 'videotest_attempts';

    /**
     * @var string
     */

    const SETTING_PLAYER_VIDEOTEST_GRADE_METHOD = 'videotest_grade_method_options';

    /**
     * @var string
     */

    const SETTING_PLAYER_LOCK_REALM = 'lock_realm_enabled';

    /**
     * @var string
     */
    const SETTING_PLAYER_PLAYBACKRATE = 'playbackrate_enabled';

    /**
     * @var string
     */
    const SETTING_PLAYER_ANNOTATION_AUDIO = 'annotation_audio_enabled';

    /**
     * @var string
     */
    const SETTING_PLAYER_ANNOTATION_AUDIO_MAX_DURATION = 'annotation_audio_max_duration';

    /**
     * @var string
     */
    const SETTING_PLAYER_ANNOTATION_COMMENT_PREVIEW_OFFSET = 'annotation_comment_preview_offset';

    /**
     * @var string
     */
    const SETTING_USER_NOTIFICATION_SETTINGS = 'user_notification_settings';

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $default;

    /**
     * @var bool
     */
    public $lockedcourse;

    /**
     * @var bool
     */
    public $lockedsite;

    /**
     * @var array|null
     */
    public $options;

    /**
     * SettingsDefinition constructor.
     *
     * @param string $name
     * @param string $title
     * @param string $description
     * @param string $type
     * @param string $default
     * @param bool $lockedcourse
     * @param bool $lockedsite
     * @param null|array $options
     */
    public function __construct($name, $title, $description, $type, $default, $lockedcourse, $lockedsite, $options = null) {
        $this->name = $name;
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->default = $default;
        $this->lockedcourse = $lockedcourse;
        $this->lockedsite = $lockedsite;
        $this->options = $options;
    }

}
