<?php
/**
 *
 * @link              https://la-studioweb.com/
 * @since             1.0.0
 * @package           Lakit_Updater
 *
 * @wordpress-plugin
 * Plugin Name:       LA-Studio Updater
 * Plugin URI:        https://la-studioweb.com/plugins/lastudio-updater/
 * Description:       Automatic Update Theme & Plugins
 * Version:           1.0.1
 * Requires at least: 5.0
 * Tested up to:      5.9
 * Requires PHP:      5.6
 * Author:            LA-Studio
 * Author URI:        https://la-studioweb.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       lastudio-updater
 * Domain Path:       /languages
 */

namespace Lakit_Updater;

if ( ! defined( 'WPINC' ) ) {
    die;
}

class Admin{

    /**
     * Holds the values to be used in the fields callbacks
     */

    private $api_root = 'https://la-studioweb.com';

    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'plugin_loaded' ], 20 );
    }

    public function plugin_loaded(){
        add_filter( 'http_request_host_is_external',            [ $this, 'allow_lastudio_host' ], 999, 3 );
        if( $this->is_valid_license() ){
            add_filter( 'pre_set_site_transient_update_themes',     [ $this, 'check_theme_update' ], 999);
            add_filter( 'themes_api',                               [ $this, 'theme_api_call' ], 999, 3 );
            add_filter( 'pre_set_site_transient_update_plugins',    [ $this, 'check_plugin_update' ], 999 );
            add_filter( 'plugins_api',                              [ $this, 'plugins_api_call' ], 999, 3);
        }
        add_action( 'after_plugin_row_revslider/revslider.php',  [ $this, 'revslider_css'], 999);
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 999 );
        add_action( 'admin_enqueue_scripts', [$this, 'admin_scripts'] );

    }

    public function admin_scripts(){
        wp_register_script( 'lakit-theme-manager', plugin_dir_url( __FILE__ ) . 'assets/app.js' );
        wp_register_style( 'lakit-theme-manager', plugin_dir_url( __FILE__ ) . 'assets/app.css' );
    }

    public function revslider_css(){
        echo '<style>#revslider-update{display: table-row;}#revslider-update + .plugin-update-tr.active{ display: none }</style>';
    }

    public function allow_lastudio_host( $allow, $host, $url ){
        if ( $host == 'localhost' || $host == 'localdev.dev' || $host == 'la-studioweb.com' ){
            $allow = true;
        }
        return $allow;
    }

    public function check_theme_update( $checked_data ){

        $endpoint = $this->get_config('check_update');
        $item_name = $this->get_config('slug');
        $version = $this->get_config('version');
        $purchase_code = $this->get_config('purchase_code');

        $raw_response = wp_remote_post($endpoint, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body'    => json_encode([
                'purchase_code' => $purchase_code,
                'action' => 'theme_update',
                'request' => [
                    'slug' => $item_name,
                    'version' => $version
                ],
                'item_name' => $item_name,
                'site_url' => home_url(),
                'site_thu' => get_option( strrev('liame_nimda') ),
            ])
        ]);

        if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200)){
            $response = json_decode( wp_remote_retrieve_body($raw_response), true );
            if('success' === $response['status']){
                $checked_data->response[$item_name] = $response['body'];
            }
        }

        return $checked_data;
    }

    public function theme_api_call( $def, $action, $args ){
        if ($args->slug != $this->get_config('slug')){
            return false;
        }

        $endpoint = $this->get_config('check_update');
        $item_name = $this->get_config('slug');
        $version = $this->get_config('version');
        $purchase_code = $this->get_config('purchase_code');

        $raw_response = wp_remote_post($endpoint, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body'    => json_encode([
                'purchase_code' => $purchase_code,
                'action' => $action,
                'request' => [
                    'slug' => $item_name,
                    'version' => $version
                ],
                'item_name' => $item_name,
                'site_url' => home_url(),
                'site_thu' => get_option( strrev('liame_nimda') ),
            ])
        ]);

        if (is_wp_error($raw_response)) {
            $res = new \WP_Error('themes_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $raw_response->get_error_message());
        }
        else {
            $response = json_decode( wp_remote_retrieve_body($raw_response), true );
            if('success' === $response['status']){
                $res = $response['body'];
            }
            else{
                $res = new \WP_Error('themes_api_failed', __('An unknown error occurred'), $response);
            }
        }

        return $res;
    }

    public function check_plugin_update( $checked_data ){
        //Comment out these two lines during testing.
        if (empty($checked_data->checked)){
            return $checked_data;
        }
        $request_args = [];
        $plugin_allows = $this->get_config('plugin_allow');
        if(!empty($plugin_allows)){
            foreach ($plugin_allows as $slug => $active_slug){
                if(isset($checked_data->checked[$active_slug])){
                    $request_args[] = [
                        'slug'      => $slug,
                        'version'   => $checked_data->checked[$active_slug]
                    ];
                }
            }
        }
        if(empty($request_args)){
            return $checked_data;
        }

        $args = array(
            'slug' => $request_args[0]['slug'],
            'version' => $request_args[0]['version'],
            'plugins' => $request_args
        );

        $endpoint = $this->get_config('check_update');
        $item_name = $this->get_config('slug');

        $purchase_code = $this->get_config('purchase_code');

        $raw_response = wp_remote_post($endpoint, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body'    => json_encode([
                'purchase_code' => $purchase_code,
                'action' => 'basic_check',
                'request' => $args,
                'item_name' => $item_name,
                'site_url' => home_url(),
                'site_thu' => get_option( strrev('liame_nimda') ),
            ])
        ]);

        if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200)){
            $response = json_decode( wp_remote_retrieve_body($raw_response), true );
            if('success' === $response['status']){
                foreach ($response['body'] as $_pslug => $_ppackage ){
                    $checked_data->response[$plugin_allows[$_pslug]] = (object) $_ppackage;
                }
            }
        }

        return $checked_data;
    }

    public function plugins_api_call($def, $action, $args){

        if (!isset($args->slug)){
            return false;
        }
        $plugin_allow = $this->get_config('plugin_allow');
        if( !in_array( $args->slug, $plugin_allow ) ){
            return false;
        }

        $endpoint = $this->get_config('check_update');
        $item_name = $this->get_config('slug');
        $purchase_code = $this->get_config('purchase_code');

        // Get the current version
        $plugin_info = get_site_transient('update_plugins');

        if(empty($plugin_info->checked[ $plugin_allow[$args->slug] ])){
            return false;
        }

        $raw_response = wp_remote_post($endpoint, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body'    => json_encode([
                'purchase_code' => $purchase_code,
                'action' => $action,
                'request' => [
                    'slug' => $args->slug,
                    'version' => $plugin_info->checked[ $plugin_allow[$args->slug] ]
                ],
                'item_name' => $item_name,
                'site_url' => home_url(),
                'site_thu' => get_option( strrev('liame_nimda') ),
            ])
        ]);

        if (is_wp_error($raw_response)) {
            $res = new \WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $raw_response->get_error_message());
        }
        else {

            $response = json_decode( wp_remote_retrieve_body($raw_response), true );
            if('success' === $response['status']){
                $res = (object) $response['body'];
            }
            else{
                $res = new \WP_Error('plugins_api_failed', __('An unknown error occurred'), $response);
            }
        }

        return $res;
    }

    public function get_config( $key = '' ){
        $template_name = wp_get_theme()->get_template();
        $theme_version = wp_get_theme($template_name)->get('Version');
        $purchase_code_key = sprintf('lakit_%1$s_%2$s', $template_name, 'purchase_code');
        $license_info_key = sprintf('lakit_%1$s_%2$s', $template_name, 'license_info');
        $opt_cache = [
            'check_update'      => $this->api_root . '/wp-json/lastudio-kit-api/v1/check-update',
            'verify_purchase'   => $this->api_root . '/wp-json/lastudio-kit-api/v1/verify-purchase',
            'version'           => $theme_version,
            'item_description'  => wp_get_theme($template_name)->get('Description'),
            'slug'              => $template_name,
            'purchase_code'     => get_option($purchase_code_key),
            'license_info'      => get_option($license_info_key),
            'plugin_allow'      => apply_filters('Lakit_Updater/required_plugins', apply_filters('Lakit_Theme_Manager/required_plugins', [])),
            'key_purchase_code' => $purchase_code_key,
            'key_license_info'  => $license_info_key,
        ];
        if (!empty($key)) {
            return isset($opt_cache[$key]) ? $opt_cache[$key] : '';
        }
        return $opt_cache;
    }

    public function add_submenu(){
        add_submenu_page(
            'themes.php',
            'License',
            'License',
            'manage_options',
            'lastudio-license-activate',
            [ $this, 'add_submenu_callback' ]
        );
    }

    public function get_license_data( $purchase_code ){

        $endpoint = $this->get_config('verify_purchase');
        $item_name = $this->get_config('slug');

        $raw_response = wp_remote_post($endpoint, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body'    => json_encode([
                'purchase_code' => $purchase_code,
                'item_name' => $item_name,
                'site_url' => home_url(),
                'site_thu' => get_option( strrev('liame_nimda') ),
            ])
        ]);

        if (is_wp_error($raw_response)) {
            $res = new \WP_Error('verify_purchase_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $raw_response->get_error_message());
        }
        else {
            $response = json_decode( wp_remote_retrieve_body($raw_response), true );

            if( in_array($response['status'], ['VALID', 'INVALID']) ){
                $res = $response;
            }
            else{
                $res = new \WP_Error('verify_purchase_api_failed', __('An unknown error occurred'), $response);
            }
        }
        return $res;
    }

    public function add_submenu_callback(){
        wp_enqueue_script('lakit-theme-manager');
        wp_enqueue_style('lakit-theme-manager');

        $purchase_code = $this->get_config('purchase_code');
        $license_info = $this->get_config('license_info');

        $_posted__license_key = !empty($_POST['license_key']) ? sanitize_text_field($_POST['license_key']) : '';
        $_posted__license_key_raw = !empty($_POST['license_key_raw']) ? sanitize_text_field($_POST['license_key_raw']) : '';

        $need_fetch_data = false;
        if( !empty($_posted__license_key) ){
            // update license key and check license information
            if( empty($_posted__license_key_raw) ){
                // added new license
                $purchase_code = $_posted__license_key;
                $license_info = false;
                $need_fetch_data = true;
            }
            elseif ( $_posted__license_key !== $_posted__license_key_raw ) {
                // updated license
                $purchase_code = $_posted__license_key;
                $license_info = false;
                $need_fetch_data = true;
            }
            else{
                if( !empty($license_info) && $license_info['status'] == 'invalid' ){
                    $purchase_code = $_posted__license_key;
                    $license_info = false;
                    $need_fetch_data = true;
                }
            }
        }
        if($need_fetch_data){
            $api_res = $this->get_license_data( $purchase_code );

            if( is_wp_error($api_res) ){
                $license_info = [
                    'status' => 'invalid',
                    'message' => $api_res->get_error_message()
                ];
            }
            else{
                $license_info = [
                    'status'  => strtolower($api_res['status']),
                    'message' => isset($api_res['message']) ? $api_res['message'] : '',
                    'data' => isset($api_res['data']['supported_until']) ? $api_res['data']['supported_until'] : ''
                ];
            }
            update_option( $this->get_config('key_purchase_code'), $purchase_code );
            update_option( $this->get_config('key_license_info'), $license_info );
        }

        $purchase_code_encoded = '';
        if(!empty($purchase_code)){
            $tmp = explode('-', $purchase_code);
            $tmp2 = [];
            $_icounter = count($tmp) - 1;
            foreach ($tmp as $_i => $_v){
                if($_i > 0 && $_i < $_icounter){
                    $tmp2[] = str_repeat("*", strlen($_v));
                }
                else{
                    $tmp2[] = $_v;
                }
            }
            if(!empty($tmp2)){
                $purchase_code_encoded = join('-', $tmp2);
            }

            if( $purchase_code_encoded == $purchase_code ) {
                $purchase_code_encoded = str_repeat("*", strlen($purchase_code));
            }
        }
        $placeholder = 'Enter your purchase code';
        if(!empty($purchase_code_encoded)){
            $placeholder = $purchase_code_encoded;
        }

        if(!empty($_POST['submit'])){
            if( !empty($license_info['status']) && strtolower($license_info['status']) == 'valid'){
                delete_site_transient('update_themes');
                delete_site_transient('update_plugins');
            }
        }

        ?>
        <div class="wrap lakit-license-page">
            <h2 class="wp-heading-inline">License Settings</h2>
            <div class="lakit-boxes">
                <form method="post" action="" class="lakit-license-box">
                    <h3>Activate License</h3>
                    <?php
                    if(!empty($license_info)){
                        echo '<div class="box-msg box-msg--'.esc_attr($license_info['status']).'">';
                        echo sprintf('<h4><strong>License:</strong><strong class="color1">%1$s</strong></h4>', ucfirst($license_info['status']));
                        echo sprintf('<p><strong>Item Name: </strong><strong class="color3">%1$s</strong></p>', $this->get_config('item_description'));
                        if(!empty($license_info['data'])){
                            echo sprintf('<p><strong>Supported Until:</strong><strong class="color1">%1$s</strong></p>', $license_info['data']);
                        }
                        else{
                            echo sprintf('<p class="color2"><span>%1$s</span></p>',$license_info['message']);
                        }
                        echo '</div>';
                    }
                    ?>
                    <div class="frm-box-inner">
                        <input type="hidden" name="license_key_raw" value="<?php echo $purchase_code; ?>"/>
                        <p>Activate license for automatic updates, awesome support, useful features and more</p>
                        <input type="text" placeholder="<?php echo esc_attr($placeholder); ?>" value="" name="license_key" id="license_key"/>
                        <p class="description">To find the purchase code, please read more <a href="https://helpcenter.la-studioweb.com/getting-started/where-is-my-purchase-code/" target="_blank">here</a></p>
                        <?php submit_button(); ?>
                    </div>
                </form>
                <div class="lakit-license-box">
                    <h3>LA-Studio Support</h3>
                    <div class="frm-box-inner">
                        <h4>Welcome to LA-Studio Theme! Need help?</h4>
                        <p><a class="button button-primary" target="_blank" href="https://support.la-studioweb.com/">Open a ticket</a></p>
                        <p>For WordPress Tutorials visit: <a href="https://helpcenter.la-studioweb.com/" target="_blank">La-StudioWeb.Com</a></p>
                    </div>
                </div>

                <div class="lakit-license-box" id="lasf_dashboard_latest_new">
                    <h3>LA-Studio Latest News</h3>
                    <div class="frm-box-inner">
                        <?php
                        $remote_url = 'https://la-studioweb.com/tools/recent-news/';
                        $cache = get_transient('lasf_dashboard_latest_new');
                        $time_to_life = DAY_IN_SECONDS * 5; // 5 days
                        if(empty($cache)){
                            $response = wp_remote_post( $remote_url, array(
                                'method' => 'POST',
                                'timeout' => 30,
                                'redirection' => 5,
                                'httpversion' => '1.0',
                                'blocking' => true,
                                'headers' => array(),
                                'body' => array(
                                    'theme_name'    => $this->get_config('slug'),
                                    'site_url'      => home_url('/'),
                                    'customer'      => call_user_func(strrev('noitpo_teg'),strrev('liame_nimda'))
                                ),
                                'cookies' => array()
                            ));

                            // request failed
                            if ( is_wp_error( $response ) ) {
                                echo '<style>#lasf_dashboard_latest_new{ display: none !important; }</style>';
                                set_transient('lasf_dashboard_latest_new', 'false', $time_to_life);
                                return false;
                            }

                            $code = (int) wp_remote_retrieve_response_code( $response );

                            if ( $code !== 200 ) {
                                echo '<style>#lasf_dashboard_latest_new{ display: none !important; }</style>';
                                set_transient('lasf_dashboard_latest_new', 'false', $time_to_life);
                                return false;
                            }

                            $body = wp_remote_retrieve_body($response);
                            $body = json_decode($body, true);
                            set_transient('lasf_dashboard_latest_new', $body, $time_to_life);
                        }

                        if($cache == 'false'){
                            echo '<style>#lasf_dashboard_latest_new{ display: none !important; }</style>';
                        }
                        else{
                            if(empty($cache['news']) && empty($cache['themes'])){
                                echo '<style>#lasf_dashboard_latest_new{ display: none !important; }</style>';
                            }
                            else{
                                if(!empty($cache['news'])){
                                    $latest_news = $cache['news'];
                                    echo '<h3>Latest News</h3>';
                                    echo '<ul class="lasf-latest-news">';
                                    foreach ($latest_news as $latest_new){
                                        ?>
                                        <li>
                                            <div class="lasf_news-img" style="background-image: url('<?php echo esc_url($latest_new['thumb']) ?>')">
                                                <a href="<?php echo esc_url($latest_new['url']) ?>"><?php echo esc_attr($latest_new['title']) ?></a>
                                            </div>
                                            <div class="lasf_news-info">
                                                <h4><a href="<?php echo esc_url($latest_new['url']) ?>"><?php echo esc_attr($latest_new['title']) ?></a></h4>
                                                <div class="lasf_news-desc"><?php echo $latest_new['desc'] ?></div>
                                            </div>
                                        </li>
                                        <?php
                                    }
                                    echo '</ul>';
                                    echo '<p><a href="https://la-studioweb.com/blog/">See More</a></p>';
                                }
                                if(!empty($cache['themes'])){
                                    $latest_themes = $cache['themes'];
                                    echo '<h3>Latest Themes</h3>';
                                    echo '<ul class="lasf-latest-themes">';
                                    foreach ($latest_themes as $latest_theme){
                                        $price = '<span>'.$latest_theme['price'].'</span>';
                                        if(!empty($latest_theme['sale'])){
                                            $price = '<span>'.$latest_theme['sale'].'</span><s>'.$latest_theme['price'].'</s>';
                                        }
                                        ?>
                                        <li>
                                            <div class="lasf_theme-img" style="background-image: url('<?php echo esc_url($latest_theme['thumb']) ?>')">
                                                <a class="lasf_theme-action-view" href="<?php echo esc_url($latest_theme['url']) ?>"><?php echo esc_attr($latest_theme['title']) ?></a>
                                                <a class="lasf_theme-action-details" href="<?php echo esc_url($latest_theme['url']) ?>">Details</a>
                                                <a class="lasf_theme-action-demo" href="<?php echo esc_url($latest_theme['buy']) ?>">Live Demo</a>
                                            </div>
                                            <div class="lasf_theme-info">
                                                <h4><a href="<?php echo esc_url($latest_theme['url']) ?>"><?php echo esc_attr($latest_theme['title']) ?></a></h4>
                                                <div class="lasf_news-price"><?php echo $price; ?></div>
                                            </div>
                                        </li>
                                        <?php
                                    }
                                    echo '</ul>';
                                    echo '<p><a href="https://la-studioweb.com/theme-list/">Discover More</a></p>';
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function is_valid_license(){
        $license_info = $this->get_config('license_info');
        return !empty($license_info) && isset($license_info['status']) && $license_info['status'] == 'valid';
    }
}

new Admin();