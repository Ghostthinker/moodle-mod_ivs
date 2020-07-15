<?php
/*************************************************************************
 *
 * GHOSTTHINKER CONFIDENTIAL
 * __________________
 *
 *  2006 - 2017 Ghostthinker GmbH
 *  All Rights Reserved.
 *
 * NOTICE:  All information contained herein is, and remains
 * the property of Ghostthinker GmbH and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Ghostthinker GmbH
 * and its suppliers and may be covered by German and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Ghostthinker GmbH.
 */

/**
 * English strings for ivs
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_ivs
 * @copyright 2017 Ghostthinker GmbH <info@ghostthinker.de>
 * @license   All Rights Reserved.
 */

defined('MOODLE_INTERNAL') || die();

$string['modulecategory'] = 'Interactive Video Suite';
$string['modulename'] = 'Interactive Video Suite';
$string['settings'] = 'Settings';
$string['modulenameplural'] = 'Interactive Video Suites';
$string['ivs:create_pinned_comments'] = 'Pin annotations (Trigger Questions)';
$string['ivs:lock_annotation_access'] = 'Set annotation access';
$string['ivs:access_reports'] = 'Access annotation reports';
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
$string['messageprovider:ivs_annotation_direct_mention'] =
        'Direct mention of your own name in the target course of a new videocomment';
$string['messageprovider:ivs_annotation_indirect_mention'] = 'Indirect mention by the course of a new video comment';
$string['messageprovider:ivs_annotation_reply'] = 'Direct answer of my video comment';
$string['messageprovider:ivs_annotation_conversation'] = 'New comment in my conversation';
$string['messageprovider:ivs_annotation_tag'] = 'New comment with a certain tag';
$string['eventannotationcreated'] = 'Annotation created';
$string['eventannotationupdated'] = 'Annotation updated';
$string['eventannotationdeleted'] = 'Annotation deleted';
$string['annotation_context_url_name'] = 'Videocomment';
$string['annotation_direct_mention_subject'] = 'New videocomment from {$a->userfromfirstname} {$a->userfromlastname}';
$string['annotation_direct_mention_fullmessage'] = 'Hello {$a->usertofirstname} {$a->usertolastname},

{$a->userfromfirstname} {$a->userfromlastname} has created a new videocomment:

{$a->annotation}

------------------------------------------------------------------------
This is a copy of a message sent to you at "{$a->course_name}". Go to {$a->annotation_url} to see the complete videocomment';
$string['annotation_direct_mention_smallmessage'] = 'New Videocomment';
$string['annotation_reply_subject'] = 'Answer to your videocomment from  {$a->userfromfirstname} {$a->userfromlastname}';
$string['annotation_reply_fullmessage'] = 'Hello {$a->usertofirstname} {$a->usertolastname},

{$a->userfromfirstname} {$a->userfromlastname} has replied to your videocomment:

{$a->annotation}

------------------------------------------------------------------------
This is a copy of a message sent to you at "{$a->course_name}". Go to {$a->annotation_url} to see the complete videocomment with all comments.';
$string['annotation_reply_smallmessage'] = 'Answer to comment';
$string['annotation_conversation_subject'] = 'New comment in your conversation from {$a->userfromfirstname} {$a->userfromlastname}';
$string['annotation_conversation_fullmessage'] = 'Hallo {$a->usertofirstname} {$a->usertolastname},

{$a->userfromfirstname} {$a->userfromlastname} has replied to your conversation:

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
$string['messageprovider:ivs_annotation_report'] = 'Annotation report';
$string['cockpit_report_mail_subject_daily'] = 'Interactive Video Suite daily report';
$string['cockpit_report_mail_subject_weekly'] = 'Interactive Video Suite weekly report';
$string['cockpit_report_mail_subject_monthly'] = 'Interactive Video Suite monthly report';
$string['cockpit_report_mail_body_rotation_daily'] = 'daily';
$string['cockpit_report_mail_body_rotation_weekly'] = 'weekly';
$string['cockpit_report_mail_body_rotation_monthly'] = 'monthly';
$string['cockpit_report_mail_annotation_header_part_1'] = 'commented';
$string['cockpit_report_mail_annotation_header_part_2'] = 'on';
$string['cockpit_report_mail_annotation_header_part_3'] = '';
$string['cockpit_report_mail_body_header'] = 'Hello {$a->usertofirstname} {$a->usertolastname},';
$string['cockpit_report_mail_body'] = 'this is your {$a->rotation} report of the ivs activities in the course {$a->course}:';
$string['cockpit_report_mail_body_footer_separator'] = '---------------------------------------';
$string['cockpit_report_mail_body_footer'] = 'Click the following link to view and edit your report in the ivs:';
$string['cockpit_heading'] = 'ivs course overview';
$string['ivs_setting_annotation_buttons'] = 'Hide Comment Buttons';
$string['ivs_setting_annotation_buttons_help'] =
        'The buttons for replying, editing and deleting an existing comment in the right sidebar are only displayed if the comment hovered over via mouse pointer.';
$string['ivs_setting_annotation_readmore'] = 'Shorten Annotations';
$string['ivs_setting_annotation_readmore_help'] =
        'Shorten comments that are more than three lines long. They can be displayed in full via the pop-up icon.';
$string['ivs_setting_switchcast_external_files_title'] = 'Switch Cast video upload';
$string['ivs_setting_switchcast_external_files_help'] = 'You can choose switch cast to upload videos.';
$string['ivs_setting_switchcast_internal_files_title'] = 'Internal video upload';
$string['ivs_setting_switchcast_internal_files_help'] = 'You can choose internal sources to upload videos.';
$string['ivs_setting_switchcast_menu_title'] = 'Switch cast video';
$string['ivs_video_config_error'] = 'No video available.';
$string['ivs_switchcast_video_chooser'] = '-none-';
$string['ivs_setting_playbackcommands'] = 'Enable playback commands';
$string['ivs_setting_playbackcommands_help'] =
        'You can adapt the video to your needs with the help of playback commands, such as drawings, playback speed, clipping. If necessary, all changes can be quickly undone.';
$string['ivs:edit_playbackcommands'] = 'Edit playback commands';
$string['ivs_setting_annotation_realm_default'] = 'Default read access of new comments set to course';
$string['ivs_setting_annotation_realm_default_help'] =
        'The read access when creating new comments is set to "course" by default. All users enrolled in the course can read the comments.';
$string['filearea_videos'] = "Video";
$string['ivs_setting_match_question'] = "Enable video questions";
$string['ivs_setting_match_question_help'] =
        "Questions allow you to capture student knowledge and feedback directly in the video using single choice, click questions and essays.";
$string['ivs:edit_match_questions'] = "Edit video questions";
$string['ivs:create_match_answers'] = "View video test answers";
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
$string['ivs_match_question_answer_menu_label_last_single_choice_selected_answer'] = "Selected answer";
$string['ivs_match_question_summary_title'] = "Overview by questions";
$string['ivs_match_question_summary_question_type_single'] = "Single-choice question";
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
$string['ivs_setting_single_choice_question_random_default'] = 'Show answers in random order';
$string['ivs_setting_single_choice_question_random_default_help'] =
        'When creating single-choice questions in the video test, enable random order.';
$string['ivs_setting_autohide_controlbar'] = 'Hide controlbar automatically';
$string['ivs_setting_autohide_controlbar_help'] = 'Show or hide the video controlbar automatically.';
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
