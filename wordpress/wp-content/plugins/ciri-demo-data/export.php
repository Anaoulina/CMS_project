<?php

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Direct script access denied.' );
}

class LaStudio_Export_Demo{

    protected $taxonomies = [];

    protected $post_type = [];

    public function __construct( $taxonomies = [], $post_type = []) {

        if(!empty($taxonomies)){
            $taxonomies = array_combine($taxonomies,$taxonomies);
        }
        else{
            $taxonomies = [
                "category" => "category",
                "post_tag" => "post_tag",
                "nav_menu" => "nav_menu",
                "elementor_library_type" => "elementor_library_type",
                "elementor_library_category" => "elementor_library_category",
                "mphb_room_type_category" => "mphb_room_type_category",
                "mphb_room_type_tag" => "mphb_room_type_tag",
                "mphb_room_type_facility" => "mphb_room_type_facility",
                "la_portfolio_category" => "la_portfolio_category"
            ];
        }

        $this->taxonomies = $taxonomies;

        if(empty($post_type)){
            $post_type = [
                "post",
                "page",
                "attachment",
                "nav_menu_item",
                "elementor_library",
                "mphb_booking",
                "mphb_room_type",
                "mphb_room_attribute",
                "mphb_season",
                "mphb_rate",
                "mphb_room_service",
                "mphb_room",
                "mphb_payment",
                "mphb_reserved_room",
                "mphb_coupon",
                "wpcf7_contact_form",
                "la_team_member",
                "la_portfolio"
            ];
        }

        $this->post_type = $post_type;

        add_action('admin_menu', [ $this, 'add_export_menu' ] );
        add_action('admin_post_lastudio_generate_demo_data', [ $this, 'lastudio_generate_demo_data' ] );
        add_action('admin_post_lastudio_generate_widget_data', [ $this, 'lastudio_generate_widget_data' ] );
        add_action('admin_post_lastudio_generate_elementor_setting', [ $this, 'lastudio_generate_elementor_setting' ] );
    }

    public function add_export_menu(){
        add_submenu_page(
            'tools.php',
            esc_html__('Export Demo Data', 'la-studio'),
            esc_html__('Export Demo Data', 'la-studio'),
            'manage_options',
            'la_export',
            [ $this, 'export_panel' ]
        );
    }

    public function lastudio_generate_demo_data(){
        $filename = 'demo-data';
        $generatedDate = date('d-m-Y_His');

        header('Content-Description: File Transfer');
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header('Content-Type: text/json; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"" . $filename . "_" . $generatedDate . ".json\";" );
        header("Content-Transfer-Encoding: binary");

        echo $this->export_all();
    }

    public function lastudio_generate_widget_data(){
        $filename = 'widget-data';
        $generatedDate = date('d-m-Y_His');

        header('Content-Description: File Transfer');
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header('Content-Type: text/json; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"" . $filename . "_" . $generatedDate . ".json\";" );
        header("Content-Transfer-Encoding: binary");

        echo $this->export_widget();
    }

    public function lastudio_generate_elementor_setting(){
        $filename = 'elementor-setting';
        $generatedDate = date('d-m-Y_His');

        header('Content-Description: File Transfer');
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header('Content-Type: text/json; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"" . $filename . "_" . $generatedDate . ".json\";" );
        header("Content-Transfer-Encoding: binary");

        if(defined( 'ELEMENTOR_VERSION' )){
            $kit_active_id = Elementor\Plugin::$instance->kits_manager->get_active_id();
            $raw_kit_settings = get_post_meta( $kit_active_id, '_elementor_page_settings', true );
            if(empty($raw_kit_settings)){
                $raw_kit_settings = [];
            }
        }
        else{
            $raw_kit_settings = [];
        }

        if(isset($raw_kit_settings['site_name'])){
            unset($raw_kit_settings['site_name']);
        }
        if(isset($raw_kit_settings['site_description'])){
            unset($raw_kit_settings['site_description']);
        }
        if(isset($raw_kit_settings['activeItemIndex'])){
            unset($raw_kit_settings['activeItemIndex']);
        }

        echo wp_json_encode($raw_kit_settings);
    }

    public function export_panel(){
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Export Demo Data', 'la-studio') ?></h1>
            <br/>
            <form action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <input type="hidden" name="action" value="lastudio_generate_demo_data" />
                <input type="submit" name="submit" class="button button-primary" value="Generate & Download Content JSON File" />
            </form>
            <br/>
            <form action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <input type="hidden" name="action" value="lastudio_generate_widget_data" />
                <input type="submit" name="submit" class="button button-primary" value="Generate & Download Widget JSON File" />
            </form>
            <br/>
            <form action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <input type="hidden" name="action" value="lastudio_generate_elementor_setting" />
                <input type="submit" name="submit" class="button button-primary" value="Generate & Download Elementor Setting JSON File" />
            </form>
        </div>
        <?php
    }

    public function export_widget(){

        global $wp_registered_widget_controls;

        $widget_controls = $wp_registered_widget_controls;

        $available_widgets = array();

        foreach ( $widget_controls as $widget ) {

            // No duplicates.
            if ( ! empty( $widget['id_base'] ) && ! isset( $available_widgets[ $widget['id_base'] ] ) ) {
                $available_widgets[ $widget['id_base'] ]['id_base'] = $widget['id_base'];
                $available_widgets[ $widget['id_base'] ]['name']    = $widget['name'];
            }

        }

        // Get all widget instances for each widget.
        $widget_instances = array();

        // Loop widgets.
        foreach ( $available_widgets as $widget_data ) {

            // Get all instances for this ID base.
            $instances = get_option( 'widget_' . $widget_data['id_base'] );

            // Have instances.
            if ( ! empty( $instances ) ) {

                // Loop instances.
                foreach ( $instances as $instance_id => $instance_data ) {

                    // Key is ID (not _multiwidget).
                    if ( is_numeric( $instance_id ) ) {
                        $unique_instance_id = $widget_data['id_base'] . '-' . $instance_id;
                        $widget_instances[ $unique_instance_id ] = $instance_data;
                    }

                }

            }

        }

        // Gather sidebars with their widget instances.
        $sidebars_widgets = get_option( 'sidebars_widgets' );
        $sidebars_widget_instances = array();
        foreach ( $sidebars_widgets as $sidebar_id => $widget_ids ) {

            // Skip inactive widgets.
            if ( 'wp_inactive_widgets' === $sidebar_id ) {
                continue;
            }

            // Skip if no data or not an array (array_version).
            if ( ! is_array( $widget_ids ) || empty( $widget_ids ) ) {
                continue;
            }

            // Loop widget IDs for this sidebar.
            foreach ( $widget_ids as $widget_id ) {

                // Is there an instance for this widget ID?
                if ( isset( $widget_instances[ $widget_id ] ) ) {

                    // Add to array.
                    $sidebars_widget_instances[ $sidebar_id ][ $widget_id ] = $widget_instances[ $widget_id ];

                }

            }

        }

        // Encode the data for file contents.
        return wp_json_encode( $sidebars_widget_instances );

    }

    public function export_all(){
        return wp_json_encode([
            'taxonomy'      => $this->export_tax(),
            'post_type'  => $this->export_content_type()
        ]);
    }

    /*
     * Taxonomy Schema
     * @term_id
     * @taxonomy
     * @name
     * @slug
     * @parent
     * @description
     */
    public function export_tax(){
        $tax_exists = [];
        $data = [];

        foreach ($this->taxonomies as $taxonomy){
            if(taxonomy_exists( $taxonomy )){
                $tax_exists[$taxonomy] = $taxonomy;
            }
        }

        if(empty($tax_exists)){
            return $data;
        }
        $custom_terms = get_terms( $tax_exists, array(
            'get' => 'all'
        ));
        if(!is_wp_error($custom_terms)){
            $terms = [];
            // Put categories in order with no child going before its parent.
            while ($t = array_shift($custom_terms)) {
                if ($t->parent == 0 || isset($terms[$t->parent])) {
                    $terms[$t->term_id] = $t;
                } else {
                    $custom_terms[] = $t;
                }
            }
            if(!empty($terms)){
                foreach ($terms as $term){
                    $data[] = [
                        'term_id' => $term->term_id,
                        'taxonomy' => $term->taxonomy,
                        'name' => $term->name,
                        'slug' => $term->slug,
                        'parent' => $term->parent,
                        'description' => $term->description
                    ];
                }
            }
        }
        return $data;
    }

    protected function get_post_ids(){
        global $wpdb;

        $join = '';
        $esses = array_fill(0, count($this->post_type), '%s');
        $where = $wpdb->prepare("{$wpdb->posts}.post_type IN (" . implode(',', $esses) . ')', $this->post_type);
        // Grab a snapshot of post IDs, just in case it changes during the export.
        return $wpdb->get_col("SELECT ID FROM {$wpdb->posts} $join WHERE $where");

    }

    protected function get_post_taxonomy( $post ){
        $taxonomies = get_object_taxonomies($post->post_type);
        if (empty($taxonomies)){
            return [];
        }
        $terms = wp_get_object_terms($post->ID, $taxonomies);

        $data = [];
        foreach ((array)$terms as $term) {
            $data[] = [
                'taxonomy' => $term->taxonomy,
                'slug' => $term->slug,
                'name' => $term->name
            ];
        }
        return $data;
    }

    protected function get_postmeta( $post ) {

        global $wpdb;

        $postmeta = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d", $post->ID));

        $data = [];

        foreach ($postmeta as $meta) {
            if( in_array($meta->meta_key, ['_vc_post_settings', '_dp_original', '_edit_last', '_edit_lock', 'slide_template', '_thumbnail_id', '_elementor_controls_usage']) ){
                continue;
            }
            $data[$meta->meta_key] = $meta->meta_value;
        }

        return $data;
    }

    /*
     * Content type Schema
     *
     * @post_title
     * @post_name
     * @guid
     * @post_status
     * @post_date
     * @post_date_gmt
     * @post_excerpt
     * @post_content
     * @post_type
     * @post_parent
     * @thumbnail_url
     * @postmeta
     * @@meta_key => @meta_value
     * @@PostInTerms
     */
    public function export_content_type(){
        global $wpdb;

        $all_data_return = [];

        $post_ids = $this->get_post_ids();

        if ($post_ids) {
            /**
             * @global WP_Query $wp_query
             */
            global $wp_query;

            // Fake being in the loop.
            $wp_query->in_the_loop = true;

            // Fetch 20 posts at a time rather than loading the entire table into memory.
            while ($next_posts = array_splice($post_ids, 0, 20)) {
                $where = 'WHERE ID IN (' . join(',', $next_posts) . ')';
                $posts = $wpdb->get_results("SELECT * FROM {$wpdb->posts} $where");
                // Begin Loop.
                foreach ($posts as $post) {
                    setup_postdata($post);
                    $data_post = [];

                    $data_post['post_title'] = $post->post_title;
                    $data_post['post_id'] = intval($post->ID);
                    $data_post['post_name'] = $post->post_name;
                    $data_post['guid'] = get_the_guid($post);
                    $data_post['post_status'] = $post->post_status;
                    $data_post['post_date'] = $post->post_date;
                    $data_post['post_date_gmt'] = $post->post_date_gmt;

                    // disable content
                    if(get_post_meta( $post->ID, '_elementor_edit_mode', true )){
                        $data_post['post_content'] = $post->post_title;
                    }
                    else{
                        $data_post['post_content'] = $post->post_content;
                    }
                    $data_post['post_excerpt'] = $post->post_excerpt;
                    if($post->post_type == 'nav_menu_item'){
                        $data_post['post_content'] = '';
                        $data_post['post_content'] = '';
                        $data_post['post_excerpt'] = '';
                    }
                    $data_post['post_type'] = $post->post_type;
                    $data_post['post_parent'] = intval($post->post_parent);
                    if(has_post_thumbnail($post)){
                        $data_post['thumbnail_url'] = get_the_post_thumbnail_url($post, 'full');
                    }
                    else{
                        $data_post['thumbnail_url'] = '';
                    }

                    $post_taxonomy = $this->get_post_taxonomy($post);
                    if(!empty($post_taxonomy)){
                        $data_post['post_taxonomy'] = $post_taxonomy;
                    }
                    $postmeta = $this->get_postmeta($post);
                    if(!empty($postmeta)){
                        $data_post['post_metadata'] = $postmeta;
                    }

                    $all_data_return[] = $data_post;
                }
            }
        }

        return $all_data_return;
    }

}