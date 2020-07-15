<?php
/**
 * Created by PhpStorm.
 * User: Ghostthinker
 * Date: 21.11.2018
 * Time: 11:12
 */

namespace mod_ivs\settings;

class Setting {

    public $id;
    public $target_id;
    public $target_type;
    public $name;
    public $value;
    public $locked = false;

    /**
     * Setting constructor.
     *
     * @param $name
     * @param $value
     * @param bool $locked
     */
    public function __construct($name = null, $value = null, $locked = null) {
        $this->name = $name;
        $this->value = $value;
        $this->locked = $locked;
    }

    public static function fromDBRecord($record) {
        $setting = new Setting();

        foreach ((array) $record as $key => $field) {
            $setting->{$key} = $field;
        }
        return $setting;
    }

}
