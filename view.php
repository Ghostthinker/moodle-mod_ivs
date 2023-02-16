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
 * The view.php renders the Interactive video suite activity.
 *
 * @package mod_ivs
 * @author Ghostthinker GmbH <info@interactive-video-suite.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Ghostthinker GmbH (https://ghostthinker.de/)
 */

use mod_ivs\ivs_match\AssessmentConfig;
use mod_ivs\settings\SettingsService;
use mod_ivs\gradebook\GradebookService;
use mod_ivs\MoodleLicenseController;
use mod_ivs\IvsHelper;
use mod_ivs\upload\ExternalSourceVideoHost;

// Replace ivs with the name of your module and remove this line.

require_once('../../config.php');
require_once('./lib.php');
require_once('./locallib.php');

global $USER, $DB, $CFG;;

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or.
$n = optional_param('n', 0, PARAM_INT);  // ... ivs instance ID - it should be named as the first character of the module.
$cid = optional_param('cid', null, PARAM_TEXT);  // The  comment id to jump to.
$embedded = optional_param('embedded', null, PARAM_INT);  // The  comment id to jump to.
$course = $DB->get_record('course', array('id' => optional_param('courseid', SITEID, PARAM_INT)), '*', MUST_EXIST);

require_login($course, false);

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

$activitycontext = \context_module::instance($id);

$settingscontroller = new SettingsService();

$activitysettings = $settingscontroller->get_settings_for_activity($ivs->id, $course->id);

$videohost = \mod_ivs\upload\VideoHostFactory::create($cm, $ivs, $course);

$videourl = $videohost->get_video();

$match_type = $activitysettings['match_question_enabled']->value;

$annotationsenabled = $activitysettings['annotations_enabled']->value;

require_login($course, true, $cm);
$context = context_course::instance($course->id);
$userroles = get_user_roles($context, $USER->id);
$courseservice = new \mod_ivs\CourseService();
$members = $courseservice->get_course_members($course->id);

if ($match_type) {
    $permissionsavematch = has_capability('mod/ivs:create_match_answers', $activitycontext);
    if (!$permissionsavematch) {
        \core\notification::info(get_string('ivs_disabled_saving_match_result', 'ivs'));
    }
}

if (!empty($members)) {

    $values = array();
    foreach ($members as $key => $value) {
        $values[] = array(
                'key' => $value->id,
                'label' => fullname($value)
        );
    }
}

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

    // Output starts here.
    echo $OUTPUT->header();
    echo $OUTPUT->heading(format_string($ivs->name));

    $lc = ivs_get_license_controller();

    $status = $lc->get_status();
    if(isset($status->freemium)) {
        \core\notification::info(get_string('ivs_freemium_activity', 'ivs'));
    }

    $hasactivelicense = $lc->has_active_license(['course' => $course]);
    if (!$hasactivelicense) {
        \core\notification::error(get_string('ivs_activity_licence_error', 'ivs'));
        echo $OUTPUT->footer();
        exit;
    }

    $activelicense = $lc->get_active_license(['course' => $course]);



    $roleid = 0;
    foreach ($userroles as $role) {
        $roleid = $role->roleid;
    }

    if ($roleid < 5) {
        $usage = $activelicense->spots_in_use / $activelicense->spots;
        if ($activelicense->usage == 'spots_nearly_full') {
            \core\notification::info(get_string('ivs_usage_info', 'ivs',
                    ['name' => $course->fullname, 'usage' => round($usage * 100)]));
        } else if ($activelicense->usage == 'spots_full') {
            \core\notification::warning(get_string('ivs_usage_warning', 'ivs',
                    ['name' => $course->fullname, 'usage' => round($usage * 100)]));
        } else if ($activelicense->usage == 'spots_overbooked') {
            \core\notification::error(get_string('ivs_usage_error', 'ivs',
                    ['name' => $course->fullname, 'usage' => round($usage * 100)]));
        }
        $time = strtotime(date("Y-m-d H:i:s"));
        $resttime = strtotime($activelicense->expires_at) - $time;
        $resttime = round($resttime / 86400);
        if ($activelicense->runtime == 'duration_nearly_end') {
            \core\notification::warning(get_string('ivs_duration_warning', 'ivs',
                    ['name' => $course->fullname, 'resttime' => $resttime]));
        }
    }

    if (empty($videourl) && !$videohost instanceof ExternalSourceVideoHost) {
        echo get_string('ivs_video_config_error', 'ivs');
        echo $OUTPUT->footer();
        exit;
    }

    // We need to check all browsers, to check if we are save in safari
    if(strpos($_SERVER['HTTP_USER_AGENT'],'Chrome')) {
    }
    else if(strpos($_SERVER['HTTP_USER_AGENT'],'Firefox')) {
    }
    else if(strpos($_SERVER['HTTP_USER_AGENT'],'Safari')) {
        \core\notification::info(get_string('ivs_activity_safari_info_text', 'ivs',
                ['url' => $CFG->IVS_CORE_URL]));
    }

    ?>

    <div class="ivs-loading-spinner-container" id="ivs-loading-spinner">
        <div class="ivs-loading-spinner">
        </div>
    </div>

<?php
    $urliframe = $_SERVER['REQUEST_URI'] . "&embedded=1";
    $videohost->prerender($urliframe);
    ?>

    <div>
        <style>
            .edubreak-responsive-iframe {
                width: 100%;
                height: 70vh;
                min-height: 500px
            }

            @media (max-width: 700px) {
                .edubreak-responsive-iframe {
                    min-height: 350px
                }
            }

        </style>
        <script>

            setInterval(function () {
                var container_width = $("div[role=main]").width();
                var good_height_min = container_width / 1.8;
                if (good_height_min > 500) {
                    good_height_min = 500;
                }
                $(".edubreak-responsive-iframe").css("min-height", good_height_min);

            }, 1000);

            const interval = setInterval(spinnerInterval, 1000);

            function spinnerInterval() {
                if($('.edubreak-responsive-iframe').contents().find('#ep5-overlay-annotations').length){
                    document.getElementById('ivs-loading-spinner').remove();
                    stopSpinnerInterval();
                }
            }

            function stopSpinnerInterval() {
                clearInterval(interval);
            }


        </script>


        <!-- IVS Video -->
        <iframe class="edubreak-responsive-iframe" name="edubreakplayer" frameborder="0" src="<?php print $urliframe ?>"
                allowfullscreen></iframe>

        <!-- Annotations URL -->
        <div style="padding:8px 0">

            <a href="<?php print new moodle_url('/mod/ivs/annotation_overview.php', array('id' => $id)) ?>">
                <i class="icon-ep5-comment-alt"></i>
                <?php print get_string('ivs:view:comment_overview', 'ivs'); ?></a>
        </div>

        <?php if ($match_type && has_capability('mod/ivs:access_match_reports', $activitycontext)): ?>

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

    $courseservice = new \mod_ivs\CourseService();
    $groups = $courseservice->get_all_course_groups($course->id);
    $members = $courseservice->get_course_members($course->id);
    $roles = $courseservice->get_role_names($course->id);


    // Build acces realms.

    $accessrealms = array();

    // Private and course realm.

    $accessrealms[] = array(
            'key' => 'private',
            'icon' => 'icon-roadblock',
            'label' => get_string('ivs:acc_label:private', 'ivs'),
    );

    $accessrealms[] = array(
            'key' => 'course',
            'icon' => 'icon-ep5-globe',
            'label' => get_string('ivs:acc_label:course', 'ivs'),
    );

    $defaultrealm = null;
    $lockrealm = null;
    // Set realm default setting - and lock if necessary.
    if ($activitysettings['lock_realm_enabled']->value && $activitysettings['lock_realm_enabled']->value != 'none') {
        $realmval = strstr($activitysettings['lock_realm_enabled']->value, 'role_') ? 'role' :
          $activitysettings['lock_realm_enabled']->value;
        $values = [];
        if ($realmval == 'role') {
            $rolekey = str_replace('role_', '', $activitysettings['lock_realm_enabled']->value);
            $values[] = ['key' => $rolekey, 'label' => 'role'];
        }

        $defaultrealm = ['key' => $realmval, 'values' => $values];
        $lockrealm = true;
    } else if ((int) $activitysettings['annotation_realm_default_enabled']->value) {
        $defaultrealm = ['key' => 'course', 'values' => []];
    }

    // Member realm.
    if (!empty($members)) {

        $values = array();
        foreach ($members as $key => $value) {
            $values[] = array(
                    'key' => $value->id,
                    'label' => fullname($value)
            );
        }
        $accessmembers = array();
        $accessmembers['key'] = 'member';
        $accessmembers['label'] = get_string('ivs:acc_label:members', 'ivs');
        $accessmembers['values'] = $values;
        $accessmembers['icon'] = 'icon-user';
        $accessrealms[] = $accessmembers;
    }



    // Group realm.
    $values = array();
    if (!empty($groups)) {
        foreach ($groups as $value) {
            $values[] = array('key' => $value->id, 'label' => $value->name);
        }
        $accessgroups = array();
        $accessgroups['key'] = 'group';
        $accessgroups['icon'] = 'icon-users';
        $accessgroups['label'] = get_string('ivs:acc_label:group', 'ivs');
        $accessgroups['values'] = $values;
        $accessrealms[] = $accessgroups;
    }

    // Role realm.
    $values = array();
    if (!empty($roles)) {
        foreach ($roles as $value) {
            $values[] = array('key' => $value->id, 'label' => $value->localname);
        }
        $accessroles = array();
        $accessroles['key'] = 'role';
        $accessroles['label'] = get_string('ivs:acc_label:role', 'ivs');
        $accessroles['values'] = $values;
        $accessroles['icon'] = 'icon-user-secret';
        $accessrealms[] = $accessroles;
    }

    $userpicture = new user_picture($USER);
    $userpictureurl = $userpicture->get_url($PAGE) . '';

    $backendurl = new \moodle_url('/mod/ivs/backend.php');

    $maycreatepinnedannotations = has_capability('mod/ivs:create_pinned_comments', $activitycontext);
    $maylockannotationaccess = has_capability('mod/ivs:lock_annotation_access', $activitycontext);

    $permissioncreatecomment = has_capability('mod/ivs:create_comment', $activitycontext);

    // Enable accessibility options.
    $accessbilityenabled = (int) $activitysettings['accessibility_enabled']->value;

    $ratingoptions = [];
    if ($accessbilityenabled) {
        $ratingoptions = [
                'rating_options' =>
                        [
                                [
                                'rating' => 34,
                                'label' => 'red',
                                'color' => '#FF0000',
                                'icon' => 'icon-traffic-triangle',
                                ],
                                [
                                        'rating' => 67,
                                        'label' => 'yellow',
                                        'color' => '#FFFF00',
                                        'icon' => 'icon-traffic-square',
                                ],
                                [
                                        'rating' => 100,
                                        'label' => 'green',
                                        'color' => '#00FF00',
                                        'icon' => 'icon-traffic-circle',
                                ]
                        ],
                'rating_options_default' => [
                        'rating' => 0,
                        'label' => 'gray',
                        'color' => '#CCCCCC',
                        'icon' => 'icon-traffic-circle-outer',
                ],
        ];
    }

    $lang = IvsHelper::get_language();

    //load annotation
    $annotation = \mod_ivs\annotation::retrieve_from_db($cid, false);
    $starttime = 0;
    if ($annotation) {
        $starttime = $annotation->get_timestamp();
    }

    $playerconfig = array(
            'overlay_mode' => false,
            'lang' => $lang,
            'align_top' => false,
'           startTime' => $starttime,
            'hide_when_inactive' => (int) $activitysettings['hide_when_inactive']->value,
            'list_item_buttons_hover_enabled' => (int) $activitysettings['list_item_buttons_hover_enabled']->value,

            'current_userdata' => array(
                    'name' => fullname($USER),
                    'picture' => $userpictureurl,
                    'url' => ''
            ),
            'interface_uri' => $backendurl . '',
            'plugins' => array(
                    'edubreak_annotations' => array(
                            'interface_uri' => $backendurl . '',
                            'default_open' => 1,
                            'default_cid' => $cid,
                            'permission_create_comment' => $permissioncreatecomment,
                            'video_id' => $cm->instance,
                            'annotation_bulk_operations_enabled' => (is_siteadmin() || has_capability('mod/ivs:access_reports', $context)),
                            'current_userdata' => array(
                                    'name' => fullname($USER),
                                    'picture' => $userpictureurl,
                                    'url' => ''
                            ),
                            'pin_mode_allowed' => $maycreatepinnedannotations,
                            'pin_mode_pause_allowed' => $maycreatepinnedannotations,
                            'readmore_enabled' => (int) $activitysettings['annotations_readmore_enabled']->value,
                            'client_side_screenshots_enabled' => true,
                            'client_side_screenshots_width' => 400,
                            'share_enabled' => true,
                            'share_callback' => 'internal',
                            'share_baseurl' => new \moodle_url('/mod/ivs/view.php', array('id' => $cm->id)) . '&cid='
                    ),
                    'edubreak_annotations_drawings' => array(),
                    'edubreak_annotations_audio_message' => array(),
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
                            'realms' => $accessrealms,
                            'default_realm' => $defaultrealm,
                            'locked_realm' => $lockrealm,
                            'inline_edit_allowed' => $maylockannotationaccess
                    ),
            )
    );

    if(!empty($activitysettings['annotation_comment_preview_offset']->value)){
        $playerconfig['plugins']['edubreak_annotations']['jump_to_annotation'] = [
                'preview' => true,
                'offset' => $activitysettings['annotation_comment_preview_offset']->value,
        ];
    }


    // Extend accessibility options, if available.
    $playerconfig['plugins']['edubreak_annotations_rating'] += $ratingoptions;

    $playbackcommandservice = new \mod_ivs\PlaybackcommandService();

    if ((int) $activitysettings['playbackcommands_enabled']->value) {
        $playerconfig['plugins']['edubreak_playbackcommands'] = array(
                'interface_uri' => $backendurl . '',
                'may_edit' => ivs_may_edit_playbackcommands($activitycontext),
                'video_id' => $cm->instance,
                'playbackcommands' => $playbackcommandservice->retrieve($cm->id)
        );
        if ($offset = $playbackcommandservice->has_sequence($cm->id)) {
            $playerconfig['offset'] = $offset;
        }
    }


    if ((int) $activitysettings['annotation_audio_enabled']->value) {
        $playerconfig['plugins']['edubreak_annotations_audio_message'] = array(
                'interface_uri' => $backendurl . '/media/'. $cm->instance,
                'max_duration' => $activitysettings['annotation_audio_max_duration']->value,
        );
    }
    else{
        unset($playerconfig['plugins']['edubreak_annotations_audio_message']);
    }

    $matchcontroller = new \mod_ivs\MoodleMatchController();

    if ($match_type) {

        $assessmentconfig =
            $matchcontroller->assessment_config_get_by_user_and_video($matchcontroller->get_current_user_id(), $cm->instance,
                false);


        $playerconfig['playbackrate_enabled'] = (int) $activitysettings['playbackrate_enabled']->value;
        $playerconfig['settings_button_enabled'] = false;

        $playerconfig['plugins']['edubreak_match'] = array(
                'interface_uri' => $backendurl . '',
                'video_id' => $cm->instance,
                'take_id' => null,
                'full_screen_start' => true,
                'may_edit' => ivs_may_edit_match_questions($activitycontext),
                'match_bulk_operations_enabled' => (is_siteadmin() || has_capability('mod/ivs:access_reports', $context)),
                'active_context' => $cm->instance,
                'assessment_config' => $assessmentconfig,
                'sounds' => [
                        'enabled' => false,
                        'correct' => new \moodle_url('/mod/ivs/assets/correct.mp3') . '',
                        'wrong' => new \moodle_url('/mod/ivs/assets/wrong.mp3') . '',
                ]
        );

        if(!ivs_may_edit_match_questions($activitycontext) && (int) $activitysettings['exam_mode_enabled']->value) {
            $playerconfig['plugins']['edubreak_match']['lock_play_button'] = true;
        }


        $playerconfig['plugins']['edubreak_match_question_choice'] = [
                'feedback_text_enabled' => true,
                'feedback_video_enabled' => true,
                'default_options' => 3,
                'max_allowed_choices' => 5,
                'default_random_question' => (int) $activitysettings['default_random_question']->value
        ];
        $playerconfig['plugins']['edubreak_match_question_click'] = array(
                'feedback_enabled' => true
        );
        $playerconfig['plugins']['edubreak_match_question_text'] = array(
                'default_max_length' => 500,
                'feedback_enabled' => true
        );
    }

    if ($match_type == AssessmentConfig::ASSESSMENT_TYPE_TIMING){
        $playerconfig['plugins']['edubreak_match_question_timing'] = [
            "show_realtime_results" => true,
            "score_enabled" => true,
            "question_duration_enabled" => true,
        ];

        unset( $playerconfig['plugins']['edubreak_match_question_choice']);
        unset( $playerconfig['plugins']['edubreak_match_question_click']);
        $playerconfig['plugins']['edubreak_match']['show_create_button'] = false;

        //disable timline previews when users are may not edit questions
        if(!ivs_may_edit_match_questions($activitycontext)) {
            $playerconfig['plugins']['edubreak_match']['show_timeline'] = false;
            $playerconfig['plugins']['edubreak_match']['lock_play_button'] = true;
        }
    }

    if (!$annotationsenabled) {
        unset($playerconfig['plugins']['edubreak_annotations']);
        unset($playerconfig['plugins']['edubreak_annotations_timebullets']);
        unset($playerconfig['plugins']['edubreak_annotations_access']);
        unset($playerconfig['plugins']['edubreak_annotations_drawings']);
        unset($playerconfig['plugins']['edubreak_annotations_rating']);
    }


    $conf = get_config('mod_ivs');

    $configstring = json_encode($playerconfig);

    $jsconf = $PAGE->requires->get_config_for_javascript($PAGE, $OUTPUT);
    $resversions = $jsconf['jsrev'];
    $crossorigintag = $videohost->getcrossorigintag();




    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title><?php echo $ivs->name ?></title>
        <!-- resources -->
       <?php

        $includefiles = ivs_ep5_get_js_and_css_dependencies();
        $jsfiles = $includefiles['js'];
        $cssfiles = $includefiles['css'];

       foreach ($jsfiles as $jsfile) {
           $type = 'application/javascript';
           if(strpos($jsfile,'recorder') > -1 || strpos($jsfile,'gt-audio-player') > -1 || strpos($jsfile,'gt-share-button') > -1){
               $type = 'module';
           }
           echo '<script type="' . $type . '" src="' . $jsfile . '"></script>';
       }

        foreach ($cssfiles as $cssfile) {
            echo '<link rel="stylesheet" href="' . $cssfile . '">';
        }

        ?>

    </head>
    <body>

    <div class="edubreakplayer" id="edubreakplayer"
         style="width:100%; min-height:200px"
         data-nid="<?php echo $cm->instance ?>">
         <?php echo $videohost->rendermediacontainer($PAGE)?>
    </div>

    <?php
    echo '<script>
  $(document).ready(function(){
    var player_configuration = ' . $configstring . ';
    $(\'.edubreakplayer:not(".ep5-processed")\').edubreakplayer(player_configuration).addClass(\'ep5-processed\');
  });
</script>';

    // Todo footer.
    ?>

    </body>
    </html>

    <?php
}
