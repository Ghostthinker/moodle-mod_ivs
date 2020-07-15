<?php
/**
 * Created by PhpStorm.
 * User: Ghostthinker
 * Date: 19.11.2018
 * Time: 15:09
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

    public $name;
    public $title;
    public $description;
    public $type;
    public $default;
    public $locked_course;
    public $locked_site;

    /**
     * SettingsDefinition constructor.
     *
     * @param $name
     * @param $title
     * @param $description
     * @param $type
     * @param $default
     */
    public function __construct($name, $title, $description, $type, $default, $locked_course, $locked_site) {
        $this->name = $name;
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->default = $default;
        $this->locked_course = $locked_course;
        $this->locked_site = $locked_site;
    }

}
