<?php

namespace LaStudioKitExtensions\Events;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use LaStudioKitExtensions\Module_Base;

class Module extends Module_Base {

    /**
     * Module version.
     *
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Module directory path.
     *
     * @since 1.0.0
     * @access protected
     * @var string $path
     */
    protected $path;

    /**
     * Module directory URL.
     *
     * @since 1.0.0
     * @access protected
     * @var string $url.
     */
    protected $url;

    public static function is_active(){
        $available_extension = lastudio_kit_settings()->get_option('avaliable_extensions', []);
        return !empty($available_extension['event_content_type']) && filter_var($available_extension['event_content_type'], FILTER_VALIDATE_BOOLEAN);
    }

    public function __construct()
    {
        $this->path = lastudio_kit()->plugin_path('includes/extensions/events/');
        $this->url  = lastudio_kit()->plugin_url('includes/extensions/events/');

		add_action( 'init', [ $this, 'register_content_type' ] );
	    add_action( 'init', [ $this, 'add_metaboxes' ], -5 );

	    add_action( 'elementor/widgets/register', function ($widgets_manager){
		    $widgets_manager->register( new Widgets\Events() );
	    } );
    }

	public function register_content_type(){

		register_post_type( 'la_event', apply_filters('lastudio-kit/admin/events/args', [
			'labels'                => [
				'name'          => _x( 'Events', 'CPT','lastudio-kit' ),
				'singular_name' => _x( 'Events', 'CPT','lastudio-kit' ),
			],
			'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
			'menu_icon'             => 'dashicons-calendar-alt',
			'public'                => true,
			'menu_position'         => 8,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'rewrite'               => array( 'slug' => 'event' )
		]));
		register_taxonomy( 'la_event_type', 'la_event', apply_filters('lastudio-kit/admin/event_types/args', [
			'hierarchical'      => true,
			'show_in_nav_menus' => true,
			'labels'            => array(
				'name'          => _x( 'Types', 'CPT','lastudio-kit' ),
				'singular_name' => _x( 'Type', 'CPT','lastudio-kit' )
			),
			'query_var'         => true,
			'show_admin_column' => true,
			'rewrite'           => array('slug' => 'event-type')
		]));
	}

	public function add_metaboxes() {

		lastudio_kit_post_meta()->add_options( array (
			'id'            => 'lastudiokit-event-settings',
			'title'         => esc_html_x( 'Event Settings', 'CPT Event Metabox','lastudio-kit' ),
			'page'          => array( 'la_event' ),
			'context'       => 'normal',
			'priority'      => 'high',
			'callback_args' => false,
			'admin_columns' => array(
				'lakit_thumb' => array(
					'label'    => sprintf('<span class="lakit-image">%1$s</span>', _x('Images', 'CPT Event Metabox','lastudio-kit')),
					'callback' => array( $this, 'metabox_callback__column' ),
					'position' => 1,
				),
				'event_start_date' => array(
					'label'    => _x( 'Event Date', 'CPT Event Metabox','lastudio-kit' ),
					'callback' => array( $this, 'metabox_callback__column' ),
				),
				'event_status' => array(
					'label'    => _x( 'Status', 'CPT Event Metabox','lastudio-kit' ),
					'callback' => array( $this, 'metabox_callback__column' ),
				),
			),
			'fields'        => array(
				'event_status' => array(
					'type'        => 'select',
					'title'       => esc_html_x( 'Event Status','CPT Event Metabox', 'lastudio-kit' ),
					'description' => esc_html_x( 'Choose a status for tickets for this event.', 'CPT Event Metabox','lastudio-kit' ),
					'options'     => array(
						'upcoming'  => esc_html_x('Up Coming', 'CPT Event Metabox','lastudio-kit'),
						'past'      => esc_html_x('Past', 'CPT Event Metabox','lastudio-kit'),
						'cancelled'  => esc_html_x('Cancelled','CPT Event Metabox', 'lastudio-kit'),
						'sold_out'   => esc_html_x('Sold Out', 'CPT Event Metabox','lastudio-kit'),
					),
				),
				'event_start_date' => array(
					'type'        => 'text',
					'input_type'  => 'date',
					'title'       => esc_html_x( 'Start Date', 'CPT Event Metabox','lastudio-kit' ),
					'description' => esc_html_x( 'Formatted like "YYYY-MM-DD".','CPT Event Metabox', 'lastudio-kit' ),
					'placeholder' => 'YYYY-MM-DD'
				),
				'event_end_date' => array(
					'type'        => 'text',
					'input_type'  => 'date',
					'title'       => esc_html_x( 'End Date', 'CPT Event Metabox','lastudio-kit' ),
					'description' => esc_html_x( 'Formatted like "YYYY-MM-DD".', 'CPT Event Metabox','lastudio-kit' ),
					'placeholder' => 'YYYY-MM-DD'
				),
				'event_time' => array(
					'type'        => 'text',
					'title'       => esc_html_x( 'Time', 'CPT Event Metabox','lastudio-kit' ),
					'description' => esc_html_x( 'Set a time for this event.', 'CPT Event Metabox','lastudio-kit' ),
					'placeholder' => 'HH:MM'
				),
				'event_location' => array(
					'type'        => 'text',
					'title'       => esc_html_x( 'Location','CPT Event Metabox', 'lastudio-kit' ),
					'description' => esc_html_x( 'Set a location for the event.', 'CPT Event Metabox','lastudio-kit' ),
					'placeholder' => esc_html_x('e.g: "Bruges, Belgium" or " New Orleans, LA"', 'CPT Event Metabox','lastudio-kit')
				),
				'event_stage' => array(
					'type'        => 'text',
					'title'       => esc_html_x( 'Stage', 'CPT Event Metabox','lastudio-kit' ),
					'description' => esc_html_x( 'Select a stage for the event.', 'CPT Event Metabox','lastudio-kit' ),
					'placeholder' => esc_html_x( 'Stage', 'CPT Event Metabox','lastudio-kit' ),
				),
				'event_website' => array(
					'type'        => 'text',
					'title'       => esc_html_x( 'Website', 'CPT Event Metabox','lastudio-kit' ),
					'description' => esc_html_x( 'Input the website for this event.','CPT Event Metabox', 'lastudio-kit' ),
					'placeholder' => esc_html_x( 'https://website.com', 'CPT Event Metabox','lastudio-kit' ),
				),
				'event_organized_by' => array(
					'type'        => 'text',
					'title'       => esc_html_x( 'Organized By', 'CPT Event Metabox','lastudio-kit' ),
					'description' => esc_html_x( 'Input the name of the event organizer.', 'CPT Event Metabox','lastudio-kit' ),
					'placeholder' => esc_html_x( 'Organized', 'CPT Event Metabox','lastudio-kit' ),
				),
				'event_ticket_link' => array(
					'type'        => 'text',
					'title'       => esc_html_x( 'Buy Tickets Link', 'CPT Event Metabox','lastudio-kit' ),
					'description' => esc_html_x( 'Input a link to the website where tickets can be purchased for this event.', 'CPT Event Metabox','lastudio-kit' ),
					'placeholder' => esc_html_x( 'https://website.com', 'CPT Event Metabox','lastudio-kit' ),
				),
				'event_backtolink' => array(
					'type'        => 'text',
					'title'       => esc_html_x( 'Back to Link', 'CPT Event Metabox','lastudio-kit' ),
					'description' => esc_html_x( 'Input a "back to" link from the event\'s single page.','CPT Event Metabox', 'lastudio-kit' ),
					'placeholder' => esc_html_x( 'https://website.com', 'CPT Event Metabox','lastudio-kit' ),
				),
			),
		) );
	}

	public function metabox_callback__column( $column, $post_id ){
		if($column === 'lakit_thumb'){
			return printf('<a href="%2$s">%1$s</a>', get_the_post_thumbnail($post_id), esc_url(get_edit_post_link($post_id)) );
		}
		elseif ( $column === 'event_status' ){
			$value = get_post_meta( $post_id, $column, true );
			$opts = array(
				'upcoming'  => esc_html_x('Up Coming', 'CPT Event Metabox','lastudio-kit'),
				'past'      => esc_html_x('Past', 'CPT Event Metabox','lastudio-kit'),
				'cancelled'  => esc_html_x('Cancelled','CPT Event Metabox', 'lastudio-kit'),
				'sold_out'   => esc_html_x('Sold Out', 'CPT Event Metabox','lastudio-kit'),
			);
			if( $value && isset($opts[$value]) ){
				return printf('<span>%1$s</span>', esc_html($opts[$value]));
			}
			else{
				return printf('<span>%1$s</span>', esc_html_x('N/A', 'CPT Event Metabox','lastudio-kit'));
			}
		}
		elseif ( $column === 'event_start_date' ){
			$_date = get_post_meta( $post_id, $column, true );
			$_time = get_post_meta( $post_id, 'event_time', true );
			return printf('<span>%1$s %2$s</span>', esc_html($_date), esc_html($_time));
		}
	}
}