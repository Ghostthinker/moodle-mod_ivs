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
 * Class for the Report Service
 *
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

namespace mod_ivs;

use Exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Class ReportService
 */
class UpdateService {

    private \moodle_database $database;
    private \moodle_transaction $transaction;

    /**
     * UpdateService constructor.
     */
    public function __construct() {
        global $DB;
        $this->database = $DB;
        $this->transaction = $this->database->start_delegated_transaction();
    }

    public function settingInvertUpdate() {

        $ivssettings =
                $this->database->get_records_sql("SELECT * FROM {ivs_settings} WHERE name = 'default_random_question' OR name = 'list_item_buttons_hover_enabled' OR name = 'hide_when_inactive' OR name = 'annotation_realm_default_enabled'");

        $configplugins =
                $this->database->get_records_sql("SELECT * FROM {config_plugins} WHERE (name = 'default_random_question' OR name = 'list_item_buttons_hover_enabled' OR name = 'hide_when_inactive' OR name = 'annotation_realm_default_enabled') AND plugin = 'mod_ivs'");
        try {
            if (!empty($ivssettings)) {
                foreach ($ivssettings as $ivssetting) {
                    $invertedvalue = (integer) !$ivssetting->value;
                    $this->database->execute("UPDATE {ivs_settings} SET value = :value WHERE target_id = :target_id",
                            ['value' => $invertedvalue, 'target_id' => $ivssetting->target_id]);
                }
            }
            if (!empty($configplugins)) {
                foreach ($configplugins as $configplugin) {
                    $invertedvalue = (integer) !$configplugin->value;
                    $this->database->execute("UPDATE {config_plugins} SET value = :value WHERE id = :id AND plugin = 'mod_ivs'",
                            ['value' => $invertedvalue, 'id' => $configplugin->id]);
                }
            }
        } catch (\dml_exception $e) {
            $this->transaction->rollback($e);
        }

        $this->transaction->allow_commit();

    }

}
