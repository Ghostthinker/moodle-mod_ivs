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

        var videoel = $video.get(0);
        if (!videoel) {
            return;
        }

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


    /**
     *
     */
    function renderPlayerIfInViewport(){

        $.each($('div.annotation-view-comment-container'), function() {

            const el = this;
            const observer = new window.IntersectionObserver(([entry]) => {
                var videoplaceholder = $(this).find('.video_placeholder')[0];
                var videolegit = $(this).find('video')[0];

                if (entry.isIntersecting && videoplaceholder) {
                    console.log("enter", this);
                    var videoelement = document.createElement('video');
                    var sourceelement = document.createElement('source');
                    var notsupportedbrowserelement = document.createElement('div');

                    videoelement.id = videoplaceholder.dataset.videotagid;

                    sourceelement.src = videoplaceholder.dataset.videosrc;
                    sourceelement.type = "video/mp4";

                    notsupportedbrowserelement.innerText = "Sorry, your browser or device is not supported!";

                    videoelement.appendChild(sourceelement);
                    videoelement.appendChild(notsupportedbrowserelement);

                    $(videoplaceholder).replaceWith(videoelement);

                    $(this).find('.field-ivs-annotation-preview').IvsMiniplayer();

                } else if(!entry.isIntersecting && videolegit) {

                    console.log("leave", this);
                    var placeholderelement = document.createElement('div');
                    var spinner = document.createElement('div');
                    spinner.classList.add('lds-dual-ring');
                    placeholderelement.appendChild(spinner);
                    placeholderelement.classList.add('video_placeholder');
                    placeholderelement.dataset.videotagid = videolegit.id;
                    placeholderelement.dataset.videosrc = $(videolegit).find('source')[0].src;
                    $(videolegit).next('svg').hide();
                    $(videolegit).replaceWith(placeholderelement);
                }
            }, {
                root: null,
                threshold: 0.0, // set offset 0.1 means trigger if atleast 10% of element in viewport
            });

            observer.observe(el);
        });
    }

    $(document).ready( () => {
        // render preview image drawings
        $(".field-ivs-annotation-preview").IvsMiniplayer();

        // render video fallback
        renderPlayerIfInViewport();

    });

})(jQuery);
