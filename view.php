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

use mod_ivs\ivs_match\AssessmentConfig;
use mod_ivs\settings\SettingsService;
use mod_ivs\MoodleLicenseController;
use mod_ivs\IvsHelper;

/**
 * Prints a particular instance of ivs
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_ivs
 * @copyright 2017 Ghostthinker GmbH <info@ghostthinker.de>
 * @license   All Rights Reserved.
 */

// Replace ivs with the name of your module and remove this line.

require_once('../../config.php');
require_once('./lib.php');
require_once('./locallib.php');
global $USER;

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n = optional_param('n', 0, PARAM_INT);  // ... ivs instance ID - it should be named as the first character of the module.
$cid = optional_param('cid', null, PARAM_INT);  // the  comment id to jump to
$embedded = optional_param('embedded', null, PARAM_INT);  // the  comment id to jump to

if ($id) {
    $cm = get_coursemodule_from_id('ivs', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $ivs = $DB->get_record('ivs', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $ivs = $DB->get_record('ivs', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $ivs->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('ivs', $ivs->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

$activity_context = \context_module::instance($id);

$settingsController = new SettingsService();

$activity_settings = $settingsController->getSettingsForActivity($ivs->id, $course->id);

$video_host = \mod_ivs\upload\VideoHostFactory::create($cm, $ivs);
$video_url = $video_host->getVideo();

$match_enabled = $activity_settings['match_question_enabled']->value;

require_login($course, true, $cm);

if (empty($embedded)) {
    $event = \mod_ivs\event\course_module_viewed::create(array(
            'objectid' => $PAGE->cm->instance,
            'context' => $PAGE->context,
    ));
    $event->add_record_snapshot('course', $PAGE->course);
    $event->add_record_snapshot($PAGE->cm->modname, $ivs);
    $event->trigger();

    // Print the page header.

    $PAGE->set_url('/mod/ivs/view.php', array('id' => $cm->id));
    $PAGE->set_title(format_string($ivs->name));
    $PAGE->set_heading(format_string($course->fullname));

    $PAGE->requires->jquery();
    //ivs_add_ep5_js_and_css_dependencies($PAGE, $CFG);

    // Output starts here.
    echo $OUTPUT->header();
    echo $OUTPUT->heading(format_string($ivs->name));

    $lc = ivs_get_license_controller();

    $hasActiveLicense = $lc->hasActiveLicense(['course' => $course]);

    if (!$hasActiveLicense) {
        \core\notification::error(get_string('ivs_activity_licence_error', 'ivs'));
        echo $OUTPUT->footer();
        exit;
    }

    $active_license = $lc->getActiveLicense(['course' => $course]);

    $context = context_course::instance($COURSE->id);
    $roles = get_user_roles($context, $USER->id);
    $roleId = 0;
    foreach ($roles as $role) {
        $roleId = $role->roleid;
    }

    if ($roleId < 5) {
        $usage = $active_license->spots_in_use / $active_license->spots;
        if ($active_license->usage == 'spots_nearly_full') {
            \core\notification::info(get_string('ivs_usage_info', 'ivs',
                    ['name' => $COURSE->fullname, 'usage' => round($usage * 100)]));
        } else if ($active_license->usage == 'spots_full') {
            \core\notification::warning(get_string('ivs_usage_warning', 'ivs',
                    ['name' => $COURSE->fullname, 'usage' => round($usage * 100)]));
        } else if ($active_license->usage == 'spots_overbooked') {
            \core\notification::error(get_string('ivs_usage_error', 'ivs',
                    ['name' => $COURSE->fullname, 'usage' => round($usage * 100)]));
        }
        $time = strtotime(date("Y-m-d H:i:s"));
        $resttime = strtotime($active_license->expires_at) - $time;
        $resttime = round($resttime / 86400);
        if ($active_license->runtime == 'duration_nearly_end') {
            \core\notification::warning(get_string('ivs_duration_warning', 'ivs',
                    ['name' => $COURSE->fullname, 'resttime' => $resttime]));
        }
    }

    if (empty($video_url)) {
        echo get_string('ivs_video_config_error', 'ivs');
        echo $OUTPUT->footer();
        exit;
    }
    $url_iframe = $_SERVER['REQUEST_URI'] . "&embedded=1";
    ?>

    <div>
        <style>
            .edubreak-responsive-iframe {
                width: 100%;
                height: 100%;
                min-height: 500px
            }

            @media (max-width: 700px) {
                .edubreak-responsive-iframe {
                    min-height: 350px
                }
            }

        </style>
        <script>

            //
            setInterval(function () {
                var container_width = $("div[role=main]").width();
                var good_height_min = container_width / 1.8;
                if (good_height_min > 500) {
                    good_height_min = 500;
                }
                $(".edubreak-responsive-iframe").css("min-height", good_height_min);

            }, 1000);


        </script>
        <!-- IVS Video -->
        <iframe class="edubreak-responsive-iframe" name="edubreakplayer" frameborder="0" src="<?php print $url_iframe ?>"
                allowfullscreen></iframe>

        <!-- Annotations URL -->
        <div style="padding:8px 0">

            <a href="<?php print new moodle_url('/mod/ivs/annotation_overview.php', array('id' => $id)) ?>">
                <i class="icon-ep5-comment-alt"></i>
                <?php print get_string('ivs:view:comment_overview', 'ivs'); ?></a>
        </div>

        <?php if ($match_enabled && has_capability('mod/ivs:access_match_reports', $activity_context)): ?>
            <!-- Questions URL -->

            <div style="padding:8px 0">

                <a href="<?php print new moodle_url('/mod/ivs/questions.php', array('id' => $id)); ?>">
                    <i class="icon-check"></i>
                    <?php print get_string('ivs:view:question_overview', 'ivs'); ?></a>
            </div>
        <?php endif ?>

    </div>
    <div><?php
        echo format_module_intro('edubreakplayer', $ivs, $cm->id); ?>
    </div>
    <?php

    echo $OUTPUT->footer();
    exit;
} else {

    $courseService = new \mod_ivs\CourseService();
    $groups = $courseService->getAllCourseGroups($course->id);
    $members = $courseService->getCourseMembers($course->id);
    $roles = $courseService->getRoleNames($course->id);

    /////////////////////
    //build acces realms
    /////////////////////
    $access_realms = array();

    //private and course realm

    $access_realms[] = array(
            'key' => 'private',
            'icon' => 'icon-roadblock',
            'label' => get_string('ivs:acc_label:private', 'ivs'),
    );

    $access_realms[] = array(
            'key' => 'course',
            'icon' => 'icon-ep5-globe',
            'label' => get_string('ivs:acc_label:course', 'ivs'),
    );

    //Swap annotation visibility if setting default is set to "course"
    if ((int) $activity_settings['annotation_realm_default_enabled']->value) {
        $array_tmp = $access_realms;
        $access_realms[0] = $array_tmp[1];
        $access_realms[1] = $array_tmp[0];
    }

    //member realm

    if (!empty($members)) {

        $values = array();
        foreach ($members as $key => $value) {
            $values[] = array(
                    'key' => $value->id,
                    'label' => $value->firstname . ' ' . $value->lastname
            );
        }
        $accessMembers = array();
        $accessMembers['key'] = 'member';
        $accessMembers['label'] = get_string('ivs:acc_label:members', 'ivs');
        $accessMembers['values'] = $values;
        $accessMembers['icon'] = 'icon-user';
        $access_realms[] = $accessMembers;
    }

    //group realm
    $values = array();
    if (!empty($groups)) {
        foreach ($groups as $value) {
            $values[] = array('key' => $value->id, 'label' => $value->name);
        }
        $accessGroups = array();
        $accessGroups['key'] = 'group';
        $accessGroups['icon'] = 'icon-users';
        $accessGroups['label'] = get_string('ivs:acc_label:group', 'ivs');
        $accessGroups['values'] = $values;
        $access_realms[] = $accessGroups;
    }

    //role realm
    $values = array();
    if (!empty($roles)) {
        foreach ($roles as $value) {
            $values[] = array('key' => $value->id, 'label' => $value->localname);
        }
        $accessRoles = array();
        $accessRoles['key'] = 'role';
        $accessRoles['label'] = get_string('ivs:acc_label:role', 'ivs');
        $accessRoles['values'] = $values;
        $accessRoles['icon'] = 'icon-user-secret';
        $access_realms[] = $accessRoles;
    }

    $user_picture = new user_picture($USER);
    $user_picture_url = $user_picture->get_url($PAGE) . '';

    $backend_url = new \moodle_url('/mod/ivs/backend.php');

    $may_create_pinned_annotations = has_capability('mod/ivs:create_pinned_comments', $activity_context);
    $may_lock_annotation_access = has_capability('mod/ivs:lock_annotation_access', $activity_context);

    $player_config = array(
            'overlay_mode' => false,
            'align_top' => false,
            'hide_when_inactive' => (int) $activity_settings['hide_when_inactive']->value,
            'list_item_buttons_hover_enabled' => (int) $activity_settings['list_item_buttons_hover_enabled']->value,
            'current_userdata' => array(
                    'name' => $USER->firstname . ' ' . $USER->lastname,
                    'picture' => $user_picture_url,
                    'url' => ''
            ),
            'plugins' => array(
                    'edubreak_annotations' => array(
                            'interface_uri' => $backend_url . '',
                            'may_create_comment' => true,
                            'default_open' => 1,
                            'default_cid' => $cid,
                            'video_id' => $cm->instance,
                            'current_userdata' => array(
                                    'name' => $USER->firstname . ' ' . $USER->lastname,
                                    'picture' => $user_picture_url,
                                    'url' => ''
                            ),
                            'pin_mode_allowed' => $may_create_pinned_annotations,
                            'pin_mode_pause_allowed' => $may_create_pinned_annotations,
                            'readmore_enabled' => (int) $activity_settings['annotations_readmore_enabled']->value,
                            'client_side_screenshots_enabled' => true,
                            'client_side_screenshots_width' => 400
                    ),
                    'edubreak_annotations_drawings' => array(),
                    'edubreak_annotations_rating' => array(
                            'type' => 'lights'
                    ),
                    'edubreak_annotations_timebullets' => array(
                            'fadein_delay' => 100,
                            'show_previewimage' => false,
                            'show_inline_image' => false,
                            'open_sidebar_on_click' => true,
                            'display_mode' => 'bullet'
                    ),
                    'edubreak_annotations_access' => array(
                            'realms' => $access_realms,
                            'inline_edit_allowed' => $may_lock_annotation_access
                    ),
            )
    );

    $playbackcommandService = new \mod_ivs\PlaybackcommandService();

    if ((int) $activity_settings['playbackcommands_enabled']->value) {
        $player_config['plugins']['edubreak_playbackcommands'] = array(
                'interface_uri' => $backend_url . '',
                'may_edit' => ivs_may_edit_playbackcommands($activity_context),
                'video_id' => $cm->instance,
                'playbackcommands' => $playbackcommandService->retrieve($cm->id)
        );
        if ($offset = $playbackcommandService->hasSequence($cm->id)) {
            $player_config['offset'] = $offset;
        }
    }

    $matchController = new \mod_ivs\MoodleMatchController();

    if ($match_enabled) {


        $assessment_config =
                $matchController->assessment_config_get_by_user_and_video($matchController->get_current_user_id(), $cm->instance,
                        false);

        $player_config['playbackrate_enabled'] = false;
        $player_config['settings_button_enabled'] = false;

        $player_config['plugins']['edubreak_match'] = array(
                'interface_uri' => $backend_url . '',
                'video_id' => $cm->instance,
                'take_id' => null,
                'full_screen_start' => true,
                'may_edit' => ivs_may_edit_match_questions($activity_context),
                'active_context' => $cm->instance,
                'assessment_config' => $assessment_config,
                'sounds' => [
                        'enabled' => false,
                        'correct' => new \moodle_url('/mod/ivs/assets/correct.mp3') . '',
                        'wrong' => new \moodle_url('/mod/ivs/assets/wrong.mp3') . '',
                ]
        );

        $player_config['plugins']['edubreak_match_question_choice'] = [
                'feedback_text_enabled' => true,
                'feedback_video_enabled' => true,
                'default_options' => 3,
                'max_allowed_choices' => 5,
                'default_random_question' => (int) $activity_settings['default_random_question']->value,
        ];
        $player_config['plugins']['edubreak_match_question_click'] = array(
                'feedback_enabled' => true
        );
        $player_config['plugins']['edubreak_match_question_text'] = array(
                'default_max_length' => 500,
                'feedabck_enabled' => true
        );
    }

    $conf = get_config('mod_ivs');

    $config_string = json_encode($player_config);

    // $lang_file = ivs_get_lang_file($CFG);

    $js_conf = $PAGE->requires->get_config_for_javascript($PAGE, $OUTPUT);
    $res_versions = $js_conf['jsrev'];

    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title><?php echo $ivs->name ?></title>
        <!-- resources -->

        <?php

        $include_files = ivs_ep5_get_js_and_css_dependencies();
        $js_files = $include_files['js'];
        $css_files = $include_files['css'];

        foreach ($js_files as $js_file) {
            echo '<script src="' . $js_file . '"></script>';
        }

        foreach ($css_files as $css_file) {
            echo '<link rel="stylesheet" href="' . $css_file . '">';
        }

        ?>

    </head>
    <body>

    <div class="edubreakplayer" id="edubreakplayer"
         style="width:100%; min-height:200px"
         data-nid="<?php echo $cm->instance ?>">
        <div class="ep5-media">
            <video crossorigin="anonymous" class="ep5-media-video" width="100%"
                   preload="metadata">
                <source src="<?php echo $video_url ?>" type="video/mp4"
                />
                <div>Sorry, your browser or device is not supported!</div>
            </video>
        </div>
    </div>

    <?php
    echo '<script>
  $(document).ready(function(){
    var player_configuration = ' . $config_string . ';
    $(\'.edubreakplayer:not(".ep5-processed")\').edubreakplayer(player_configuration).addClass(\'ep5-processed\');
  });

</script>';

    //todo footer
    ?>

    </body>
    </html>

    <?php
}
