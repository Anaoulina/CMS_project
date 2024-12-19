(function ($) {
	"use strict";

	$(window).on('elementor/frontend/init', function () {
		window.elementorFrontend.hooks.addAction('frontend/element_ready/lakit-counter.default', function ($scope) {
			const $el = $scope.find('.lakit-counter-number')
			let animation_opt = $el.data('animation') ?? 'count',
				duration_opt = $el.data('duration') ?? 1000,
				start_opt = $el.data('fromValue') ?? 0,
				to_opt = $el.data('toValue') ?? 0,
				delimiter_opt = $el.data('delimiter') ?? ''

			let format_opt = !delimiter_opt ?'(ddd).dd' : `(${delimiter_opt}ddd).dd`

			const odometerInstance = new Odometer({
				el: $el.get(0),
				value: start_opt,
				format: format_opt,
				duration: duration_opt,
				animation: animation_opt,
				theme: 'lakit',
			})

			const intersectionObserver = elementorModules.utils.Scroll.scrollObserver({
				callback: (evt) => {
					if (evt.isInViewport) {
						intersectionObserver.unobserve($el.get(0));
						odometerInstance.update(to_opt)
					}
				}
			})
			intersectionObserver.observe($el.get(0))
		})
	});

}(jQuery));