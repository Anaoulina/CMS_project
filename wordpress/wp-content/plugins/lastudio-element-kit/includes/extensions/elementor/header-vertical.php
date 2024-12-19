<?php

namespace LaStudioKitExtensions\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use Elementor\Controls_Manager;

class Header_Vertical {
    public function __construct() {
        add_action('elementor/theme/before_do_header', [ $this, 'add_open_wrap' ], PHP_INT_MIN );
        add_action('wp_footer', [ $this, 'add_close_wrap' ], PHP_INT_MIN );
        add_action('elementor/element/header/document_settings/before_section_end', [ $this, 'register_control_settings' ]);

	    if(lastudio_kit()->get_theme_support('elementor::product-grid-v2') ){
		    add_action('elementor/element/section/section_advanced/after_section_end', [ $this, 'add_transparency_controls' ]);
	    }
	    /**
	     * Add header controls to Page/Archive/Single location
	     */
		add_action('elementor/element/before_section_end', [ $this, 'add_header_control_to_elementor_location' ], 10, 3);

	    add_filter( 'body_class', [ $this, 'body_class' ], 20);
	    add_action( 'wp_body_open', [ $this, 'wp_body_open' ], 0);

        add_action('elementor/theme/before_do_header', [ $this, 'detect_seciton_in_header' ], PHP_INT_MAX );
        add_action('elementor/theme/after_do_header', [ $this, 'un_detect_seciton_in_header' ], PHP_INT_MAX );
    }

    public function detect_seciton_in_header(){
        global $lakit_is_header_loc;
        $lakit_is_header_loc = true;
    }

    public function un_detect_seciton_in_header(){
        global $lakit_is_header_loc;
        $lakit_is_header_loc = false;
    }

    public function add_transparency_controls( $stack ){
        $stack->start_controls_section('section_transparency_style', [
            'label' => esc_html__('LA-Kit Transparency Style', 'lastudio-kit'),
            'tab' => \Elementor\Controls_Manager::TAB_ADVANCED,
        ]);
        $stack->add_control(
            'lakit_section_transparency_enable',
            [
                'label' => __( 'Enable transparency style ?', 'lastudio-kit' ),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'prefix_class' => 'lakit--transparency-',
            ]
        );
        $stack->add_control(
            'lakit_section_transparency_note',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => __( 'Note: This option may not work properly in some cases', 'lastudio-kit' ),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
                'condition' => array(
                    'lakit_section_transparency_enable' => 'yes',
                ),
            ]
        );
        $stack->add_control(
            'lakit_section_bg_color',
            array(
                'label' => esc_html__( 'Section Background Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}}' => '--lakit-section-bg-color: {{VALUE}}',
                ),
                'condition' => array(
                    'lakit_section_transparency_enable' => 'yes',
                ),
            )
        );
        $stack->add_control(
            'lakit_section_text_color',
            array(
                'label' => esc_html__( 'Section Text Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}}' => '--lakit-section-text-color: {{VALUE}}',
                ),
                'condition' => array(
                    'lakit_section_transparency_enable' => 'yes',
                ),
            )
        );
        $stack->add_control(
            'lakit_section_link_color',
            array(
                'label' => esc_html__( 'Section Link Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}}' => '--lakit-section-link-color: {{VALUE}}',
                ),
                'condition' => array(
                    'lakit_section_transparency_enable' => 'yes',
                ),
            )
        );
        $stack->add_control(
            'lakit_section_link_hover_color',
            array(
                'label' => esc_html__( 'Section Hover Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}}' => '--lakit-section-link-hover-color: {{VALUE}}',
                ),
                'condition' => array(
                    'lakit_section_transparency_enable' => 'yes',
                ),
            )
        );
        $stack->end_controls_section();
    }

    public function register_control_settings( $stack ){
        $stack->add_control(
            'lakit_header_vertical',
            [
                'label' => __( 'Vertical Header Layout ?', 'lastudio-kit' ),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'yes'
            ]
        );
        $stack->add_responsive_control(
            'lakit_header_vertical_width',
            array(
                'label'      => esc_html__( 'Header Width', 'lastudio-kit' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array( 'px', 'em', 'vw', 'vh', '%' ),
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'vw' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors'  => array(
                    '{{WRAPPER}}lakit.lakit--is-vheader' => '--lakit-vheader-width: {{SIZE}}{{UNIT}}',
                ),
                'condition' => array(
                    'lakit_header_vertical' => 'yes',
                ),
            )
        );

        $stack->add_control(
            'lakit_header_vertical_alignment',
            array(
                'label'   => esc_html__( 'Header Alignment', 'lastudio-kit' ),
                'type'    => Controls_Manager::CHOOSE,
                'options' => array(
                    'left'    => array(
                        'title' => esc_html__( 'Left', 'lastudio-kit' ),
                        'icon'  => 'eicon-h-align-left',
                    ),
                    'right' => array(
                        'title' => esc_html__( 'Right', 'lastudio-kit' ),
                        'icon'  => 'eicon-h-align-right',
                    ),
                ),
                'condition' => array(
                    'lakit_header_vertical' => 'yes',
                ),
            )
        );
        $stack->add_control(
            'lakit_header_vertical_disable_on',
            array(
                'label'   => esc_html__( 'Disable Vertical Header On', 'lastudio-kit' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'tablet',
                'options' => lastudio_kit_helper()->get_active_breakpoints(false, true),
                'condition' => array(
                    'lakit_header_vertical' => 'yes',
                ),
            )
        );
    }

    public function add_open_wrap(){
        global $lakit_site_wrapper_open;

        if(lastudio_kit()->has_elementor_pro()){
            $documents_by_conditions = \ElementorPro\Modules\ThemeBuilder\Module::instance()->get_conditions_manager()->get_documents_for_location( 'header' );
        }
        else{
            $documents_by_conditions = \LaStudioKitThemeBuilder\Modules\ThemeBuilder\Module::instance()->get_conditions_manager()->get_documents_for_location( 'header' );
        }

        $document_id = key($documents_by_conditions);
        $settings = get_post_meta( $document_id, '_elementor_page_settings', true );
        $lakit_site_wrapper_open = true;
        $classes = ['lakit-site-wrapper'];
        $classes[] = 'elementor-' . $document_id . 'lakit';
        $vcss = '';
        $added_css = false;
        if(isset($settings['lakit_header_vertical']) && filter_var($settings['lakit_header_vertical'], FILTER_VALIDATE_BOOLEAN)){
            $alignment = !empty($settings['lakit_header_vertical_alignment']) ? $settings['lakit_header_vertical_alignment'] : 'left';
            $disable_on = !empty($settings['lakit_header_vertical_disable_on']) ? $settings['lakit_header_vertical_disable_on'] : 'tablet';
            if($alignment !== 'right'){
                $alignment = 'left';
            }
            $classes[] = 'lakit--is-vheader';
            $classes[] = 'lakit-vheader-p' . $alignment;
            $classes[] = 'lakit-vheader--hide' . $disable_on;
	        if( !lastudio_kit_settings()->is_combine_js_css() ){
		        wp_enqueue_script('lastudio-kit-header-vertical');
		        wp_enqueue_style('lastudio-kit-base');
	        }
            $vcss = '<style>' . $this->vheader_css() . '</style>';
            $added_css = true;
        }
        if(!$added_css && lastudio_kit()->elementor()->editor->is_edit_mode()){
            $vcss = '<style>' . $this->vheader_css() . '</style>';
        }
        echo sprintf('%2$s<div class="%1$s">', esc_attr(join(' ', $classes)), $vcss);
    }

    public function add_close_wrap(){
        global $lakit_site_wrapper_open;
        if( $lakit_site_wrapper_open ){
            echo '</div><!-- .lakit-site-wrapper -->';
        }
        $lakit_site_wrapper_open = false;
    }

	/**
	 * @description Add header controls to Page/Archive/Single location
	 * @param $stack \Elementor\Modules\Library\Documents\Library_Document
	 * @param $section_id
	 * @param $args
	 *
	 * @return void
	 */
	public function add_header_control_to_elementor_location( $stack, $section_id, $args ){
		if($section_id !== 'document_settings'){
			return;
		}
		$stack->add_control(
			'lakit_doc_enable_header_transparency',
			[
				'label' => __( 'Enable Header Transparency ?', 'lastudio-kit' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
                'separator' => 'before',
			]
		);
		$stack->add_control(
			'lakit_doc_header_transparency_note',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => __( 'Note: This option may not work properly in some cases', 'lastudio-kit' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
				'condition' => array(
					'lakit_doc_enable_header_transparency' => 'yes',
				),
			]
		);
		$stack->add_control(
			'lakit_doc_swap_logo',
			[
				'label' => __( 'Swap Logos ?', 'lastudio-kit' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'condition' => array(
					'lakit_doc_enable_header_transparency' => 'yes',
				),
			]
		);
		$stack->add_control(
			'lakit_doc_header_bg_color',
			array(
				'label' => esc_html__( 'Header Background Color', 'lastudio-kit' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--lakit-doc-headerts-bg-color: {{VALUE}}',
				),
				'condition' => array(
					'lakit_doc_enable_header_transparency' => 'yes',
				),
			)
		);
		$stack->add_control(
			'lakit_doc_header_text_color',
			array(
				'label' => esc_html__( 'Header Text Color', 'lastudio-kit' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--lakit-doc-headerts-text-color: {{VALUE}}',
				),
				'condition' => array(
					'lakit_doc_enable_header_transparency' => 'yes',
				),
			)
		);
		$stack->add_control(
			'lakit_doc_header_link_color',
			array(
				'label' => esc_html__( 'Header Link Color', 'lastudio-kit' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--lakit-doc-headerts-link-color: {{VALUE}}',
				),
				'condition' => array(
					'lakit_doc_enable_header_transparency' => 'yes',
				),
			)
		);
		$stack->add_control(
			'lakit_doc_header_link_hover_color',
			array(
				'label' => esc_html__( 'Header Link Hover Color', 'lastudio-kit' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--lakit-doc-headerts-link-hover-color: {{VALUE}}',
				),
				'condition' => array(
					'lakit_doc_enable_header_transparency' => 'yes',
				),
			)
		);
		$stack->add_control(
			'lakit_doc_header_heading__menu',
			[
				'label' => esc_html__( 'Header Menu (Only for LaStudioKit Nav Menu)', 'lastudio-kit' ),
				'type'  => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'lakit_doc_enable_header_transparency' => 'yes',
				),
			]
		);
		$stack->add_control(
			'lakit_doc_header__menu_color',
			array(
				'label' => esc_html__( 'Menu Color', 'lastudio-kit' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--lakit-doc-headerts--menu-color: {{VALUE}}',
				),
				'condition' => array(
					'lakit_doc_enable_header_transparency' => 'yes',
				),
			)
		);
		$stack->add_control(
			'lakit_doc_header__menu_bgcolor',
			array(
				'label' => esc_html__( 'Menu Background Color', 'lastudio-kit' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--lakit-doc-headerts--menu-bgcolor: {{VALUE}}',
				),
				'condition' => array(
					'lakit_doc_enable_header_transparency' => 'yes',
				),
			)
		);
		$stack->add_control(
			'lakit_doc_header__menu_bdcolor',
			array(
				'label' => esc_html__( 'Menu Border Color', 'lastudio-kit' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--lakit-doc-headerts--menu-bdcolor: {{VALUE}}',
				),
				'condition' => array(
					'lakit_doc_enable_header_transparency' => 'yes',
				),
			)
		);

		$stack->add_control(
			'lakit_doc_header__menua_color',
			array(
				'label' => esc_html__( '[Hover/Active] Menu Color', 'lastudio-kit' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--lakit-doc-headerts--menua-color: {{VALUE}}',
				),
				'condition' => array(
					'lakit_doc_enable_header_transparency' => 'yes',
				),
			)
		);
		$stack->add_control(
			'lakit_doc_header__menua_bgcolor',
			array(
				'label' => esc_html__( '[Hover/Active] Menu Background Color', 'lastudio-kit' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--lakit-doc-headerts--menua-bgcolor: {{VALUE}}',
				),
				'condition' => array(
					'lakit_doc_enable_header_transparency' => 'yes',
				),
			)
		);
		$stack->add_control(
			'lakit_doc_header__menua_bdcolor',
			array(
				'label' => esc_html__( '[Hover/Active] Menu Border Color', 'lastudio-kit' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--lakit-doc-headerts--menua-bdcolor: {{VALUE}}',
				),
				'condition' => array(
					'lakit_doc_enable_header_transparency' => 'yes',
				),
			)
		);

		$stack->add_control(
			'lakit_doc_header_heading__btn',
			[
				'label' => esc_html__( 'Header Button', 'lastudio-kit' ),
				'type'  => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'lakit_doc_enable_header_transparency' => 'yes',
				),
			]
		);
		$stack->add_control(
			'lakit_doc_header_button_color',
			array(
				'label' => esc_html__( 'Header Button Color', 'lastudio-kit' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--lakit-doc-headerts-button-color: {{VALUE}}',
				),
				'condition' => array(
					'lakit_doc_enable_header_transparency' => 'yes',
				),
			)
		);
		$stack->add_control(
			'lakit_doc_header_button_bgcolor',
			array(
				'label' => esc_html__( 'Header Button Background Color', 'lastudio-kit' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--lakit-doc-headerts-button-bgcolor: {{VALUE}}',
				),
				'condition' => array(
					'lakit_doc_enable_header_transparency' => 'yes',
				),
			)
		);
        $stack->add_control(
            'lakit_doc_header_button_border_color',
            array(
                'label' => esc_html__( 'Header Button Border Color', 'lastudio-kit' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}}' => '--lakit-doc-headerts-button-border-color: {{VALUE}}',
                ),
                'condition' => array(
                    'lakit_doc_enable_header_transparency' => 'yes',
                ),
            )
        );
		$stack->add_control(
			'lakit_doc_header_button_hover_color',
			array(
				'label' => esc_html__( 'Header Button Hover Color', 'lastudio-kit' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--lakit-doc-headerts-button-hover-color: {{VALUE}}',
				),
				'condition' => array(
					'lakit_doc_enable_header_transparency' => 'yes',
				),
			)
		);
		$stack->add_control(
			'lakit_doc_header_button_hover_bgcolor',
			array(
				'label' => esc_html__( 'Header Button Hover Background Color', 'lastudio-kit' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--lakit-doc-headerts-button-hover-bgcolor: {{VALUE}}',
				),
				'condition' => array(
					'lakit_doc_enable_header_transparency' => 'yes',
				),
			)
		);
        $stack->add_control(
            'lakit_doc_header_button_hover_border_color',
            array(
                'label' => esc_html__( 'Header Button Hover Border Color', 'lastudio-kit' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}}' => '--lakit-doc-headerts-button-hover-border-color: {{VALUE}}',
                ),
                'condition' => array(
                    'lakit_doc_enable_header_transparency' => 'yes',
                ),
            )
        );
	}

	public function body_class( $classes ){
		$tmp = join('|', $classes);
		preg_match('/elementor-page-(\d+)/i', $tmp, $matches);
		if(!empty($matches[1])){
			$settings = get_post_meta( $matches[1], '_elementor_page_settings', true );
			if(!empty($settings['lakit_doc_enable_header_transparency']) && $settings['lakit_doc_enable_header_transparency'] == 'yes'){
				$classes[] = 'lakitdoc-enable-header-transparency';
				$classes[] = 'lakitdoc-h-t';
				if(!empty($settings['lakit_doc_swap_logo']) && $settings['lakit_doc_swap_logo'] == 'yes'){
					$classes[] = 'lakitdoc-swap-logo';
					$classes[] = 'lakitdoc-s-l';
				}
			}
            $classes[] = 'lakit--enabled';
		}
        $classes[] = 'lakit-nojs';
		return $classes;
	}

    public function wp_body_open(){
        ?>
        <script>document?.body?.classList?.remove('lakit-nojs')</script>
        <?php
    }

    private function vheader_css(){
        $vheader_css = "
.lakit-vheader--hide__DEVICE__.lakit--is-vheader {
  position: relative
}
.lakit-vheader--hide__DEVICE__.lakit--is-vheader.lakit-vheader-pleft {
  padding-left: var(--lakit-vheader-width)
}
.lakit-vheader--hide__DEVICE__.lakit--is-vheader.lakit-vheader-pright {
  padding-right: var(--lakit-vheader-width)
}
.lakit-vheader--hide__DEVICE__.lakit--is-vheader > .elementor-location-header.elementor-edit-area {
  position: static
}
.lakit-vheader--hide__DEVICE__.lakit--is-vheader > .elementor-location-header > .elementor-element:first-child,
.lakit-vheader--hide__DEVICE__.lakit--is-vheader > .elementor-location-header > .elementor-section-wrap > .elementor-element:first-child {
  position: absolute;
  top: 0;
  bottom: 0;
  width: var(--lakit-vheader-width);
  height: auto;
  z-index: 3;
  min-height: calc(100vh - var(--lakit-adminbar-height))
}
.lakit-vheader--hide__DEVICE__.lakit--is-vheader.lakit-vheader-pleft > .elementor-location-header > .elementor-section-wrap > .e-parent:first-child,
.lakit-vheader--hide__DEVICE__.lakit--is-vheader.lakit-vheader-pleft > .elementor-location-header > .e-parent:first-child {
  left: 0
}
.lakit-vheader--hide__DEVICE__.lakit--is-vheader.lakit-vheader-pright > .elementor-location-header > .elementor-section-wrap > .e-parent:first-child,
.lakit-vheader--hide__DEVICE__.lakit--is-vheader.lakit-vheader-pright > .elementor-location-header > .e-parent:first-child {
  right: 0
}
.lakit-vheader--hide__DEVICE__.lakit--is-vheader > .elementor-location-header > .elementor-section-wrap > .e-parent:first-child > .e-con-inner,
.lakit-vheader--hide__DEVICE__.lakit--is-vheader > .elementor-location-header > .e-parent:first-child > .e-con-inner{
  height: auto;
  position: sticky;
  top: var(--lakit-adminbar-height);
  left: 0;
  min-height: calc(100vh - var(--lakit-adminbar-height))
}
.lakit-vheader--hide__DEVICE__.lakit--is-vheader > .elementor-location-header > .elementor-section-wrap > .e-parent:first-child > .elementor-container,
.lakit-vheader--hide__DEVICE__.lakit--is-vheader > .elementor-location-header > .e-parent:first-child > .elementor-container {
  flex-flow: row wrap;
  height: auto;
  position: sticky;
  top: var(--lakit-adminbar-height);
  left: 0;
  min-height: calc(100vh - var(--lakit-adminbar-height))
}
.lakit-vheader--hide__DEVICE__.lakit--is-vheader > .elementor-location-header > .elementor-section-wrap > .e-parent:first-child > .elementor-container > .elementor-column,
.lakit-vheader--hide__DEVICE__.lakit--is-vheader > .elementor-location-header > .e-parent:first-child > .elementor-container > .elementor-column {
  width: 100%
}
.lakit-vheader--hide__DEVICE__.lakit--is-vheader > .elementor-location-header > .e-con:first-child,
.lakit-vheader--hide__DEVICE__.lakit--is-vheader > .elementor-location-header > .elementor-section-wrap > .e-con:first-child {
  display: block
}
";
        $css = ':root{--lakit-adminbar-height: 0px}';
        $breakpoints = lastudio_kit_helper()->get_active_breakpoints();
        asort($breakpoints);
        foreach ($breakpoints as $device_name => $device_value){
            if(in_array($device_name, ['tablet', 'mobile_extra', 'mobile'])){
                $css .= '@media(min-width:'.($device_value+1).'px){'.str_replace('__DEVICE__', $device_name, $vheader_css).'}';
            }
        }
        return lastudio_kit_helper()->minify_css($css);
    }
}
