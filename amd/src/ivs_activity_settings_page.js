define(['core/notification', 'core/custom_interaction_events', 'core/modal', 
        'core/modal_registry', 'core/modal_factory', 'core/templates', 'core/str', 'core/modal_events'],
    function(Notification, CustomEvents, Modal, ModalRegistry, ModalFactory, Templates, Str, ModalEvents) {
        let selectedPanoptoVideo = '';
        
        return {
            init: function (panopto_data) {

                // Hide and update gradepass core function with separate videotest gradepass setting
                // Please note: The actual setting for grade to pass will be hidden und manipulated by the
                // activity setting which can be locked in the activity, course and system settings.
                const modStandardGrade = document.querySelector('#id_modstandardgrade');
                if (modStandardGrade) {
                    modStandardGrade.style.display = 'none';
                }

                // Hide Settings depending on other settings
                const examModeElement = document.querySelector('#id_exam_mode_enabled_value');
                if (examModeElement) {
                    examModeElement.addEventListener('change', function () {
                        set_display_option('#id_exam_mode_enabled_value', '#id_mod_ivsgrades');
                    });
                }
                
                // Hide Settings depending on other settings
                const annotationsElement = document.querySelector('#id_annotations_enabled_value');
                if (annotationsElement) {
                    annotationsElement.addEventListener('change', function () {
                        set_display_option('#id_annotations_enabled_value', '#id_mod_ivsnotification');
                    });
                }

                set_display_option('#id_annotations_enabled_value', '#id_mod_ivsnotification');
                set_display_option('#id_exam_mode_enabled_value', '#id_mod_ivsgrades');
                set_display_option_timing_mode('#id_match_question_enabled_value', '#fgroup_id_show_realtime_results');
                set_display_option_timing_mode('#id_match_question_enabled_value', '#fgroup_id_show_timing_take_summary');

                const matchQuestionElement = document.querySelector('#id_match_question_enabled_value');
                if (matchQuestionElement) {
                    matchQuestionElement.addEventListener('change', function () {
                        // Show timing mode checkboxes
                        set_display_option_timing_mode('#id_match_question_enabled_value', '#fgroup_id_show_realtime_results');
                        set_display_option_timing_mode('#id_match_question_enabled_value', '#fgroup_id_show_timing_take_summary');
                    });
                }

                if (panopto_data) {

                    let servername = panopto_data.servername;
                    let instancename = panopto_data.instancename;
                    let sessiongroupid = panopto_data.sessiongroupid;
                    let iframeURL = 'https://' + servername + 
                        '/Panopto/Pages/Sessions/EmbeddedUpload.aspx?playlistsEnabled=false&instance=' + 
                        instancename + '&folderID=' + sessiongroupid;

                    let btn = document.createElement("button");
                    btn.innerHTML = panopto_data.buttonname;
                    btn.classList.add('btn', 'btn-primary', 'panopto-selector');
                    btn.style.marginLeft = '20px';
                    
                    const panoptoVideoElement = document.querySelector('#id_panopto_video');
                    if (panoptoVideoElement && panoptoVideoElement.parentNode) {
                        panoptoVideoElement.parentNode.insertBefore(btn, panoptoVideoElement.nextSibling);
                    }

                    // The event to open the modal
                    ModalFactory.create({
                        type: ModalFactory.types.SAVE_CANCEL,
                        title: 'Panopto videos',
                        large: true,
                        body: Templates.render('mod_ivs/panopto_modal', {iframeurl: iframeURL}),
                    }, [btn])
                        .done(function (modal) {
                            modal.getRoot().on(ModalEvents.save, function (e) {

                                // Stop the default save button behaviour which is to close the modal.
                                e.preventDefault();
                                modal.hide();

                                if (selectedPanoptoVideo.ids && selectedPanoptoVideo.ids.length <= 1) {
                                    let panopto_video_name = selectedPanoptoVideo.names;
                                    let panopto_video_id = selectedPanoptoVideo.ids;
                                    let panopto_data_obj = JSON.stringify({
                                        'servername': servername,
                                        'instancename': instancename,
                                        'sessiongroupid': sessiongroupid,
                                        'videoname': panopto_video_name,
                                        'sessionId': panopto_video_id
                                    });
                                    
                                    const jsonField = document.querySelector('#id_panopto_video_json_field');
                                    const videoField = document.querySelector('#id_panopto_video');
                                    
                                    if (panopto_video_name && panopto_video_name.length == 1) {
                                        if (jsonField) {
                                            jsonField.value = panopto_data_obj;
                                        }
                                    } else {
                                        if (jsonField) {
                                            jsonField.value = '';
                                            jsonField.setAttribute('value', '');
                                        }
                                        if (videoField) {
                                            videoField.setAttribute('value', '');
                                        }
                                    }

                                    if (videoField) {
                                        videoField.value = panopto_video_name;
                                    }
                                }
                            });
                        });

                    window.addEventListener('message', (e) => {
                        let panopto_selected_video_data = JSON.parse(e.data);
                        selectedPanoptoVideo = JSON.parse(e.data);
                        if (panopto_selected_video_data.cmd === 'ready') {
                            let win = document.getElementById('panopto_iframe').contentWindow;
                            let message = {cmd: 'createEmbeddedFrame'};
                            win.postMessage(JSON.stringify(message), 'https://' + servername);
                        }
                        if (panopto_selected_video_data.cmd === 'deliveryList') {
                            const modalFooter = document.querySelector('.modal-footer');
                            const primaryBtn = modalFooter ? modalFooter.querySelector('.btn-primary') : null;
                            
                            if (primaryBtn) {
                                if (panopto_selected_video_data.ids.length > 1) {
                                    primaryBtn.setAttribute('disabled', 'true');
                                    primaryBtn.setAttribute('title', panopto_data.tooltip);
                                } else {
                                    primaryBtn.removeAttribute('disabled');
                                    primaryBtn.removeAttribute('title');
                                }
                            }
                        }
                    });
                }

            }
        };
    });

/**
 * Set visibility depending on checkbox state
 * @param {string} fieldSelector - The field selector
 * @param {string} elementSelector - The element selector
 */
function set_display_option(fieldSelector, elementSelector) {
    const field = document.querySelector(fieldSelector);
    const element = document.querySelector(elementSelector);
    
    if (field && element) {
        if (!field.checked) {
            element.style.display = 'none';
        } else {
            element.style.display = '';
        }
    }
}

/**
 * Hide and show timing mode settings
 * @param {string} fieldSelector - The field selector  
 * @param {string} elementSelector - The element selector
 */
function set_display_option_timing_mode(fieldSelector, elementSelector) {
    const field = document.querySelector(fieldSelector);
    const element = document.querySelector(elementSelector);
    
    if (field && element) {
        if (field.value != 2) {
            element.style.display = 'none';
        } else {
            element.style.display = '';
        }
    }
}
