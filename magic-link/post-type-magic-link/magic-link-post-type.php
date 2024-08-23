<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


/**
 * Class Disciple_Tools_Plugin_Starter_Template_Magic_Link
 */
class Disciple_Tools_Plugin_Starter_Template_Magic_Link extends DT_Magic_Url_Base {

    public $magic = false;
    public $parts = false;
    public $page_title = 'Starter - Magic Links - Post Type';
    public $page_description = 'Post Type - Magic Links.';
    public $root = 'starter_magic_app'; // @todo define the root of the url {yoursite}/root/type/key/action
    public $type = 'starter_magic_type'; // @todo define the type
    public $post_type = 'starter_post_type'; // @todo set the post type this magic link connects with.
    private $meta_key = '';
    public $show_bulk_send = false;
    public $show_app_tile = true; // show this magic link in the Apps tile on the post record

    private static $_instance = null;
    public $meta = []; // Allows for instance specific data.

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {

        /**
         * Specify metadata structure, specific to the processing of current
         * magic link type.
         *
         * - meta:              Magic link plugin related data.
         *      - app_type:     Flag indicating type to be processed by magic link plugin.
         *      - post_type     Magic link type post type.
         *      - contacts_only:    Boolean flag indicating how magic link type user assignments are to be handled within magic link plugin.
         *                          If True, lookup field to be provided within plugin for contacts only searching.
         *                          If false, Dropdown option to be provided for user, team or group selection.
         *      - fields:       List of fields to be displayed within magic link frontend form.
         */
        $this->meta = [
            'app_type'      => 'magic_link',
            'post_type'     => $this->post_type,
            'contacts_only' => true,
            'fields'        => [
                [
                    'id'    => 'name',
                    'label' => 'Name'
                ]
            ]
        ];

        $this->meta_key = $this->root . '_' . $this->type . '_magic_key';
        parent::__construct();

        /**
         * post type and module section
         */
//        add_action( 'dt_details_additional_section', [ $this, 'dt_details_additional_section' ], 30, 2 );
//        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 10, 2 );
        add_action( 'rest_api_init', [ $this, 'add_endpoints' ] );


        /**
         * tests if other URL
         */
        $url = dt_get_url_path();
        if ( strpos( $url, $this->root . '/' . $this->type ) === false ) {
            return;
        }
        /**
         * tests magic link parts are registered and have valid elements
         */
        if ( !$this->check_parts_match() ){
            return;
        }

        // load if valid url
        add_action( 'dt_blank_body', [ $this, 'body' ] ); // body for no post key
        add_filter( 'dt_magic_url_base_allowed_css', [ $this, 'dt_magic_url_base_allowed_css' ], 10, 1 );
        add_filter( 'dt_magic_url_base_allowed_js', [ $this, 'dt_magic_url_base_allowed_js' ], 10, 1 );
        add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ], 100 );
    }

    public function wp_enqueue_scripts(){
        wp_enqueue_script( 'magic_link_scripts', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'magic-link.js', [
            'jquery',
            'lodash',
        ], filemtime( plugin_dir_path( __FILE__ ) . 'magic-link.js' ), true );
        wp_localize_script(
            'magic_link_scripts', 'jsObject', [
                'map_key' => DT_Mapbox_API::get_key(),
                'rest_base' => esc_url( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'parts' => $this->parts,
                'translations' => [
                    'add' => __( 'Add Magic', 'disciple-tools-plugin-starter-template' ),
                ],
                'rest_namespace' => $this->root . '/v1/' . $this->type,
            ]
        );
        wp_enqueue_style( 'magic_link_css', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'magic-link.css', [], filemtime( plugin_dir_path( __FILE__ ) . 'magic-link.css' ) );
    }

    public function dt_magic_url_base_allowed_js( $allowed_js ) {
        // @todo add or remove js files with this filter
        $allowed_js[] = 'magic_link_scripts';
        return $allowed_js;
    }

    public function dt_magic_url_base_allowed_css( $allowed_css ) {
        // @todo add or remove js files with this filter
        $allowed_css[] = 'magic_link_css';
        return $allowed_css;
    }

    /**
     * Post Type Tile Examples
     */
    public function dt_details_additional_tiles( $tiles, $post_type = '' ) {
        if ( $post_type === $this->post_type ){
            $tiles['dt_starters_magic_url'] = [
                'label' => __( 'Magic Url', 'disciple-tools-plugin-starter-template' ),
                'description' => 'The Magic URL sets up a page accessible without authentication, only the link is needed. Useful for small applications liked to this record, like quick surveys or updates.'
            ];
        }
        return $tiles;
    }
    public function dt_details_additional_section( $section, $post_type ) {
        // test if campaigns post type and campaigns_app_module enabled
        if ( $post_type === $this->post_type ) {
            if ( 'dt_starters_magic_url' === $section ) {
                $link = DT_Magic_URL::get_link_url_for_post( $post_type, get_the_ID(), $this->root, $this->type )
                ?>
                <p>See help <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/> for description.</p>
                <a class="button" href="<?php echo esc_html( $link ); ?>" target="_blank">Open magic link</a>
                <?php
            }
        }
    }

    public function body(){
        ?>
        <div id="magic-link-wrapper">
            <div class="grid-x">
                <div class="cell center">
                    <h2 id="title">Title</h2>
                </div>
            </div>
            <hr>
            <div id="content">
                <h3>List From API</h3>
                <div class="grid-x" id="api-content">
                    <!-- javascript container -->
                    <span class="loading-spinner active"></span>
                </div>

                <br>
                <br>
                <br>
                <h3>Form</h3>
                <div class="grid-x" id="form-content">
                    <?php
                    $post_id = $this->parts['post_id'];

                    // get the post. Make sure to only display the needed pieces on the front end as this link does net require auth
                    $post = DT_Posts::get_post( $this->post_type, $post_id, true, false );
                    if ( is_wp_error( $post ) ){
                        return;
                    }
                    $fields = DT_Posts::get_post_field_settings( $this->post_type );
                    render_field_for_display( 'start_date', $fields, $post );
                    ?>

                    <label style="width: 100%">
                        <strong>Comment</strong>
                        <textarea name="comment" id="comment-input"></textarea>
                    </label>

                    <button type="button" class="button loader" id="submit-form">Submit Update</button>
                </div>
            </div>

        </div>
        <?php
    }

    /**
     * Register REST Endpoints
     * @link https://github.com/DiscipleTools/disciple-tools-theme/wiki/Site-to-Site-Link for outside of wordpress authentication
     */
    public function add_endpoints() {
        $namespace = $this->root . '/v1';
        register_rest_route(
            $namespace, '/' . $this->type, [
                [
                    'methods'  => 'GET',
                    'callback' => [ $this, 'endpoint_get' ],
                    'permission_callback' => function( WP_REST_Request $request ){
                        $magic = new DT_Magic_URL( $this->root );

                        return $magic->verify_rest_endpoint_permissions_on_post( $request );
                    },
                ],
            ]
        );
        register_rest_route(
            $namespace, '/' . $this->type, [
                [
                    'methods'  => 'POST',
                    'callback' => [ $this, 'update_record' ],
                    'permission_callback' => function( WP_REST_Request $request ){
                        $magic = new DT_Magic_URL( $this->root );

                        return $magic->verify_rest_endpoint_permissions_on_post( $request );
                    },
                ],
            ]
        );
    }

    public function update_record( WP_REST_Request $request ) {
        $params = $request->get_params();
        $params = dt_recursive_sanitize_array( $params );

        $post_id = $params['parts']['post_id']; //has been verified in verify_rest_endpoint_permissions_on_post()

        $args = [];
        if ( !is_user_logged_in() ){
            $global_name = apply_filters( 'dt_magic_link_global_name', __( 'Magic Link', 'disciple_tools' ) );
            $args['comment_author'] = sprintf( __( '%s Submission', 'disciple_tools' ), $global_name );
            wp_set_current_user( 0 );
            $current_user = wp_get_current_user();
            $current_user->add_cap( 'magic_link' );
            $current_user->display_name = sprintf( __( '%s Submission', 'disciple_tools' ), $global_name );
        }

        if ( isset( $params['update']['comment'] ) && !empty( $params['update']['comment'] ) ){
            $update = DT_Posts::add_post_comment( $this->post_type, $post_id, $params['update']['comment'], 'comment', $args, false );
            if ( is_wp_error( $update ) ){
                return $update;
            }
        }

        if ( isset( $params['update']['start_date'] ) && !empty( $params['update']['start_date'] ) ){
            $update = DT_Posts::update_post( $this->post_type, $post_id, [ 'start_date' => $params['update']['start_date'] ], false, false );
            if ( is_wp_error( $update ) ){
                return $update;
            }
        }

        return true;
    }

    public function endpoint_get( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( ! isset( $params['parts'], $params['action'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters', [ 'status' => 400 ] );
        }

        $data = [];

        $data[] = [ 'name' => 'List item' ]; // @todo remove example
        $data[] = [ 'name' => 'List item' ]; // @todo remove example

        return $data;
    }
}
Disciple_Tools_Plugin_Starter_Template_Magic_Link::instance();
