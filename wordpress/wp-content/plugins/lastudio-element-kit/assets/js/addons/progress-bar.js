(function ($) {
    "use strict";

    $(window).on('elementor/frontend/init', function () {

        window.elementorFrontend.hooks.addAction('frontend/element_ready/lakit-progress-bar.default', function ($scope) {
            var $target = $scope.find('.lakit-progress-bar')

            const callback = ( $elm ) => {
                var animeObject = {charged: 0},
                    percent = $elm.data('percent'),
                    type = $target.data('type'),
                    $statusBar = $('.lakit-progress-bar__status-bar', $elm),
                    $percent = $('.lakit-progress-bar__percent-value', $elm)

                $elm.addClass('e-lazyloaded')

                if ('type-7' === type) {
                    $statusBar.css({
                        'height': percent + '%'
                    });
                } else {
                    $statusBar.css({
                        'width': percent + '%'
                    });
                }
                anime({
                    targets: animeObject,
                    charged: percent,
                    round: 1,
                    duration: 1000,
                    easing: 'easeInOutQuad',
                    update: function () {
                        $percent.html(animeObject.charged);
                    }
                });
            }
            const E_LazyLoad = new IntersectionObserver( ( entries ) => {
                entries.forEach( ( entry ) => {
                    if ( entry.isIntersecting ) {
                        let elm = entry.target;
                        if( elm ) {
                            callback( $(elm) )
                        }
                        E_LazyLoad.unobserve( entry.target );
                    }
                });
            }, { rootMargin: '100px' } );
            E_LazyLoad.observe($scope.find('.lakit-progress-bar').get(0))
        });
    });

}(jQuery));