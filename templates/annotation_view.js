(function ($) {

    $.fn.IvsMiniplayer = function () {

        return this.each(function () {
            var $el = $(this);
            var mp = new IvsMiniplayer();
            mp.init($el);
        });
    };
    IvsMiniplayer = function () {
    };

    IvsMiniplayer.prototype.init = function ($el) {
        this.$el = $el;
        var self = this;
        var data = $el.data("setup");
        this.$svg = $el.find("svg");
        this.$svg.hide();

        var $video = $el.find("video");
        var $img_preview = $el.find("img");

        if ($img_preview.length) {
            this.$container = $img_preview;

            $img_preview.on('load', function () {
                self.resize();
            }).on('error', function () {
                self.resize();
            });

            //fallback for cached images - they do not trigger the load event
            if ($img_preview[0].complete) {
                requestAnimationFrame(function () {
                    self.resize();
                });
            }

            return;
        }

        this.$container = $video;


        $video.get(0).addEventListener('loadedmetadata', function () {
            this.currentTime = data.comment_timestamp;

            self.resize();

        }, false);


    };

    IvsMiniplayer.prototype.resize = function () {

        this.$svg.css({
            'width': this.$container.width() + 'px',
            'height': this.$container.height() + 'px'
        });
        this.$svg.show();
    };

    $(document).ready(function () {
        $(".field-ivs-annotation-preview").IvsMiniplayer();
    });

})(jQuery);
