<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if(!function_exists('ciri_setup_customizer')){
    function ciri_setup_customizer( $args ){

        $args['prefix']      = 'ciri';
        $args['options']    = [
            /** `General` panel */
            'general_settings' => array(
                'title'       => esc_html__( 'General Site settings', 'ciri' ),
                'priority'    => 40,
                'type'        => 'panel',
            ),
            /** `Favicon` section */
            'favicon' => array(
                'title'       => esc_html__( 'Favicon', 'ciri' ),
                'priority'    => 10,
                'panel'       => 'general_settings',
                'type'        => 'section',
            ),
            /** `Logo` section */
            'logos' => array(
                'title'       => esc_html__( 'Logo', 'ciri' ),
                'priority'    => 15,
                'panel'       => 'general_settings',
                'type'        => 'section',
            ),
            'logo_default' => array(
                'title'    => esc_html__( 'Logo', 'ciri' ),
                'section'  => 'logos',
                'priority' => 20,
                'field'     => 'image',
                'type'     => 'control',
                'button_labels' => array(
                    'select' => esc_html__( 'Select Logo', 'ciri' ),
                    'remove' => esc_html__( 'Remove Logo', 'ciri' ),
                    'change' => esc_html__( 'Change Logo', 'ciri' ),
                ),
            ),
            'logo_transparency' => array(
                'title'    => esc_html__( 'Logo Transparency', 'ciri' ),
                'section'  => 'logos',
                'priority' => 25,
                'field'     => 'file',
                'type'     => 'control',
                'button_labels' => array(
                    'select' => esc_html__( 'Select Logo', 'ciri' ),
                    'remove' => esc_html__( 'Remove Logo', 'ciri' ),
                    'change' => esc_html__( 'Change Logo', 'ciri' ),
                ),
            ),
            /** `Preloader` panel */
            'preloader' => array(
                'title'       => esc_html__( 'Page preloader', 'ciri' ),
                'priority'    => 15,
                'panel'       => 'general_settings',
                'type'        => 'section',
            ),
            'page_preloader' => array(
                'title'    => esc_html__( 'Show page preloader', 'ciri' ),
                'section'  => 'preloader',
                'priority' => 10,
                'default'  => false,
                'field'     => 'checkbox',
                'type'     => 'control',
            ),
            'page_preloader_type' => array(
                'title'    => esc_html__( 'Page preloader type', 'ciri' ),
                'section'  => 'preloader',
                'priority' => 15,
                'field'     => 'select',
                'default'  => '1',
                'type'     => 'control',
                'choices'  => [
                    '1' => esc_html__( 'Type 1', 'ciri' ),
                    '2' => esc_html__( 'Type 2', 'ciri' ),
                    '3' => esc_html__( 'Type 3', 'ciri' ),
                    '4' => esc_html__( 'Type 4', 'ciri' ),
                    '5' => esc_html__( 'Type 5', 'ciri' ),
                    'custom' => esc_html__( 'Custom', 'ciri' ),
                ],
            ),
            'page_preloader_custom' => array(
                'title'    => esc_html__( 'Custom page preloader image', 'ciri' ),
                'section'  => 'preloader',
                'priority' => 20,
                'field'     => 'image',
                'type'     => 'control',
            ),
            'page_preloader_bgcolor' => array(
	            'title'   => esc_html__( 'Preloader background color', 'ciri' ),
	            'section' => 'colors',
	            'field'   => 'hex_color',
	            'type'    => 'control',
	            'priority' => 20,
	            'transport'=> 'postMessage',
	            'css' => [
		            [
			            'selector' => '.la-image-loading',
			            'property' => 'background-color'
		            ]
	            ]
            ),
            'page_preloader_textcolor' => array(
	            'title'   => esc_html__( 'Preloader text color', 'ciri' ),
	            'section' => 'colors',
	            'field'   => 'hex_color',
	            'type'    => 'control',
	            'priority' => 20,
	            'transport'=> 'postMessage',
	            'css' => [
		            [
			            'selector' => '.la-image-loading',
			            'property' => 'color'
		            ]
	            ]
            ),
            /** `Color Schema` panel */
            'body_bgcolor' => array(
	            'title'   => esc_html__( 'Body Background Color', 'ciri' ),
	            'section' => 'colors',
	            'field'   => 'hex_color',
	            'type'    => 'control',
	            'priority' => 20,
	            'transport'=> 'postMessage',
	            'css' => [
		            [
			            'selector' => ':root',
			            'property' => '--theme-body-bg-color'
		            ]
	            ]
            ),
            'text_color' => array(
	            'title'   => esc_html__( 'Text color', 'ciri' ),
	            'section' => 'colors',
	            'field'   => 'hex_color',
	            'type'    => 'control',
	            'priority' => 20,
	            'transport'=> 'postMessage',
	            'css' => [
		            [
			            'selector' => ':root',
			            'property' => '--theme-body-font-color'
		            ]
	            ]
            ),
            'primary_color' => array(
                'title'   => esc_html__( 'Primary color', 'ciri' ),
                'section' => 'colors',
                'field'   => 'hex_color',
                'type'    => 'control',
                'priority' => 25,
                'transport'=> 'postMessage',
                'css' => [
	                [
		                'selector' => ':root',
		                'property' => '--theme-primary-color'
	                ]
                ]
            ),
            'secondary_color' => array(
                'title'   => esc_html__( 'Secondary color', 'ciri' ),
                'section' => 'colors',
                'field'   => 'hex_color',
                'type'    => 'control',
                'priority' => 30,
                'transport'=> 'postMessage',
                'css' => [
	                [
		                'selector' => ':root',
		                'property' => '--theme-secondary-color'
	                ]
                ]
            ),
            'third_color' => array(
	            'title'   => esc_html__( 'Third color', 'ciri' ),
	            'section' => 'colors',
	            'field'   => 'hex_color',
	            'type'    => 'control',
	            'priority' => 35,
	            'transport'=> 'postMessage',
	            'css' => [
		            [
			            'selector' => ':root',
			            'property' => '--theme-three-color'
		            ]
	            ]
            ),
            'border_color' => array(
	            'title'   => esc_html__( 'Border color', 'ciri' ),
	            'section' => 'colors',
	            'field'   => 'hex_color',
	            'type'    => 'control',
	            'priority' => 38,
	            'transport'=> 'postMessage',
	            'css' => [
		            [
			            'selector' => ':root',
			            'property' => '--theme-border-color'
		            ]
	            ]
            ),
            'link_color' => array(
                'title'   => esc_html__( 'Link color', 'ciri' ),
                'section' => 'colors',
                'field'   => 'hex_color',
                'type'    => 'control',
                'priority' => 40,
                'transport'=> 'postMessage',
                'css' => [
	                [
		                'selector' => ':root',
		                'property' => '--theme-link-color'
	                ]
                ]
            ),
            'link_hover_color' => array(
                'title'   => esc_html__( 'Link hover color', 'ciri' ),
                'section' => 'colors',
                'field'   => 'hex_color',
                'type'    => 'control',
                'priority' => 45,
                'transport'=> 'postMessage',
                'css' => [
	                [
		                'selector' => ':root',
		                'property' => '--theme-link-hover-color'
	                ]
                ]
            ),
            'h_color' => array(
                'title'   => esc_html__( 'Heading color', 'ciri' ),
                'section' => 'colors',
                'field'   => 'hex_color',
                'type'    => 'control',
                'priority' => 48,
                'transport'=> 'postMessage',
                'css' => [
	                [
		                'selector' => ':root',
		                'property' => '--theme-heading-font-color'
	                ],
	                [
		                'selector' => 'h1, h2, h3, h4, h5, h6, .theme-heading',
		                'property' => 'color'
	                ]
                ]
            ),
            'h1_color' => array(
                'title'   => esc_html__( 'H1 color', 'ciri' ),
                'section' => 'colors',
                'field'   => 'hex_color',
                'type'    => 'control',
                'priority' => 50,
                'transport'=> 'postMessage',
                'css' => [
	                [
		                'selector' => '.h1, h1',
		                'property' => 'color'
	                ]
                ]
            ),
            'h2_color' => array(
                'title'   => esc_html__( 'H2 color', 'ciri' ),
                'section' => 'colors',
                'field'   => 'hex_color',
                'type'    => 'control',
                'priority' => 55,
                'transport'=> 'postMessage',
                'css' => [
	                [
		                'selector' => '.h2, h2',
		                'property' => 'color'
	                ]
                ]
            ),
            'h3_color' => array(
                'title'   => esc_html__( 'H3 color', 'ciri' ),
                'section' => 'colors',
                'field'   => 'hex_color',
                'type'    => 'control',
                'priority' => 60,
                'transport'=> 'postMessage',
                'css' => [
	                [
		                'selector' => '.h3, h3',
		                'property' => 'color'
	                ]
                ]
            ),
            'h4_color' => array(
                'title'   => esc_html__( 'H4 color', 'ciri' ),
                'section' => 'colors',
                'field'   => 'hex_color',
                'type'    => 'control',
                'priority' => 65,
                'transport'=> 'postMessage',
                'css' => [
	                [
		                'selector' => '.h4, h4',
		                'property' => 'color'
	                ]
                ]
            ),
            'h5_color' => array(
                'title'   => esc_html__( 'H5 color', 'ciri' ),
                'section' => 'colors',
                'field'   => 'hex_color',
                'type'    => 'control',
                'priority' => 70,
                'transport'=> 'postMessage',
                'css' => [
	                [
		                'selector' => '.h5, h5',
		                'property' => 'color'
	                ]
                ]
            ),
            'h6_color' => array(
                'title'   => esc_html__( 'H6 color', 'ciri' ),
                'section' => 'colors',
                'field'   => 'hex_color',
                'type'    => 'control',
                'priority' => 75,
                'transport'=> 'postMessage',
                'css' => [
	                [
		                'selector' => '.h6 h6',
		                'property' => 'color'
	                ]
                ]
            ),
            /** `Typography Settings` panel */
            'typography' => array(
                'title'       => esc_html__( 'Typography', 'ciri' ),
                'description' => esc_html__( 'Configure typography settings', 'ciri' ),
                'priority'    => 45,
                'type'        => 'panel',
            ),
            /** `Body text` section */
            'body_typography' => array(
                'title'       => esc_html__( 'Body text', 'ciri' ),
                'priority'    => 5,
                'panel'       => 'typography',
                'type'        => 'section',
            ),
            'body_font_family' => array(
                'title'   => esc_html__( 'Font Family', 'ciri' ),
                'section' => 'body_typography',
                'field'   => 'fonts',
                'type'    => 'control',
                'css' => [
	                [
		                'selector' => ':root',
		                'property' => '--theme-body-font-family'
	                ]
                ]
            ),
            'body_font_style' => array(
                'title'   => esc_html__( 'Font Style', 'ciri' ),
                'section' => 'body_typography',
                'field'   => 'select',
                'choices' => ciri_customizer_get_font_styles(),
                'type'    => 'control',
                'transport'=> 'postMessage',
                'css' => [
	                [
		                'selector' => ':root',
		                'property' => '--theme-body-font-style'
	                ]
                ]
            ),
            'body_font_weight' => array(
                'title'   => esc_html__( 'Font Weight', 'ciri' ),
                'section' => 'body_typography',
                'field'   => 'select',
                'choices' => ciri_customizer_get_font_weight(),
                'type'    => 'control',
                'transport'=> 'postMessage',
                'css' => [
	                [
		                'selector' => ':root',
		                'property' => '--theme-body-font-weight'
	                ]
                ]
            ),
            'body_font_size' => array(
                'title'       => esc_html__( 'Font Size, px', 'ciri' ),
                'section'     => 'body_typography',
                'field'       => 'number',
                'unit'       => 'px',
                'input_attrs' => array(
                    'min'  => 6,
                    'max'  => 50,
                    'step' => 1,
                ),
                'type' => 'control',
                'transport'=> 'postMessage',
                'css' => [
	                [
		                'selector' => ':root',
		                'property' => '--theme-body-font-size'
	                ]
                ]
            ),
            'body_line_height' => array(
                'title'       => esc_html__( 'Line Height', 'ciri' ),
                'description' => esc_html__( 'Relative to the font-size of the element', 'ciri' ),
                'section'     => 'body_typography',
                'field'       => 'number',
                'input_attrs' => array(
                    'min'  => 1.0,
                    'max'  => 3.0,
                    'step' => 0.1,
                ),
                'type' => 'control',
                'transport'=> 'postMessage',
                'css' => [
	                [
		                'selector' => ':root',
		                'property' => '--theme-body-line-height'
	                ]
                ]
            ),
            'body_letter_spacing' => array(
                'title'       => esc_html__( 'Letter Spacing, px', 'ciri' ),
                'section'     => 'body_typography',
                'field'       => 'number',
                'unit'       => 'px',
                'input_attrs' => array(
                    'min'  => -10,
                    'max'  => 10,
                    'step' => 1,
                ),
                'type' => 'control',
                'transport'=> 'postMessage',
                'css' => [
	                [
		                'selector' => ':root',
		                'property' => '--theme-body-font-spacing'
	                ]
                ]
            ),
            'body_character_set' => array(
                'title'   => esc_html__( 'Character Set', 'ciri' ),
                'section' => 'body_typography',
                'default' => 'latin',
                'field'   => 'select',
                'choices' => ciri_customizer_get_character_sets(),
                'type'    => 'control',
            ),
            'body_text_align' => array(
                'title'   => esc_html__( 'Text Align', 'ciri' ),
                'section' => 'body_typography',
                'field'   => 'select',
                'choices' => ciri_customizer_get_text_aligns(),
                'type'    => 'control',
                'transport'=> 'postMessage',
                'css' => [
	                [
		                'selector' => ':root',
		                'property' => '--theme-body-font-align'
	                ]
                ]
            ),

            /** `Heading` section */
            'heading_typography' => array(
	            'title'       => esc_html__( 'Heading Typography', 'ciri' ),
	            'priority'    => 10,
	            'panel'       => 'typography',
	            'type'        => 'section',
            ),
            'heading_font_family' => array(
	            'title'   => esc_html__( 'Font Family', 'ciri' ),
	            'section' => 'heading_typography',
	            'field'   => 'fonts',
	            'type'    => 'control',
	            'css' => [
		            [
			            'selector' => ':root',
			            'property' => '--theme-heading-font-family'
		            ]
	            ]
            ),
            'heading_font_style' => array(
	            'title'   => esc_html__( 'Font Style', 'ciri' ),
	            'section' => 'heading_typography',
	            'field'   => 'select',
	            'choices' => ciri_customizer_get_font_styles(),
	            'type'    => 'control',
	            'transport'=> 'postMessage',
	            'css' => [
		            [
			            'selector' => ':root',
			            'property' => '--theme-heading-font-style'
		            ]
	            ]
            ),
            'heading_font_weight' => array(
	            'title'   => esc_html__( 'Font Weight', 'ciri' ),
	            'section' => 'heading_typography',
	            'field'   => 'select',
	            'choices' => ciri_customizer_get_font_weight(),
	            'type'    => 'control',
	            'transport'=> 'postMessage',
	            'css' => [
		            [
			            'selector' => ':root',
			            'property' => '--theme-heading-font-weight'
		            ]
	            ]
            ),
            'heading_line_height' => array(
	            'title'       => esc_html__( 'Line Height', 'ciri' ),
	            'description' => esc_html__( 'Relative to the font-size of the element', 'ciri' ),
	            'section'     => 'heading_typography',
	            'field'       => 'number',
	            'input_attrs' => array(
		            'min'  => 1.0,
		            'max'  => 3.0,
		            'step' => 0.1,
	            ),
	            'type' => 'control',
	            'transport'=> 'postMessage',
	            'css' => [
		            [
			            'selector' => ':root',
			            'property' => '--theme-heading-font-line-height'
		            ]
	            ]
            ),
            'heading_letter_spacing' => array(
	            'title'       => esc_html__( 'Letter Spacing, px', 'ciri' ),
	            'section'     => 'heading_typography',
	            'field'       => 'number',
                'unit'       => 'px',
	            'input_attrs' => array(
		            'min'  => -10,
		            'max'  => 10,
		            'step' => 1,
	            ),
	            'type' => 'control',
	            'transport'=> 'postMessage',
	            'css' => [
		            [
			            'selector' => ':root',
			            'property' => '--theme-heading-font-spacing'
		            ]
	            ]
            ),
            'heading_character_set' => array(
	            'title'   => esc_html__( 'Character Set', 'ciri' ),
	            'section' => 'heading_typography',
	            'default' => 'latin',
	            'field'   => 'select',
	            'choices' => ciri_customizer_get_character_sets(),
	            'type'    => 'control',
            ),
            'heading_text_align' => array(
	            'title'   => esc_html__( 'Text Align', 'ciri' ),
	            'section' => 'heading_typography',
	            'field'   => 'select',
	            'choices' => ciri_customizer_get_text_aligns(),
	            'type'    => 'control',
	            'transport'=> 'postMessage',
	            'css' => [
		            [
			            'selector' => ':root',
			            'property' => '--theme-heading-font-align'
		            ]
	            ]
            ),
        ];

	    if(function_exists('WC')){

		    $woo_default_attr = Ciri_WooCommerce_Compare::get_default_attributes();
		    $woo_tax_attr = Ciri_WooCommerce_Compare::get_taxonomies();
		    $woo_all_attr = array_merge($woo_default_attr, $woo_tax_attr);

	    	$woo_opts = [
			    /** WooCommerce */
			    'shop_settings' => array(
				    'title'       => esc_html__( 'Shop settings', 'ciri' ),
				    'priority'    => 70,
				    'panel'       => 'general_settings',
				    'type'        => 'section',
			    ),
			    'shopdetail_settings' => array(
				    'title'       => esc_html__( 'Product settings', 'ciri' ),
				    'priority'    => 75,
				    'panel'       => 'general_settings',
				    'type'        => 'section',
			    ),
			    'compare_wishlist' => array(
				    'title'       => esc_html__( 'Compare & Wishlist', 'ciri' ),
				    'priority'    => 80,
				    'panel'       => 'general_settings',
				    'type'        => 'section',
			    ),
			    'shop_cart' => array(
				    'title'       => esc_html__( 'Cart', 'ciri' ),
				    'priority'    => 85,
				    'panel'       => 'general_settings',
				    'type'        => 'section',
			    ),
			    'freeshipping_thresholds' => array(
				    'title'         => esc_html__( 'WooCommerce Enable Free Shipping Thresholds', 'ciri' ),
				    'description'   => esc_html__( 'Enable Free shipping amount notice', 'ciri' ),
				    'section'       => 'shop_cart',
				    'default'       => false,
				    'field'          => 'checkbox',
				    'type'          => 'control',
			    ),
			    'thresholds_text1' => array(
				    'title'         => esc_html__( 'Shipping bar text 1', 'ciri' ),
				    'section'       => 'shop_cart',
				    'default'       => esc_html__('[icon]Spend [amount] to get Free Shipping', 'ciri'),
				    'description'       => esc_html__('[icon]Spend [amount] to get Free Shipping', 'ciri'),
				    'field'          => 'text',
				    'type'          => 'control',
			    ),
			    'thresholds_text2' => array(
				    'title'         => esc_html__( 'Shipping bar text 2', 'ciri' ),
				    'section'       => 'shop_cart',
				    'default'       => esc_html__('[icon]Congratulations! You\'ve got free shipping!', 'ciri'),
				    'description'       => esc_html__('[icon]Congratulations! You\'ve got free shipping!', 'ciri'),
				    'field'          => 'text',
				    'type'          => 'control',
			    ),
			    'woocommerce_gallery_zoom' => array(
				    'title'         => esc_html__( 'Enable WooCommerce Zoom', 'ciri' ),
				    'section'       => 'shopdetail_settings',
				    'default'       => false,
				    'field'          => 'checkbox',
				    'type'          => 'control',
			    ),
			    'woocommerce_gallery_lightbox' => array(
				    'title'         => esc_html__( 'Enable WooCommerce LightBox', 'ciri' ),
				    'section'       => 'shopdetail_settings',
				    'default'       => false,
				    'field'          => 'checkbox',
				    'type'          => 'control',
			    ),
			    'single_ajax_add_cart' => array(
				    'title'         => esc_html__( 'Ajax Add to Cart', 'ciri' ),
				    'description'   => esc_html__( 'Support Ajax Add to cart for all types of products', 'ciri' ),
				    'section'       => 'shopdetail_settings',
				    'default'       => false,
				    'field'          => 'checkbox',
				    'type'          => 'control',
			    ),
			    'wc_per_page_default' => array(
				    'title'         => esc_html__( 'Per Page Default', 'ciri' ),
				    'section'       => 'shop_settings',
				    'default'       => '',
				    'description'   => esc_html__('Enter total products display per page', 'ciri'),
				    'field'          => 'text',
				    'type'          => 'control',
			    ),
			    'wc_per_page_allow' => array(
				    'title'         => esc_html__( 'Per Page Allow', 'ciri' ),
				    'section'       => 'shop_settings',
				    'default'       => '',
				    'description'       => esc_html__('Comma-separated. ( i.e: 3,6,9)', 'ciri'),
				    'field'          => 'text',
				    'type'          => 'control',
			    ),
			    'wc_per_row_allow' => array(
				    'title'         => esc_html__( 'Per Row Allow', 'ciri' ),
				    'section'       => 'shop_settings',
				    'default'       => '',
				    'description'       => esc_html__('Comma-separated. ( i.e: 2,3,4)', 'ciri'),
				    'field'          => 'text',
				    'type'          => 'control',
			    ),
			    'catalog_mode' => array(
				    'title'         => esc_html__( 'Catalog Mode', 'ciri' ),
				    'description'   => esc_html__( 'Turn on to disable the shopping functionality of WooCommerce.', 'ciri' ),
				    'section'       => 'shop_settings',
				    'default'       => false,
				    'field'          => 'checkbox',
				    'type'          => 'control',
			    ),
			    'catalog_mode_price' => array(
				    'title'         => esc_html__( 'Catalog Mode Price', 'ciri' ),
				    'description'   => esc_html__( 'Turn on to do not show product price', 'ciri' ),
				    'section'       => 'shop_settings',
				    'default'       => false,
				    'field'          => 'checkbox',
				    'type'          => 'control',
			    ),
			    'woocommerce_enable_crossfade_effect' => array(
				    'title'         => esc_html__( 'Enable Crossfade Image Effect', 'ciri' ),
				    'description'   => esc_html__( 'Turn on to display the product crossfade image effect on the product.', 'ciri' ),
				    'section'       => 'shop_settings',
				    'default'       => false,
				    'field'          => 'checkbox',
				    'type'          => 'control',
			    ),
			    'woocommerce_show_rating_on_catalog' => array(
				    'title'         => esc_html__( 'Show Ratings', 'ciri' ),
				    'description'   => esc_html__( 'Turn on to display the ratings on the main shop page and archive shop pages.', 'ciri' ),
				    'section'       => 'shop_settings',
				    'default'       => false,
				    'field'          => 'checkbox',
				    'type'          => 'control',
			    ),
			    'woocommerce_show_addcart_btn' => array(
				    'title'         => esc_html__( 'Show Add Cart Button', 'ciri' ),
				    'section'       => 'shop_settings',
				    'default'       => false,
				    'field'          => 'checkbox',
				    'type'          => 'control',
			    ),
			    'woocommerce_show_quickview_btn' => array(
				    'title'         => esc_html__( 'Show Quick View Button', 'ciri' ),
				    'section'       => 'shop_settings',
				    'default'       => false,
				    'field'          => 'checkbox',
				    'type'          => 'control',
			    ),
			    'woocommerce_show_wishlist_btn' => array(
				    'title'         => esc_html__( 'Show Wishlist Button', 'ciri' ),
				    'section'       => 'shop_settings',
				    'default'       => false,
				    'field'          => 'checkbox',
				    'type'          => 'control',
			    ),
			    'woocommerce_show_compare_btn' => array(
				    'title'         => esc_html__( 'Show Compare Button', 'ciri' ),
				    'section'       => 'shop_settings',
				    'default'       => false,
				    'field'          => 'checkbox',
				    'type'          => 'control',
			    ),
			    'woocommerce_show_action_btn_mobile' => array(
				    'title'         => esc_html__( 'Force display Product Actions on mobile', 'ciri' ),
				    'description'   => esc_html__( 'Display add-to-cart, wishlist, compare buttons on the mobile', 'ciri' ),
				    'section'       => 'shop_settings',
				    'default'       => false,
				    'field'          => 'checkbox',
				    'type'          => 'control',
			    ),

			    /* Wishlist Compare */
			    'wishlist_page' => array(
				    'title'   => esc_html__( 'Wishlist Page', 'ciri' ),
				    'description'   => esc_html__( 'The content of page must be contain [la_wishlist] shortcode', 'ciri' ),
				    'section' => 'compare_wishlist',
				    'field'   => 'dropdown-pages',
				    'type'    => 'control',
			    ),
			    'compare_page' => array(
				    'title'   => esc_html__( 'Compare Page', 'ciri' ),
				    'description'   => esc_html__( 'The content of page must be contain [la_compare] shortcode', 'ciri' ),
				    'section' => 'compare_wishlist',
				    'field'   => 'dropdown-pages',
				    'type'    => 'control',
			    ),
			    'compare_attribute' => array(
				    'title'   => esc_html__( 'Compare fields to show', 'ciri' ),
				    'description'   => esc_html__( 'Select the fields to show in the comparison table', 'ciri' ),
				    'section' => 'compare_wishlist',
				    'field'   => 'checkbox-multiple',
				    'choices' => $woo_all_attr,
				    'type'    => 'control',
			    ),
		    ];
		    $args['options'] = array_merge($args['options'], $woo_opts);
	    }

	    $args['options'] = array_merge($args['options'], ciri_customizer_heading_typo());

        return $args;
    }
}

add_filter('lastudio-kit/theme/customizer/options', 'ciri_setup_customizer');

if(!function_exists('ciri_customizer_heading_typo')){
	function ciri_customizer_heading_typo(){
		$options = [];
		for ($i = 1; $i <=6; $i++){
			$options['h'.$i.'_typography'] = [
				'title'       => sprintf(__('H%s Heading', 'ciri'), $i),
				'priority'    => 10 + ( $i * 5 ),
				'panel'       => 'typography',
				'type'        => 'section',
			];
			$options['h'.$i.'_font_family'] = [
				'title'   => esc_html__( 'Font Family', 'ciri' ),
				'section' => 'h'.$i.'_typography',
				'field'   => 'fonts',
				'type'    => 'control',
				'css' => [
					[
						'selector' => ':root',
						'property' => '--theme-h'.$i.'-font-family'
					]
				]
			];
			$options['h'.$i.'_font_style'] = [
				'title'   => esc_html__( 'Font Style', 'ciri' ),
				'section' => 'h'.$i.'_typography',
				'field'   => 'select',
				'choices' => ciri_customizer_get_font_styles(),
				'type'    => 'control',
				'transport'=> 'postMessage',
				'css' => [
					[
						'selector' => ':root',
						'property' => '--theme-h'.$i.'-font-style'
					]
				]
			];
			$options['h'.$i.'_font_weight'] = [
				'title'   => esc_html__( 'Font Weight', 'ciri' ),
				'section' => 'h'.$i.'_typography',
				'field'   => 'select',
				'choices' => ciri_customizer_get_font_weight(),
				'type'    => 'control',
				'transport'=> 'postMessage',
				'css' => [
					[
						'selector' => ':root',
						'property' => '--theme-h'.$i.'-font-weight'
					]
				]
			];
			$options['h'.$i.'_font_size'] = [
				'title'       => esc_html__( 'Font Size, px', 'ciri' ),
				'section' => 'h'.$i.'_typography',
				'field'        => 'lakit_responsive',
				'unit'        => 'px',
				'responsive'  => true,
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 200,
					'step' => 1,
				),
				'type' => 'control',
				'transport'=> 'postMessage',
				'css' => [
					[
						'selector' => ':root',
						'property' => '--theme-h'.$i.'-font-size'
					]
				]
			];
			$options['h'.$i.'_line_height'] = [
				'title'       => esc_html__( 'Line Height', 'ciri' ),
				'description' => esc_html__( 'Relative to the font-size of the element', 'ciri' ),
				'section' => 'h'.$i.'_typography',
				'field'       => 'number',
				'input_attrs' => array(
					'min'  => 1.0,
					'max'  => 3.0,
					'step' => 0.1,
				),
				'type' => 'control',
				'transport'=> 'postMessage',
				'css' => [
					[
						'selector' => ':root',
						'property' => '--theme-h'.$i.'-font-line-height'
					]
				]
			];
			$options['h'.$i.'_letter_spacing'] = [
				'title'       => esc_html__( 'Letter Spacing, px', 'ciri' ),
				'section' => 'h'.$i.'_typography',
				'field'        => 'lakit_responsive',
				'unit'        => 'px',
				'responsive'  => true,
				'input_attrs' => array(
					'min'  => -10,
					'max'  => 10,
					'step' => 1,
				),
				'type' => 'control',
				'transport'=> 'postMessage',
				'css' => [
					[
						'selector' => ':root',
						'property' => '--theme-h'.$i.'-font-spacing'
					]
				]
			];
			$options['h'.$i.'_character_set'] = [
				'title'   => esc_html__( 'Character Set', 'ciri' ),
				'section' => 'h'.$i.'_typography',
				'default' => 'latin',
				'field'   => 'select',
				'choices' => ciri_customizer_get_character_sets(),
				'type'    => 'control',
			];
			$options['h'.$i.'_text_align'] = [
				'title'   => esc_html__( 'Text Align', 'ciri' ),
				'section' => 'h'.$i.'_typography',
				'field'   => 'select',
				'choices' => ciri_customizer_get_text_aligns(),
				'type'    => 'control',
				'transport'=> 'postMessage',
				'css' => [
					[
						'selector' => ':root',
						'property' => '--theme-h'.$i.'-font-align'
					]
				]
			];
		}
		return $options;
	}
}

/**
 * Move native `site_icon` control (based on WordPress core) into custom section.
 *
 * @since 1.0.0
 * @param  object $wp_customize
 * @return void
 */
if(!function_exists('ciri_customizer_change_core_controls')){
    function ciri_customizer_change_core_controls( $wp_customize ) {
        $wp_customize->remove_control('display_header_text');
        $wp_customize->remove_control('header_textcolor');
	    $wp_customize->remove_control( 'background_color' );
        $wp_customize->get_control( 'site_icon' )->section      = 'ciri_favicon';
        $wp_customize->get_section( 'colors' )->title = esc_html__( 'Color Scheme', 'ciri' );
    }
}

// Move native `site_icon` control (based on WordPress core) in custom section.
add_action( 'customize_register', 'ciri_customizer_change_core_controls', 20 );


/**
 * Get font styles
 *
 * @since 1.0.0
 * @return array
 */
if(!function_exists('ciri_customizer_get_font_styles')){
    function ciri_customizer_get_font_styles() {
        return apply_filters( 'ciri-theme/font/styles', array(
            'normal'  => esc_html__( 'Normal', 'ciri' ),
            'italic'  => esc_html__( 'Italic', 'ciri' ),
            'oblique' => esc_html__( 'Oblique', 'ciri' ),
            'inherit' => esc_html__( 'Inherit', 'ciri' ),
        ) );
    }    
}


/**
 * Get character sets
 *
 * @since 1.0.0
 * @return array
 */
if(!function_exists('ciri_customizer_get_character_sets')){
    function ciri_customizer_get_character_sets() {
        return apply_filters( 'ciri-theme/font/character_sets', array(
            'latin'        => esc_html__( 'Latin', 'ciri' ),
            'greek'        => esc_html__( 'Greek', 'ciri' ),
            'greek-ext'    => esc_html__( 'Greek Extended', 'ciri' ),
            'vietnamese'   => esc_html__( 'Vietnamese', 'ciri' ),
            'cyrillic-ext' => esc_html__( 'Cyrillic Extended', 'ciri' ),
            'latin-ext'    => esc_html__( 'Latin Extended', 'ciri' ),
            'cyrillic'     => esc_html__( 'Cyrillic', 'ciri' ),
        ) );
    }
}

/**
 * Get text aligns
 *
 * @since 1.0.0
 * @return array
 */
if(!function_exists('ciri_customizer_get_text_aligns')){
    function ciri_customizer_get_text_aligns() {
        return apply_filters( 'ciri-theme/font/text-aligns', array(
            'inherit' => esc_html__( 'Inherit', 'ciri' ),
            'center'  => esc_html__( 'Center', 'ciri' ),
            'justify' => esc_html__( 'Justify', 'ciri' ),
            'left'    => esc_html__( 'Left', 'ciri' ),
            'right'   => esc_html__( 'Right', 'ciri' ),
        ) );
    }   
}

/**
 * Get font weights
 *
 * @since 1.0.0
 * @return array
 */
if(!function_exists('ciri_customizer_get_font_weight')){
    function ciri_customizer_get_font_weight() {
        return apply_filters( 'ciri-theme/font/weight', array(
            '100' => '100',
            '200' => '200',
            '300' => '300',
            '400' => '400',
            '500' => '500',
            '600' => '600',
            '700' => '700',
            '800' => '800',
            '900' => '900',
        ) );
    }   
}

if(!function_exists('ciri_customizer_list_pages')){
	function ciri_customizer_list_pages(){
		$pages = get_pages();
		$opts = [
			'' => ''
		];
		foreach ($pages as $page){
			$opts[$page->ID] = $page->post_title;
		}
		return $opts;
	}
}