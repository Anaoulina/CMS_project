/*
 Initialize LaStudio Swatches
 */
(function($) {
    'use strict';

    function variation_calculator(variation_attributes, product_variations) {

        this.recalc_needed = true;
        this.variation_attributes = variation_attributes;

        //The actual variations that are configured in woocommerce.
        this.variations_available = product_variations;

        //Stores the calculation result for attribute + values that are available based on the selected attributes.
        this.variations_current = {};

        //Stores the selected attributes + values
        this.variations_selected = {};

        //Reset all the attributes + values to disabled.  They will be reenabled durring the calcution.
        this.reset_current = function () {
            for (var attribute in this.variation_attributes) {
                this.variations_current[attribute] = {};
                for (var av = 0; av < this.variation_attributes[attribute].length; av++) {
                    this.variations_current[attribute.toString()][this.variation_attributes[attribute][av].toString()] = 0;
                }
            }
        };

        //Do the things to update the variations_current object with attributes + values which are enabled.
        this.update_current = function () {
            this.reset_current();
            for (var i = 0; i < this.variations_available.length; i++) {
                if (!this.variations_available[i].variation_is_active) {
                    continue; //Variation is unavailable, probably out of stock.
                }

                //the variation attributes for the product this variation.
                var variation_attributes = this.variations_available[i].attributes;

                //loop though each variation attribute, turning on and off attributes which won't be available.
                for (var attribute in variation_attributes) {

                    var maybe_available_attribute_value = variation_attributes[attribute];
                    var selected_value = this.variations_selected[attribute];

                    if (selected_value && selected_value == maybe_available_attribute_value) {
                        this.variations_current[attribute][maybe_available_attribute_value] = 1; //this is a currently selected attribute value
                    } else {

                        var result = true;

                        /*

                         Loop though any other item that is selected,
                         checking to see if the attribute value does not match one of the attributes for this variation.
                         If it does not match the attributes for this variation we do nothing.
                         If none have matched at the end of these loops, the atttribute_option will remain off and unavailable.

                         */
                        for (var other_selected_attribute in this.variations_selected) {

                            if (other_selected_attribute == attribute) {
                                //We are looking to see if any attribute that is selected will cause this to fail.
                                //Continue the loop since this is the attribute from above and we don't need to check against ourselves.
                                continue;
                            }

                            //Grab the value that is selected for the other attribute.
                            var other_selected_attribute_value = this.variations_selected[other_selected_attribute];

                            //Grab the current product variations attribute value for the other selected attribute we are checking.
                            var other_available_attribute_value = variation_attributes[other_selected_attribute];

                            if (other_selected_attribute_value) {
                                if (other_available_attribute_value) {
                                    if (other_selected_attribute_value != other_available_attribute_value) {
                                        /*
                                         The value this variation has for the "other_selected_attribute" does not match.
                                         Since it does not match it does not allow us to turn on an available attribute value.

                                         Set the result to false so we skip turning anything on.

                                         Set the result to false so that we do not enable this attribute value.

                                         If the value does match then we know that the current attribute we are looping through
                                         might be available for us to set available attribute values.
                                         */
                                        result = false;
                                        //Something on this variation didn't match the current selection, so we don't care about any of it's attributes.
                                    }
                                }
                            }
                        }

                        /**
                         After checking this attribute against this variation's attributes
                         we either have an attribute which should be enabled or not.

                         If the result is false we know that something on this variation did not match the currently selected attribute values.

                         **/
                        if (result) {
                            if (maybe_available_attribute_value === "") {
                                for (var av in this.variations_current[attribute]) {
                                    this.variations_current[attribute][av] = 1;
                                }

                            } else {
                                this.variations_current[attribute][maybe_available_attribute_value] = 1;
                            }
                        }

                    }
                }
            }

            this.recalc_needed = false;
        };

        this.get_current = function () {
            if (this.recalc_needed) {
                this.update_current();
            }
            return this.variations_current;
        };

        this.reset_selected = function () {
            this.recalc_needed = true;
            this.variations_selected = {};
        }

        this.set_selected = function (key, value) {
            this.recalc_needed = true;
            this.variations_selected[key] = value;
        };

        this.get_selected = function () {
            return this.variations_selected;
        }
    }

    function la_generator_gallery_html( variation, is_in_widget ){
        var _html = '';
        if( typeof variation !== "undefined" && $.isArray(variation.la_additional_images) ){
            $.each(variation.la_additional_images, function(idx, val){
                _html += '<div data-thumb="'+val.thumb[0]+'" class="woocommerce-product-gallery__image">';
                if(!is_in_widget){
                    _html += '<div class="zoomouter"><div class="zoominner">';
                }
                _html += '<a href="'+val.large[0]+'" data-videolink="'+val.videolink+'">';
                _html += '<img ';
                _html += 'width="'+val.single[1]+'" ';
                _html += 'height="'+val.single[2]+'" ';
                _html += 'src="'+val.single[0]+'" ';
                _html += 'class="attachment-shop_single size-shop_single" ';
                _html += 'alt="'+val.alt+'" ';
                _html += 'title="'+val.title+'" ';
                _html += 'data-caption="'+val.caption+'" ';
                _html += 'data-src="'+val.large[0]+'" ';
                _html += 'data-large_image="'+val.large[0]+'" ';
                _html += 'data-large_image_width="'+val.large[1]+'" ';
                _html += 'data-large_image_height="'+val.large[2]+'" ';
                _html += 'srcset="'+val.srcset+'" ';
                _html += 'sizes="'+val.sizes+'" ';
                _html += '</a>';
                if(!is_in_widget) {
                    _html += '</div></div>';
                }
                _html += '</div>';
            });
        }
        return _html;
    }

    function la_update_swatches_gallery($form, variation ){
        var $product_selector = $form.closest('.product'),
            $main_image_col = $product_selector.find('.woocommerce-product-gallery.images').parent(),
            _html = '',
            $product = false,
            is_in_widget = false;

        if($form.closest('.product_item').length){
            $product = $form.closest('.product_item');
        }

        if($main_image_col.closest('.elementor-widget').length){
            is_in_widget = true;
        }

        if(variation !== null){
            _html = la_generator_gallery_html(variation, is_in_widget);
        }
        else{
            var _old_gallery = $main_image_col.data('old_gallery') || false;
            if(_old_gallery){
                _html = _old_gallery;
            }
            else{
                $main_image_col.data('old_gallery', $main_image_col.find('.woocommerce-product-gallery__wrapper').html());
            }
        }
        if (_html != '' && !$product) {

            if(!!$main_image_col.data('prev_gallery')){

                var $_oldGalleryObject = $($main_image_col.data('prev_gallery')),
                    $_newGalleryObject = $(_html);

                var _donot_swap = true;

                if($_oldGalleryObject.length == $_newGalleryObject.length){
                    for (var idx = 0; idx < $_oldGalleryObject.length; idx++){
                        if($($_oldGalleryObject[idx]).attr('data-thumb') != $($_newGalleryObject[idx]).attr('data-thumb')){
                            _donot_swap = false;
                        }
                    }
                }
                else{
                    _donot_swap = false;
                }

                if(_donot_swap){
                    return;
                }

            }

            $main_image_col.data('prev_gallery', _html);

            var _html2 = '<div class="woocommerce-product-gallery--with-images woocommerce-product-gallery la-woo-product-gallery images" data-columns="'+$main_image_col.find('.woocommerce-product-gallery.images').data('columns')+'">';
            if(!!$main_image_col.data('gallery_action')){
                _html2 += $main_image_col.data('gallery_action');
            }
            _html2 += '<figure class="woocommerce-product-gallery__wrapper">'+_html+'</figure><div class="la_woo_loading"><div class="la-loader spinner3"><div class="dot1"></div><div class="dot2"></div><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div></div>';

            $main_image_col.removeAttr('data-element-loaded').css({
                'max-height': $main_image_col.height(),
                'min-height': $main_image_col.height()
            }).addClass('swatch-loading');

            $main_image_col.html(_html2);
            var $la_gallery_selector = $main_image_col.find('.woocommerce-product-gallery.images');

            try{
                $la_gallery_selector.wc_product_gallery().addClass('swatch-loaded');
            }catch (ex){}

            $main_image_col.css({
                'max-height': 'none',
                'min-height': '200px'
            }).removeClass('swatch-loading');

            $('div.product').first().get(0).scrollIntoView({ inline: "nearest", block: "start", behavior: "smooth"});

            if(typeof LaStudioKits !== "undefined" && is_in_widget){
                LaStudioKits.wooGallery($main_image_col.closest('.elementor-widget'));
            }
            LaStudioWooCommerce.InitGallery($main_image_col.closest('.single-product-article'), false);
        }
    }

    $.fn.lastudio_variation_form = function () {
        var $form = this;
        var $product_id = parseInt($form.data('product_id'), 10);
        var calculator = null;
        var $use_ajax = false;
        var $swatches_xhr = null;
        var is_in_list = false;

        if($form.closest('.product_item').length){
            is_in_list = true;
        }
        $form.addClass('la-init-swatches');

        $form.find('td.label').each(function(){
            var $label = $(this).find('label');
            $label.append('<span class="swatch-label"></span>');
        });

        if(is_in_list){
            var p_link = $form.closest('.product_item').find('a.woocommerce-loop-product__link').first().attr('href') || $form.attr('action');
            $form.find('.swatch-control').each(function (){
                $(this).append('<div class="swatch-wrapper-more"><a href="'+p_link+'"><i class="lastudioicon-i-add"></i><span>'+(la_theme_config.i18n.more_text || 'more')+'</span></a></div>');
            })
        }

        $form.on('bind_calculator', function () {

            var $product_variations = $form.data('product_variations');
            $use_ajax = $product_variations === false;

            if ($use_ajax) {
                $form.block({message: null, overlayCSS: {background: '#fff', opacity: 0.6}});
            }

            var attribute_keys = {};

            //Set the default label.
            $form.find('.select-option.selected').each(function (index, el) {
                var $this = $(this);

                //Get the wrapper select div
                var $option_wrapper = $this.closest('div.select').eq(0);
                var $label = $option_wrapper.closest('tr').find('.swatch-label').eq(0);
                var $la_select_box = $option_wrapper.find('select').first();

                // Decode entities
                var attr_val = $('<div/>').html($this.data('value')).text();

                // Add slashes
                attr_val = attr_val.replace(/'/g, '\\\'');
                attr_val = attr_val.replace(/"/g, '\\\"');

                if ($label) {
                    $label.html($la_select_box.children("[value='" + attr_val + "']").eq(0).text());
                }
                $la_select_box.trigger('change');
            });

            $form.find('.variations select').each(function (index, el) {
                var $current_attr_select = $(el);
                var current_attribute_name = $current_attr_select.data('attribute_name') || $current_attr_select.attr('name');

                attribute_keys[current_attribute_name] = [];

                //Build out a list of all available attributes and their values.
                var current_options = '';
                current_options = $current_attr_select.find('option:gt(0)').get();

                if (current_options.length) {
                    for (var i = 0; i < current_options.length; i++) {
                        var option = current_options[i];
                        attribute_keys[current_attribute_name].push($(option).val());
                    }
                }
            });

            if ($use_ajax) {
                if ($swatches_xhr) {
                    $swatches_xhr.abort();
                }

                var data = {
                    product_id: $product_id,
                    action: 'la_swatch_get_product_variations'
                };

                $swatches_xhr = $.ajax({
                    url: la_theme_config.ajax_url,
                    type: 'POST',
                    data: data,
                    success: function (response) {
                        calculator = new variation_calculator(attribute_keys, response.data, null, null);
                        $form.unblock();
                    }
                });
            } else {
                calculator = new variation_calculator(attribute_keys, $product_variations, null, null);
            }

            $form.trigger('woocommerce_variation_has_changed');
        });

        $form
            .on('change', '.wc-default-select', function(e){
                var $__that = $(this);
                var $label = $__that.closest('tr').find('.swatch-label').eq(0);
                if($__that.val() != ''){
                    $label.html($__that.find('option:selected').html());
                }else{
                    $label.html('');
                }
            });

        $form.find('.wc-default-select').trigger('change');

        $form
        // On clicking the reset variation button
            .on('click', '.reset_variations', function () {
                $form.find('.swatch-label').html('');
                $form.find('.select-option').removeClass('selected');
                $form.find('.radio-option').prop('checked', false);
                $('span.price', $('.single-price-wrapper[data-product_id="'+$form.data('product_id')+'"]')).remove();
                return false;
            })
            .on('click', '.select-option', function (e) {
                e.preventDefault();

                var $this = $(this);

                //Get the wrapper select div
                var $option_wrapper = $this.closest('div.select').eq(0);
                var $label = $option_wrapper.closest('tr').find('.swatch-label').eq(0);
                var $la_select_box = $option_wrapper.find('select').first();
                if ($this.hasClass('disabled')) {
                    return false;
                }
                else if ($this.hasClass('selected')) {
                    $this.removeClass('selected');
                    $la_select_box.children('option:eq(0)').prop("selected", "selected").change();
                    if ($label) {
                        $label.html('');
                    }
                }
                else {

                    $option_wrapper.find('.select-option').removeClass('selected');
                    //Set the option to selected.
                    $this.addClass('selected');

                    // Decode entities
                    var attr_val = $('<div/>').html($this.data('value')).text();

                    // Add slashes
                    attr_val = attr_val.replace(/'/g, '\\\'');
                    attr_val = attr_val.replace(/"/g, '\\\"');

                    $la_select_box.trigger('focusin').children("[value='" + attr_val + "']").prop("selected", "selected").change();
                    if ($label) {
                        $label.html($la_select_box.children("[value='" + attr_val + "']").eq(0).text());
                    }
                }
            })
            .on('change', '.radio-option', function (e) {

                var $this = $(this);

                //Get the wrapper select div
                var $option_wrapper = $this.closest('div.select').eq(0);

                //Select the option.
                var $la_select_box = $option_wrapper.find('select').first();

                // Decode entities
                var attr_val = $('<div/>').html($this.val()).text();

                // Add slashes
                attr_val = attr_val.replace(/'/g, '\\\'');
                attr_val = attr_val.replace(/"/g, '\\\"');

                $la_select_box.trigger('focusin').children("[value='" + attr_val + "']").prop("selected", "selected").change();


            })
            .on('woocommerce_variation_has_changed', function () {
                if (calculator === null) {
                    return;
                }

                $form.find('.variations select').each(function () {
                    var attribute_name = $(this).data('attribute_name') || $(this).attr('name');
                    calculator.set_selected(attribute_name, $(this).val());
                });

                var current_options = calculator.get_current();

                //Grey out or show valid options.
                $form.find('div.select').each(function (index, element) {
                    var $la_select_box = $(element).find('select').first();

                    var attribute_name = $la_select_box.data('attribute_name') || $la_select_box.attr('name');
                    var avaiable_options = current_options[attribute_name];

                    $(element).find('div.select-option').each(function (index, option) {
                        if (!avaiable_options[$(option).data('value')]) {
                            $(option).addClass('disabled', 'disabled');
                        } else {
                            $(option).removeClass('disabled');
                        }
                    });

                    $(element).find('input.radio-option').each(function (index, option) {
                        if (!avaiable_options[$(option).val()]) {
                            $(option).attr('disabled', 'disabled');
                            $(option).parent().addClass('disabled', 'disabled');
                        } else {
                            $(option).removeAttr('disabled');
                            $(option).parent().removeClass('disabled');
                        }
                    });
                });

                if ($use_ajax) {
                    //Manage a regular  default select list.
                    // WooCommerce core does not do this if it's using AJAX for it's processing.
                    $form.find('.wc-default-select').each(function (index, element) {
                        var $la_select_box = $(element);

                        var attribute_name = $la_select_box.data('attribute_name') || $la_select_box.attr('name');
                        var avaiable_options = current_options[attribute_name];

                        $la_select_box.find('option:gt(0)').removeClass('attached');
                        $la_select_box.find('option:gt(0)').removeClass('enabled');
                        $la_select_box.find('option:gt(0)').removeAttr('disabled');

                        //Disable all options
                        $la_select_box.find('option:gt(0)').each(function (optindex, option_element) {
                            if (!avaiable_options[$(option_element).val()]) {
                                $(option_element).addClass('disabled', 'disabled');
                            } else {
                                $(option_element).addClass('attached');
                                $(option_element).addClass('enabled');
                            }
                        });

                        $la_select_box.find('option:gt(0):not(.enabled)').attr('disabled', 'disabled');

                    });
                }
            })
            .on('found_variation', function( event, variation ){
                la_update_swatches_gallery($form, variation);
            })
            .on('reset_image', function( event ){
                la_update_swatches_gallery($form, null);
            });

        $form.find('.single_variation').on('show_variation', function(e, variation, purchasable ){
            if(is_in_list){
                var $p_item = $form.closest('.product_item');
                if(variation.price_html != ''){
                    $('span.price', $p_item).replaceWith(variation.price_html);
                }
                var $btn_add = $p_item.find('.button.la-addcart').first(),
                    _bk_text;
                if($btn_add.data('tmptext')){
                    _bk_text = $btn_add.data('tmptext');
                }
                else{
                    _bk_text = $btn_add.text();
                    $btn_add.data('tmptext', _bk_text);
                }
                if(purchasable){
                    if($form.find('.single_add_to_cart_button').length){
                        _bk_text = $form.find('.single_add_to_cart_button').text()
                    }
                }
                $p_item.find('.button.la-addcart').attr('title', _bk_text);
                $p_item.find('.button.la-addcart .labtn-text').text(_bk_text).attr('title', _bk_text);
            }
            else{
                var $priceWrapper = $('.single-price-wrapper[data-product_id="'+$form.data('product_id')+'"]');
                $('span.price', $priceWrapper).remove();
                $priceWrapper.append(variation.price_html);
            }
        })
    };

    $(function () {
        var forms = [];

        if(la_theme_config.la_extension_available.swatches){

            $(document).on('wc_variation_form', 'form.variations_form',  function (e) {
                var $form = $(e.target);
                forms.push($form);
                if ( !$form.data('has_swatches_form') ) {
                    if (true || $form.find('.swatch-control').length) {
                        $form.data('has_swatches_form', true);
                        $form.lastudio_variation_form();
                        $form.trigger('bind_calculator');
                        $form.on('reload_product_variations', function () {
                            for (var i = 0; i < forms.length; i++) {
                                forms[i].trigger('woocommerce_variation_has_changed');
                                forms[i].trigger('bind_calculator');
                                forms[i].trigger('woocommerce_variation_has_changed');
                            }
                        })
                    }
                }
            });
        }
        else{
            console.log('la_theme_config.la_extension_available.swatches is not activate');
        }
    });

})(jQuery);