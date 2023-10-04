<?php

namespace mod_ivs\ivs_match;

/**
* Class MatchTake
*/
class MatchTimingType {

    public $id;
    public $label;
    public $title;
    public $score;
    public $style;
    public $description;
    public $shortcut;
    public $position;
    public $duration;
    public $weight;
    public $cooldown;

    public function __construct($values = [])
    {
        $this->title = $values['title'] ?? NULL;
        $this->id = $values['id'] ?? NULL;
        $this->label = $values['btn']['label'] ?? NULL;
        $this->score = $values['btn']['score'] ?? NULL;
        $this->style = $values['btn']['style'] ?? NULL;
        $this->description = $values['btn']['description'] ?? NULL;
        $this->shortcut = $values['btn']['shortcut'] ?? NULL;
        $this->position = $values['btn']['position'] ?? NULL;
        $this->duration = $values['duration'] ?? NULL;
        $this->weight = $values['weight'] ?? NULL;
        $this->cooldown = $values['btn']['cooldown'] ?? NULL;
    }

    public function to_player_json()
    {
        return [
            'btn' => [
                'label' => $this->label,
                'score' => $this->score,
                'style' => $this->style,
                'description' => $this->description,
                'shortcut' => $this->shortcut,
                'position' => $this->position,
                'cooldown' => $this->cooldown,
            ],
            'title' => $this->title,
            'id' => $this->id,
            'duration' => $this->duration,
            'weight' => $this->weight
        ];
    }
}
