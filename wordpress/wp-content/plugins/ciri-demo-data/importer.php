<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

//defined('IMPORT_DEBUG') or define('IMPORT_DEBUG', true);

class LaStudio_Importer {

	protected $fetch_attachments = true;

	protected $demo_data, $current_id, $setting_args, $wxr_import, $theme_name;

	protected $demo_site_url;

	protected $logger = null;

    protected $missing_menu_items = array();
	protected $options = [];
    protected $mapping = [];
    protected $requires_remapping = [];
    protected $exists = [];

	public function __construct( $theme_name = '', $demo_data = array(), $demo_site = '' ) {
		$this->theme_name  = $theme_name;
		$this->demo_data  = $demo_data;
		$this->demo_site_url = $demo_site;
		$this->current_site_url = site_url('/');

        // Initialize some important variables
        $empty_types = array(
            'post'    => array(),
            'comment' => array(),
            'term'    => array(),
            'user'    => array(),
        );

        $this->mapping = $empty_types;
        $this->mapping['user_slug'] = array();
        $this->mapping['term_id'] = array();
        $this->requires_remapping = $empty_types;
        $this->exists = $empty_types;
        $this->options = [
            'prefill_existing_posts'    => true,
            'prefill_existing_comments' => true,
            'prefill_existing_terms'    => true,
            'update_attachment_guids'   => false,
            'fetch_attachments'         => false,
            'aggressive_url_search'     => false,
            'default_author'            => null
        ];

		$this->init();
	}

	private function init(){

		global $pagenow;

		if( 'tools.php' == $pagenow ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}

		if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			if( isset($_REQUEST['action']) && $_REQUEST['action'] == 'lastudio-importer' ) {
				$this->set_logger();
			}
		}

		add_action( 'wp_ajax_lastudio-importer', array( $this, 'action_process_importer' ) );
		add_filter( 'LaStudio_Importer/widgets/widget_setting_object', array( $this, 'fixed_nav_menu_widget_settings' ) );

		add_action( 'init', array( $this, 'clear_imported_data'), 1);

        add_filter( 'https_ssl_verify', '__return_false' );
        add_filter( 'https_local_ssl_verify', '__return_false' );
        add_filter( 'http_request_host_is_external', '__return_true' );
	}

	public static function admin_enqueue_scripts(){
		wp_register_script( 'eventsource', plugin_dir_url( __FILE__ ) . 'assets/js/eventsource.js' );
		wp_enqueue_script( 'lastudio-importer-js', plugin_dir_url( __FILE__ ) . 'assets/js/import.js' , array( 'jquery', 'eventsource' ) );
		wp_localize_script( 'lastudio-importer-js', 'lastudio_importer',
			array(
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'ajax_nonce'  => wp_create_nonce( 'lastudio-importer-security' ),
				'loader_text' => esc_html__( 'Importing now, please wait!', 'la-studio' ),
			)
		);
		wp_enqueue_style( 'lastudio-importer-css', plugin_dir_url( __FILE__ ) . 'assets/css/import.css', array()  );
	}

	public static function fixed_nav_menu_widget_settings( $widgets ) {
		if(isset($widgets->nav_menu)){
			$nav_menu = wp_get_nav_menu_object($widgets->nav_menu);
			if(isset($nav_menu->term_id)){
				$widgets->nav_menu = $nav_menu->term_id;
			}
		}
		return $widgets;
	}

	/**
	 * Get data from filters, after the theme has loaded and instantiate the importer.
	 */

	public function get_demo_data(){
		return $this->demo_data;
	}

	public function action_process_importer(){

		if( false === check_ajax_referer( 'lastudio-importer-security', 'security', false ) ) {
			wp_send_json( __( 'Access define!', 'la-studio' ) );
		}
		if( !current_user_can( 'import' ) ) {
			wp_send_json( __( 'Access define!', 'la-studio' ) );
		}

		$id = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : false;
		$args = shortcode_atts( array(
			'content'   => true,
			'product'   => true,
			'widget'    => true,
			'slider'    => true,
			'option'    => true,
			'elementor' => true,
			'fetch_attachments' => true
		), isset($_REQUEST['args']) ? $_REQUEST['args'] : array() );

		if( empty( $id ) || !array_key_exists( $id, $this->demo_data ) ) {
			wp_send_json( __( 'Access define!', 'la-studio' ) );
		}

		$this->current_id = $id;
		$this->setting_args = $args;

		if(isset($_REQUEST['start_import']) && $_REQUEST['start_import'] == 'true' && !empty($this->current_id) && !empty($this->demo_data)){
			do_action('LaStudio_Importer/copy_image');
			add_filter( 'intermediate_image_sizes_advanced', '__return_null' );
			update_option('elementor_unfiltered_files_upload', 1);
			$this->_start_import_stream();
		}
		else{
			if(!isset($this->importer_is_running)){
				$this->_start_ajax_importer_handling();
			}
		}
	}

	protected function set_logger(){
		$this->logger = new LaStudio_Importer_Logger_ServerSentEvents();
	}

	protected function _start_ajax_importer_handling(){
		/**
		 * Check data-sample.xml has imported
		 */
		$opts = get_option($this->theme_name . '_imported_demos');
		if(!empty($opts)){
			/*
			 * If content has been imported. We need import theme options, custom setting, slider ... etc.
			 */

//			if( array_key_exists($this->current_id, $opts) ) {
//				$this->setting_args = array(
//					'content'   => false,
//					'product'   => false,
//					'widget'    => false,
//					'slider'    => false,
//					'option'    => true,
//					'fetch_attachments' => true
//				);
//			}

			$ajax_import_url = add_query_arg(
				array(
					'action' => 'lastudio-importer',
					'id'      => $this->current_id,
					'args'    => $this->setting_args,
					'security' => $_REQUEST['security'],
					'start_import' => 'true'
					//'start_import_without_content' => 'true'
				),
				admin_url( 'admin-ajax.php' )
			);
		}
		else{
			$ajax_import_url = add_query_arg(
				array(
					'action' => 'lastudio-importer',
					'id'      => $this->current_id,
					'args'    => $this->setting_args,
					'security' => $_REQUEST['security'],
					'start_import' => 'true'
				),
				admin_url( 'admin-ajax.php' )
			);
		}

		$fetch_attachments = ( ! empty( $this->setting_args['fetch_attachments'] ) && $this->setting_args['fetch_attachments'] && $this->allow_fetch_attachments() );
		$this->fetch_attachments = $fetch_attachments;
		$settings = ['fetch_attachments' => $fetch_attachments];

		update_option('_wxr_import_settings', $settings);

		wp_send_json(array(
			'status' => 'success',
			'data'  => array(
				'count' => array(
					'posts' => 0,
					'media' => 0,
					'users' => 0,
					'comments' => 0,
					'terms' => 0,
				),
				'url' => $ajax_import_url,
				'strings' => array(
					'complete' => __( 'Import complete!', 'la-studio' ),
				)
			)
		));

	}

	protected function _start_import_stream(){

		@ini_set( 'output_buffering', 'off' );
		@ini_set( 'zlib.output_compression', false );

		if ( $GLOBALS['is_nginx'] ) {
			header( 'X-Accel-Buffering: no' );
			header( 'Content-Encoding: none' );
		}
		// Start the event stream.
		header( 'Content-Type: text/event-stream' );
		$demo_selected = $this->demo_data[ $this->current_id ];

		$settings = get_option('_wxr_import_settings');

		if ( empty( $settings ) ) {
			// Tell the browser to stop reconnecting.
			status_header( 204 );
			exit;
		}

		// 2KB padding for IE
		echo ':' . str_repeat(' ', 2048) . "\n\n";

		// Time to run the import!
		set_time_limit(0);

		// Ensure we're not buffered.
		wp_ob_end_flush_all();
		flush();

		// Keep track of our progress
		add_action( 'lastudio_importer.pre_process.post_meta', array( $this, 'imported_post_meta' ), 10, 2 );
		add_action( 'lastudio_importer.processed.post', array( $this, 'imported_post' ), 10, 2 );
		add_action( 'lastudio_importer.process_failed.post', array( $this, 'imported_post' ), 10, 2 );
		add_action( 'lastudio_importer.processed.term', array( $this, 'imported_term' ) );
		add_action( 'lastudio_importer.process_failed.term', array( $this, 'imported_term' ) );


		// Clean up some memory
		unset( $settings );

		// Flush once more.
		flush();
		if( $this->setting_args['content'] && !empty( $demo_selected['content'] ) ) {

			if(!isset($this->running_import_content)){

                $err = $this->process_import_wp_default();

				// Let the browser know we're done.
				$complete = array(
					'action' => 'ImportingContent',
					'error' => false,
				);
				if ( is_wp_error( $err ) ) {
					$complete['error'] = $err->get_error_message();
				}
				$this->running_import_content = false;
				$this->emit_sse_message( $complete );
			}
		}

        if( $this->setting_args['product'] && !empty( $demo_selected['product'] ) ) {

            if(!isset($this->running_import_product_content)){

                $err = $this->process_import_product();

                // Let the browser know we're done.
                $complete = array(
                    'action' => 'ImportingProductContent',
                    'error' => false,
                );
                if ( is_wp_error( $err ) ) {
                    $complete['error'] = $err->get_error_message();
                }
                $this->running_import_product_content = false;
                $this->emit_sse_message( $complete );
            }
        }

		if( $this->setting_args['slider'] && !empty( $demo_selected['slider'] ) ) {
			if(!isset($this->running_import_slider)){
				$this->handling_importer_slider( $demo_selected['slider'] );
				$this->running_import_slider = false;
			}
		}
		if( $this->setting_args['widget'] && !empty( $demo_selected['widget'] ) ) {
			if(!isset($this->running_import_widget)){
				$this->handling_importer_widgets( $demo_selected['widget'] );
				$this->running_import_widget = false;
			}
		}
		if( $this->setting_args['option'] && !empty( $demo_selected['option'] ) ) {
			if(!isset($this->running_import_option)){
				$this->handling_importer_option( $demo_selected['option'] );
				$this->running_import_option = false;
			}
		}

		if( $this->setting_args['elementor'] && !empty( $demo_selected['elementor'] ) ) {
			if(!isset($this->running_import_elementor_conditions)){
				$this->handling_importer_elementor_conditions( $demo_selected['elementor'], $demo_selected['elementor_kit_settings'] );
				$this->running_import_elementor_conditions = false;
			}
		}

		if(!isset($this->running_import_theme_mode)){
			$this->handling_importer_theme_mode( $this->current_id );
			$this->running_import_theme_mode = false;
		}

		if(!isset($this->importer_is_running)){
			if( false !== has_action('LaStudio_Importer/after_import') ){
				do_action( 'LaStudio_Importer/after_import', $demo_selected, $this->current_id, $this );
			}
			$this->importer_is_running = false;
		}

		// Remove the settings to stop future reconnects.
		delete_option('_wxr_import_settings');

		unset($this->running_import_content);
		unset($this->running_import_product_content);
		unset($this->running_import_slider);
		unset($this->running_import_widget);
		unset($this->running_import_option);
		unset($this->running_import_elementor_conditions);
		unset($this->running_import_theme_mode);
		unset($this->importer_is_running);
		exit;

	}

	/**
	 * Decide whether or not the importer should attempt to download attachment files.
	 * Default is true, can be filtered via import_allow_fetch_attachments. The choice
	 * made at the import options screen must also be true, false here hides that checkbox.
	 *
	 * @return bool True if downloading attachments is allowed
	 */
	protected function allow_fetch_attachments() {
		return apply_filters( 'import_allow_fetch_attachments', true );
	}

	/**
	 * Emit a Server-Sent Events message.
	 *
	 * @param mixed $data Data to be JSON-encoded and sent in the message.
	 */
	protected function emit_sse_message( $data ) {
		echo "event: message\n";
		echo 'data: ' . wp_json_encode( $data ) . "\n\n";

		// Extra padding.
		echo ':' . str_repeat(' ', 2048) . "\n\n";

		flush();
	}

	/**
	 *
	 * Modify _menu_item_url
	 *
	 * @param $meta_item
	 * @param $post_id
	 * @return mixed
	 */

	public function imported_post_meta( $meta_item, $post_id ) {

		if(empty($this->demo_site_url)){
			return $meta_item;
		}
		if(isset($meta_item['key']) && isset($meta_item['value']) && $meta_item['key'] == '_menu_item_url'){
			$meta_item['value'] = str_replace($this->demo_site_url, $this->current_site_url, $meta_item['value']);
		}

		return $meta_item;
	}

	/**
	 * Send message when a post has been imported.
	 *
	 * @param int $id Post ID.
	 * @param array $data Post data saved to the DB.
	 */
	public function imported_post( $id, $data ) {
		$this->emit_sse_message( array(
			'action' => 'updateDelta',
			'type'   => ( $data['post_type'] === 'attachment' ) ? 'media' : 'posts',
			'delta'  => 1,
		));
	}

	/**
	 * Send message when a term has been imported.
	 */
	public function imported_term() {
		$this->emit_sse_message( array(
			'action' => 'updateDelta',
			'type'   => 'terms',
			'delta'  => 1,
		));
	}

	/**
	 * Get data from a file
	 *
	 * @param string $file_path file path where the content should be saved.
	 * @return string $data, content of the file or WP_Error object with error message.
	 */
	public static function data_from_file( $file_path ) {

		// Check if the file-system method is 'direct', if not display an error.
		if ( ! 'direct' === get_filesystem_method() ) {
			return self::return_direct_filesystem_error();
		}

		// Verify WP file-system credentials.
		$verified_credentials = self::check_wp_filesystem_credentials();

		if ( is_wp_error( $verified_credentials ) ) {
			return $verified_credentials;
		}

		// By this point, the $wp_filesystem global should be working, so let's use it to read a file.
		global $wp_filesystem;

		$data = $wp_filesystem->get_contents( $file_path );

		if ( ! $data ) {
			return new WP_Error(
				'failed_reading_file_from_server',
				sprintf(
					__( 'An error occurred while reading a file from your server! Tried reading file from path: %s%s.', 'la-studio' ),
					'<br>',
					$file_path
				)
			);
		}

		// Return the file data.
		return $data;
	}

	/**
	 * Helper function: return the "no direct access file-system" error.
	 *
	 * @return WP_Error
	 */
	private static function return_direct_filesystem_error() {
		return new WP_Error(
			'no_direct_file_access',
			sprintf(
				__( 'This WordPress page does not have %sdirect%s write file access. This plugin needs it in order to save the demo import xml file to the upload directory of your site. You can change this setting with these instructions: %s.', 'la-studio' ),
				'<strong>',
				'</strong>',
				'<a href="http://gregorcapuder.com/wordpress-how-to-set-direct-filesystem-method/" target="_blank">How to set <strong>direct</strong> filesystem method</a>'
			)
		);
	}

	/**
	 * Helper function: check for WP file-system credentials needed for reading and writing to a file.
	 *
	 * @return boolean|WP_Error
	 */
	private static function check_wp_filesystem_credentials() {

		// Get user credentials for WP file-system API.
		$demo_import_page_url = wp_nonce_url( 'themes.php?page=theme_options', 'theme_options' );
		$demo_import_page_url = '';

		if ( false === ( $creds = request_filesystem_credentials( $demo_import_page_url, '', false, false, null ) ) ) {
			return new WP_error(
				'filesystem_credentials_could_not_be_retrieved',
				__( 'An error occurred while retrieving reading/writing permissions to your server (could not retrieve WP filesystem credentials)!', 'la-studio' )
			);
		}

		// Now we have credentials, try to get the wp_filesystem running.
		if ( ! WP_Filesystem( $creds ) ) {
			return new WP_Error(
				'wrong_login_credentials',
				__( 'Your WordPress login credentials don\'t allow to use WP_Filesystem!', 'la-studio' )
			);
		}

		return true;
	}

	/**
	 * Imports widgets from a json file.
	 *
	 * @param string $data_file path to json file with WordPress widget export data.
	 */
	private function handling_importer_widgets( $file ) {

		$response = array(
			'action' => 'ImportingWidget',
			'error'  => __( 'Widget import file could not be found.', 'la-studio' )
		);

		if( empty($file) || !file_exists($file) ) {
			$this->emit_sse_message( $response );
			return;
		}

		$data = self::data_from_file( $file );

		if ( is_wp_error( $data ) ) {
			$this->emit_sse_message( $response );
			return;
		}
		$data = json_decode( $data );
		// Import the widget data and save the results.
		$result = $this->import_widget_data( $data );
		if ( is_wp_error( $result ) ) {
			$response['error'] = $result->get_error_message();
		}else{
			$this->logger->info(__('Widget has been importer !', 'la-studio'));
			$response['error'] = __( 'Widget has been importer !', 'la-studio');
		}
		$this->emit_sse_message( $response );
		return;

	}
	/**
	 * Import widget JSON data
	 *
	 * @global array $wp_registered_sidebars
	 * @param object $data JSON widget data.
	 * @return array $results
	 */
	private function import_widget_data( $data ) {
		global $wp_registered_sidebars;
		// Have valid data? If no data or could not decode.
		if ( empty( $data ) || ! is_object( $data ) ) {
			return new WP_Error(
				'corrupted_widget_import_data',
				__( 'Widget import data could not be read. Please try a different file.', 'la-studio' )
			);
		}
		// Hook before import.
		do_action( 'LaStudio_Importer/widgets/before_import' );
		$data = apply_filters( 'LaStudio_Importer/widgets/before_import_data', $data );
		// Get all available widgets site supports.
		$available_widgets = $this->available_widgets();
		// Get all existing widget instances.
		$widget_instances = array();
		foreach ( $available_widgets as $widget_data ) {
			$widget_instances[ $widget_data['id_base'] ] = get_option( 'widget_' . $widget_data['id_base'] );
		}
		// Begin results.
		$results = array();

		// Loop import data's sidebars.
		foreach ( $data as $sidebar_id => $widgets ) {
			// Skip inactive widgets (should not be in export file).
			if ( 'wp_inactive_widgets' == $sidebar_id ) {
				continue;
			}
			// Check if sidebar is available on this site. Otherwise add widgets to inactive, and say so.
			if ( isset( $wp_registered_sidebars[ $sidebar_id ] ) ) {
				$sidebar_available    = true;
				$use_sidebar_id       = $sidebar_id;
				$sidebar_message_type = 'success';
				$sidebar_message      = '';
			}
			else {
				$sidebar_available    = false;
				$use_sidebar_id       = 'wp_inactive_widgets'; // Add to inactive if sidebar does not exist in theme.
				$sidebar_message_type = 'error';
				$sidebar_message      = __( 'Sidebar does not exist in theme (moving widget to Inactive)', 'la-studio' );
			}
			// Result for sidebar.
			$results[ $sidebar_id ]['name']         = ! empty( $wp_registered_sidebars[ $sidebar_id ]['name'] ) ? $wp_registered_sidebars[ $sidebar_id ]['name'] : $sidebar_id; // Sidebar name if theme supports it; otherwise ID.
			$results[ $sidebar_id ]['message_type'] = $sidebar_message_type;
			$results[ $sidebar_id ]['message']      = $sidebar_message;
			$results[ $sidebar_id ]['widgets']      = array();
			// Loop widgets.
			foreach ( $widgets as $widget_instance_id => $widget ) {
				$fail = false;
				// Get id_base (remove -# from end) and instance ID number.
				$id_base            = preg_replace( '/-[0-9]+$/', '', $widget_instance_id );
				$instance_id_number = str_replace( $id_base . '-', '', $widget_instance_id );

				// Does site support this widget?
				if ( ! $fail && ! isset( $available_widgets[ $id_base ] ) ) {
					$fail                = true;
					$widget_message_type = 'error';
					$widget_message      = __( 'Site does not support widget', 'la-studio' ); // Explain why widget not imported.
				}

				// Filter to modify settings object before conversion to array and import.
				// Leave this filter here for backwards compatibility with manipulating objects (before conversion to array below).
				// Ideally the newer wie_widget_settings_array below will be used instead of this.
				$widget = apply_filters( 'LaStudio_Importer/widgets/widget_setting_object', $widget ); // Object.

				// Convert multidimensional objects to multidimensional arrays.
				// Some plugins like Jetpack Widget Visibility store settings as multidimensional arrays.
				// Without this, they are imported as objects and cause fatal error on Widgets page.
				// If this creates problems for plugins that do actually intend settings in objects then may need to consider other approach: https://wordpress.org/support/topic/problem-with-array-of-arrays.
				// It is probably much more likely that arrays are used than objects, however.
				$widget = json_decode( json_encode( $widget ), true );

				// Filter to modify settings array.
				// This is preferred over the older wie_widget_settings filter above.
				// Do before identical check because changes may make it identical to end result (such as URL replacements).
				$widget = apply_filters( 'LaStudio_Importer/widgets/widget_setting_array', $widget );

				// Does widget with identical settings already exist in same sidebar?
				if ( ! $fail && isset( $widget_instances[ $id_base ] ) ) {

					// Get existing widgets in this sidebar.
					$sidebars_widgets = get_option( 'sidebars_widgets' );
					$sidebar_widgets  = isset( $sidebars_widgets[ $use_sidebar_id ] ) ? $sidebars_widgets[ $use_sidebar_id ] : array(); // Check Inactive if that's where will go.

					// Loop widgets with ID base.
					$single_widget_instances = ! empty( $widget_instances[ $id_base ] ) ? $widget_instances[ $id_base ] : array();
					foreach ( $single_widget_instances as $check_id => $check_widget ) {

						// Is widget in same sidebar and has identical settings?
						if ( in_array( "$id_base-$check_id", $sidebar_widgets ) && (array) $widget == $check_widget ) {
							$fail                = true;
							$widget_message_type = 'warning';
							$widget_message      = __( 'Widget already exists', 'la-studio' ); // Explain why widget not imported.

							break;
						}
					}
				}
				// No failure.
				if ( ! $fail ) {
					// Add widget instance.
					$single_widget_instances   = get_option( 'widget_' . $id_base ); // All instances for that widget ID base, get fresh every time.
					$single_widget_instances   = ! empty( $single_widget_instances ) ? $single_widget_instances : array( '_multiwidget' => 1 ); // Start fresh if have to.
					$single_widget_instances[] = $widget; // Add it.
					// Get the key it was given.
					end( $single_widget_instances );
					$new_instance_id_number = key( $single_widget_instances );
					// If key is 0, make it 1.
					// When 0, an issue can occur where adding a widget causes data from other widget to load, and the widget doesn't stick (reload wipes it).
					if ( '0' === strval( $new_instance_id_number ) ) {
						$new_instance_id_number                           = 1;
						$single_widget_instances[ $new_instance_id_number ] = $single_widget_instances[0];
						unset( $single_widget_instances[0] );
					}
					// Move _multiwidget to end of array for uniformity.
					if ( isset( $single_widget_instances['_multiwidget'] ) ) {
						$multiwidget = $single_widget_instances['_multiwidget'];
						unset( $single_widget_instances['_multiwidget'] );
						$single_widget_instances['_multiwidget'] = $multiwidget;
					}
					// Update option with new widget.
					update_option( 'widget_' . $id_base, $single_widget_instances );
					// Assign widget instance to sidebar.
					$sidebars_widgets = get_option( 'sidebars_widgets' ); // Which sidebars have which widgets, get fresh every time.
					$new_instance_id = $id_base . '-' . $new_instance_id_number; // Use ID number from new widget instance.
					$sidebars_widgets[ $use_sidebar_id ][] = $new_instance_id; // Add new instance to sidebar.
					update_option( 'sidebars_widgets', $sidebars_widgets ); // Save the amended data.
					// After widget import action.
					$after_widget_import = array(
						'sidebar'           => $use_sidebar_id,
						'sidebar_old'       => $sidebar_id,
						'widget'            => $widget,
						'widget_type'       => $id_base,
						'widget_id'         => $new_instance_id,
						'widget_id_old'     => $widget_instance_id,
						'widget_id_num'     => $new_instance_id_number,
						'widget_id_num_old' => $instance_id_number,
					);
					do_action( 'LaStudio_Importer/widgets/after_single_widget_import', $after_widget_import );

					// Success message.
					if ( $sidebar_available ) {
						$widget_message_type = 'success';
						$widget_message      = __( 'Imported', 'la-studio' );
					}
					else {
						$widget_message_type = 'warning';
						$widget_message      = __( 'Imported to Inactive', 'la-studio' );
					}
				}

				// Result for widget instance.
				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['name']         = isset( $available_widgets[ $id_base ]['name'] ) ? $available_widgets[ $id_base ]['name'] : $id_base; // Widget name or ID if name not available (not supported by site).
				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['title']        = ! empty( $widget['title'] ) ? $widget['title'] : __( '', 'la-studio' ); // Show "No Title" if widget instance is untitled.
				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['message_type'] = $widget_message_type;
				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['message']      = $widget_message;

			}
		}

		// Hook after import.
		do_action( 'LaStudio_Importer/widgets/after_import' );

		// Return results.
		return apply_filters( 'LaStudio_Importer/widgets/import_results', $results );
	}

	/**
	 * Available widgets.
	 *
	 * Gather site's widgets into array with ID base, name, etc.
	 *
	 * @global array $wp_registered_widget_controls
	 * @return array $available_widgets, Widget information
	 */
	private function available_widgets() {
		global $wp_registered_widget_controls;
		$widget_controls   = $wp_registered_widget_controls;
		$available_widgets = array();

		foreach ( $widget_controls as $widget ) {
			if ( ! empty( $widget['id_base'] ) && ! isset( $available_widgets[ $widget['id_base'] ] ) ) {
				$available_widgets[ $widget['id_base'] ]['id_base'] = $widget['id_base'];
				$available_widgets[ $widget['id_base'] ]['name']    = $widget['name'];
			}
		}
		return apply_filters( 'LaStudio_Importer/widgets/available_widgets', $available_widgets );
	}

	private function handling_importer_slider( $file ) {

		if ( !empty( $file ) && file_exists( $file ) ) {
			if( class_exists('RevSlider') ) {
				$slider = new RevSlider();
				$result = $slider->importSliderFromPost( true, true, $file );
				if( is_wp_error( $result ) ) {
					$response['error'] = $result->get_error_message();
					$this->logger->error(
						sprintf(__('ImportingSlider %s', 'la-studio'), $result->get_error_message())
					);
				}
				else{
					$this->logger->info(
						__('Slider has been imported !', 'la-studio')
					);
				}
			}
		}

	}

	private function handling_importer_option( $file ) {
		if( empty( $file ) || ( !empty($file) && !file_exists( $file ) ) ) {
			$this->emit_sse_message( array(
				'action' => 'ImportingOption',
				'error'  => __( 'Access define!', 'la-studio' )
			) );
			return;
		}

		$data = self::data_from_file( $file );
		if( is_wp_error( $data ) ) {
			$this->logger->error(
				__('Option config not found!', 'la-studio')
			);
			return;
		}

		$data = json_decode( $data, true );
		$data = maybe_unserialize( $data );

		if( empty( $data ) || !is_array( $data ) ) {
			$this->emit_sse_message( array(
				'action' => 'ImportingOption',
				'error'  => __( 'Options is null', 'la-studio')
			) );
			return;
		}

        $data['la_extension_available'] = array(
            'swatches' => true,
            '360' => true,
            'content_type' => true
        );

        $default_modules = [
            'advanced-carousel' => true,
            'advanced-map' => true,
            'animated-box' => true,
            'animated-text' => true,
            'audio' => true,
            'banner' => true,
            'button' => true,
            'circle-progress' => true,
            'countdown-timer' => true,
            'dropbar'  => true,
            'headline' => true,
            'horizontal-timeline' => true,
            'image-comparison' => true,
            'images-layout' => true,
            'instagram-gallery' => true,
            'portfolio' => true,
            'posts' => true,
            'price-list' => true,
            'pricing-table' => true,
            'progress-bar' => true,
            'scroll-navigation' => true,
            'services' => true,
            'subscribe-form' => true,
            'table' => true,
            'tabs' => true,
            'team-member' => true,
            'testimonials' => true,
            'timeline' => true,
            'video' => true,
            'breadcrumbs' => true,
            'post-navigation' => true,
            'slides' => true
        ];

        $data['lastudio_elementor_modules'] = $default_modules;

		update_option( $this->theme_name . '_options', $data );
		$this->logger->info(
			__('Theme setting has been set', 'la-studio')
		);
		return;

	}

	private function handling_importer_elementor_conditions( $conditions, $kit_settings ){

	    $post_meta_key = '_elementor_conditions';

        $caches = get_option('lastudiokit_theme_builder_conditions');
        if(empty($caches)){
            $caches = [];
        }

        foreach ($conditions as $type => $condition){
            $location = $condition['location'];
            $value = $condition['value'];
            if(!empty($value)){
                foreach ($value as $item => $roles){
                    $template_id = la_import_get_post_id_by_slug( $item, 'elementor_library' );
                    $value_to_save = [];
                    if(!empty($template_id)){
                        if($type == 'single-page') {
                            foreach ($roles as $role) {
                                $page = explode('/', $role);
                                $page_name = array_pop($page);
                                $page_id = la_import_get_post_id_by_slug($page_name, 'page');
                                if (!empty($page_id)) {
                                    array_push($page, $page_id);
                                    $new_path = join('/', $page);
                                    $value_to_save[] = $new_path;
                                }
                            }
                            if (!empty($value_to_save)) {
                                update_post_meta($template_id, $post_meta_key, $value_to_save);
                                if (empty($caches[$location])) {
                                    $caches[$location] = [];
                                }
                                $caches[$location][$template_id] = $value_to_save;
                            } else {
                                delete_post_meta($template_id, $post_meta_key);
                            }
                        }
                        else{
                            $caches = $this->handling_import_elementor_sub_condition( $template_id, $type, $location, $roles, $caches );
                        }
                    }
                }
            }
        }

        update_option('lastudiokit_theme_builder_conditions', $caches);

        if(defined( 'ELEMENTOR_VERSION' )){
            $kit_active_id = Elementor\Plugin::$instance->kits_manager->get_active_id();
            update_post_meta( $kit_active_id, '_elementor_page_settings', $kit_settings );
        }

        $this->logger->info(
            __('Elementor Builder Location has been set', 'la-studio')
        );

        return;
    }

    private function handling_import_elementor_sub_condition( $template_id = 0, $type = '', $location = '', $condition = '', $caches = [] ) {
	    if( empty($template_id) || empty($type) || empty($location) || empty($condition) ){
	        return $caches;
        }
	    $post_meta_key = '_elementor_conditions';
        $active = [
            $location => []
        ];
        $inactive = [
            $location => []
        ];
        $query = new WP_Query( [
            'posts_per_page' => -1,
            'post_type' => 'elementor_library',
            'fields' => 'ids',
            'meta_key' => '_elementor_template_type',
            'meta_value' => $type
        ] );
        foreach ( $query->posts as $post_id ) {
            $meta = get_post_meta( $post_id, '_elementor_conditions', true );
            if(empty($meta)){
                $meta = [];
            }

            if($template_id === $post_id){
                if( false !== $key = array_search($condition, $meta) ){
                    unset($meta[$key]);
                }
                array_unshift( $meta, $condition );
            }
            else{
                if( false !== $key = array_search($condition, $meta) ){
                    unset($meta[$key]);
                }
            }
            if(empty($meta)){
                delete_post_meta( $post_id, $post_meta_key );
                $inactive[$location][] = $post_id;
            }
            else{
                update_post_meta( $post_id, $post_meta_key, $meta );
                $active[$location][$post_id] = array_values($meta);
            }
        }

        //remove from caches
        if(!empty($inactive[$location])){
            foreach ($inactive[$location] as $item){
                if(isset($caches[$location]) && isset($caches[$location][$item])){
                    unset($caches[$location][$item]);
                }
            }
        }

        // add to caches
        if(!empty($active[$location])){
            if(!isset($caches[$location])){
                $caches[$location] = $active[$location];
            }
            else{
                $caches[$location] = array_replace($caches[$location], $active[$location]);
            }
        }

        return $caches;
    }

	private function handling_importer_theme_mode( $demo_id ) {

		$demo_data = $this->demo_data[ $demo_id ];
		$menu_locations = array();
		$menu_array = isset($demo_data['menu-locations']) ? $demo_data['menu-locations'] : array();
		if(!empty($menu_array)){
			foreach ($menu_array as $key => $menu){
				$menu_object = get_term_by( 'name', esc_attr($menu), 'nav_menu' );
				$menu_locations[$key] = isset($menu_object->term_id) ? $menu_object->term_id : '';
			}
		}
		if(!empty($menu_locations)){
			set_theme_mod( 'nav_menu_locations', $menu_locations);
			$this->logger->info(
				__('Menu Location has been set', 'la-studio')
			);
		}
		$pages = array();
		$page_array = isset($demo_data['pages']) ? $demo_data['pages'] : array();
		if(!empty($page_array)){
			foreach($page_array as $key => $title){
				$page = get_page_by_title( $title );
				if ( isset( $page->ID ) ) {
					update_option( $key, $page->ID );
					$pages[] = $page->ID;
				}
			}
		}
		if(!empty($pages)){
			update_option( 'show_on_front', 'page' );
			$this->logger->info(
				__('Home Page and Blog Page has been set!', 'la-studio')
			);
		}

		$options_name = $this->theme_name . '_imported_demos';
		$imported_demos = get_option( $options_name );
		if ( empty( $imported_demos ) ) {
			$imported_demos = array(
				$demo_id => $demo_data
			);
		}else {
			$imported_demos[$demo_id] = $demo_data;
		}
		$imported_demos['active_import'] = $demo_id;
		update_option( $options_name, $imported_demos );

		if(!empty($demo_data['other_setting'])){
			$other_setting = $demo_data['other_setting'];
			foreach( $other_setting as $k => $v){
				update_option( $k, $v );
			}
			$this->logger->info(
				__('Import other setting require success', 'la-studio')
			);
		}

        try{
            Elementor\Plugin::$instance->files_manager->clear_cache();
            $this->logger->info(__('Regenerate css files successfully!', 'la-studio'));
        }
        catch (Exception $exception ){

        }

		$this->logger->info(
			__('Active demo success !', 'la-studio')
		);
		$this->emit_sse_message(
			array(
				'action' => 'complete',
				'error'	 => false
			)
		);
	}

	public function clear_imported_data(){

		$options_name = $this->theme_name . '_imported_demos';

		if( isset($_GET['la_delete_demo_status']) && $_GET['la_delete_demo_status'] == 'yes'){
			delete_option($options_name);
		}

	}

    private function process_import_wp_default(){

        $selected_demo = $this->demo_data[ $this->current_id ];
        $content_file = $selected_demo['content'];

        if ( ! is_file( $content_file ) ) {
            return new WP_Error( 'wxr_importer.file_missing', __( 'The file does not exist, please try again.', 'la-studio' ) );
        }

        // Suspend bunches of stuff in WP core
        wp_defer_term_counting( true );
        wp_defer_comment_counting( true );
        wp_suspend_cache_invalidation( true );

        // Prefill exists calls if told to
        if ( $this->options['prefill_existing_posts'] ) {
            $this->prefill_existing_posts();
        }
        if ( $this->options['prefill_existing_terms'] ) {
            $this->prefill_existing_terms();
        }

        /**
         * Begin the import.
         *
         * Fires before the import process has begun. If you need to suspend
         * caching or heavy processing on hooks, do so here.
         */
        do_action( 'import_start' );

        $data = json_decode( file_get_contents( $content_file ), true );

        $taxonomy = !empty($data['taxonomy']) ? $data['taxonomy'] : false;
        $post_types = !empty($data['post_type']) ? $data['post_type'] : false;

        if($taxonomy){
            foreach ($taxonomy as $term){
                $term_data = [
                    'id'          => $term['term_id'],
                    'taxonomy'    => $term['taxonomy'],
                    'slug'        => $term['slug'],
                    'parent'      => $term['parent'],
                    'name'        => $term['name'],
                    'description' => $term['description']
                ];
                $term_meta = [];
                $this->process_term($term_data, $term_meta);
            }
        }

        if($post_types){
            foreach ($post_types as $item){
                $data = [
                    'post_id' => $item['post_id'],
                    'post_title' => $item['post_title'],
                    'post_name' => $item['post_name'],
                    'guid' => $item['guid'],
                    'post_status' => $item['post_status'],
                    'post_date' => $item['post_date'],
                    'post_date_gmt' => $item['post_date_gmt'],
                    'post_excerpt' => $item['post_excerpt'],
                    'post_content' => $item['post_content'],
                    'post_type' => $item['post_type'],
                    'post_parent' => $item['post_parent'],
                ];
                if(!empty($item['thumbnail_url'])){
                    $data['thumbnail_url'] = $item['thumbnail_url'];
                }
                $term = [];
                $meta = [];
                if(!empty($item['post_taxonomy'])){
                    $term = $item['post_taxonomy'];
                }
                if(!empty($item['post_metadata'])){
                    $meta = $item['post_metadata'];
                }
                $this->process_post($data, $meta, $term);
            }
        }

        // Now that we've done the main processing, do any required
        // post-processing and remapping.
        $this->post_process();

        // Re-enable stuff in core
        wp_suspend_cache_invalidation( false );
        wp_cache_flush();
        foreach ( get_taxonomies() as $tax ) {
            delete_option( "{$tax}_children" );
            _get_term_hierarchy( $tax );
        }

        wp_defer_term_counting( false );
        wp_defer_comment_counting( false );

        /**
         * Complete the import.
         *
         * Fires after the import process has finished. If you need to update
         * your cache or re-enable processing, do so here.
         */
        do_action( 'import_end' );

    }

    private function process_import_product(){

        if ( ! class_exists( 'WooCommerce', false ) ) {
            return new WP_Error( 'wxr_importer.woocommerce_not_install', __( 'WooCommerce is not installed.', 'la-studio' ) );
        }

        $selected_demo = $this->demo_data[ $this->current_id ];
        $content_file = $selected_demo['product'];

        if ( ! is_file( $content_file ) ) {
            return new WP_Error( 'wxr_importer.woocommerce.file_missing', __( 'The file does not exist, please try again.', 'la-studio' ) );
        }

        include_once 'wc-import.php';
        include_once WC_ABSPATH . 'includes/import/class-wc-product-csv-importer.php';

        $controller = new LaStudio_Importer_WC();

        $params = array(
            'delimiter'         => ',', // CSV delimiter.
            'start_pos'         =>  0, // File pointer start.
            'update_existing'   => false, // Whether to update existing items.
            'prevent_timeouts'  => false, // Check memory and time usage and abort if reaching limit.
            'lines'             => -1, // Max lines to read.
            'parse'             => true, // Whether to sanitize and format data.
            'mapping'           => $controller->get_mappings($content_file), // Column mapping. csv_heading => schema_heading.
        );
        
        add_filter('woocommerce_product_importer_default_time_limit', function (){
            return 300;
        }, 999);

        // Log failures.
        if ( 0 !== $params['start_pos'] ) {
            $error_log = array_filter( (array) get_user_option( 'product_import_error_log' ) );
        } else {
            $error_log = array();
        }

        $importer         = LaStudio_Importer_WC::get_importer( $content_file, $params );
        $results          = $importer->import();
        $percent_complete = $importer->get_percent_complete();
        $error_log        = array_merge( $error_log, $results['failed'], $results['skipped'] );

        update_user_option( get_current_user_id(), 'product_import_error_log', $error_log );

        if ( 100 === $percent_complete ) {

            global $wpdb;

            // @codingStandardsIgnoreStart.
            $wpdb->delete( $wpdb->postmeta, array( 'meta_key' => '_original_id' ) );
            $wpdb->delete( $wpdb->posts, array(
                'post_type'   => 'product',
                'post_status' => 'importing',
            ) );
            $wpdb->delete( $wpdb->posts, array(
                'post_type'   => 'product_variation',
                'post_status' => 'importing',
            ) );

            // @codingStandardsIgnoreEnd.

            // Clean up orphaned data.
            $wpdb->query(
                "
				DELETE {$wpdb->posts}.* FROM {$wpdb->posts}
				LEFT JOIN {$wpdb->posts} wp ON wp.ID = {$wpdb->posts}.post_parent
				WHERE wp.ID IS NULL AND {$wpdb->posts}.post_type = 'product_variation'
			"
            );
            $wpdb->query(
                "
				DELETE {$wpdb->postmeta}.* FROM {$wpdb->postmeta}
				LEFT JOIN {$wpdb->posts} wp ON wp.ID = {$wpdb->postmeta}.post_id
				WHERE wp.ID IS NULL
			"
            );
            // @codingStandardsIgnoreStart.
            $wpdb->query( "
				DELETE tr.* FROM {$wpdb->term_relationships} tr
				LEFT JOIN {$wpdb->posts} wp ON wp.ID = tr.object_id
				LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE wp.ID IS NULL
				AND tt.taxonomy IN ( '" . implode( "','", array_map( 'esc_sql', get_object_taxonomies( 'product' ) ) ) . "' )
			" );
            // @codingStandardsIgnoreEnd.

            $this->logger->info( __( 'Successfully imported products', 'la-studio' ) );

        } else {

            $this->logger->info( __( 'Successfully imported products', 'la-studio' ) );
        }

    }

    protected function post_process() {
        // Time to tackle any left-over bits
        if ( ! empty( $this->requires_remapping['post'] ) ) {
            $this->post_process_posts( $this->requires_remapping['post'] );
        }
    }

    protected function post_process_posts( $todo ) {
        foreach ( $todo as $post_id => $_ ) {
            $this->logger->debug( sprintf(
            // Note: title intentionally not used to skip extra processing
            // for when debug logging is off
                __( 'Running post-processing for post %d', 'la-studio' ),
                $post_id
            ) );

            $data = array();

            $parent_id = get_post_meta( $post_id, '_wxr_import_parent', true );
            if ( ! empty( $parent_id ) ) {
                // Have we imported the parent now?
                if ( isset( $this->mapping['post'][ $parent_id ] ) ) {
                    $data['post_parent'] = $this->mapping['post'][ $parent_id ];
                } else {
                    $this->logger->warning( sprintf(
                        __( 'Could not find the post parent for "%s" (post #%d)', 'la-studio' ),
                        get_the_title( $post_id ),
                        $post_id
                    ) );
                    $this->logger->debug( sprintf(
                        __( 'Post %d was imported with parent %d, but could not be found', 'la-studio' ),
                        $post_id,
                        $parent_id
                    ) );
                }
            }

            if ( get_post_type( $post_id ) === 'nav_menu_item' ) {
                $this->post_process_menu_item( $post_id );
            }

            // Do we have updates to make?
            if ( empty( $data ) ) {
                $this->logger->debug( sprintf(
                    __( 'Post %d was marked for post-processing, but none was required.', 'la-studio' ),
                    $post_id
                ) );
                continue;
            }

            // Run the update
            $data['ID'] = $post_id;
            $result = wp_update_post( $data, true );
            if ( is_wp_error( $result ) ) {
                $this->logger->warning( sprintf(
                    __( 'Could not update "%s" (post #%d) with mapped data', 'la-studio' ),
                    get_the_title( $post_id ),
                    $post_id
                ) );
                $this->logger->debug( $result->get_error_message() );
                continue;
            }

            // Clear out our temporary meta keys
            delete_post_meta( $post_id, '_wxr_import_parent' );
            delete_post_meta( $post_id, '_wxr_import_user_slug' );
            delete_post_meta( $post_id, '_wxr_import_has_attachment_refs' );
        }
    }

    protected function post_process_menu_item( $post_id ) {
        $menu_object_id = get_post_meta( $post_id, '_wxr_import_menu_item', true );
        if ( empty( $menu_object_id ) ) {
            // No processing needed!
            return;
        }

        $menu_item_type = get_post_meta( $post_id, '_menu_item_type', true );
        switch ( $menu_item_type ) {
            case 'taxonomy':
                if ( isset( $this->mapping['term_id'][ $menu_object_id ] ) ) {
                    $menu_object = $this->mapping['term_id'][ $menu_object_id ];
                }
                break;

            case 'post_type':
                if ( isset( $this->mapping['post'][ $menu_object_id ] ) ) {
                    $menu_object = $this->mapping['post'][ $menu_object_id ];
                }
                break;

            default:
                // Cannot handle this.
                return;
        }

        if ( ! empty( $menu_object ) ) {
            update_post_meta( $post_id, '_menu_item_object_id', wp_slash( $menu_object ) );
        }
        else {
            $this->logger->warning( sprintf(
                __( 'Could not find the menu object for "%s" (post #%d)', 'la-studio' ),
                get_the_title( $post_id ),
                $post_id
            ) );
            $this->logger->debug( sprintf(
                __( 'Post %d was imported with object "%d" of type "%s", but could not be found', 'la-studio' ),
                $post_id,
                $menu_object_id,
                $menu_item_type
            ) );
        }

        delete_post_meta( $post_id, '_wxr_import_menu_item' );
    }

    /**
     * Create new posts based on import information
     *
     * Posts marked as having a parent which doesn't exist will become top level items.
     * Doesn't create a new post if: the post type doesn't exist, the given post ID
     * is already noted as imported or a post with the same title and date already exists.
     * Note that new/updated terms, comments and meta are imported for the last of the above.
     */
    protected function process_post( $data, $meta, $terms ) {
        /**
         * Pre-process post data.
         *
         * @param array $data Post data. (Return empty to skip.)
         * @param array $meta Meta data.
         * @param array $terms Terms on the post.
         */

        $data = apply_filters( 'lastudio_importer.pre_process.post', $data, $meta, $terms );
        if ( empty( $data ) ) {
            return false;
        }

        $original_id = isset( $data['post_id'] )     ? (int) $data['post_id']     : 0;
        $parent_id   = isset( $data['post_parent'] ) ? (int) $data['post_parent'] : 0;
        $author_id   = isset( $data['post_author'] ) ? (int) $data['post_author'] : 0;

        // Have we already processed this?
        if ( isset( $this->mapping['post'][ $original_id ] ) ) {
            return;
        }

        $post_type_object = get_post_type_object( $data['post_type'] );
        // Is this type even valid?
        if ( ! $post_type_object ) {
            $this->logger->warning( sprintf(
                __( 'Failed to import "%s": Invalid post type %s', 'la-studio' ),
                $data['post_title'],
                $data['post_type']
            ) );
            return false;
        }

        $post_exists = $this->post_exists( $data );

        if ( $post_exists ) {
            $this->logger->info( sprintf(
                __( '%s "%s" already exists.', 'la-studio' ),
                $post_type_object->labels->singular_name,
                $data['post_title']
            ) );

            /**
             * Post processing already imported.
             *
             * @param array $data Raw data imported for the post.
             */
            do_action( 'lastudio_importer.process_already_imported.post', $data );

            return false;
        }

        // Map the parent post, or mark it as one we need to fix
        $requires_remapping = false;

        if ( $parent_id ) {
            if ( isset( $this->mapping['post'][ $parent_id ] ) ) {
                $data['post_parent'] = $this->mapping['post'][ $parent_id ];
            }
            else {
                $meta[] = array( 'key' => '_wxr_import_parent', 'value' => $parent_id );
                $requires_remapping = true;

                $data['post_parent'] = 0;
            }
        }

        // Map the author, or mark it as one we need to fix
        $data['post_author'] = (int) get_current_user_id();

        // Whitelist to just the keys we allow
        $postdata = array(
            'import_id' => $data['post_id'],
        );
        $allowed = array(
            'post_author'    => true,
            'post_date'      => true,
            'post_date_gmt'  => true,
            'post_content'   => true,
            'post_excerpt'   => true,
            'post_title'     => true,
            'post_status'    => true,
            'post_name'      => true,
            'comment_status' => true,
            'ping_status'    => true,
            'guid'           => true,
            'post_parent'    => true,
            'menu_order'     => true,
            'post_type'      => true,
            'post_password'  => true,
        );

        foreach ( $data as $key => $value ) {
            if ( ! isset( $allowed[ $key ] ) ) {
                continue;
            }

            $postdata[ $key ] = $data[ $key ];
        }

        $postdata = apply_filters( 'wp_import_post_data_processed', $postdata, $data );

        $post_id = wp_insert_post( $postdata, true );
        do_action( 'wp_import_insert_post', $post_id, $original_id, $postdata, $data );

        if ( is_wp_error( $post_id ) ) {
            $this->logger->error( sprintf(
                __( 'Failed to import "%s" (%s)', 'la-studio' ),
                $data['post_title'],
                $post_type_object->labels->singular_name
            ) );
            $this->logger->debug( $post_id->get_error_message() );

            /**
             * Post processing failed.
             *
             * @param WP_Error $post_id Error object.
             * @param array $data Raw data imported for the post.
             * @param array $meta Raw meta data, already processed by {@see process_post_meta}.
             * @param array $terms Raw term data, already processed.
             */
            do_action( 'lastudio_importer.process_failed.post', $post_id, $data, $meta, $terms );
            return false;
        }

        // map pre-import ID to local ID
        $this->mapping['post'][ $original_id ] = (int) $post_id;
        if ( $requires_remapping ) {
            $this->requires_remapping['post'][ $post_id ] = true;
        }
        $this->mark_post_exists( $data, $post_id );

        $this->logger->info( sprintf(
            __( 'Imported "%s" (%s)', 'la-studio' ),
            $data['post_title'],
            $post_type_object->labels->singular_name
        ) );
        $this->logger->debug( sprintf(
            __( 'Post %d remapped to %d', 'la-studio' ),
            $original_id,
            $post_id
        ) );

        // Handle the terms too
        $terms = apply_filters( 'wp_import_post_terms', $terms, $post_id, $data );

        if ( ! empty( $terms ) ) {

            $term_ids = array();
            foreach ( $terms as $term ) {
                $taxonomy = $term['taxonomy'];
                $key = sha1( $taxonomy . ':' . $term['slug'] );

                if ( isset( $this->mapping['term'][ $key ] ) ) {
                    $term_ids[ $taxonomy ][] = (int) $this->mapping['term'][ $key ];
                } else {
                    $meta[] = array( 'key' => '_wxr_import_term', 'value' => $term );
                    $requires_remapping = true;
                }
            }

            foreach ( $term_ids as $tax => $ids ) {
                $tt_ids = wp_set_post_terms( $post_id, $ids, $tax );
                do_action( 'wp_import_set_post_terms', $tt_ids, $ids, $tax, $post_id, $data );
            }
        }

        if(isset($data['thumbnail_url'])){
            $this->process_fetch_featured_image($data['thumbnail_url'], $post_id, $data['post_title']);
        }

        $this->process_post_meta( $meta, $post_id, $data );

        if ( 'nav_menu_item' === $data['post_type'] ) {
            $this->process_menu_item_meta( $post_id, $data, $meta );
        }

        /**
         * Post processing completed.
         *
         * @param int $post_id New post ID.
         * @param array $data Raw data imported for the post.
         * @param array $meta Raw meta data, already processed by {@see process_post_meta}.
         * @param array $terms Raw term data, already processed.
         */
        do_action( 'lastudio_importer.processed.post', $post_id, $data, $meta, $terms );

    }

    protected function process_fetch_featured_image( $url, $post_id, $post_title ){
        if(empty($url)){
            return true;
        }
        if(empty($post_id)){
            return true;
        }

        $import_images = new LaStudio_Importer_Images();
        $settings = $import_images->import(['url' => $url]);

        if(!empty($settings['id'])){
            update_post_meta($post_id, '_thumbnail_id', $settings['id']);
            $this->logger->info( sprintf(
                __( 'Setup featured image for "%s"', 'la-studio' ),
                $post_title
            ) );
        }

    }

    /**
     * Process and import post meta items.
     *
     * @param array $meta List of meta data arrays
     * @param int $post_id Post to associate with
     * @param array $post Post data
     * @return int|WP_Error Number of meta items imported on success, error otherwise.
     */
    protected function process_post_meta( $meta, $post_id, $post ) {
        if ( empty( $meta ) ) {
            return true;
        }

        foreach ( $meta as $meta_key => $meta_value ) {
            $meta_item = [
                'key' => $meta_key,
                'value' => $meta_value,
            ];
            /**
             * Pre-process post meta data.
             *
             * @param array $meta_item Meta data. (Return empty to skip.)
             * @param int $post_id Post the meta is attached to.
             */
            $meta_item = apply_filters( 'lastudio_importer.pre_process.post_meta', $meta_item, $post_id );
            if ( empty( $meta_item ) ) {
                return false;
            }

            $key = apply_filters( 'import_post_meta_key', $meta_item['key'], $post_id, $post );
            $value = false;

            if ( '_edit_last' === $key ) {
                $value = intval( $meta_item['value'] );
                if ( ! isset( $this->mapping['user'][ $value ] ) ) {
                    // Skip!
                    continue;
                }

                $value = $this->mapping['user'][ $value ];
            }

            if( '_elementor_data' === $key ) {
                $instance = new LaStudio_Importer_Elementor();
                $instance->import_element_data($meta_value, $post_id);
                continue;
            }

            if ( $key ) {
                // export gets meta straight from the DB so could have a serialized string
                if ( ! $value ) {
                    $value = maybe_unserialize( $meta_item['value'] );
                }

                add_post_meta( $post_id, $key, $value );
                do_action( 'import_post_meta', $post_id, $key, $value );
            }
        }

        return true;
    }

    /**
     * Attempt to create a new menu item from import data
     *
     * Fails for draft, orphaned menu items and those without an associated nav_menu
     * or an invalid nav_menu term. If the post type or term object which the menu item
     * represents doesn't exist then the menu item will not be imported (waits until the
     * end of the import to retry again before discarding).
     *
     * @param array $item Menu item details from WXR file
     */
    protected function process_menu_item_meta( $post_id, $data, $meta ) {

        $item_type = get_post_meta( $post_id, '_menu_item_type', true );
        $original_object_id = get_post_meta( $post_id, '_menu_item_object_id', true );
        $object_id = null;

        $this->logger->debug( sprintf( 'Processing menu item %s', $item_type ) );

        $requires_remapping = false;
        switch ( $item_type ) {
            case 'taxonomy':
                if ( isset( $this->mapping['term_id'][ $original_object_id ] ) ) {
                    $object_id = $this->mapping['term_id'][ $original_object_id ];
                } else {
                    add_post_meta( $post_id, '_wxr_import_menu_item', wp_slash( $original_object_id ) );
                    $requires_remapping = true;
                }
                break;

            case 'post_type':
                if ( isset( $this->mapping['post'][ $original_object_id ] ) ) {
                    $object_id = $this->mapping['post'][ $original_object_id ];
                } else {
                    add_post_meta( $post_id, '_wxr_import_menu_item', wp_slash( $original_object_id ) );
                    $requires_remapping = true;
                }
                break;

            case 'custom':
                // Custom refers to itself, wonderfully easy.
                $object_id = $post_id;
                break;

            default:
                // associated object is missing or not imported yet, we'll retry later
                $this->missing_menu_items[] = $data;
                $this->logger->debug( 'Unknown menu item type' );
                break;
        }

        if ( $requires_remapping ) {
            $this->requires_remapping['post'][ $post_id ] = true;
        }

        if ( empty( $object_id ) ) {
            // Nothing needed here.
            return;
        }

        $this->logger->debug( sprintf( 'Menu item %d mapped to %d', $original_object_id, $object_id ) );
        update_post_meta( $post_id, '_menu_item_object_id', wp_slash( $object_id ) );
    }

    protected function process_term( $data, $meta ) {
        /**
         * Pre-process term data.
         *
         * @param array $data Term data. (Return empty to skip.)
         * @param array $meta Meta data.
         */
        $data = apply_filters( 'lastudio_importer.pre_process.term', $data, $meta );
        if ( empty( $data ) ) {
            return false;
        }

        $original_id = isset( $data['id'] )      ? (int) $data['id']      : 0;
        $parent_id   = isset( $data['parent'] )  ? (int) $data['parent']  : 0;

        $mapping_key = sha1( $data['taxonomy'] . ':' . $data['slug'] );
        $existing = $this->term_exists( $data );
        if ( $existing ) {

            /**
             * Term processing already imported.
             *
             * @param array $data Raw data imported for the term.
             */
            do_action( 'lastudio_importer.process_already_imported.term', $data );

            $this->mapping['term'][ $mapping_key ] = $existing;
            $this->mapping['term_id'][ $original_id ] = $existing;
            return false;
        }

        // WP really likes to repeat itself in export files
        if ( isset( $this->mapping['term'][ $mapping_key ] ) ) {
            return false;
        }

        $termdata = array();
        $allowed = array(
            'slug' => true,
            'description' => true,
        );

        foreach ( $data as $key => $value ) {
            if ( ! isset( $allowed[ $key ] ) ) {
                continue;
            }

            $termdata[ $key ] = $data[ $key ];
        }

        $result = wp_insert_term( $data['name'], $data['taxonomy'], $termdata );
        if ( is_wp_error( $result ) ) {
            $this->logger->warning( sprintf(
                __( 'Failed to import %s %s', 'la-studio' ),
                $data['taxonomy'],
                $data['name']
            ) );
            $this->logger->debug( $result->get_error_message() );
            do_action( 'wp_import_insert_term_failed', $result, $data );

            /**
             * Term processing failed.
             *
             * @param WP_Error $result Error object.
             * @param array $data Raw data imported for the term.
             * @param array $meta Meta data supplied for the term.
             */
            do_action( 'lastudio_importer.process_failed.term', $result, $data, $meta );
            return false;
        }

        $term_id = $result['term_id'];

        $this->mapping['term'][ $mapping_key ] = $term_id;
        $this->mapping['term_id'][ $original_id ] = $term_id;

        $this->logger->info( sprintf(
            __( 'Imported "%s" (%s)', 'la-studio' ),
            $data['name'],
            $data['taxonomy']
        ) );
        $this->logger->debug( sprintf(
            __( 'Term %d remapped to %d', 'la-studio' ),
            $original_id,
            $term_id
        ) );

        do_action( 'wp_import_insert_term', $term_id, $data );

        /**
         * Term processing completed.
         *
         * @param int $term_id New term ID.
         * @param array $data Raw data imported for the term.
         */
        do_action( 'lastudio_importer.processed.term', $term_id, $data );
    }


    /**
     * Does the post exist?
     *
     * @param array $data Post data to check against.
     * @return int|bool Existing post ID if it exists, false otherwise.
     */
    protected function post_exists( $data ) {
        // Constant-time lookup if we prefilled
        $exists_key = html_entity_decode($data['guid']);

        if ( $this->options['prefill_existing_posts'] ) {
            return isset( $this->exists['post'][ $exists_key ] ) ? $this->exists['post'][ $exists_key ] : false;
        }

        // No prefilling, but might have already handled it
        if ( isset( $this->exists['post'][ $exists_key ] ) ) {
            return $this->exists['post'][ $exists_key ];
        }

        // Still nothing, try post_exists, and cache it
        $exists = la_import_check_post_exists( $data['post_title'], $data['post_content'], $data['post_date'], $data['post_type'] );
        $this->exists['post'][ $exists_key ] = $exists;

        return $exists;
    }

    /**
     * Mark the post as existing.
     *
     * @param array $data Post data to mark as existing.
     * @param int $post_id Post ID.
     */
    protected function mark_post_exists( $data, $post_id ) {
        $exists_key = html_entity_decode($data['guid']);
        $this->exists['post'][ $exists_key ] = $post_id;
    }

    /**
     * Does the term exist?
     *
     * @param array $data Term data to check against.
     * @return int|bool Existing term ID if it exists, false otherwise.
     */
    protected function term_exists( $data ) {
        $exists_key = sha1( $data['taxonomy'] . ':' . $data['slug'] );

        // Constant-time lookup if we prefilled
        if ( $this->options['prefill_existing_terms'] ) {
            return isset( $this->exists['term'][ $exists_key ] ) ? $this->exists['term'][ $exists_key ] : false;
        }

        // No prefilling, but might have already handled it
        if ( isset( $this->exists['term'][ $exists_key ] ) ) {
            return $this->exists['term'][ $exists_key ];
        }

        // Still nothing, try comment_exists, and cache it
        $exists = term_exists( $data['slug'], $data['taxonomy'] );
        if ( is_array( $exists ) ) {
            $exists = $exists['term_id'];
        }

        $this->exists['term'][ $exists_key ] = $exists;

        return $exists;
    }

    /**
     * Prefill existing post data.
     *
     * This preloads all GUIDs into memory, allowing us to avoid hitting the
     * database when we need to check for existence. With larger imports, this
     * becomes prohibitively slow to perform SELECT queries on each.
     *
     * By preloading all this data into memory, it's a constant-time lookup in
     * PHP instead. However, this does use a lot more memory, so for sites doing
     * small imports onto a large site, it may be a better tradeoff to use
     * on-the-fly checking instead.
     */
    protected function prefill_existing_posts() {
        global $wpdb;
        $posts = $wpdb->get_results( "SELECT ID, guid FROM {$wpdb->posts}" );

        foreach ( $posts as $item ) {
            $this->exists['post'][ html_entity_decode($item->guid) ] = $item->ID;
        }
    }

    /**
     * Prefill existing term data.
     *
     * @see self::prefill_existing_posts() for justification of why this exists.
     */
    protected function prefill_existing_terms() {
        global $wpdb;
        $query = "SELECT t.term_id, tt.taxonomy, t.slug FROM {$wpdb->terms} AS t";
        $query .= " JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id";
        $terms = $wpdb->get_results( $query );

        foreach ( $terms as $item ) {
            $exists_key = sha1( $item->taxonomy . ':' . $item->slug );
            $this->exists['term'][ $exists_key ] = $item->term_id;
        }
    }

}

class LaStudio_Importer_Logger {
    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency( $message, array $context = array() ) {
        return $this->log( 'emergency', $message, $context );
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert( $message, array $context = array() ) {
        return $this->log( 'alert', $message, $context );
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical( $message, array $context = array() ) {
        return $this->log( 'critical', $message, $context );
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error( $message, array $context = array()) {
        return $this->log( 'error', $message, $context );
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning( $message, array $context = array() ) {
        return $this->log( 'warning', $message, $context );
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice( $message, array $context = array() ) {
        return $this->log( 'notice', $message, $context );
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info( $message, array $context = array() ) {
        return $this->log( 'info', $message, $context );
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug( $message, array $context = array() ) {
        return $this->log( 'debug', $message, $context );
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log( $level, $message, array $context = array() ) {
        $this->messages[] = array(
            'timestamp' => time(),
            'level'     => $level,
            'message'   => $message,
            'context'   => $context,
        );
    }
}

class LaStudio_Importer_Logger_ServerSentEvents extends LaStudio_Importer_Logger {
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log( $level, $message, array $context = array() ) {
        $data = compact( 'level', 'message' );

        switch ( $level ) {
            case 'emergency':
            case 'alert':
            case 'critical':
            case 'error':
            case 'warning':
            case 'notice':
            case 'info':
                echo "event: log\n";
                echo 'data: ' . wp_json_encode( $data ) . "\n\n";
                flush();
                break;

            case 'debug':
                if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
                    echo "event: log\n";
                    echo 'data: ' . wp_json_encode( $data ) . "\n\n";
                    flush();
                    break;
                }
                break;
        }
    }
}

/**
 * @since 1.0.0
 */
class LaStudio_Importer_Images {

    /**
     * Get image hash.
     *
     * Retrieve the sha1 hash of the image URL.
     *
     * @since 1.0.0
     * @access private
     *
     * @param string $attachment_url The attachment URL.
     *
     * @return string Image hash.
     */
    private function get_hash_image( $attachment_url ) {
        return sha1( $attachment_url );
    }

    /**
     * Get saved image.
     *
     * Retrieve new image ID, if the image has a new ID after the import.
     *
     * @since 1.0.0
     * @access private
     *
     * @param array $attachment The attachment.
     *
     * @return false|array New image ID  or false.
     */
    private function get_saved_image( $attachment ) {
        global $wpdb;

        $post_id = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT `post_id` FROM `' . $wpdb->postmeta . '`
					WHERE `meta_key` = \'_elementor_source_image_hash\'
						AND `meta_value` = %s
				;',
                $this->get_hash_image( $attachment['url'] )
            )
        );

        if ( $post_id ) {
            $new_attachment = [
                'id' => $post_id,
                'url' => wp_get_attachment_url( $post_id ),
            ];

            return $new_attachment;
        }

        return false;
    }

    /**
     * Import image.
     *
     * Import a single image from a remote server, upload the image WordPress
     * uploads folder, create a new attachment in the database and updates the
     * attachment metadata.
     *
     * @since 1.0.0
     * @access public
     *
     * @param array $attachment The attachment.
     *
     * @return false|array Imported image data, or false.
     */
    public function import( $attachment ) {
        $saved_image = $this->get_saved_image( $attachment );

        if ( $saved_image ) {
            return $saved_image;
        }

        // Extract the file name and extension from the url.
        $filename = basename( $attachment['url'] );

        $file_content = wp_remote_retrieve_body( wp_safe_remote_get( $attachment['url'] ) );

        if ( empty( $file_content ) ) {
            return false;
        }

        $upload = wp_upload_bits(
            $filename,
            null,
            $file_content
        );

        $post = [
            'post_title' => $filename,
            'guid' => $upload['url'],
        ];

        $info = wp_check_filetype( $upload['file'] );
        if ( $info ) {
            $post['post_mime_type'] = $info['type'];
        } else {
            // For now just return the origin attachment
            return $attachment;
        }

        $post_id = wp_insert_attachment( $post, $upload['file'] );
        wp_update_attachment_metadata(
            $post_id,
            wp_generate_attachment_metadata( $post_id, $upload['file'] )
        );
        update_post_meta( $post_id, '_elementor_source_image_hash', $this->get_hash_image( $attachment['url'] ) );

        $new_attachment = [
            'id' => $post_id,
            'url' => $upload['url'],
        ];
        return $new_attachment;
    }

    /**
     * Template library import images constructor.
     *
     * Initializing the images import class used by the template library through
     * the WordPress Filesystem API.
     *
     * @since 1.0.0
     * @access public
     */
    public function __construct() {
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        WP_Filesystem();
    }
}

class LaStudio_Importer_Elementor {

    public function __construct() {
    }

    public function import_element_data( $data, $post_id ) {
        $content = json_decode($data, true);
        $content_new = $this->process_export_import_content( $content, 'on_import' );

        $document = Elementor\Plugin::$instance->documents->get($post_id);

        if ( is_wp_error( $document ) ) {
            /**
             * @var \WP_Error $document
             */
            return $document;
        }

        if ( ! current_user_can( 'unfiltered_html' ) ) {
            $content_new = wp_kses_post_deep( $content_new );
        }

        $editor_data = $document->get_elements_raw_data( $content_new );

        // We need the `wp_slash` in order to avoid the unslashing during the `update_post_meta`
        $json_value = wp_slash( wp_json_encode( $content_new ) );

        // Don't use `update_post_meta` that can't handle `revision` post type
        $is_meta_updated = update_metadata( 'post', $post_id, '_elementor_data', $json_value );

        do_action( 'elementor/db/before_save', get_post_status($post_id), $is_meta_updated );

        if( 'nav_menu_item' !== get_post_type($post_id) ) {
            Elementor\Plugin::$instance->db->save_plain_text( $post_id );
        }

        /**
         * After saving data.
         *
         * Fires after Elementor saves data to the database.
         *
         * @since 1.0.0
         *
         * @param int   $post_id     The ID of the post.
         * @param array $editor_data Sanitize posted data.
         */
        do_action( 'elementor/editor/after_save', $post_id, $editor_data );

        return $post_id ;
    }

    /**
     * Process content for export/import.
     *
     * Process the content and all the inner elements, and prepare all the
     * elements data for export/import.
     *
     * @since 1.0.0
     * @access protected
     *
     * @param array  $content A set of elements.
     * @param string $method  Accepts either `on_export` to export data or
     *                        `on_import` to import data.
     *
     * @return mixed Processed content data.
     */
    protected function process_export_import_content( $content, $method ) {
        return Elementor\Plugin::$instance->db->iterate_data(
            $content, function( $element_data ) use ( $method ) {
            $element = Elementor\Plugin::$instance->elements_manager->create_element_instance( $element_data );

            // If the widget/element isn't exist, like a plugin that creates a widget but deactivated
            if ( ! $element ) {
                return null;
            }

            return $this->process_element_export_import_content( $element, $method );
        }
        );
    }

    /**
     * Process single element content for export/import.
     *
     * Process any given element and prepare the element data for export/import.
     *
     * @since 1.0.0
     * @access protected
     *
     * @param Elementor\Controls_Stack $element
     * @param string         $method
     *
     * @return array Processed element data.
     */
    protected function process_element_export_import_content( Elementor\Controls_Stack $element, $method ) {
        $element_data = $element->get_data();

        if ( method_exists( $element, $method ) ) {
            // TODO: Use the internal element data without parameters.
            $element_data = $element->{$method}( $element_data );
        }

        foreach ( $element->get_controls() as $control ) {
            $control_class = Elementor\Plugin::$instance->controls_manager->get_control( $control['type'] );

            // If the control isn't exist, like a plugin that creates the control but deactivated.
            if ( ! $control_class ) {
                return $element_data;
            }

            if ( method_exists( $control_class, $method ) ) {
                $element_data['settings'][ $control['name'] ] = $control_class->{$method}( $element->get_settings( $control['name'] ), $control );
            }

            // On Export, check if the control has an argument 'export' => false.
            if ( 'on_export' === $method && isset( $control['export'] ) && false === $control['export'] ) {
                unset( $element_data['settings'][ $control['name'] ] );
            }
        }

        return $element_data;
    }

}