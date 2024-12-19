/*
 Initialize LaStudioProductGallery
 */
(function($) {
    'use strict';

    /**
     * Product gallery class.
     */
    var LaStudioProductGallery = function( $target, args ) {

        this.$target = $target;
        this.$images = $( '.woocommerce-product-gallery__image', $target );

        if(!$target.parent('.product--large-image').data('old_gallery')){

            var _old_html = $target.find('.woocommerce-product-gallery__wrapper').html();

            $target.parent('.product--large-image').data('old_gallery', _old_html).data('prev_gallery', _old_html);
        }

        if(!$target.parent('.product--large-image').data('gallery_action')){

            var _tmp_action = $target.find('.woocommerce-product-gallery__actions').clone();
            _tmp_action.find('.woocommerce-product-gallery__trigger').remove();

            var _old_html = '<div class="woocommerce-product-gallery__actions">';
            _old_html += _tmp_action.html();
            _old_html += '</div>';

            $target.parent('.product--large-image').data('gallery_action', _old_html);
        }

        this.$target.parent().attr('data-totalG', this.$images.length);

        // No images? Abort.
        if ( 0 === this.$images.length ) {
            this.$target.css( 'opacity', 1 );
            this.$target.parent().addClass('no-gallery');
            return;
        }
        if( 1 === this.$images.length ){
            this.$target.parent().addClass('no-gallery');
        }
        else{
            this.$target.parent().removeClass('no-gallery');
        }

        // Make this object available.
        $target.data( 'product_gallery', this );

        // Pick functionality to initialize...
        this.flexslider_enabled = true;

        if ($target.hasClass('no-slider-script') || $target.hasClass('force-disable-slider-script') ){
            this.flexslider_enabled = false;
        }

        //this.flexslider_enabled = false;
        this.zoom_enabled       = $.isFunction( $.fn.zoom ) && wc_single_product_params.zoom_enabled;
        this.photoswipe_enabled = typeof PhotoSwipe !== 'undefined' && wc_single_product_params.photoswipe_enabled;

        // ...also taking args into account.
        if ( args ) {
            this.flexslider_enabled = false === args.flexslider_enabled ? false : this.flexslider_enabled;
            this.zoom_enabled       = false === args.zoom_enabled ? false : this.zoom_enabled;
            this.photoswipe_enabled = false === args.photoswipe_enabled ? false : this.photoswipe_enabled;
        }

        if($target.hasClass('force-disable-slider-script')){
            this.flexslider_enabled = false;
            //this.zoom_enabled       = false;
        }

        this.thumb_verital = false;


        if(this.$images.length < 2){
            this.flexslider_enabled = false;
        }

        try {
            if(la_theme_config.product_single_design == 2){
                this.thumb_verital = true;
            }
        }catch (ex){
            this.thumb_verital = false;
        }

        this.parent_is_quickview = false;

        if($target.closest('.featherlight').length){
            this.thumb_verital = true;
            //this.zoom_enabled = false;
            this.parent_is_quickview = true;
        }

        // Bind functions to this.
        this.initSlickslider       = this.initSlickslider.bind( this );
        this.initZoom             = this.initZoom.bind( this );
        this.initPhotoswipe       = this.initPhotoswipe.bind( this );
        this.onResetSlidePosition = this.onResetSlidePosition.bind( this );
        this.getGalleryItems      = this.getGalleryItems.bind( this );
        this.openPhotoswipe       = this.openPhotoswipe.bind( this );

        if ( this.flexslider_enabled ) {

            if($.isFunction( $.fn.slick )){
                this.initSlickslider();
                $target.on( 'woocommerce_gallery_reset_slide_position', this.onResetSlidePosition );
            }
            else{
                var _self = this;
                LaStudio.global.loadDependencies([ LaStudio.global.loadJsFile('slick') ], function () {
                    _self.initSlickslider();
                    $target.on( 'woocommerce_gallery_reset_slide_position', _self.onResetSlidePosition );
                } );
            }
        }
        else {
            if(this.parent_is_quickview){
                $('body').removeClass('lightcase--pending').addClass('lightcase--completed');
            }
            else{
                setTimeout(function(){
                    $('body').trigger("la_sticky:recalc");
                },200);
            }

            this.$target.css( 'opacity', 1 );
            $target.removeClass('la-rebuild-product-gallery').parent().removeClass('swatch-loading');
        }

        if ( this.zoom_enabled ) {
            this.initZoom();
            $target.on( 'woocommerce_gallery_init_zoom', this.initZoom );
        }

        if ( this.photoswipe_enabled ) {
            this.initPhotoswipe();
        }

        this.$images.find('a').attr('href', 'javascript:;');

    };

    /**
     * Initialize flexSlider.
     */
    LaStudioProductGallery.prototype.initSlickslider = function() {
        var images  = this.$images,
            $target = this.$target,
            $slides = $target.find('.woocommerce-product-gallery__wrapper'),
            $thumb = $target.parent().find('.la-thumb-inner'),
            rand_num = Math.floor((Math.random() * 100) + 1),
            thumb_id = 'la_woo_thumb_' + rand_num,
            target_id = 'la_woo_target_' + rand_num,
            is_quickview = this.parent_is_quickview;

        $slides.attr('id', target_id);
        $thumb.attr('id', thumb_id);

        images.each(function(){
            var $that = $(this);
            var video_code = $that.find('a[data-videolink]').data('videolink');
            var image_h = $slides.css('height');
            var thumb_html = '<div class="la-thumb"><img src="'+ $that.attr('data-thumb') +'"/></div>';
            if (typeof video_code != 'undefined' && video_code) {

                $that.unbind('click');
                $that.find('.zoomImg').css({
                    'display': 'none!important'
                });

                if (video_code.indexOf("http://selfhosted/") == 0) {
                    video_code = video_code.replace('http://selfhosted/', '');
                    thumb_html = '<div class="la-thumb has-thumb-video"><div><img src="'+ $that.attr('data-thumb') +'"/><span class="play-overlay"><i class="fa fa-play-circle-o" aria-hidden="true"></i></span></div></div>';
                    $that.append('<video class="selfhostedvid  noLightbox" width="460" height="315" controls preload="auto"><source src="' + video_code + '" /></video>');
                    $that.attr('data-video', '<div class="la-media-wrapper"><video class="selfhostedvid  noLightbox" width="460" height="315" controls preload="auto"><source src="' + video_code + '" /></video></div>');
                } else {
                    thumb_html = '<div class="la-thumb has-thumb-video"><div><img src="'+ $that.attr('data-thumb') +'"/><span class="play-overlay"><i class="fa-play-circle-o"></i></span></div></div>';
                    $that.append('<iframe src ="' + video_code + '" width="460" " style="height:' + image_h + '; z-index:999999;" frameborder="no"></iframe>');
                    $that.attr('data-video', '<div class="la-media-wrapper"><iframe src ="' + video_code + '" width="980" height="551" frameborder="no" allowfullscreen></iframe></div>');
                }

                $that.find('img').css({
                    'opacity': '0',
                    'z-index': '-1'
                });

                $that.find('iframe').next().remove();
            }
            $thumb.append(thumb_html);
        });

        var _thumb_column = $.extend({
            'mobile'            : 3,
            'mobile_landscape'  : 3,
            'tablet'            : 3,
            'laptop'            : 3,
            'desktop'           : 3
        }, (JSON.parse(la_theme_config.product_gallery_column) || {}) );

        var _thumb_carousel_config = {
            infinite: false,
            slidesToShow: parseInt(_thumb_column['desktop']),
            slidesToScroll: 1,
            asNavFor: '#' + target_id,
            dots: false,
            arrows: true,
            focusOnSelect: true,
            prevArrow: '<span class="slick-prev"><i class="lastudioicon-left-arrow"></i></span>',
            nextArrow: '<span class="slick-next"><i class="lastudioicon-right-arrow"></i></span>',
            vertical: this.thumb_verital,
            responsive: [
                {
                    breakpoint: 1500,
                    settings: {
                        vertical: this.thumb_verital,
                        slidesToShow: parseInt(_thumb_column['laptop'])
                    }
                },
                {
                    breakpoint: 1300,
                    settings: {
                        vertical: this.thumb_verital,
                        slidesToShow: parseInt(_thumb_column['tablet'])
                    }
                },
                {
                    breakpoint: 800,
                    settings: {
                        vertical: false,
                        slidesToShow: parseInt(_thumb_column['mobile_landscape'])
                    }
                },
                {
                    breakpoint: 577,
                    settings: {
                        vertical: false,
                        slidesToShow: parseInt(_thumb_column['mobile'])
                    }
                }
            ]
        };

        if(!this.thumb_verital){
            _thumb_carousel_config.infinite = false;
            _thumb_carousel_config.centerMode = false;
            _thumb_carousel_config.centerPadding = '0px';
        }

        var _slide_carousel_config = {
            infinite: false,
            swipe: true,
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: true,
            asNavFor: '#' + thumb_id,
            prevArrow: '<span class="slick-prev"><i class="lastudioicon-left-arrow"></i></span>',
            nextArrow: '<span class="slick-next"><i class="lastudioicon-right-arrow"></i></span>',
            adaptiveHeight: (la_theme_config.product_single_design == 1) ? true : false
        };
        if(!this.thumb_verital){
            _slide_carousel_config.infinite = false;
        }
        $slides.find('a').attr('href', 'javascript:;');
        if(la_theme_config.product_single_design != 5){
            $thumb.slick(_thumb_carousel_config);
        }
        else{
            if(is_quickview){
                _thumb_column = {
                    'mobile'            : 1,
                    'mobile_landscape'  : 1,
                    'tablet'            : 1,
                    'laptop'            : 1,
                    'desktop'           : 1
                }
            }

            _slide_carousel_config.slidesToShow = parseInt(_thumb_column['desktop']);
            _slide_carousel_config.slidesToScroll = 1;
            _slide_carousel_config.infinite = true;
            _slide_carousel_config.asNavFor = '';
            _slide_carousel_config.centerMode = true;
            _slide_carousel_config.centerPadding = '0';
            _slide_carousel_config.responsive = [
                {
                    breakpoint: 1500,
                    settings: {
                        vertical: this.thumb_verital,
                        slidesToShow: parseInt(_thumb_column['laptop'])
                    }
                },
                {
                    breakpoint: 1300,
                    settings: {
                        vertical: false,
                        slidesToShow: parseInt(_thumb_column['tablet'])
                    }
                },
                {
                    breakpoint: 800,
                    settings: {
                        vertical: false,
                        slidesToShow: parseInt(_thumb_column['mobile_landscape'])
                    }
                },
                {
                    breakpoint: 577,
                    settings: {
                        vertical: false,
                        slidesToShow: parseInt(_thumb_column['mobile'])
                    }
                }
            ];
        }

        $slides.slick(_slide_carousel_config);

        LaStudio.global.LazyLoad(
            $target.parent(),
            {
                rootMargin: '100px',
                load: function(){},
                complete: function () {

                    LaStudio.global.eventManager.publish('LaStudio:Component:LazyLoadImage', [$target.parent()]);
                    $target.css( 'opacity', 1 );
                    if(la_theme_config.product_single_design != 5) {
                        $thumb.slick('setPosition');
                    }

                    $target.parent().removeClass('swatch-loading');

                    if(is_quickview){
                        setTimeout(function(){
                            $slides.resize();
                            setTimeout(function(){
                                $('body').removeClass('lightcase--pending').addClass('lightcase--completed');
                            }, 50);
                        }, 150);
                    }
                    else{
                        setTimeout(function(){
                            $('body').trigger("la_sticky:recalc");
                        },200);
                    }
                }
            }
        ).observe();
    };

    /**
     * Init zoom.
     */
    LaStudioProductGallery.prototype.initZoom = function() {
        this.initZoomForTarget( this.$images );
    };

    LaStudioProductGallery.prototype.initZoomForTarget = function( zoomTarget ) {
        if ( ! this.zoom_enabled ) {
            return false;
        }

        var galleryWidth = this.$target.width(),
            zoomEnabled  = false,
            zoom_options;

        $( zoomTarget ).each( function( index, target ) {
            var image = $( target ).find( 'img' );

            if ( image.data( 'large_image_width' ) > galleryWidth ) {
                zoomEnabled = true;
                return false;
            }
        } );

        // But only zoom if the img is larger than its container.
        if ( zoomEnabled ) {
            try{
                zoom_options = $.extend( {
                    touch: false
                }, wc_single_product_params.zoom_options );
            }
            catch (ex){
                zoom_options = {
                    touch: false
                };
            }

            if ( 'ontouchstart' in document.documentElement ) {
                zoom_options.on = 'click';
            }

            zoomTarget.trigger( 'zoom.destroy' );
            zoomTarget.zoom( zoom_options );
        }
    };

    /**
     * Init PhotoSwipe.
     */
    LaStudioProductGallery.prototype.initPhotoswipe = function() {
        if ( this.zoom_enabled && this.$images.length > 0 ) {
            this.$target.find('.woocommerce-product-gallery__actions').prepend( '<a href="#" class="woocommerce-product-gallery__trigger"><span><i class="lastudioicon-full-screen"></i></span></a>' );
            this.$target.on( 'click', '.woocommerce-product-gallery__trigger', this.openPhotoswipe );
        }
        this.$target.on( 'click', '.woocommerce-product-gallery__image a', this.openPhotoswipe );
    };

    /**
     * Reset slide position to 0.
     */
    LaStudioProductGallery.prototype.onResetSlidePosition = function() {
        this.$target.parent().removeClass('swatch-loading');
        this.$target.find('.woocommerce-product-gallery__wrapper').slick('slickGoTo', 0);
    };

    /**
     * Get product gallery image items.
     */
    LaStudioProductGallery.prototype.getGalleryItems = function() {
        var $slides = this.$images,
            items   = [];

        if ( $slides.length > 0 ) {
            $slides.each( function( i, el ) {
                var img = $( el ).find( 'img' ),
                    large_image_src = img.attr( 'data-large_image' ),
                    large_image_w   = img.attr( 'data-large_image_width' ),
                    large_image_h   = img.attr( 'data-large_image_height' ),
                    item            = {
                        src: large_image_src,
                        w:   large_image_w,
                        h:   large_image_h,
                        title: img.attr( 'title' )
                    };
                if($(el).attr('data-video')){
                    item = {
                        html: $(el).attr('data-video')
                    };
                }
                items.push( item );
            } );
        }

        return items;
    };

    /**
     * Open photoswipe modal.
     */
    LaStudioProductGallery.prototype.openPhotoswipe = function( e ) {
        e.preventDefault();

        var pswpElement = $( '.pswp' )[0],
            items       = this.getGalleryItems(),
            eventTarget = $( e.target ),
            clicked,
            index;

        if ( ! eventTarget.is( '.woocommerce-product-gallery__trigger' ) ) {
            clicked = eventTarget.closest( '.woocommerce-product-gallery__image' );
            index = $( clicked ).index();
            if($('.la-p-single-5').length){
                index = parseInt(clicked.attr('data-slick-index'));
                if(index < 0){
                    index = items.length + index;
                }
            }
        }
        else {
            clicked = this.$target.find( '.slick-current' ) || this.$target.find( '.woocommerce-product-gallery__image' ).first();
            index = $( clicked ).index();
        }



        var options = {
            index:                 index,
            shareEl:               false,
            closeOnScroll:         false,
            history:               false,
            hideAnimationDuration: 0,
            showAnimationDuration: 0
        };

        // Initializes and opens PhotoSwipe.
        var photoswipe = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options );
        photoswipe.init();
    };

    /**
     * Function to call la_product_gallery on jquery selector.
     */
    $.fn.lastudio_product_gallery = function( args ) {
        new LaStudioProductGallery( this, args );
        return this;
    };

}(jQuery));