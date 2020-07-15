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
 * Capability definitions for the ivs module
 *
 * The capabilities are loaded into the database table when the module is
 * installed or updated. Whenever the capability definitions are updated,
 * the module version number should be bumped up.
 *
 * The system has four possible values for a capability:
 * CAP_ALLOW, CAP_PREVENT, CAP_PROHIBIT, and inherit (not set).
 *
 * It is important that capability names are unique. The naming convention
 * for capabilities that are specific to modules and blocks is as follows:
 *   [mod/block]/<plugin_name>:<capabilityname>
 *
 * component_name should be the same as the directory name of the mod or block.
 *
 * Core moodle capabilities are defined thus:
 *    moodle/<capabilityclass>:<capabilityname>
 *
 * Examples: mod/forum:viewpost
 *           block/recent_activity:view
 *           moodle/site:deleteuser
 *
 * The variable name for the capability definitions array is $capabilities
 *
 * @package    mod_ivs
 * @copyright 2017 Ghostthinker GmbH <info@ghostthinker.de>
 * @license   All Rights Reserved.
 */

defined('MOODLE_INTERNAL') || die();

// Modify capabilities as needed and remove this comment.
$capabilities = array(
        'mod/ivs:addinstance' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW
                ),
                'clonepermissionsfrom' => 'moodle/course:manageactivities'
        ),

        'mod/ivs:view' => array(
                'captype' => 'read',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => array(
                        'guest' => CAP_ALLOW,
                        'user' => CAP_ALLOW,
                )
        ),

        'mod/ivs:view_any_comment' => array(
                'captype' => 'read',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => array()
        ),
        'mod/ivs:edit_any_comment' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => array()
        ),
        'mod/ivs:create_comment' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => array(
                        'user' => CAP_ALLOW,
                )
        ),
        'mod/ivs:create_pinned_comments' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => array(
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW
                )
        ),

        'mod/ivs:submit' => array(
                'riskbitmask' => RISK_SPAM,
                'captype' => 'write',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => array(
                        'student' => CAP_ALLOW
                )
        ),

        'mod/ivs:access_reports' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => array(
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW
                )
        ),

        'mod/ivs:access_course_settings' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                )
        ),

        'mod/ivs:lock_annotation_access' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => array(
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW
                )
        ),

        'mod/ivs:edit_playbackcommands' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => array(
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW
                )
        ),

        'mod/ivs:edit_match_questions' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => array(
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW
                )
        ),

        'mod/ivs:create_match_answers' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => array(
                        'editingteacher' => CAP_ALLOW,
                        'teacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW,
                        'student' => CAP_ALLOW,
                )
        ),

        'mod/ivs:access_match_reports' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => array(
                        'editingteacher' => CAP_ALLOW,
                        'teacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW,
                )
        ),

);
