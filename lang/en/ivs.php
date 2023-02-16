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
$string['settings'] = 'Settings';
$string['modulenameplural'] = 'Interactive Video Suites';
$string['ivs:create_pinned_comments'] = 'Pin annotations (Trigger Questions)';
$string['ivs:lock_annotation_access'] = 'Set annotation access';
$string['ivs:access_reports'] = 'Access annotation reports';
$string['ivs:download_annotations'] = 'Export Videocomments';
$string['ivs:access_course_settings'] = 'Access course settings';
$string['ivs:create_comment'] = 'Create video annotation';
$string['ivs:edit_any_comment'] = 'Edit all annotations';
$string['ivs:view_any_comment'] = 'View all annotations';
$string['modulename_help'] = 'Interactive Video Suite module for video annotation';
$string['ivs:addinstance'] = 'Add a new Interactive Video Suite';
$string['ivs:submit'] = 'Submit Interactive Video Suite';
$string['ivs:view'] = 'View Interactive Video Suite';
$string['ivsname'] = 'Interactive Video Suite name';
$string['ivsname_help'] = 'The title of the Interactive Video Suite';
$string['ivs'] = 'Interactive Video Suite';
$string['pluginadministration'] = 'Interactive Video Suite administration';
$string['pluginname'] = 'Interactive Video Suite';
$string['videourl'] = 'Video url';
$string['ivs:view:comment_overview'] = 'Show comments';
$string['ivs:view:question_overview'] = 'Show results';
$string['ivs:acc_label:private'] = 'private';
$string['ivs:acc_label:course'] = 'course';
$string['ivs:acc_label:members'] = 'members';
$string['ivs:acc_label:member'] = 'members';
$string['ivs:acc_label:group'] = 'groups';
$string['ivs:acc_label:role'] = 'roles';
$string['ivs:acc_label:group_video'] = 'Video';
$string['ivs:acc_label:group_user'] = 'User';
$string['messageprovider:assign_notification'] = 'Assignment notifications';
$string['messageprovider:ivs_annotation_direct_mention'] = 'New comment in an IVS activity within my courses.';
$string['messageprovider:ivs_annotation_indirect_mention'] = 'Someone mentioned me in a new comment';
$string['messageprovider:ivs_annotation_reply'] = 'Someone mentioned me by a group or role.';
$string['messageprovider:ivs_annotation_conversation'] = 'Someone replied to my comment';
$string['messageprovider:ivs_annotation_tag'] = 'Someone replied to a conversation I am in';
$string['eventannotationcreated'] = 'Annotation created';
$string['eventannotationupdated'] = 'Annotation updated';
$string['eventannotationdeleted'] = 'Annotation deleted';
$string['annotation_context_url_name'] = 'Videocomment';
$string['annotation_direct_mention_subject'] = 'New videocomment from {$a->fullname}';
$string['annotation_direct_mention_fullmessage'] = 'Hello {$a->fullname},

{$a->fullname} has created a new videocomment:

{$a->annotation}

------------------------------------------------------------------------
This is a copy of a message sent to you at "{$a->course_name}". Go to {$a->annotation_url} to see the complete videocomment';
$string['annotation_direct_mention_smallmessage'] = 'New Videocomment';
$string['annotation_reply_subject'] = 'Answer to your videocomment from  {$a->fullname}';
$string['annotation_reply_fullmessage'] = 'Hello {$a->fullname},

{$a->fullname} has replied to your videocomment:

{$a->annotation}

------------------------------------------------------------------------
This is a copy of a message sent to you at "{$a->course_name}". Go to {$a->annotation_url} to see the complete videocomment with all comments.';
$string['annotation_reply_smallmessage'] = 'Answer to comment';
$string['annotation_conversation_subject'] = 'New comment in your conversation from {$a->fullname}';
$string['annotation_conversation_fullmessage'] = 'Hallo {$a->fullname},

{$a->fullname} has replied to your conversation:

{$a->annotation}

------------------------------------------------------------------------
This is a copy of a message sent to you at "{$a->course_name}". Go to {$a->annotation_url} to see the complete conversation with all comments.';
$string['annotation_conversation_smallmessage'] = 'New comment in conversation';
$string['messageprovider:annotation_created'] = 'Create video annotation';
$string['annotation_overview_menu_item'] = 'Interactive Video Suite (IVS) Annotations';
$string['apply_filter'] = 'Apply filter';
$string['filter_all'] = '- Select option -';
$string['block_filter_title'] = 'Filter';
$string['block_grouping_title'] = 'Grouping';
$string['filter_label_has_drawing'] = 'Drawing';
$string['cockpit_filter_empty'] = 'No annotations found';
$string['cockpit_summary'] = '{$a->total} annotations found';
$string['block_filter_sort'] = 'Sorting';
$string['block_filter_timestamp'] = 'Timestamp';
$string['block_filter_timecreated'] = 'Date created';
$string['block_filter_timestamp_alt_asc'] = 'Timestamp smallest first';
$string['block_filter_timestamp_alt_desc'] = 'Timestamp biggest first';
$string['block_filter_timecreated_alt_asc'] = 'Date created oldest first';
$string['block_filter_timecreated_alt_desc'] = 'Date created newest first';
$string['filter_label_rating'] = 'Rating';
$string['rating_option_none'] = 'none';
$string['rating_option_red'] = 'red';
$string['rating_option_yellow'] = 'yellow';
$string['rating_option_green'] = 'green';
$string['filter_label_access'] = 'Access';
$string['block_report_title'] = 'Reports';
$string['block_report_title_single'] = 'Report';
$string['create_report'] = 'Create new report';
$string['create_report_hint'] = 'Current filter and grouping will be applied';
$string['save_report'] = 'Save report';
$string['report_start_date'] = 'Start date';
$string['report_rotation'] = 'Rotation';
$string['report_rotation_daily'] = 'daily';
$string['report_rotation_weekly'] = 'weekly';
$string['report_rotation_monthly'] = 'monthly';
$string['report_edit'] = 'Edit';
$string['report_delete'] = 'Delete';
$string['cockpit_report_daily'] = 'daily cockpit report';
$string['cockpit_report_weekly'] = 'weekly cockpit Report';
$string['cockpit_report_monthly'] = 'monthly cockpit Report';
$string['messageprovider:ivs_annotation_report'] = 'Annotation report has been created.';
$string['cockpit_report_mail_subject_daily'] = 'Interactive Video Suite daily report';
$string['cockpit_report_mail_subject_weekly'] = 'Interactive Video Suite weekly report';
$string['cockpit_report_mail_subject_monthly'] = 'Interactive Video Suite monthly report';
$string['cockpit_report_mail_body_rotation_daily'] = 'daily';
$string['cockpit_report_mail_body_rotation_weekly'] = 'weekly';
$string['cockpit_report_mail_body_rotation_monthly'] = 'monthly';
$string['cockpit_report_mail_annotation_header_part_1'] = 'commented';
$string['cockpit_report_mail_annotation_header_part_2'] = 'on';
$string['cockpit_report_mail_annotation_header_part_3'] = '';
$string['cockpit_report_mail_body_header'] = 'Hello {$a->fullname},';
$string['cockpit_report_mail_body'] = 'this is your {$a->rotation} report of the ivs activities in the course {$a->course}:';
$string['cockpit_report_mail_body_footer_separator'] = '---------------------------------------';
$string['cockpit_report_mail_body_footer'] = 'Click the following link to view and edit your report in the ivs:';
$string['cockpit_heading'] = 'ivs course overview';
$string['ivs_setting_annotation_buttons'] = 'Hide comment action buttons';
$string['ivs_setting_annotation_buttons_help'] =
        'The reply, edit and delete actions for comments will only be shown on mouseover.';
$string['ivs_setting_annotation_readmore'] = 'Shorten Annotations';
$string['ivs_setting_annotation_readmore_help'] =
        'Shorten comments that are too long. The whole message can still be displayed by clicking to the pop-up icon.';
$string['ivs_setting_opencast_external_files_title'] = 'Open Cast video upload';
$string['ivs_setting_opencast_external_files_help'] = 'You can choose Open Cast to upload videos.';
$string['ivs_setting_opencast_internal_files_title'] = 'Internal video upload';
$string['ivs_setting_opencast_internal_files_help'] = 'You can choose internal sources to upload videos.';
$string['ivs_setting_opencast_menu_title'] = 'Open Cast video';
$string['ivs_video_config_error'] = 'No video available.';
$string['ivs_opencast_video_chooser'] = '-none-';
$string['ivs_setting_playbackcommands'] = 'Video editor';
$string['ivs_setting_playbackcommands_help'] =
        'Add markers, text cues, zoom, pause, slow motion, and fast motion to your video. Use these to draw learners attention to important aspects and encourage discussion in the video.';
$string['ivs:edit_playbackcommands'] = 'Edit playback commands';
$string['ivs_setting_annotation_realm_default'] = 'Set default read access to "course"';
$string['ivs_setting_annotation_realm_default_help'] =
        'The read access when creating new comments is set to "course" by default. All users enrolled in the course can read the comments.';
$string['filearea_videos'] = "Video";
$string['ivs_setting_match_question'] = "Video questions (match)";
$string['ivs_setting_match_question_help'] =
        "In quiz mode, you use different question types (including single/multiple choice questions and free text questions) to test learners' prior knowledge and understanding directly in the video. When the question appears, the video is paused and learners cannot continue until the question is answered (correctly).

Timing mode tests knowledge and reaction to the video scenes shown. These can be identified by the learners at the right time via buttons.

Translated with www.DeepL.com/Translator (free version)";
$string['ivs:edit_match_questions'] = "Edit video questions";
$string['ivs:create_match_answers'] = "Create video test answers";
$string['ivs:access_match_reports'] = "Access video test reports";
$string['ivs_setting_match_answer_setting'] = "Include answers of video with video questions";
$string['ivs_match_config'] = "Grade";
$string['ivs_match_config_mode'] = "Assessment Mode";
$string['ivs_match_config_assessment_mode_formative'] = "Formative assessment";
$string['ivs_match_config_enable_video_test'] = "Video questions enabled";
$string['ivs_match_config_video_test'] = "Video questions";
$string['ivs_match_question_title_not_available'] = "Question without title #";
$string['ivs_match_context_label'] = "Edit and test questions";
$string['ivs_match_context_label_help'] =
        "<strong>Note:</strong> In this mode the questions are only simulated and the answers will not be saved.";
$string['ivs_match_question_answer_menu_label_name'] = "Name";
$string['ivs_match_question_answer_menu_label_user_id'] = "User-ID";
$string['ivs_match_question_answer_menu_label_first_text_answer'] = "First answer";
$string['ivs_match_question_answer_menu_label_last_text_answer'] = "Last answer";
$string['ivs_match_question_answer_menu_label_elements_per_page'] = "Elements per page";
$string['ivs_match_question_answer_menu_label_elements_per_summary'] = "Summary";
$string['ivs_match_question_answer_menu_label_elements_per_questions'] = "Questions";
$string['ivs_match_question_answer_menu_label_first_click_answer'] = "First attempt: correct";
$string['ivs_match_question_answer_menu_label_last_click_answer'] = "Last attempt: correct";
$string['ivs_match_question_answer_menu_label_click_retries'] = "Retries";
$string['ivs_match_question_answer_menu_label_first_single_choice_answer'] = "First attempt";
$string['ivs_match_question_answer_menu_label_single_choice_retries'] = "Retries";
$string['ivs_match_question_answer_menu_label_last_single_choice_answer'] = "Last attempt: correct";
$string['ivs_match_question_answer_menu_label_single_choice_correct'] = "Correct";
$string['ivs_match_question_answer_menu_label_last_single_choice_selected_answer'] = "Selected answer(s)";
$string['ivs_match_question_summary_title'] = "Overview by questions";
$string['ivs_match_question_summary_question_type_single'] = "Single-/ multiple choice question";
$string['ivs_match_question_summary_question_type_click'] = "Click question";
$string['ivs_match_question_summary_question_type_text'] = "Essay";
$string['ivs_match_question_summary_question_id'] = "Question ID";
$string['ivs_match_question_summary_question_title'] = "Title";
$string['ivs_match_question_summary_question_body'] = "Question";
$string['ivs_match_question_summary_question_type'] = "Question type";
$string['ivs_match_question_summary_question_first_try'] = "First attempt: correct";
$string['ivs_match_question_summary_question_last_try'] = "Last attempt: correct";
$string['ivs_match_question_summary_question_answered'] = "Participation";
$string['ivs_match_config_assessment_mode_formative_help'] =
        "Please answer the questions shown in this video. With the button \"Repeat\" you can change your answer.";
$string['ivs_match_question_header_id_label'] = "Question ID: ";
$string['ivs_match_question_header_type_label'] = "Question type: ";
$string['ivs_match_question_header_title_label'] = "Label: ";
$string['ivs_match_question_header_question_label'] = "Question: ";
$string['ivs_settings'] = "IVS-Settings";
$string['ivs_settings_title'] = "Interactive Video Suite (IVS) Settings";
$string['ivs_player_settings'] = "Player-Settings";
$string['ivs_player_settings_locked'] = "Locked";
$string['ivs_restore_include_match_answers'] = "Restore answers of video questions";
$string['ivs_restore_include_videocomments'] = "Restore video annotations";
$string['ivs_restore_include_videocomments_all'] = "All";
$string['ivs_restore_include_videocomments_none'] = "None";
$string['ivs_restore_include_videocomments_student'] = "Students only";
$string['ivs_restore_include_videocomments_teacher'] = "Teachers only";
$string['ivs_match_download_summary_label'] = "Download table data as";
$string['ivs_match_question_export_summary_filename'] = "-IVS-Export-Summary";
$string['ivs_match_question_export_question_filename'] = "-IVS-Export-Question-ID-";
$string['ivs_setting_single_choice_question_random_default'] = 'Shuffle answers in Single Choice questions';
$string['ivs_setting_single_choice_question_random_default_help'] =
        'Answers in Single Choice questions will be displayed in random order.';
$string['ivs_setting_autohide_controlbar'] = 'Hide controls';
$string['ivs_setting_autohide_controlbar_help'] = 'Hides the videocontrollbar on user inactivity.';
$string['ivs_setting_accessibility'] = 'Enable accessibility';
$string['ivs_setting_accessibility_help'] = 'Enable all available features for better accessibility.';
$string['ivs_setting_read_access_lock'] = 'Lock read access';
$string['ivs_setting_read_access_lock_help'] = 'The read access will be locked and cannot be changed by users.';
$string['ivs_match_question_summary_details_last_try'] = "Last attempt";
$string['ivs_match_question_summary_details_label'] = "Label";
$string['ivs_match_download_summary_details_label'] = "Download all question summary details";
$string['ivs_match_question_export_summary_details_filename'] = "-IVS-Export-Summary-Details";
$string['ivs_license'] = "License";
$string['ivs_instance_id_label'] = "Instance identification";
$string['ivs_package_label'] = "Current package";
$string['ivs_package_label_active'] = "Active licences";
$string['ivs_package_label_overbooked'] = "Overbooked licences";
$string['ivs_package_label_expired'] = "Expired licences";
$string['ivs_package_inactive'] = "No active licenses found";
$string['ivs_package_value'] = "You have already purchased a license or want to get an overview?";
$string['ivs_license_button'] = "Go to Shop";
$string['ivs_license_data_policy'] =
        "Data protection: Your privacy is important to us. To enable us to create a custom-made IVS-Player for you, only pseudonymized usage statistics are transferred via an encrypted connection. For detailed information please check our AGBs.";
$string['ivs_package_active'] = "Active license";
$string['ivs_activity_licence_error'] = "Please activate a valid license for your instance first. Follow the instructions in the plugin settings of the IVS-Plugin.
If you do not have access, please contact your system administrator.";
$string['ivs_package_button_label'] = "Go to IVS Shop";
$string['ivs_course_title'] = "Course title";
$string['ivs_course_spots_title'] = "Spots";
$string['ivs_course_package_title'] = "Package";
$string['ivs_current_package_courses_label'] = "Courses";
$string['ivs_activate_course_license_label'] = "Activate IVS License";
$string['ivs_submit_button_label'] = "Submit";
$string['ivs_duration'] = "Duration";
$string['ivs_license_spots'] = "License spots";
$string['ivs_occupied_spots'] = "Occupied spots";
$string['ivs_available_spots'] = "Available spots";
$string['ivs_license_period'] = "License expires at";
$string['ivs_clock'] = "Clock";
$string['ivs_course_selector_none'] = "No course chosen";
$string['ivs_course_license_selector_label'] = "Select courses";
$string['ivs_course_license_selector_flat_label'] = "Select Product";
$string['ivs_course_license_error_no_selected_course'] = 'License activation failed. No course selected.';
$string['ivs_course_license_error_no_licenses_available'] = 'Licenses not available.';
$string['ivs_course_license_error_no_free_licenses_available'] = 'No free course license available.';
$string['ivs_course_license_available'] = 'Licenses successfully updated.';
$string['ivs_course_license_released'] = 'Licenses successfully released.';
$string['ivs_course_license_error_release'] = 'Licenses could not be released.';
$string['ivs_course_license_error_no_course_selected'] = 'Select a course first, please.';
$string['ivs_course_license_modal_confirmation'] =
        'Are you sure? The license will be released after this action an can be assigned again.';
$string['ivs_plugin'] = 'IVS Plugin Schedule Task';
$string['ivs_usage_info'] = 'The license in course {$a->name} has a utilization of {$a->usage} %.';
$string['ivs_usage_warning'] =
        'The license in course {$a->name} has a utilization of {$a->usage} %. From 110% the license will be deactivated!';
$string['ivs_usage_error'] = 'The license in course {$a->name} has a utilization of {$a->usage} %. The licence is deactivated';
$string['ivs_usage_error_with_license'] =
        'The license in course {$a->name} has a utilization of {$a->usage} %. The license will be deactivated when the {$a->product_name} is full';
$string['ivs_usage_error_instance'] =
        'The instance license {$a->name} has a utilization of {$a->usage} %. The licence is deactivated';
$string['ivs_duration_warning'] = 'The license in course {$a->name} expires in {$a->resttime} days.';
$string['ivs_duration_error'] = 'The license in course {$a->name} is expired.';
$string['ivs_duration_error_instance'] = 'The instance license {$a->name} is expired.';
$string['ivs_delete_licence'] = 'The license will be removed from the history.';
$string['ivs_course_package_delete'] = 'Remove';
$string['ivs_course_package_reassign'] = 'Reassign';
$string['ivs_move_user_to_instance_from_course'] = '({$a->overbooked_spots} user added to {$a->product_name})';
$string['ivs_shop_hint'] = 'Please visit our shop to get a licence';
$string['ivs_set_testsystem'] = 'Set test system';
$string['ivs_set_testsystem_success'] = 'Set successfully test system';
$string['ivs_set_testsystem_success_released'] = 'Released successfully test system';
$string['ivs_testsystem_info_message'] = 'This instance runs under a test system';
$string['ivs_testsystem'] = 'Test system instance identification';
$string['ivs_set_player_version'] = 'Set player version';
$string['ivs_same_player_version'] = 'The playerversion matches already';
$string['ivs_changed_player_successfully'] = 'Player version set';
$string['ivs_actual_player_version'] = 'Actual player version: ';
$string['ivs_course_license_core_offline'] = 'The license server could not be reached. Please reload this page and try again.';
$string['ivs_disabled_saving_match_result'] = 'Your match results will not be saved.';
$string['ivs_disabled_create_comments'] = 'Comments can.';
$string['ivs_setting_playbackrate_enabled'] = 'Playback speed';
$string['ivs_setting_playbackrate_enabled_help'] = 'Control to change the videos playback speed.';
$string['privacy:metadata:ivs_matchanswer:user_id'] = 'Information about the user which match answers he made';
$string['privacy:metadata:ivs_matchanswer:data'] = 'Information about the user what answer he made';
$string['privacy:metadata:ivs_matchquestion:user_id'] = 'Information about the user who created the match answer';
$string['privacy:metadata:ivs_matchtake:user_id'] = 'Information about the user how often he tried to answer the match questions';
$string['privacy:metadata:ivs_report:user_id'] = 'Information about the user to create the report from the questions';
$string['privacy:metadata:ivs_videocomment:user_id'] = 'Information about the user which videocomment belongs him';
$string['privacy:metadata:ivs_videocomment:body'] = 'Information about the content from an videocomment for an user';
$string['ivs_videocomment_header_id_label'] = 'ID';
$string['ivs_videocomment_header_title_label'] = 'Videotitle';
$string['ivs_videocomment_header_author_name_label'] = 'Name of the Author';
$string['ivs_videocomment_header_timecode_label'] = 'Timestamp';
$string['ivs_videocomment_header_textcontent_label'] = 'Videocomment (Text)';
$string['ivs_videocomment_header_stoplightrating_label'] = 'Rating';
$string['ivs_videocomment_header_creationdate_label'] = 'Creation Date';
$string['ivs_videocomment_header_question_id_label'] = 'Response to';
$string['ivs_videocomment_header_link_to_videotimecode_label'] = 'Link to moment in Video';
$string['ivs_videocomment_export_filename'] = 'IVS-Comment-Export';
$string['ivs_videocomment_menu_label_elements_per_page'] = "Elements per page";
$string['privacy:metadata:ivs_videocomment:body'] = 'Information about the content from an videocomment for an user';
$string['ivs_setting_read_access_none'] = 'None';
$string['ivs_setting_read_access_private'] = 'Private';
$string['ivs_setting_read_access_course'] = 'Course';
$string['ivs_setting_read_access_role:teacher'] = 'Role teacher';
$string['ivs_setting_annotations_enabled'] = 'Video comments';
$string['ivs_setting_annotations_enabled_help'] = 'Time-marker specific video comments: Comments and re-comments make it possible to ask questions, lead discussions and contribute ideas directly in the video. Colored icons in the playbar highlight the timing of the comments.';
$string['ivs_setting_panopto_external_files_title'] = 'Panopto video upload';
$string['ivs_setting_panopto_external_files_help'] = 'You can choose Panopto to upload videos. The panopto_block plugin is required to use panopto videos';
$string['ivs_setting_panopto_menu_title'] = 'Panopto video';
$string['ivs_setting_panopto_menu_button'] = 'Add Panopto video';
$string['ivs_setting_panopto_menu_tooltip'] = 'Only one video can be selected';
$string['ivs_setting_annotation_audio'] = 'Audio message';
$string['ivs_setting_annotation_audio_help'] = 'Record and send time-marker-specific audio messages!';
$string['ivs_setting_annotation_audio_max_duration'] = 'Max duration in seconds';
$string['ivs_setting_annotation_audio_max_duration_help'] = 'Limit the duration of audio messages. ';
$string['ivs_setting_annotation_audio_max_duration_validation'] = 'Only values between 0 and 300 are allowed';
$string['ivs_setting_user_notification_settings'] = 'Mute notifications';
$string['ivs_setting_user_notification_settings_help'] = 'Mute the notification for new videocomments. No system notifications will be sent.';
$string['ivs_freemium_start'] = 'Have fun with the Interactive Video Suite. Get started right away and create your first video comment. If you want to upgrade your license, visit <a href="https://interactive-video-suite.de/de/preise">our store</a>';
$string['ivs_freemium_activity'] = 'You want to learn more about the possibilities of social video with the Interactive Video Suite? <a href="https://interactive-video-suite.de/de/demo-anfrage">Sign up for the free demo course including personal consultation.</a>';
$string['ivs_freemium_end'] = 'Buy now a larger license to invite more people to your course and get the most out of Interactive Video Suite for your scenario. Click here for our <a href="https://interactive-video-suite.de/de/preise">store</a>';
$string['ivs_setting_annotation_comment_preview_offset'] = 'Preview of a videocomment';
$string['ivs_setting_annotation_comment_preview_offset_help'] = 'Clicking on a video comment, the video will be rewound and played until the comment is reached. (value in seconds)';
$string['ivs_activity_safari_info_text'] = 'If you have problems playing this video visit <a href="{$a->url}/archive/docs/manual/general-help/safari-panopto>">our help page</a>. If you still have problems, please use another browser (Chrome, Firefox, Edge)';
$string['ivs_usage_instance_info'] = 'When purchasing an IVS Flat, please note that {$a->usage} users of your instance are counted';
$string['ivs_setting_external_sources_title'] = 'Embed videos from external source';
$string['ivs_setting_external_sources_help'] = 'You can choose external sources to upload videos.';
$string['ivs_setting_external_source_menu_title'] = 'External video';
$string['ivs_setting_kaltura_external_files_title'] = 'Use Kaltura video source';
$string['ivs_setting_kaltura_external_files_help'] = 'You can use kaltura as video source.';
$string['ivs_setting_kaltura_menu_title'] = 'Kaltura Video';
$string['ivs_setting_external_video_source_validation'] = 'Unsupported external video source. Please check the url and make sure the video has public access.';
$string['ivs_player_settings_notification'] = 'Notification';
$string['ivs_player_settings_controls'] = 'Controls & Appearance';
$string['ivs_player_settings_advanced'] = 'IVS Advanced Options';
$string['ivs_player_settings_advanced_comments'] = 'Video comments';
$string['ivs_player_settings_advanced_match'] = 'Video questions (match)';
$string['ivs_player_settings_advanced_video_source'] = 'Video sources';
$string['ivs_player_settings_features'] = 'Player Features';
$string['ivs_player_settings_misc'] = 'Misc settings';
$string['ivs_player_settings_main'] = 'IVS Player options';
$string['ivs_player_settings_statistics'] = 'Usage statistics';
$string['ivs_statistics_title'] = 'Send statistics';
$string['ivs_statistics_help'] = 'Submit statistics on the use of the IVS to Ghostthinker. Only static data and no personal data will be submitted. Thank you for helping us to advance the Interactive Video Suite and further increase the added value for learners.';
$string['ivs_statistics'] = 'Statistics';
$string['ivs_videohosts_label'] = 'Videohosts';
$string['ivs_match_takes_label'] = 'Completed videotests';
$string['ivs_activities_label'] = 'IVS activities';
$string['ivs_activities_courses_label'] = 'Courses with IVS activities';
$string['ivs_comments_label'] = 'IVS comments';
$string['ivs_audio_comments_label'] = 'IVS audio comments';
$string['ivs_match_question_label'] = 'IVS match questions';
$string['ivs_match_question_types_label'] = 'IVS match questions types';
$string['statistic_info_general'] = 'General statistics';
$string['statistic_info_text'] = 'Here is an overview of relevant statistics of the use of the Interactive Video Suite. In order to support Ghostthinker in advancing the IVS, this data can also be transmitted. Please activate the transmission in the admin plugin settings';
$string['ivs_setting_exam_mode'] = 'Exam mode';
$string['ivs_setting_exam_mode_help'] = 'Results of the video test count in the Moodle Gradebook';
$string['statistic_info_text'] = 'Here is an overview of relevant statistics of the use of the Interactive Video Suite. In order to support Ghostthinker in advancing the IVS, this data can also be transmitted. Please activate the transmission in the admin plugin settings';
$string['statistic_info_text'] = 'Here is an overview of relevant statistics of the use of the Interactive Video Suite. In order to support Ghostthinker in advancing the IVS, this data can also be transmitted. Please activate the transmission in the admin plugin settings';
$string['ivs_setting_exam_mode'] = 'Exam mode';
$string['ivs_setting_exam_mode_help'] = 'Results of the video test count in the Moodle Gradebook';
$string['ivs_attemptsallowed'] = 'Allowed attempts';
$string['ivs_grademethod'] = 'Grading method';
$string['ivs_grademethod_best_attempt'] = 'Best attempt';
$string['ivs_grademethod_average'] = 'Average';
$string['ivs_grademethod_first_attempt'] = 'First attempt';
$string['ivs_grademethod_last_attempt'] = 'Last attempt';
$string['ivs_gradepass'] = 'Grade to pass';
$string['ivs_attempts'] = 'Allowed attempts';
$string['ivs_gradepass_help'] = 'This setting determines the minimum grade required to pass. The value is used in activity completion and in the gradebook.';
$string['ivs_attempts_help'] = 'How often are users allowed to take the video test?';
$string['ivs_grademethod_help'] = 'If multiple attempts are allowed, the following methods are available to calculate the final test score:

* Best attempt
* Average
* First attempt (all other attempts are ignored)
* Last attempt (all other attempts are ignored)';
$string['ivs_grade'] = 'Grade';
$string['ivs_match_config_assessment_mode_none'] = "Deactivated";
$string['ivs_match_config_assessment_mode_quiz'] = "Quiz-Mode";
$string['ivs_match_config_assessment_mode_timing'] = "Timing-Mode";
$string['ivs_match_config_grade_mode_best_score_label'] = "Best score is ";
$string['ivs_match_config_grade_mode_average_score_label'] = "Average score is ";
$string['ivs_match_config_grade_mode_first_attempt_score_label'] = "First attempt score is ";
$string['ivs_match_config_grade_mode_last_attempt_score_label'] = "Last attempt score is ";
$string['ivs_match_config_status_passed_label'] = "Passed - ";
$string['ivs_match_config_status_not_started_label'] = "Not startet";
$string['ivs_match_config_status_failed_label'] = "Failed, no attempts left - ";
$string['ivs_match_config_status_progress_label'] = "Attempt in progress";
$string['ivs_match_config_status_not_passed_label'] = "Not passed - ";
$string['ivs_setting_player_controls_enabled'] = "Allow navigation in the video test";
$string['ivs_setting_player_controls_enabled_help'] = "Specify whether users can navigate forwards or backwards within the video.";
$string['ivs_setting_player_show_videotest_feedback'] = "Show results for learners directly";
$string['ivs_setting_player_show_videotest_feedback_help'] = "Show results for learners directly after answering a video test question.";
$string['ivs_setting_player_show_videotest_solution'] = "Show summary of results for learners at the end";
$string['ivs_setting_player_show_videotest_solution_help'] = "Show summary of results for learners at the end";
