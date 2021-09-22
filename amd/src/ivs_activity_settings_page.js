define(['jquery', 'core/notification', 'core/custom_interaction_events', 'core/modal', 'core/modal_registry', 'core/modal_factory','core/templates','core/str','core/modal_events'], function($, Notification, CustomEvents, Modal, ModalRegistry, ModalFactory, Templates,Str,ModalEvents) {
    return {
        init: function (panopto_data) {
            // register 'enable video question' change event
            $('#id_match_question_enabled_value').change(function () {
                set_display_option();
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
                            save = true;
                            win = document.getElementsByTagName('iframe')[1].contentWindow;
                            message = {cmd: 'createEmbeddedFrame'};
                            win.postMessage(JSON.stringify(message), 'https://' + servername);
                        });
                    });
                ;

                window.addEventListener('message', (e) => {
                        let panopto_selected_video_data = JSON.parse(e.data);

                        if(panopto_selected_video_data.cmd === 'ready'){
                            win = document.getElementsByTagName('iframe')[1].contentWindow;
                            message = {cmd: 'createEmbeddedFrame'};
                            win.postMessage(JSON.stringify(message), 'https://' + servername);
                        }

                        if(panopto_selected_video_data.cmd === 'deliveryList'){
                            if(panopto_selected_video_data.ids.length > 1){
                                $('.modal-footer').children().first().attr('disabled',true);
                                $('.modal-footer').children().first().attr('title',panopto_data.tooltip);
                            }
                            else{
                                $('.modal-footer').children().first().attr('disabled',false);
                                $('.modal-footer').children().first().removeAttr('title',true);
                            }
                        }

                        if (panopto_selected_video_data.cmd === 'deliveryList' && save && panopto_selected_video_data.ids.length <= 1) {
                            let panopto_video_name = panopto_selected_video_data.names;
                            let panopto_video_id = panopto_selected_video_data.ids;
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
                    }
                )
            }

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


