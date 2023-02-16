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
 * File for all tasks
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

defined('MOODLE_INTERNAL') || die();

$tasks = array(
        array(
                'classname' => 'mod_ivs\task\cockpit_report_daily',
                'blocking' => 0,        // Do not change to 1. Other tasks will be blocked.
                'minute' => '0',
                'hour' => '0',
                'day' => '*'            // Every Day.
        ),

        array(
                'classname' => 'mod_ivs\task\cockpit_report_weekly',
                'blocking' => 0,        // Do not change to 1. Other tasks will be blocked.
                'minute' => '0',
                'hour' => '0',
                'dayofweek' => '0'      // Every Sunday  (0 ... 6).
        ),

        array(
                'classname' => 'mod_ivs\task\cockpit_report_monthly',
                'blocking' => 0,        // Do not change to 1. Other tasks will be blocked.
                'minute' => '0',
                'hour' => '0',
                'month' => '*'          // Every month.
        ),
        array(
                'classname' => 'mod_ivs\task\ivs_plugin_usage',
                'blocking' => 0,        // Do not change to 1. Other tasks will be blocked.
                'minute' => '0',
                'hour' => '0',
                'day' => '*',         // Every day.
        ),
          array(
            'classname' => 'mod_ivs\task\ivs_plugin_statistic',
            'blocking' => 0,        // Do not change to 1. Other tasks will be blocked.
            'minute' => '0',
            'hour' => '0',
            'day' => '*',         // Every day.
          )
);
