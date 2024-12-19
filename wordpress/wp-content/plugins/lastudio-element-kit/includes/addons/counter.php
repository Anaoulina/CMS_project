<?php

/**
 * Class: LaStudioKit_Counter
 * Name: Counter
 * Slug: lakit-counter
 */

namespace Elementor;

if (!defined('WPINC')) {
    die;
}

/**
 * Countdown_Timer Widget
 */
class LaStudioKit_Counter extends LaStudioKit_Base {

    protected function enqueue_addon_resources(){
        if ( ! lastudio_kit_settings()->is_combine_js_css() ) {
	          $this->add_script_depends( 'lastudio-kit-w__counter' );
            if ( ! lastudio_kit()->is_optimized_css_mode() ) {
                wp_register_style( $this->get_name(), lastudio_kit()->plugin_url( 'assets/css/addons/counter.min.css' ), [], lastudio_kit()->get_version() );
                $this->add_style_depends( $this->get_name() );
            }
        }
    }

    public function get_widget_css_config( $widget_name ) {
        $file_url  = lastudio_kit()->plugin_url( 'assets/css/addons/counter.min.css' );
        $file_path = lastudio_kit()->plugin_path( 'assets/css/addons/counter.min.css' );

        return [
            'key'       => $widget_name,
            'version'   => lastudio_kit()->get_version( true ),
            'file_path' => $file_path,
            'data'      => [
                'file_url' => $file_url
            ]
        ];
    }

    public function get_name() {
        return 'lakit-counter';
    }

    protected function get_widget_title() {
        return esc_html__( 'Counter', 'lastudio-kit' );
    }

    public function get_icon() {
        return 'eicon-counter';
    }

    protected function register_controls() {

        $start = is_rtl() ? 'right' : 'left';
        $end = ! is_rtl() ? 'right' : 'left';

        $this->start_controls_section(
            'section_counter',
            [
                'label' => esc_html__( 'Counter', 'elementor' ),
            ]
        );

        $this->add_control(
            'starting_number',
            [
                'label' => esc_html__( 'Starting Number', 'elementor' ),
                'type' => Controls_Manager::NUMBER,
                'default' => 0,
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'ending_number',
            [
                'label' => esc_html__( 'Ending Number', 'elementor' ),
                'type' => Controls_Manager::NUMBER,
                'default' => 100,
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'prefix',
            [
                'label' => esc_html__( 'Number Prefix', 'elementor' ),
                'type' => Controls_Manager::TEXT,
                'dynamic' => [
                    'active' => true,
                ],
                'ai' => [
                    'active' => false,
                ],
                'default' => '',
            ]
        );

        $this->add_control(
            'suffix',
            [
                'label' => esc_html__( 'Number Suffix', 'elementor' ),
                'type' => Controls_Manager::TEXT,
                'dynamic' => [
                    'active' => true,
                ],
                'ai' => [
                    'active' => false,
                ],
                'default' => '',
            ]
        );

        $this->add_control(
            'duration',
            [
                'label' => esc_html__( 'Animation Duration', 'elementor' ) . ' (ms)',
                'type' => Controls_Manager::NUMBER,
                'default' => 2000,
                'min' => 100,
                'step' => 100,
            ]
        );
        $this->add_control(
            'counter_animation',
            [
                'label' => esc_html__( 'Animation', 'elementor' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'count',
                'options'      => array(
                    'count'   => esc_html__( 'Count', 'lastudio-kit' ),
                    'slide'   => esc_html__( 'Slide', 'lastudio-kit' )
                ),
            ]
        );

        $this->add_control(
            'thousand_separator',
            [
                'label' => esc_html__( 'Thousand Separator', 'elementor' ),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_on' => esc_html__( 'Show', 'elementor' ),
                'label_off' => esc_html__( 'Hide', 'elementor' ),
            ]
        );

        $this->add_control(
            'thousand_separator_char',
            [
                'label' => esc_html__( 'Separator', 'elementor' ),
                'type' => Controls_Manager::SELECT,
                'condition' => [
                    'thousand_separator' => 'yes',
                ],
                'options' => [
                    '' => 'Default',
                    '.' => 'Dot',
                    ' ' => 'Space',
                    '_' => 'Underline',
                    "'" => 'Apostrophe',
                ],
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => esc_html__( 'Title', 'elementor' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'separator' => 'before',
                'dynamic' => [
                    'active' => true,
                ],
                'default' => esc_html__( 'Cool Number', 'elementor' ),
            ]
        );

        $this->add_control(
            'title_tag',
            [
                'label' => esc_html__( 'Title HTML Tag', 'elementor' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'div',
                    'span' => 'span',
                    'p' => 'p',
                ],
                'default' => 'div',
                'condition' => [
                    'title!' => '',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_counter_style',
            [
                'label' => esc_html__( 'Counter', 'elementor' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'title_position',
            [
                'label' => esc_html__( 'Title Position', 'elementor' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'before' => [
                        'title' => esc_html__( 'Before', 'elementor' ),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'after' => [
                        'title' => esc_html__( 'After', 'elementor' ),
                        'icon' => 'eicon-v-align-bottom',
                    ],

                    'start' => [
                        'title' => esc_html__( 'Start', 'elementor' ),
                        'icon' => "eicon-h-align-$start",
                    ],
                    'end' => [
                        'title' => esc_html__( 'End', 'elementor' ),
                        'icon' => "eicon-h-align-$end",
                    ],
                ],
                'selectors_dictionary' => [
                    'before' => 'flex-direction: column;',
                    'after' => 'flex-direction: column-reverse;',
                    'start' => 'flex-direction: row;',
                    'end' => 'flex-direction: row-reverse;',
                ],
                'selectors' => [
                    '{{WRAPPER}} .lakit-counter' => '{{VALUE}}',
                ],
                'condition' => [
                    'title!' => '',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_horizontal_alignment',
            [
                'label' => esc_html__( 'Title Horizontal Alignment', 'elementor' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__( 'Start', 'elementor' ),
                        'icon' => "eicon-h-align-$start",
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'elementor' ),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'end' => [
                        'title' => esc_html__( 'End', 'elementor' ),
                        'icon' => "eicon-h-align-$end",
                    ],
                ],
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .lakit-counter-title' => 'justify-content: {{VALUE}};',
                ],
                'condition' => [
                    'title!' => '',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_vertical_alignment',
            [
                'label' => esc_html__( 'Title Vertical Alignment', 'elementor' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__( 'Top', 'elementor' ),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Middle', 'elementor' ),
                        'icon' => 'eicon-v-align-middle',
                    ],
                    'end' => [
                        'title' => esc_html__( 'Bottom', 'elementor' ),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .lakit-counter-title' => 'align-items: {{VALUE}};',
                ],
                'condition' => [
                    'title!' => '',
                    'title_position' => [ 'start', 'end' ],
                ],
            ]
        );

        $this->add_responsive_control(
            'title_gap',
            [
                'label' => esc_html__( 'Title Gap', 'elementor' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}} .lakit-counter' => 'gap: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'title!' => '',
                    'title_position' => [ '', 'before', 'after' ],
                ],
            ]
        );

        $this->add_responsive_control(
            'number_position',
            [
                'label' => esc_html__( 'Number Position', 'elementor' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__( 'Start', 'elementor' ),
                        'icon' => "eicon-h-align-$start",
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'elementor' ),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'end' => [
                        'title' => esc_html__( 'End', 'elementor' ),
                        'icon' => "eicon-h-align-$end",
                    ],
                    'stretch' => [
                        'title' => esc_html__( 'Stretch', 'elementor' ),
                        'icon' => 'eicon-grow',
                    ],
                ],
                'selectors_dictionary' => [
                    'start' => 'text-align: {{VALUE}}; --counter-prefix-grow: 0; --counter-suffix-grow: 1; --counter-number-grow: 0;',
                    'center' => 'text-align: {{VALUE}}; --counter-prefix-grow: 1; --counter-suffix-grow: 1; --counter-number-grow: 0;',
                    'end' => 'text-align: {{VALUE}}; --counter-prefix-grow: 1; --counter-suffix-grow: 0; --counter-number-grow: 0;',
                    'stretch' => '--counter-prefix-grow: 0; --counter-suffix-grow: 0; --counter-number-grow: 1;',
                ],
                'selectors' => [
                    '{{WRAPPER}} .lakit-counter-number-wrapper' => '{{VALUE}}',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'number_alignment',
            [
                'label' => esc_html__( 'Number Alignment', 'elementor' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__( 'Start', 'elementor' ),
                        'icon' => "eicon-text-align-$start",
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'elementor' ),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'end' => [
                        'title' => esc_html__( 'End', 'elementor' ),
                        'icon' => "eicon-text-align-$end",
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .lakit-counter-number' => 'text-align: {{VALUE}};',
                ],
                'condition' => [
                    'number_position' => 'stretch',
                ],
            ]
        );

        $this->add_responsive_control(
            'number_gap',
            [
                'label' => esc_html__( 'Number Gap', 'elementor' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}} .lakit-counter-number-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
                ],
                'conditions' => [
                    'relation' => 'and',
                    'terms' => [
                        [
                            'name' => 'number_position',
                            'operator' => '!==',
                            'value' => 'stretch',
                        ],
                        [
                            'relation' => 'or',
                            'terms' => [
                                [
                                    'name' => 'prefix',
                                    'operator' => '!==',
                                    'value' => '',
                                ],
                                [
                                    'name' => 'suffix',
                                    'operator' => '!==',
                                    'value' => '',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_number',
            [
                'label' => esc_html__( 'Number', 'elementor' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'number_color',
            [
                'label' => esc_html__( 'Text Color', 'elementor' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .lakit-counter-number-wrapper' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography_number',
                'selector' => '{{WRAPPER}} .lakit-counter-number-wrapper',
            ]
        );

        $this->add_group_control(
            Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'number_stroke',
                'selector' => '{{WRAPPER}} .lakit-counter-number-wrapper',
            ]
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'number_shadow',
                'selector' => '{{WRAPPER}} .lakit-counter-number-wrapper',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_title',
            [
                'label' => esc_html__( 'Title', 'elementor' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'title!' => '',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => esc_html__( 'Text Color', 'elementor' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .lakit-counter-title' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'title!' => '',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography_title',
                'selector' => '{{WRAPPER}} .lakit-counter-title',
                'condition' => [
                    'title!' => '',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'title_stroke',
                'selector' => '{{WRAPPER}} .lakit-counter-title',
                'condition' => [
                    'title!' => '',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'title_shadow',
                'selector' => '{{WRAPPER}} .lakit-counter-title',
                'condition' => [
                    'title!' => '',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function content_template() {
        ?>
        <#
        view.addRenderAttribute( 'lakit-counter', 'class', 'lakit-counter' );

        view.addRenderAttribute( 'counter-number', 'class', 'lakit-counter-number-wrapper' );

        view.addRenderAttribute(
            'counter',
            {
                'class': 'lakit-counter-number',
                'data-duration': settings.duration,
                'data-to-value': settings.ending_number,
                'data-from-value': settings.starting_number,
                'data-animation': settings.counter_animation
            }
        );

        if ( settings.thousand_separator ) {
        const delimiter = settings.thousand_separator_char ? settings.thousand_separator_char : ',';
        view.addRenderAttribute( 'counter', 'data-delimiter', delimiter );
        }

        view.addRenderAttribute( 'prefix', 'class', 'lakit-counter-number-prefix' );

        view.addRenderAttribute( 'suffix', 'class', 'lakit-counter-number-suffix' );

        view.addRenderAttribute( 'counter-title', 'class', 'lakit-counter-title' );

        view.addInlineEditingAttributes( 'counter-title' );

        const titleTag = elementor.helpers.validateHTMLTag( settings.title_tag );
        #>
        <div {{{ view.getRenderAttributeString( 'lakit-counter' ) }}}>
        <# if ( settings.title ) {
        #><{{ titleTag }} {{{ view.getRenderAttributeString( 'counter-title' ) }}}>{{{ settings.title }}}</{{ titleTag }}><#
        } #>
        <div {{{ view.getRenderAttributeString( 'counter-number' ) }}}>
        <span {{{ view.getRenderAttributeString( 'prefix' ) }}}>{{{ settings.prefix }}}</span>
        <span {{{ view.getRenderAttributeString( 'counter' ) }}}>{{{ settings.starting_number }}}</span>
        <span {{{ view.getRenderAttributeString( 'suffix' ) }}}>{{{ settings.suffix }}}</span>
        </div>
        </div>
        <?php
    }

    /**
     * Render counter widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $this->add_render_attribute( 'lakit-counter', 'class', 'lakit-counter' );

        $this->add_render_attribute( 'counter-number', 'class', 'lakit-counter-number-wrapper' );

        $this->add_render_attribute(
            'counter',
            [
                'class' => 'lakit-counter-number',
                'data-duration' => $settings['duration'],
                'data-to-value' => $settings['ending_number'],
                'data-from-value' => $settings['starting_number'],
                'data-animation' => $settings['counter_animation'],
            ]
        );

        if ( ! empty( $settings['thousand_separator'] ) ) {
            $delimiter = empty( $settings['thousand_separator_char'] ) ? ',' : $settings['thousand_separator_char'];
            $this->add_render_attribute( 'counter', 'data-delimiter', $delimiter );
        }

        $this->add_render_attribute( 'prefix', 'class', 'lakit-counter-number-prefix' );

        $this->add_render_attribute( 'suffix', 'class', 'lakit-counter-number-suffix' );

        $this->add_render_attribute( 'counter-title', 'class', 'lakit-counter-title' );

        $this->add_inline_editing_attributes( 'counter-title' );

        $title_tag = Utils::validate_html_tag( $settings['title_tag'] );
        ?>
        <div <?php $this->print_render_attribute_string( 'lakit-counter' ); ?>>
            <?php
            if ( $settings['title'] ) :
                ?><<?php Utils::print_validated_html_tag( $title_tag ); ?> <?php $this->print_render_attribute_string( 'counter-title' ); ?>><?php $this->print_unescaped_setting( 'title' ); ?></<?php Utils::print_validated_html_tag( $title_tag ); ?>><?php
            endif;
            ?>
                <div <?php $this->print_render_attribute_string( 'counter-number' ); ?>>
                    <span <?php $this->print_render_attribute_string( 'prefix' ); ?>><?php $this->print_unescaped_setting( 'prefix' ); ?></span>
                    <span <?php $this->print_render_attribute_string( 'counter' ); ?>><?php $this->print_unescaped_setting( 'ending_number' ); ?></span>
                    <span <?php $this->print_render_attribute_string( 'suffix' ); ?>><?php $this->print_unescaped_setting( 'suffix' ); ?></span>
                </div>
        </div>
        <?php
    }
}