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
 * This class is used to restore all settings
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

// This activity has not particular settings but the inherited from the generic.
// backup_activity_task so here there isn't any class definition, like the ones.
// existing in /backup/moodle2/backup_settingslib.php (activities section).

defined('MOODLE_INTERNAL') || die;

/**
 * Class restore_match_answer_setting
 */
class restore_match_answer_setting extends restore_activity_generic_setting {

    /**
     * restore_match_answer_setting constructor.
     *
     * Defaults to on: a restore that never renders the checkbox keeps the
     * constructed value, and a falsy one drops every take and answer. '1',
     * not true, because the setting is IS_TEXT.
     *
     * @param string $name Name of the setting
     * @param mixed $value Value of the setting
     * @param bool $visibility Is the setting visible in the UI, eg {@see base_setting::VISIBLE}
     * @param int $status Status of the setting with regards to the locking, eg {@see base_setting::NOT_LOCKED}
     */
    public function __construct($name, $value = '1', $visibility = self::VISIBLE, $status = self::NOT_LOCKED) {
        parent::__construct($name, self::IS_TEXT, $value, $visibility, $status);
        $this->make_ui(self::UI_HTML_CHECKBOX, get_string("ivs_restore_include_match_answers", 'ivs'), null);
    }
}

/**
 * Class restore_videocomments_setting
 */
class restore_videocomments_setting extends restore_activity_generic_setting {

    /**
     * restore_videocomments_setting constructor.
     *
     * Defaults to 'all', not null: a restore that never reaches the dropdown
     * keeps the constructed value, and an unrecognised one drops every
     * comment.
     *
     * @param string $name Name of the setting
     * @param mixed $value Value of the setting
     * @param bool $visibility Is the setting visible in the UI, eg {@see base_setting::VISIBLE}
     * @param int $status Status of the setting with regards to the locking, eg {@see base_setting::NOT_LOCKED}
     */
    public function __construct($name, $value = 'all', $visibility = self::VISIBLE, $status = self::NOT_LOCKED) {
        parent::__construct($name, self::IS_TEXT, $value, $visibility, $status);
        $this->make_ui(self::UI_HTML_DROPDOWN, get_string("ivs_restore_include_videocomments", 'ivs'), null, ['options' => [
                'all' => get_string("ivs_restore_include_videocomments_all", 'ivs'),
                'none' => get_string("ivs_restore_include_videocomments_none", 'ivs'),
                'students only' => get_string("ivs_restore_include_videocomments_student", 'ivs'),
                'teacher only' => get_string("ivs_restore_include_videocomments_teacher", 'ivs')]]);
    }
}

/**
 * Class restore_videocomment_authors_setting
 */
class restore_videocomment_authors_setting extends restore_activity_generic_setting {

    /**
     * restore_videocomment_authors_setting constructor.
     *
     * What to do with an annotation whose author cannot reach the target
     * course: drop, keep, or anonymise. Defaults to anonymise, which keeps
     * the text but cannot be undone - use 'keep' where attribution matters.
     *
     * @param string $name Name of the setting
     * @param mixed $value Value of the setting
     * @param bool $visibility Is the setting visible in the UI, eg {@see base_setting::VISIBLE}
     * @param int $status Status of the setting with regards to the locking, eg {@see base_setting::NOT_LOCKED}
     */
    public function __construct($name, $value = 'anonymise', $visibility = self::VISIBLE, $status = self::NOT_LOCKED) {
        parent::__construct($name, self::IS_TEXT, $value, $visibility, $status);
        $this->make_ui(self::UI_HTML_DROPDOWN, get_string("ivs_restore_videocomment_authors", 'ivs'), null, ['options' => [
                'anonymise' => get_string("ivs_restore_videocomment_authors_anonymise", 'ivs'),
                'skip' => get_string("ivs_restore_videocomment_authors_skip", 'ivs'),
                'keep' => get_string("ivs_restore_videocomment_authors_keep", 'ivs')]]);
    }
}
