<?php

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Direct script access denied.' );
}

function la_ciri_get_demo_array($dir_url, $dir_path){

    $demo_items = array(
        'ciri-furniture-01' => array(
            'link' => 'https://ciri.la-studioweb.com/ciri-furniture-01/',
            'title' => 'Furniture 01',
            'data_elementor' => array(
                'header' => array(
                    'location' => 'header',
                    'value' => array(
                        'ciri-header-01' => 'include/general',
                    ),
                ),
                'footer' => array(
                    'location' => 'footer',
                    'value' => array(
                        'ciri-footer-01' => 'include/general',
                    ),
                ),
            ),
            'category' => array(
                0 => 'Demo',
            ),
            'data_slider' => '',
        ),
        'ciri-furniture-02' => array(
            'link' => 'https://ciri.la-studioweb.com/ciri-furniture-02/',
            'title' => 'Furniture 02',
            'data_elementor' => array(
                'header' => array(
                    'location' => 'header',
                    'value' => array(
                        'ciri-header-01' => 'include/general',
                    ),
                ),
                'footer' => array(
                    'location' => 'footer',
                    'value' => array(
                        'ciri-footer-01' => 'include/general',
                    ),
                ),
            ),
            'category' => array(
                0 => 'Demo',
            ),
            'data_slider' => '',
        ),
        'ciri-furniture-03' => array(
            'link' => 'https://ciri.la-studioweb.com/ciri-furniture-03/',
            'title' => 'Furniture 03',
            'data_elementor' => array(
                'header' => array(
                    'location' => 'header',
                    'value' => array(
                        'ciri-header-02' => 'include/general',
                    ),
                ),
                'footer' => array(
                    'location' => 'footer',
                    'value' => array(
                        'ciri-footer-02' => 'include/general',
                    ),
                ),
            ),
            'category' => array(
                0 => 'Demo',
            ),
            'data_slider' => '',
        ),
        'ciri-furniture-04' => array(
            'link' => 'https://ciri.la-studioweb.com/ciri-furniture-04/',
            'title' => 'Furniture 04',
            'data_elementor' => array(
                'header' => array(
                    'location' => 'header',
                    'value' => array(
                        'ciri-header-01' => 'include/general',
                    ),
                ),
                'footer' => array(
                    'location' => 'footer',
                    'value' => array(
                        'ciri-footer-02' => 'include/general',
                    ),
                ),
            ),
            'category' => array(
                0 => 'Demo',
            ),
            'data_slider' => '',
        ),
        'ciri-furniture-05' => array(
            'link' => 'https://ciri.la-studioweb.com/ciri-furniture-05/',
            'title' => 'Furniture 05',
            'data_elementor' => array(
                'header' => array(
                    'location' => 'header',
                    'value' => array(
                        'ciri-header-03' => 'include/general',
                    ),
                ),
                'footer' => array(
                    'location' => 'footer',
                    'value' => array(
                        'ciri-footer-02' => 'include/general',
                    ),
                ),
            ),
            'category' => array(
                0 => 'Demo',
            ),
            'data_slider' => '',
        ),
        'ciri-furniture-06' => array(
            'link' => 'https://ciri.la-studioweb.com/ciri-furniture-06/',
            'title' => 'Furniture 06',
            'data_elementor' => array(
                'header' => array(
                    'location' => 'header',
                    'value' => array(
                        'ciri-header-01' => 'include/general',
                    ),
                ),
                'footer' => array(
                    'location' => 'footer',
                    'value' => array(
                        'ciri-footer-01' => 'include/general',
                    ),
                ),
            ),
            'category' => array(
                0 => 'Demo',
            ),
            'data_slider' => '',
        ),
        'ciri-furniture-07' => array(
            'link' => 'https://ciri.la-studioweb.com/ciri-furniture-07/',
            'title' => 'Furniture 07',
            'data_elementor' => array(
                'header' => array(
                    'location' => 'header',
                    'value' => array(
                        'ciri-header-03' => 'include/general',
                    ),
                ),
                'footer' => array(
                    'location' => 'footer',
                    'value' => array(
                        'ciri-footer-01' => 'include/general',
                    ),
                ),
            ),
            'category' => array(
                0 => 'Demo',
            ),
            'data_slider' => '',
        ),
    );

    $default_image_setting = array(
        'woocommerce_single_image_width' => 1000,
        'woocommerce_thumbnail_image_width' => 600,
        'woocommerce_thumbnail_cropping' => 'custom',
        'woocommerce_thumbnail_cropping_custom_width' => 10,
        'woocommerce_thumbnail_cropping_custom_height' => 12,
        'thumbnail_size_w' => 0,
        'thumbnail_size_h' => 0,
        'medium_size_w' => 0,
        'medium_size_h' => 0,
        'medium_large_size_w' => 0,
        'medium_large_size_h' => 0,
        'large_size_w' => 0,
        'large_size_h' => 0,
    );

    $default_menu = array(
        'main-nav'              => 'Ciri Primary Menu'
    );

    $default_page = array(
        'page_for_posts' 	            => 'Blog',
        'woocommerce_shop_page_id'      => 'Shop',
        'woocommerce_cart_page_id'      => 'Cart',
        'woocommerce_checkout_page_id'  => 'Checkout',
        'woocommerce_myaccount_page_id' => 'My Account'
    );

    $slider = $dir_path . 'Slider/';
    $content = $dir_path . 'Content/';
    $product = $dir_path . 'Product/';
    $widget = $dir_path . 'Widget/';
    $setting = $dir_path . 'Setting/';
    $preview = $dir_url;

    $default_elementor = [
        'single-post'       => [
            'location' => 'single',
            'value' => [
                'ciri-single-post-01' => 'include/singular/post',
            ],
        ],
        'single-page'       => [
            'location' => 'single',
            'value' => '',
            'default' => [
                'ciri-woopages' => [
                    'include/singular/page/wishlist',
                    'include/singular/page/compare',
                    'include/singular/page/cart',
                    'include/singular/page/checkout'
                ],
            ]
        ],
        'archive'           => [
            'location' => 'archive',
            'value' => [
                'ciri-blog-sidebar' => 'include/archive'
            ]
        ],
        'search-results'    => [
            'location' => 'archive',
            'value'    => '',
            'default' => [
                'name' => 'include/archive/search'
            ],
        ],
        'error-404'         => [
            'location' => 'single',
            'value' => [
	            'ciri-404' => 'include/singular/not_found404'
            ],
        ],
        'product'           => [
            'location' => 'single',
            'value' => [
                'ciri-product-layout-01' => 'include/product'
            ]
        ],
        'product-archive'   => [
            'location' => 'archive',
            'value' => [
                'ciri-shop-default' => 'include/product_archive'
            ]
        ],
    ];

    $elementor_kit_settings = json_decode( file_get_contents( $setting . 'settings.json' ), true );

    $data_return = array();

    foreach ($demo_items as $demo_key => $demo_detail){
	    $value = array();

	    $value['title']             = $demo_detail['title'];
	    $value['category']          = !empty($demo_detail['category']) ? $demo_detail['category'] : array('Demo');
	    $value['demo_preset']       = $demo_key;
	    $value['demo_url']          = $demo_detail['link'];
	    $value['preview']           = !empty($demo_detail['preview']) ? $demo_detail['preview'] : ($preview . $demo_key . '.jpg');
	    $value['option']            = $setting . $demo_key . '.json';
	    $value['content']           = !empty($demo_detail['data_sample']) ? $content . $demo_detail['data_sample'] : $content . 'demo-data.json';
	    $value['product']           = !empty($demo_detail['data_product']) ? $product . $demo_detail['data_product'] : $product . 'products.csv';
	    $value['widget']            = !empty($demo_detail['data_widget']) ? $widget . $demo_detail['data_widget'] : $widget . 'widgets.json';
	    $value['pages']             = array_merge( $default_page, array( 'page_on_front' => $demo_detail['title'] ));
	    $value['menu-locations']    = array_merge( $default_menu, isset($demo_detail['menu-locations']) ? $demo_detail['menu-locations'] : array());
	    $value['other_setting']     = array_merge( $default_image_setting, isset($demo_detail['other_setting']) ? $demo_detail['other_setting'] : array());
	    if(!empty($demo_detail['data_slider'])){
		    $value['slider'] = $slider . $demo_detail['data_slider'];
	    }
        $value['elementor']         = array_merge( $default_elementor, isset($demo_detail['data_elementor']) ? $demo_detail['data_elementor'] : array());
        $value['elementor_kit_settings']         = array_merge( $elementor_kit_settings, isset($demo_detail['elementor_kit_settings']) ? $demo_detail['elementor_kit_settings'] : array());
	    $data_return[$demo_key] = $value;
    }

    return $data_return;
}