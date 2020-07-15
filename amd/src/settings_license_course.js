define(['jquery', 'core/modal_factory', 'core/modal_events'], function ($, ModalFactory, ModalEvents) {
    return {
        init: function ($params, $params1) {
            $('.ivs-license-course-remove').each(function (index) {
                $(this).click(function (e) {
                    if (!confirm($params['modal_confirm_string'])) {
                        e.preventDefault();
                    }
                });
            });
            $('.ivs-license-delete').each(function (index) {
                $(this).click(function (e) {
                    if (!confirm($params1['modal_confirm_delete'])) {
                        e.preventDefault();
                    }
                });
            });
        }
    }
});
