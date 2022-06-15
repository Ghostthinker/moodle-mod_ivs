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
 * Translation file
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

defined('MOODLE_INTERNAL') || die();

$string['modulecategory'] = 'Interactive Video Suite';
$string['modulename'] = 'Interactive Video Suite';
$string['settings'] = 'Einstellungen';
$string['modulenameplural'] = 'Interactive Video Suites';
$string['ivs:create_pinned_comments'] = 'Trigger Fragen erstellen';
$string['ivs:lock_annotation_access'] = 'Videokommentare freischalten';
$string['ivs:access_reports'] = 'Videokommentar Berichte verwalten';
$string['ivs:download_annotations'] = 'Videokommentare Exportieren';
$string['ivs:access_course_settings'] = 'Player Einstellungen des Kurses verwalten';
$string['ivs:create_comment'] = 'Videokommentar erstellen';
$string['ivs:edit_any_comment'] = 'Alle Videokommentare bearbeiten';
$string['ivs:view_any_comment'] = 'Alle Videokommentare betrachten';
$string['modulename_help'] = 'Mit der Interactive Video Suite können Sie Videos zeitpunktgenau kommentieren und diskutieren';
$string['ivs:addinstance'] = 'Neue Interactive Video Suite hinzufügen';
$string['ivs:submit'] = 'Interactive Video Suite abschicken';
$string['ivs:view'] = 'Interactive Video Suite anzeigen';
$string['ivsname'] = 'Titel';
$string['ivsname_help'] = 'Der Titel der Interactive Video Suite';
$string['ivs'] = 'Interactive Video Suite';
$string['pluginadministration'] = 'Interactive Video Suite-Administration';
$string['pluginname'] = 'Interactive Video Suite';
$string['videourl'] = 'Video url';
$string['ivs:view:comment_overview'] = 'Kommentare anzeigen';
$string['ivs:view:question_overview'] = 'Fragenresultate anzeigen';
$string['ivs:acc_label:private'] = 'Privat';
$string['ivs:acc_label:course'] = 'Kurs';
$string['ivs:acc_label:members'] = 'Personen';
$string['ivs:acc_label:member'] = 'Personen';
$string['ivs:acc_label:group'] = 'Gruppen';
$string['ivs:acc_label:role'] = 'Rollen';
$string['ivs:acc_label:group_video'] = 'Video';
$string['ivs:acc_label:group_user'] = 'Person';
$string['messageprovider:assign_notification'] = 'Aufgabenbenachrichtigung';
$string['messageprovider:ivs_annotation_direct_mention'] =
        'Direkte Erwähnung des eigenen Namens in der Zielgruppe eines neuen Videokommentars';
$string['messageprovider:ivs_annotation_indirect_mention'] = 'Indirekte Erwähnung durch die Zielgruppe eines neuen Videokommentars';
$string['messageprovider:ivs_annotation_reply'] = 'Direkte Antwort, also ein Rekommentar, auf einen meiner Videokommentare';
$string['messageprovider:ivs_annotation_conversation'] = 'Neuer Kommentar meiner Unterhaltung';
$string['messageprovider:ivs_annotation_tag'] = 'Neuer Kommentar mit bestimmten Tag';
$string['eventannotationcreated'] = 'Kommentar erstellt';
$string['eventannotationupdated'] = 'Kommentar geändert';
$string['eventannotationdeleted'] = 'Kommentar gelöscht';
$string['annotation_context_url_name'] = 'Videokommentar';
$string['annotation_direct_mention_subject'] = 'Neuer Videokommentar von {$a->fullname}';
$string['annotation_direct_mention_fullmessage'] = 'Hallo {$a->fullname},

{$a->fullname} hat einen neuen Videokommentar verfasst:

{$a->annotation}

---------------------------------------
Dies ist eine Kopie einer Nachricht im Kurs {$a->course_name}. Klicken Sie folgenden Link, um den kompletten Videokommentar zu sehen: {$a->annotation_url}';
$string['annotation_direct_mention_smallmessage'] = 'Neuer Videokommentar';
$string['annotation_reply_subject'] = 'Antwort zu Ihrem Kommentar von  {$a->fullname}';
$string['annotation_reply_fullmessage'] = 'Hallo {$a->fullname},

 {$a->fullname} hat einen Kommentar zu Ihrem Videokommentar verfasst:

{$a->annotation}

------------------------------------------------------------------------
Dies ist eine Kopie einer Nachricht im Kurs "{$a->course_name}". Klicken Sie folgenden Link, um den kompletten Videokommentar mit allen Kommentaren zu sehen: {$a->annotation_url}';
$string['annotation_reply_smallmessage'] = 'Antwort zum Kommentar';
$string['annotation_conversation_subject'] =
        'Neuer Kommentar in der Unterhaltung von {$a->fullname}';
$string['annotation_conversation_fullmessage'] = 'Hallo {$a->fullname},

{$a->fullname} hat einen Kommentar in Ihrer Unterhaltung verfasst:

{$a->annotation}

------------------------------------------------------------------------
Dies ist eine Kopie einer Nachricht im Kurs "{$a->course_name}". Klicken Sie folgenden Link, um die komplette Unterhaltung mit allen Kommentaren zu sehen: {$a->annotation_url}';
$string['annotation_conversation_smallmessage'] = 'Kommentar in Unterhaltung';
$string['messageprovider:annotation_created'] = 'Videokommentar verfassen';
$string['annotation_overview_menu_item'] = 'Interactive Video Suite (IVS) Kommentare';
$string['apply_filter'] = 'Filter anwenden';
$string['filter_all'] = '- Auswahl -';
$string['block_filter_title'] = 'Filter';
$string['block_grouping_title'] = 'Gruppieren';
$string['filter_label_has_drawing'] = 'Zeichnung';
$string['cockpit_filter_empty'] = 'Keine Videokommentare gefunden';
$string['cockpit_summary'] = 'Zeige {$a->totep5-sidebar-headeral} Kommentare';
$string['block_filter_sort'] = 'Sortierung';
$string['block_filter_timestamp'] = 'Zeitmarke';
$string['block_filter_timecreated'] = 'Erstellungsdatum';
$string['block_filter_timestamp_alt_asc'] = 'Zeitmarke kleinste zuerst';
$string['block_filter_timestamp_alt_desc'] = 'Zeitmarke größte zuerst';
$string['block_filter_timecreated_alt_asc'] = 'Erstellungsdatum älteste zuerst';
$string['block_filter_timecreated_alt_desc'] = 'Erstellungsdatum neuste zuerst';
$string['filter_label_rating'] = 'Bewertung';
$string['rating_option_none'] = 'keine';
$string['rating_option_red'] = 'rot';
$string['rating_option_yellow'] = 'gelb';
$string['rating_option_green'] = 'grün';
$string['filter_label_access'] = 'Zielgruppe';
$string['block_report_title'] = 'Berichte';
$string['block_report_title_single'] = 'Bericht';
$string['create_report'] = 'Neuen Bericht erstellen';
$string['create_report_hint'] = 'Aktuelle Filter und Gruppierung werden übernommen.';
$string['save_report'] = 'Bericht speichern';
$string['report_start_date'] = 'Startdatum';
$string['report_rotation'] = 'Turnus';
$string['report_rotation_daily'] = 'Täglich';
$string['report_rotation_weekly'] = 'Wöchentlich';
$string['report_rotation_monthly'] = 'Monatlich';
$string['report_edit'] = 'Bearbeiten';
$string['report_delete'] = 'Löschen';
$string['cockpit_report_daily'] = 'Täglicher Cockpit Report';
$string['cockpit_report_weekly'] = 'Wöchentlicher Cockpit Report';
$string['cockpit_report_monthly'] = 'Monatlicher Cockpit Report';
$string['messageprovider:ivs_annotation_report'] = 'Videokommentar Report';
$string['cockpit_report_mail_subject_daily'] = 'Interactive Video Suite täglicher Bericht';
$string['cockpit_report_mail_subject_weekly'] = 'Interactive Video Suite wöchentlicher Bericht';
$string['cockpit_report_mail_subject_monthly'] = 'Interactive Video Suite monatlicher Bericht';
$string['cockpit_report_mail_body_rotation_daily'] = 'täglicher';
$string['cockpit_report_mail_body_rotation_weekly'] = 'wöchentlicher';
$string['cockpit_report_mail_body_rotation_monthly'] = 'monatlicher';
$string['cockpit_report_mail_annotation_header_part_1'] = 'hat';
$string['cockpit_report_mail_annotation_header_part_2'] = 'am';
$string['cockpit_report_mail_annotation_header_part_3'] = 'kommentiert: ';
$string['cockpit_report_mail_body_header'] = 'Hallo {$a->fullname},';
$string['cockpit_report_mail_body'] = 'dies ist Ihr {$a->rotation} Bericht zu den Aktivitäten der ivs im Kurs {$a->course}:';
$string['cockpit_report_mail_body_footer_separator'] = '---------------------------------------';
$string['cockpit_report_mail_body_footer'] =
        'Klicken Sie folgenden Link, um über die ivs diesen Report einzusehen oder anzupassen:';
$string['cockpit_heading'] = 'ivs Kursübersicht';
$string['ivs_setting_annotation_buttons'] = 'Kommentar-Schaltflächen ausblenden';
$string['ivs_setting_annotation_buttons_help'] =
        'Die Schaltflächen für Antworten, Bearbeiten und Löschen eines bestehenden Kommentars in der rechten Seitenleiste werden nur angezeigt, wenn der Kommentar mit der Maus überfahren wird.';
$string['ivs_setting_annotation_readmore'] = 'Kommentare kürzen';
$string['ivs_setting_annotation_readmore_help'] =
        'Kommentare, die über drei Zeilen lang sind, werden automatisch gekürzt angezeigt. Sie können via Aufklapp-Icon bei Bedarf vollständig angezeigt werden.';
$string['ivs_setting_opencast_external_files_title'] = 'Open Cast Video Upload';
$string['ivs_setting_opencast_external_files_help'] = 'Beim Video Upload können Videos von Open Cast ausgewählt werden.';
$string['ivs_setting_opencast_internal_files_title'] = 'Interner Video Upload';
$string['ivs_setting_opencast_internal_files_help'] = 'Beim Video Upload können Videos von internen Quellen ausgewählt werden.';
$string['ivs_setting_opencast_menu_title'] = 'Open Cast Video';
$string['ivs_video_config_error'] = 'Keine Video ausgewählt.';
$string['ivs_opencast_video_chooser'] = '-keine-';
$string['ivs_setting_playbackcommands'] = 'Anweisungen aktivieren';
$string['ivs_setting_playbackcommands_help'] =
        'Mit Hilfe von Anweisungen, wie Zeichnungen, Abspielgeschwindigkeit, Ausschnitt können Sie das Video an Ihre Bedürfnisse anpassen. Dabei können alle Änderungen bei Bedarf auch wieder schnell rückgängig gemacht werden.';
$string['ivs:edit_playbackcommands'] = 'Anweisungen bearbeiten';
$string['ivs_setting_annotation_realm_default'] = 'Standard-Lesezugriff neuer Kommentare für Kurs';
$string['ivs_setting_annotation_realm_default_help'] =
        'Der Lesezugriff beim Erstellen neuer Kommentare ist standardmäßig auf "Kurs" gesetzt. Alle im Kurs eingeschrieben NutzerInnen können die Kommentare lesen.';
$string['filearea_videos'] = "Video";
$string['ivs_setting_match_question'] = "Fragen ermöglichen";
$string['ivs_setting_match_question_help'] =
        "Über Fragen können Sie das Wissen und Feedback von Studierenden mit Hilfe von Single Choice-, Klick- und Freitext-Fragen direkt im Video erfassen.";
$string['ivs:edit_match_questions'] = "Video Test bearbeiten";
$string['ivs:create_match_answers'] = "Video Test-Antworten erstellen";
$string['ivs:access_match_reports'] = "Video Test-Reports einsehen";
$string['ivs_setting_match_answer_setting'] = "Inklusive Antworten eines Videos mit Video Fragen";
$string['ivs_match_config'] = "Bewertung";
$string['ivs_match_config_mode'] = "Bewertungsmodus";
$string['ivs_match_config_assessment_mode_formative'] = "Formatives Assessment";
$string['ivs_match_config_enable_video_test'] = "Video Fragen aktiviert";
$string['ivs_match_config_video_test'] = "Video Fragen";
$string['ivs_match_question_title_not_available'] = "Aufgabe ohne Titel #";
$string['ivs_match_context_label'] = "Fragen erstellen, bearbeiten und testen";
$string['ivs_match_context_label_help'] =
        "<strong>Hinweis:</strong> In diesem Modus werden die Fragen nur simuliert und die Antworten nicht abgespeichert.";
$string['ivs_match_question_answer_menu_label_name'] = "Name";
$string['ivs_match_question_answer_menu_label_user_id'] = "Pers-ID";
$string['ivs_match_question_answer_menu_label_first_text_answer'] = "Erste Antwort";
$string['ivs_match_question_answer_menu_label_last_text_answer'] = "Letzte Antwort";
$string['ivs_match_question_answer_menu_label_elements_per_page'] = "Elemente pro Seite";
$string['ivs_match_question_answer_menu_label_elements_per_summary'] = "Zusammenfassung";
$string['ivs_match_question_answer_menu_label_elements_per_questions'] = "Fragen";
$string['ivs_match_question_answer_menu_label_first_click_answer'] = "Erster Versuch: Korrekt";
$string['ivs_match_question_answer_menu_label_last_click_answer'] = "Letzer Versuch: Korrekt";
$string['ivs_match_question_answer_menu_label_click_retries'] = "Wiederholungen";
$string['ivs_match_question_answer_menu_label_first_single_choice_answer'] = "Erster Versuch";
$string['ivs_match_question_answer_menu_label_single_choice_retries'] = "Wiederholungen";
$string['ivs_match_question_answer_menu_label_last_single_choice_answer'] = "Letzter Versuch: Korrekt";
$string['ivs_match_question_answer_menu_label_single_choice_correct'] = "Korrekt";
$string['ivs_match_question_answer_menu_label_last_single_choice_selected_answer'] = "Gewählte Antwort";
$string['ivs_match_question_summary_title'] = "Übersicht nach Fragen";
$string['ivs_match_question_summary_question_type_single'] = "Single-Choice Frage";
$string['ivs_match_question_summary_question_type_click'] = "Klick Frage";
$string['ivs_match_question_summary_question_type_text'] = "Freitext";
$string['ivs_match_question_summary_question_id'] = "Fragen ID";
$string['ivs_match_question_summary_question_title'] = "Titel";
$string['ivs_match_question_summary_question_body'] = "Frage";
$string['ivs_match_question_summary_question_type'] = "Fragen Typ";
$string['ivs_match_question_summary_question_first_try'] = "Erster Versuch: Korrekt";
$string['ivs_match_question_summary_question_last_try'] = "Letzter Versuch: Korrekt";
$string['ivs_match_question_summary_question_answered'] = "Teilnahme";
$string['ivs_match_config_assessment_mode_formative_help'] =
        "Bitte beantworten Sie die in diesem Video eingebauten Fragen. Mit dem Button “Wiederholen” können Sie Ihre Antwort ändern.";
$string['ivs_match_question_header_id_label'] = "Fragen ID: ";
$string['ivs_match_question_header_type_label'] = "Fragen Typ: ";
$string['ivs_match_question_header_title_label'] = "Bezeichnung: ";
$string['ivs_match_question_header_question_label'] = "Frage: ";
$string['ivs_match_question_header_question_label'] = "Frage: ";
$string['ivs_settings'] = "IVS-Einstellungen";
$string['ivs_settings_title'] = "Interactive Video Suite (IVS) Einstellungen";
$string['ivs_player_settings'] = "Player-Einstellungen";
$string['ivs_player_settings_locked'] = "Gesperrt";
$string['ivs_restore_include_match_answers'] = "Antworten auf Video Fragen wiederherstellen";
$string['ivs_restore_include_videocomments'] = "Videokommentare wiederherstellen";
$string['ivs_restore_include_videocomments_all'] = "Alle";
$string['ivs_restore_include_videocomments_none'] = "Keine";
$string['ivs_restore_include_videocomments_student'] = "Nur Studenten";
$string['ivs_restore_include_videocomments_teacher'] = "Nur Dozenten";
$string['ivs_match_download_summary_label'] = "Tabellendaten herunterladen als";
$string['ivs_match_question_export_summary_filename'] = "-IVS-Export-Fragenresultate";
$string['ivs_match_question_export_question_filename'] = "-IVS-Export-Fragen-ID-";
$string['ivs_setting_single_choice_question_random_default'] = 'Antworten in zufälliger Reihenfolge anzeigen';
$string['ivs_setting_single_choice_question_random_default_help'] =
        'Beim Erstellen von Single-Choice Fragen im Videotest zufällige Reihenfolge aktivieren.';
$string['ivs_setting_autohide_controlbar'] = 'Abspielleiste automatisch ausblenden';
$string['ivs_setting_autohide_controlbar_help'] = 'Video Abspielleiste mit Schaltflächen automatisch ein bzw. ausblenden.';
$string['ivs_setting_accessibility'] = 'Barrierefreiheit aktivieren';
$string['ivs_setting_accessibility_help'] = 'Alle verfügbaren Features für mehr Barrierefreiheit nutzen.';
$string['ivs_setting_read_access_lock'] = 'Lesezugriff einschränken';
$string['ivs_setting_read_access_lock_help'] = 'Der Lesezugriff beim Erstellen neuer oder beim Bearbeiten bestehender Videokommentare kann nicht geändert und entspricht dann der gewählten Einstellung.';
$string['ivs_match_question_summary_details_last_try'] = "Letzter Versuch";
$string['ivs_match_question_summary_details_label'] = "Bezeichnung";
$string['ivs_match_download_summary_details_label'] = "Alle Fragenresultate herunterladen";
$string['ivs_match_question_export_summary_details_filename'] = "-IVS-Export-Fragenresultate-Details";
$string['ivs_license'] = "Lizenz";
$string['ivs_instance_id_label'] = "Instanzkennung";
$string['ivs_package_label'] = "Aktuelles Paket";
$string['ivs_package_label_active'] = "Aktive Lizenzen";
$string['ivs_package_label_overbooked'] = "Überbuchte Lizenzen";
$string['ivs_package_label_expired'] = "Abgelaufene Lizenzen";
$string['ivs_package_inactive'] = "Momentan ist keine Lizenz aktiv";
$string['ivs_package_value'] = "Sie haben bereits eine Lizenz erworben oder möchten sich einen Überblick verschaffen?";
$string['ivs_license_button'] = "Zum Shop";
$string['ivs_license_data_policy'] =
        "Datenschutz: Ihre Privatsphäre ist uns wichtig. Damit wir für Sie einen massgeschneiderten IVS-Player erstellen können, werden nur anonymisierte Daten über eine verschlüsselte Verbindung übertragen. Detaillierte Informationen entnehmen Sie bitte unseren AGBs.";
$string['ivs_package_active'] = "Aktive Lizenz";
$string['ivs_activity_licence_error'] = "Bitte aktivieren Sie zunächst eine gültige Lizenz für Ihre Instanz. Befolgen Sie hierzu die Hinweise in den Plugineinstellungen des IVS-Plugins.

Sollten Sie keinen Zugriff haben, wenden Sie sich bitte an Ihren Systemadministrator.";
$string['ivs_package_button_label'] = "Zum IVS Shop";
$string['ivs_course_title'] = "Kurstitel";
$string['ivs_course_spots_title'] = "Plätze";
$string['ivs_course_package_title'] = "Paket";
$string['ivs_current_package_courses_label'] = "Kurse";
$string['ivs_activate_course_license_label'] = "IVS Kurslizenz freischalten";
$string['ivs_submit_button_label'] = "Bestätigen";
$string['ivs_duration'] = "Laufzeit";
$string['ivs_license_spots'] = "Lizenplätze";
$string['ivs_occupied_spots'] = "Belegte Plätze";
$string['ivs_available_spots'] = "Freie Plätze";
$string['ivs_license_period'] = "Lizenzlaufzeit bis";
$string['ivs_clock'] = "Uhr";
$string['ivs_course_selector_none'] = "Kein Kurs ausgewählt";
$string['ivs_course_license_selector_label'] = "Kurse wählen";
$string['ivs_course_license_selector_flat_label'] = "Produkt wählen";
$string['ivs_course_license_error_no_selected_course'] = 'Lizenz Aktivierung fehlgeschlagen. Zu viele Kurse ausgewählt.';
$string['ivs_course_license_error_no_licenses_available'] = 'Lizenzen nicht verfügbar.';
$string['ivs_course_license_error_no_free_licenses_available'] = 'Keine freie Kurslizenz verfügbar.';
$string['ivs_course_license_available'] = 'Lizenzen erfolgreich aktualisiert.';
$string['ivs_course_license_released'] = 'Die Lizenzen wurde freigegeben.';
$string['ivs_course_license_error_release'] = 'Die Lizenz konnte nicht freigegeben werden.';
$string['ivs_course_license_error_no_course_selected'] = 'Bitte wählen Sie einen Kurs aus.';
$string['ivs_course_license_modal_confirmation'] =
        'Sind Sie sicher? Die Lizenz wird nach dieser Aktion freigegeben und kann erneut vergeben werden.';
$string['ivs_plugin'] = 'IVS Plugin Schedule Task';
$string['ivs_usage_info'] = 'Die Lizenz im Kurs {$a->name} hat eine Auslastung von {$a->usage} %.';
$string['ivs_usage_warning'] =
        'Die Lizenz im Kurs {$a->name} hat eine Auslastung von {$a->usage} %. Ab 110% wird die Lizenz deaktiviert!';
$string['ivs_usage_error'] = 'Die Lizenz im Kurs {$a->name} hat eine Auslastung von {$a->usage} %. Die Lizenz wurde deaktivert';
$string['ivs_usage_error_with_license'] =
        'Die Lizenz im Kurs {$a->name} hat eine Auslastung von {$a->usage} %. Die Lizenz wird deaktiviert sobald die {$a->product_name} voll ist';
$string['ivs_duration_warning'] = 'Die Lizenz im Kurs {$a->name} läuft in {$a->resttime} Tagen aus';
$string['ivs_duration_error'] = 'Die Lizenz im Kurs {$a->name} ist ausgelaufen';
$string['ivs_duration_error_instance'] = 'Die Instanz Lizenz {$a->name} ist ausgelaufen';
$string['ivs_delete_licence'] = 'Die Lizenz wird aus dem Verlauf entfernt.';
$string['ivs_course_package_delete'] = 'Entfernen';
$string['ivs_course_package_reassign'] = 'Neu vergeben';
$string['ivs_move_user_to_instance_from_course'] = '({$a->overbooked_spots} User zur {$a->product_name} hinzugefügt)';
$string['ivs_shop_hint'] = 'Um eine Lizenz zu erwerben besucht unseren Shop';
$string['ivs_set_testsystem'] = 'Testsystem festlegen';
$string['ivs_set_testsystem_success'] = 'Testsystem erfolgreich festgelegt';
$string['ivs_set_testsystem_success_released'] = 'Testsystem erfolgreich entfernt';
$string['ivs_testsystem_info_message'] = 'Diese Instanz läuft unter einen Testsystem';
$string['ivs_testsystem'] = 'Instanzkennung des Testsystems';
$string['ivs_set_player_version'] = 'Player Version festlegen';
$string['ivs_same_player_version'] = 'Diese Player Version wird bereits genutzt';
$string['ivs_changed_player_successfully'] = 'Die Player Version wurde geändert';
$string['ivs_actual_player_version'] = 'Aktuelle Player Version: ';
$string['ivs_course_license_core_offline'] = 'Der Lizenzserver konnte nicht erreicht werden. Bitte laden Sie die Seite neu und versuchen Sie es erneut.';
$string['ivs_disabled_saving_match_result'] = 'Match Ergebnisse werden nicht gespeichert.';
$string['ivs_disabled_create_comments'] = 'Kommentare können nicht erstellt werden.';
$string['ivs_setting_playbackrate_enabled'] = 'Wiedergabegeschwindigkeit ändern';
$string['ivs_setting_playbackrate_enabled_help'] = 'Ist diese Einstellung gesetzt, kann die Wiedergabegeschwindigkeit des Videos verändert werden.';
$string['ivs_videocomment_header_id_label'] = 'ID';
$string['ivs_videocomment_header_title_label'] = 'Titel des Videos';
$string['ivs_videocomment_header_author_name_label'] = 'Name der Autor:in';
$string['ivs_videocomment_header_timecode_label'] = 'Zeitmarke';
$string['ivs_videocomment_header_textcontent_label'] = 'Videokommentar (Text)';
$string['ivs_videocomment_header_stoplightrating_label'] = 'Ampelbewertung';
$string['ivs_videocomment_header_creationdate_label'] = 'Erstellungsdatum';
$string['ivs_videocomment_header_question_id_label'] = 'Antwort auf';
$string['ivs_videocomment_header_link_to_videotimecode_label'] = 'Link zur Stelle im Video';
$string['ivs_videocomment_export_filename'] = 'IVS-Kommentar-Export';
$string['ivs_videocomment_menu_label_elements_per_page'] = "Elemente pro Seite";
$string['ivs_setting_read_access_none'] = 'Keine Einschränkung';
$string['ivs_setting_read_access_private'] = 'Privat';
$string['ivs_setting_read_access_course'] = 'Kurs';
$string['ivs_setting_read_access_role:teacher'] = 'Rolle Trainer:in';
$string['ivs_setting_annotations_enabled'] = 'Kommentare ermöglichen';
$string['ivs_setting_annotations_enabled_help'] = 'Über Kommentare kann ein punktgenauen Austausch rund um die Videoinhalte mit Hilfe von Videokommentaren und Antworten auf bestehende Videokommentare stattfinden.';
$string['ivs_setting_panopto_external_files_title'] = 'Panopto Video Upload';
$string['ivs_setting_panopto_external_files_help'] = 'Beim Video Upload können Videos von Panopto ausgewählt werden. Das panopto_block Plugin wird dafür benötigt';
$string['ivs_setting_panopto_menu_title'] = 'Panopto Video';
$string['ivs_setting_panopto_menu_button'] = 'Panopto Video hinzufügen';
$string['ivs_setting_panopto_menu_tooltip'] = 'Es kann nur ein Video ausgewählt werden';
$string['ivs_setting_annotation_audio'] = 'Audionachrichten';
$string['ivs_setting_annotation_audio_help'] = 'Mit dem Mikrofon des verwendeten Gerätes können Audionachrichten aufgezeichnet und in einem Videokommentar gespeichert werden';
$string['ivs_setting_annotation_audio_max_duration'] = 'Maximale Aufnahmedauer in Sekunden';
$string['ivs_setting_annotation_audio_max_duration_help'] = 'Festlegen der maximalen Dauer der Audionachricht in Sekunden. Maximale Dauer beträgt 300 Sekunden';
$string['ivs_setting_annotation_audio_max_duration_validation'] = 'Bitte nur Werte zwischen 0 und 300 eingeben';
$string['ivs_setting_user_notification_settings'] = 'Benachrichtigung bei neuem Videokommentar';
$string['ivs_setting_user_notification_settings_help'] = 'Benachrichtigung bei neuem Videokommentar für alle Kursteilnehmer:innen deaktivieren/aktivieren';
$string['ivs_freemium_start'] = 'Wir wünschen dir viel Spaß mit der Interactive Video Suite. Lege direkt los und erstelle deinen ersten Videokommentar. Falls du deine Lizenz upgraden möchtest, gelangst du <a href="https://interactive-video-suite.de/de/preise">hier zum IVS-Shop</a>';
$string['ivs_freemium_activity'] = 'Du willst mehr über die Möglichkeiten von Social Video und der Interactive Video Suite erfahren? <a href="https://interactive-video-suite.de/de/demo-anfrage">Dann melde dich zum kostenlosen Demokurs inklusive persönlicher Beratung an.</a>';
$string['ivs_freemium_end'] = 'Erwerbe jetzt eine größere Lizenz, um mehr Personen in einen Kurs einzuladen und die Interactive Video Suite optimal für dein Szenario zu nutzen. Hier gehts zum <a href="https://interactive-video-suite.de/de/preise">Shop</a>';
$string['ivs_setting_annotation_comment_preview_offset'] = 'Anspielen eines Videokommentars';
$string['ivs_setting_annotation_comment_preview_offset_help'] = 'Beim Klick auf einen Videokommentar wird das Video um diesen Wert zurückgespult und bis zum Kommentar abgespielt';
$string['ivs_activity_safari_info_text'] = 'Falls Sie Probleme haben dieses Video abzuspielen besuchen Sie <a href="{$a->url}/archive/docs/anleitung/allgemeine-hilfe/safari-panopto>">unsere Hilfe</a>. Sollte es dennoch Probleme geben, benutzen Sie bitte einen anderen Browser (Chrome, Firefox, Edge)';
$string['ivs_usage_instance_info'] = 'Bitte beachten Sie beim Kauf einer IVS Flat, dass {$a->usage} Benutzer Ihrer Instanz gezählt werden.';
