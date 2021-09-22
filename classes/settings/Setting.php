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
 * Stores all the ivs settings
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs\settings;

/**
 * Class Setting
 */
class Setting {

    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $targetid;

    /**
     * @var mixed
     */
    public $targettype;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var mixed
     */
    public $value;

    /**
     * @var bool|null
     */
    public $locked = false;

    /**
     * Setting constructor.
     *
     * @param string $name
     * @param mixed $value
     * @param bool $locked
     */
    public function __construct($name = null, $value = null, $locked = null) {
        $this->name = $name;
        $this->value = $value;
        $this->locked = $locked;
    }

    /**
     * Get all settings from the db
     * @param array $record
     *
     * @return \mod_ivs\settings\Setting
     */
    public static function from_db_record($record) {
        $setting = new Setting();

        foreach ((array) $record as $key => $field) {
            $setting->{$key} = $field;
        }
        return $setting;
    }

}
