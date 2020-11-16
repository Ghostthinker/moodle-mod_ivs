define(['jquery'], function ($) {
    return {
        init: function () {
            // register 'enable video question' change event
            $('#id_match_question_enabled_value').change(function () {
                set_display_option();
                return;
            });

            // set option initially
            set_display_option();
        }
    }
});

/**
 * set 'adjust playback speed' visibility depending on 'enable video question' checkbox
 */
function set_display_option() {
    if (!$('#id_match_question_enabled_value').is(":checked")) {
        $('[data-groupname="playbackrate_enabled"]').css('display', 'none');
    } else {
        $('[data-groupname="playbackrate_enabled"]').css('display', '');
    }
}


