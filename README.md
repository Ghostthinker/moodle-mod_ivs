# About
moodle-mod_ivs is a Moodle activity plugin to allow Social Video Learning.
Enrich your videos by the powerful features of the social video player. Create pinpointed video comments for real discussions, add drawings or mark important situations directly in your videos by using just one tool.
- follow the link to the [Moodle plugins directory](https://moodle.org/plugins/mod_ivs) for more details

# Requirements
* Requires a [IVS-License](https://interactive-video-suite.de/en/pricing) to setup and use
* Tested with Moodle 3.5+

# Installation
* Install plugin to **mod/ivs** <br/>
  Please see [Installing_a_plugin](https://docs.moodle.org/39/en/Installing_plugins#Installing_a_plugin) for detailed installation instructions
* Get your Instance-Identification at **/mod/ivs/admin/admin_settings_license.php**
* Buy your [IVS-License](https://interactive-video-suite.de/en/pricing) using your Instance-Identification
* Create Interactive Video Suite activities


# Recommendations
* Use Moodle filter plugin to enhance the Interactive Video Suite Activity. Share a comment link which will be rendered as a Interactive Video Suite activity https://github.com/Ghostthinker/moodle-filter_ivs

# Features
* Interactive Video Suite activity
* Reports
* Backup & Restore
* Supports test environment

# Changelog

### v1.31
* minor bugfixes

### v1.30
* added compatibility with Moodle 5

### v1.29
* support for calendar events
* resolved issues that occurred when deleting annotations

### v1.28
* fixed issues related to Panopto integration
* resolved bugs in export functionality

### v1.27
* Moodle 4.5 compatibility
* missing translation strings added

### v1.26
* fixed panopto help link
* changed license status messages
* resolved an issue where teachers and managers were unable to set and lock annotation access.

### v1.25.2
* improved player navigation
* bug fixes an little player improvements
* ``ep5 version 2.92``

### v1.25
* picture in picture disabled in exam mode
* video feedback restricted to HTML5 video player
* ``ep5 version 2.91``

### v1.24
* fixed bug concerning activity completion 'on view'
* fixed bug associated with display of correct answers in exam mode
* ``ep5 version 2.9``

### v1.23
* Improved compatibility for Moodle 3.9 - Moodle 4.2.3 and PHP 8.0 - 8.2
* ``ep5 version 2.9``

### v1.22
* bug fixes
  * scrolling fix in quiz mode
* further improvements
  * unlink videos in IVS activities
* ``ep5 version 2.9``

### v1.21
* bug fixes 
  * disabled comments in exam mode
  * changed db column settings in “ivs-matchtake”
* further improvements
  * timing-mode: cool down function 
  * timing-mode: ui improvements
* ``ep5 version 2.9``

### v1.20
* Vimp integration
* Panopto player api
* Kaltura improvements
* ``ep5 version 2.8``

### v1.19
* ui improvements and bug fixes

### v1.18
* fixed notification bug

### v1.17
* fixed upgrade bug

### v1.16
* added Timing Mode
* bug fixes
* ``ep5 version 2.6``

### v1.15
* added Gradebook integration
* added Multiple-choice questions to Quiz-mode
* added exam mode
* minor bug fixes
* ``ep5 version 2.5``

### v1.14
* added Kaltura support

### v1.13
* fixed upgrade issue

### v1.12
* added Youtube, Vimeo and external source support
* improved ivs setting pages
* ``ep5 version 2.4``

### v1.11
* annotation preview
* export video comments
* ``ep5 version 2.3``

### v1.10
* share comments
* improved player UX
* ``ep5 version 2.1``, ``ep5 version 2.2``

### v1.10
* share comments
* improved player UX
* ``ep5 version 2.1``, ``ep5 version 2.2``

### v1.9
* audiomessages
* fixed multiple issues and bugs
* ``ep5 version 2.0``

### v1.8
* panopto integration
* fix moodle proxy configuration
* ``ep5 version 1.4``

### v1.7
* annotation export
* visibility lock (needed ``ep5 version 1.4``)
* setting to enable/disable comments

### v1.6
* Fixed license issues
* Added Bulk operations settings for comments and match
* Videoplayer: autorending of clickable links in comments and match
* Videoplayer: jump to active context and comment initially
* Videoplayer: added MathJax support
* Videoplayer: added Markdown support
* Fixed switchcast interface
* ``ep5 version 1.3``

### v1.5
* Refactoring and fixes for Moodle Directory listing
* Fix 4:3 thumbnail preview image layout
* Fix report edit, delete and start date
* ``ep5 version 1.2``

### v1.4
* Fixed cron report
* ``ep5 version 1.2``

### v1.3
* Fixed language selection
* ``ep5 version 1.2``

### v1.2
* Added setting to enable video playback rate for MATCH videos
* ``ep5 version 1.2``

### v1.1
* Updated constants

### v1.0
* Initial public release
* ``ep5 version 1.0``
