define(['jquery', 'core/notification', 'core/custom_interaction_events', 'core/modal', 'core/modal_registry', 'core/modal_factory','core/templates','core/str','core/modal_events'], function($, Notification, CustomEvents, Modal, ModalRegistry, ModalFactory, Templates,Str,ModalEvents) {
    let selectedPanoptoVideo = '';
    return {
        init: function (panopto_data) {

            //hide and update gradepass core function with separate videotest gradepass setting
            //Please note: The actual setting for grade to pass will be hidden und manipulated by the
            //activity setting which can be locked in the activity, course and system settings.
            $('#id_modstandardgrade').hide();


            //Hide Settings depending on other settings
            $('#id_exam_mode_enabled_value').change(function () {
                set_display_option('#id_exam_mode_enabled_value', '#id_mod_ivsgrades');
                return;
            });
  //Hide Settings depending on other settings
            $('#id_annotations_enabled_value').change(function () {
                set_display_option('#id_annotations_enabled_value', '#id_mod_ivsnotification');
                return;
            });

            set_display_option('#id_annotations_enabled_value', '#id_mod_ivsnotification');
            set_display_option('#id_exam_mode_enabled_value', '#id_mod_ivsgrades');
            set_display_option_timing_mode( '#id_match_question_enabled_value', '#fgroup_id_show_videotest_feedback');
            set_display_option_timing_mode('#id_match_question_enabled_value', '#fgroup_id_show_videotest_solution');

             $('#id_match_question_enabled_value').change(function () {
                             //Show timing mode checkboxes
                 set_display_option_timing_mode( $('#id_match_question_enabled_value'), '#fgroup_id_show_videotest_feedback');
                 set_display_option_timing_mode($('#id_match_question_enabled_value'), '#fgroup_id_show_videotest_solution');

                return;
            });

            if (panopto_data) {

                let servername = panopto_data.servername;
                let instancename = panopto_data.instancename;
                let sessiongroupid = panopto_data.sessiongroupid;
                let iframeURL = 'https://' + servername + '/Panopto/Pages/Sessions/EmbeddedUpload.aspx?playlistsEnabled=false&instance=' + instancename + '&folderID=' + sessiongroupid;
                let save = false;

                let btn = document.createElement("button");
                btn.innerHTML = panopto_data.buttonname;
                btn.classList.add('btn');
                btn.classList.add('btn-primary');
                btn.classList.add('panopto-selector');
                btn.style.marginLeft = '20px';
                $('#id_panopto_video').after(btn);
                let eventToOpenModal = $('.panopto-selector');

                //The event to open the modal
                ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: 'Panopto videos',
                    large: true,
                    body: Templates.render('mod_ivs/panopto_modal', {iframeurl: iframeURL}),
                }, eventToOpenModal)
                    .done(function (modal) {
                        modal.getRoot().on(ModalEvents.save, function (e) {

                            // Stop the default save button behaviour which is to close the modal.
                            e.preventDefault();
                            modal.hide();

                            if (selectedPanoptoVideo.ids.length <= 1) {
                                let panopto_video_name = selectedPanoptoVideo.names;
                                let panopto_video_id = selectedPanoptoVideo.ids;
                                let panopto_data = JSON.stringify({
                                    'servername': servername,
                                    'instancename': instancename,
                                    'sessiongroupid': sessiongroupid,
                                    'videoname': panopto_video_name,
                                    'sessionId': panopto_video_id
                                });
                                if(panopto_video_name.length == 1){
                                    $('#id_panopto_video_json_field').val(panopto_data);
                                }
                                else{
                                    $('#id_panopto_video_json_field').val('');
                                    $('#id_panopto_video_json_field').attr('value','')
                                    $('#id_panopto_video').attr('value','')
                                }

                                $('#id_panopto_video').val(panopto_video_name);
                            }
                        });
                    });
                ;

                window.addEventListener('message', (e) => {
                        let panopto_selected_video_data = JSON.parse(e.data);
                        selectedPanoptoVideo = JSON.parse(e.data);
                        if(panopto_selected_video_data.cmd === 'ready'){
                            win = document.getElementById('panopto_iframe').contentWindow;
                            message = {cmd: 'createEmbeddedFrame'};
                            win.postMessage(JSON.stringify(message), 'https://' + servername);
                        }
                        if(panopto_selected_video_data.cmd === 'deliveryList'){
                            if(panopto_selected_video_data.ids.length > 1){
                                $('.modal-footer').children().closest('.btn-primary').attr('disabled',true);
                                $('.modal-footer').children().closest('.btn-primary').attr('title',panopto_data.tooltip);
                            }
                            else{
                                $('.modal-footer').children().closest('.btn-primary').attr('disabled',false);
                                $('.modal-footer').children().closest('.btn-primary').removeAttr('title',true);
                            }
                        }
                    }
                )
            }

        }
    }
});

/**
 * set 'adjust playback speed' visibility depending on 'enable video question' checkbox
 */
function set_display_option(field,element) {
    if (!$(field).is(":checked")) {
        $(element).css('display', 'none');
    } else {
        $(element).css('display', '');
    }
}


/**
 * hide and show timing mode settings
 */
function set_display_option_timing_mode(field, element) {
    if ($(field).val() != 2){
        $(element).css('display', 'none');
    } else {
        $(element).css('display', '');
    }
}
