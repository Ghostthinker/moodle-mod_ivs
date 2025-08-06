define([], function() {
    return {
        init: function() {
            // Vanilla JavaScript equivalent of jQuery plugin
            function IvsMiniplayer() {
                this.init = function(el) {
                    this.el = el;
                    var self = this;
                    var data = JSON.parse(el.dataset.setup || '{}');
                    this.svg = el.querySelector('svg');
                    if (this.svg) {
                        this.svg.style.display = 'none';
                    }

                    var video = el.querySelector('video');
                    var imgPreview = el.querySelector('img');

                    if (imgPreview) {
                        this.container = imgPreview;

                        imgPreview.addEventListener('load', function() {
                            self.resize();
                        });
                        imgPreview.addEventListener('error', function() {
                            self.resize();
                        });

                        // Fallback for cached images - they do not trigger the load event
                        if (imgPreview.complete) {
                            requestAnimationFrame(function() {
                                self.resize();
                            });
                        }

                        return;
                    }

                    this.container = video;

                    if (!video) {
                        return;
                    }

                    video.addEventListener('loadedmetadata', function() {
                        video.currentTime = data.comment_timestamp;
                        self.resize();
                    }, false);
                };

                this.resize = function() {
                    if (this.svg && this.container) {
                        this.svg.style.width = this.container.offsetWidth + 'px';
                        this.svg.style.height = this.container.offsetHeight + 'px';
                        this.svg.style.display = 'block';
                    }
                };
            }

            // Initialize miniplayer for elements
            function initMiniplayer() {
                const elements = document.querySelectorAll('.field-ivs-annotation-preview');
                elements.forEach(function(el) {
                    const mp = new IvsMiniplayer();
                    mp.init(el);
                });
            }

            /**
             * Render player if in viewport
             */
            function renderPlayerIfInViewport() {
                const containers = document.querySelectorAll('div.annotation-view-comment-container');

                containers.forEach(function(container) {
                    const observer = new window.IntersectionObserver(([entry]) => {
                        var videoPlaceholder = container.querySelector('.video_placeholder');
                        var videoLegit = container.querySelector('video');

                        if (entry.isIntersecting && videoPlaceholder) {
                            console.log('enter', container);
                            var videoElement = document.createElement('video');
                            var sourceElement = document.createElement('source');
                            var notSupportedBrowserElement = document.createElement('div');

                            videoElement.id = videoPlaceholder.dataset.videotagid;

                            sourceElement.src = videoPlaceholder.dataset.videosrc;
                            sourceElement.type = 'video/mp4';

                            notSupportedBrowserElement.innerText = 'Sorry, your browser or device is not supported!';

                            videoElement.appendChild(sourceElement);
                            videoElement.appendChild(notSupportedBrowserElement);

                            videoPlaceholder.parentNode.replaceChild(videoElement, videoPlaceholder);

                            // Re-initialize miniplayer for the new video element
                            const previewElement = container.querySelector('.field-ivs-annotation-preview');
                            if (previewElement) {
                                const mp = new IvsMiniplayer();
                                mp.init(previewElement);
                            }

                        } else if (!entry.isIntersecting && videoLegit) {
                            console.log('leave', container);
                            var placeholderElement = document.createElement('div');
                            var spinner = document.createElement('div');
                            spinner.classList.add('lds-dual-ring');
                            placeholderElement.appendChild(spinner);
                            placeholderElement.classList.add('video_placeholder');
                            placeholderElement.dataset.videotagid = videoLegit.id;

                            const source = videoLegit.querySelector('source');
                            if (source) {
                                placeholderElement.dataset.videosrc = source.src;
                            }

                            const svg = videoLegit.nextElementSibling;
                            if (svg && svg.tagName === 'svg') {
                                svg.style.display = 'none';
                            }

                            videoLegit.parentNode.replaceChild(placeholderElement, videoLegit);
                        }
                    }, {
                        root: null,
                        threshold: 0.0, // Set offset 0.1 means trigger if atleast 10% of element in viewport
                    });

                    observer.observe(container);
                });
            }

            // Initialize when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    // Render preview image drawings
                    initMiniplayer();

                    // Render video fallback
                    renderPlayerIfInViewport();
                });
            } else {
                // DOM is already ready
                // Render preview image drawings
                initMiniplayer();

                // Render video fallback
                renderPlayerIfInViewport();
            }
        }
    };
}); 