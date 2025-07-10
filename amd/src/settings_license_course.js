define(['core/modal_factory', 'core/modal_events'], function (ModalFactory, ModalEvents) {
    return {
        init: function (params, params1) {
            // Handle license course remove buttons
            const removeButtons = document.querySelectorAll('.ivs-license-course-remove');
            removeButtons.forEach(function (button) {
                button.addEventListener('click', function (e) {
                    if (!confirm(params['modal_confirm_string'])) {
                        e.preventDefault();
                    }
                });
            });

            // Handle license delete buttons
            const deleteButtons = document.querySelectorAll('.ivs-license-delete');
            deleteButtons.forEach(function (button) {
                button.addEventListener('click', function (e) {
                    if (!confirm(params1['modal_confirm_delete'])) {
                        e.preventDefault();
                    }
                });
            });
        }
    };
});
