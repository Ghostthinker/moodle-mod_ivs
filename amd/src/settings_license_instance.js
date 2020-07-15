define(['jquery', 'mod_ivs/progressbar'], function ($, progressbar) {
    return {
        init: function (params) {

            $('.ivs-progress:not(.processed)').each(function () {
                var circle = new progressbar.Circle(this, {
                    color: '#1177d1',
                    strokeWidth: 6,
                    trailWidth: 5,
                    easing: 'easeInOut',
                    //easing: 'bounce',
                    duration: 1000,
                    text: {
                        // Initial value for text.
                        // Default: null
                        value: $(this).data('label'),

                        // Class name for text element.
                        // Default: 'progressbar-text'
                        className: 'progressbar__label',

                        // Inline CSS styles for the text element.
                        // If you want to modify all CSS your self, set null to disable
                        // all default styles.
                        // If the style option contains values, container is automatically
                        // set to position: relative. You can disable behavior this with
                        // autoStyleContainer: false
                        // If you specify anything in this object, none of the default styles
                        // apply
                        // Default: object speficied below
                        style: {
                            // Text color.
                            // Default: same as stroke color (options.color)
                            color: '#f00',
                            position: 'absolute',
                            left: '50%',
                            top: '50%',
                            padding: 0,
                            margin: 0,
                            // You can specify styles which will be browser prefixed
                            transform: {
                                prefix: true,
                                value: 'translate(-50%, -50%)'
                            }
                        },
                        // Only effective if the text.style is not null
                        // By default position: relative is applied to container if text
                        // is set. Setting this to false disables that feature.
                        autoStyleContainer: true,

                        // Only effective if the shape is SemiCircle.
                        // If true, baseline for text is aligned with bottom of
                        // the SVG canvas. If false, bottom line of SVG canvas
                        // is in the center of text.
                        // Default: true
                        alignToBottom: true
                    },
                });

                circle.animate($(this).data('progress'));
            }).addClass('processed');
        }
    };
});
