<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

add_filter('dt_magic_link_template_types', function( $types ) {
    $types['contacts'][] = [
        'value' => 'starter-template',
        'text' => 'Starter Template',
    ];
    $types['default-options'][] = [
        'value' => 'starter-template',
        'text' => 'Starter Template',
    ];
    return $types;
});

add_action('dt_magic_link_template_load', function ( $template ) {
    if ( isset( $template['type'] ) && $template['type'] === 'starter-template' ) {
        new Disciple_Tools_Magic_Links_Template_Starter_Template( $template );
    }
} );

/**
 * Class Disciple_Tools_Magic_Links_Templates
 */
class Disciple_Tools_Magic_Links_Template_Starter_Template extends DT_Magic_Url_Base {

    protected $template_type = 'starter-template';
    public $page_title = 'Starter Template';
    public $page_description = 'Edit all connections to a given post';
    public $root = 'templates'; // @todo define the root of the url {yoursite}/root/type/key/action
    public $type = 'template_id'; // Placeholder to be replaced with actual template ids
    public $type_name = '';
    public $post_type = 'contacts'; // Main post type that the ML is linked to.
    public $record_post_type = 'groups'; // Child post type determined by the connection field selected
    private $post = null;
    private $items = [];
    private $meta_key = '';

    public $show_bulk_send = true;
    public $show_app_tile = true;

    private static $_instance = null;
    public $meta = []; // Allows for instance specific data.
    public $translatable = [
        'query',
        'user',
        'contact'
    ]; // Order of translatable flags to be checked. Translate on first hit..!

    private $template = null;
    private $link_obj = null;
    private $layout = null;

    public function __construct( $template = null ) {

        // only handle this template type
        if ( empty( $template ) || $template['type'] !== $this->template_type ) {
            return;
        }

        $this->template         = $template;
        $this->post_type        = $template['post_type'];
        $this->type             = array_map( 'sanitize_key', wp_unslash( explode( '_', $template['id'] ) ) )[1];
        $this->type_name        = $template['name'];
        $this->page_title       = $template['name'];
        $this->page_description = '';

        if ( !isset( $this->template['record_type'] ) ) {
            $this->template['record_type'] = $this->record_post_type;
        }

        /**
         * Specify metadata structure, specific to the processing of current
         * magic link class type.
         *
         * - meta:              Magic link plugin related data.
         *      - app_type:     Flag indicating type to be processed by magic link plugin.
         *      - class_type:   Flag indicating template class type.
         */

        $show_in_apps = false;

        if ( $template['post_type'] == 'contacts' ) {
            $show_in_apps = true;
        }

        $this->meta = [
            'app_type'   => 'magic_link',
            'class_type' => 'template',
            'show_in_home_apps' => $show_in_apps,
            'icon' => 'mdi mdi-account-network',
        ];

        /**
         * Once adjustments have been made, proceed with parent instantiation!
         */

        $this->meta_key = $this->root . '_' . $this->type;
        parent::__construct();
        add_action( 'rest_api_init', [ $this, 'add_endpoints' ] );

        /**
         * Test magic link parts are registered and have valid elements.
         */

        if ( ! $this->check_parts_match() ) {
            return;
        }

        /**
         * Attempt to load sooner, rather than later; corresponding post record details.
         */

        $this->post = DT_Posts::get_post( $this->post_type, $this->parts['post_id'], true, false );

        // @todo remove example and replace with DT_Posts::list_posts()
        $data = [];
        $data[] = [
            'ID' => '123',
            'name' => 'List item 1',
            'last_modified' => [
                'timestamp' => 1735678800,
            ],
        ];
        $data[] = [
            'ID' => '124',
            'name' => 'List item 2',
            'last_modified' => [
                'timestamp' => 1735678800,
            ],
        ];
        $this->items = [
            'posts' => $data
        ];

        /**
         * Attempt to load corresponding link object, if a valid incoming id has been detected.
         */

        $this->link_obj = Disciple_Tools_Bulk_Magic_Link_Sender_API::fetch_option_link_obj( $this->fetch_incoming_link_param( 'id' ) );

        // Revert back to dt translations
        $this->hard_switch_to_default_dt_text_domain();

        // Initialize layout front-end
        $this->layout = new Disciple_Tools_Magic_Links_Layout_List_Detail(
            $this->template,
            $this->post,
            $this->link_obj
        );

        /**
         * Load if valid url
         */

        add_action( 'dt_blank_body', [ $this, 'body' ] );
        add_filter( 'dt_magic_url_base_allowed_css', [ $this, 'dt_magic_url_base_allowed_css' ], 10, 1 );
        add_filter( 'dt_magic_url_base_allowed_js', [ $this, 'dt_magic_url_base_allowed_js' ], 10, 1 );
        add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ], 100 );
        add_filter( 'dt_can_update_permission', [ $this, 'can_update_permission_filter' ], 10, 3 );
    }

    // Ensure template fields remain editable
    public function can_update_permission_filter( $has_permission, $post_id, $post_type ) {
        return true;
    }

    public function wp_enqueue_scripts() {
        $this->layout->wp_enqueue_scripts();

        Disciple_Tools_Bulk_Magic_Link_Sender_API::enqueue_magic_link_utilities_script();
    }

    public function dt_magic_url_base_allowed_js( $allowed_js ) {
        $allowed_js[] = Disciple_Tools_Bulk_Magic_Link_Sender_API::get_magic_link_utilities_script_handle();

        return $this->layout->allowed_js( $allowed_js );
    }

    public function dt_magic_url_base_allowed_css( $allowed_css ) {
        return $this->layout->allowed_css( $allowed_css );
    }

    /**
     * Writes javascript to the footer
     *
     * @see DT_Magic_Url_Base()->footer_javascript() for default state
     */
    public function footer_javascript() {
        $this->layout->footer_javascript( $this->parts, $this->items );
    }

    public function body() {
        $this->layout->body();
    }

    /**
     * Register REST Endpoints
     * @link https://github.com/DiscipleTools/disciple-tools-theme/wiki/Site-to-Site-Link for outside of wordpress authentication
     */
    public function add_endpoints() {
        $namespace = $this->root . '/v1';
        register_rest_route(
            $namespace, '/' . $this->type . '/post', [
                [
                    'methods'             => 'POST',
                    'callback'            => [ $this, 'get_post' ],
                    'permission_callback' => function ( WP_REST_Request $request ) {
                        $magic = new DT_Magic_URL( $this->root );

                        return $magic->verify_rest_endpoint_permissions_on_post( $request );
                    },
                ],
            ]
        );
        register_rest_route(
            $namespace, '/' . $this->type . '/update', [
                [
                    'methods'             => 'POST',
                    'callback'            => [ $this, 'update_record' ],
                    'permission_callback' => function ( WP_REST_Request $request ) {
                        $magic = new DT_Magic_URL( $this->root );

                        $params = $request->get_params();

                        $permissions = $this->check_permissions( $params['parts']['post_id'], $params['post_id'] );
                        if ( !$permissions ) {
                            return false;
                        }

                        return $magic->verify_rest_endpoint_permissions_on_post( $request );
                    },
                ],
            ]
        );
        register_rest_route(
            $namespace, '/' . $this->type . '/comment', [
                [
                    'methods'             => 'POST',
                    'callback'            => [ $this, 'new_comment' ],
                    'permission_callback' => function ( WP_REST_Request $request ) {
                        $magic = new DT_Magic_URL( $this->root );

                        $params = $request->get_params();

                        $permissions = $this->check_permissions( $params['parts']['post_id'], $params['post_id'] );
                        if ( !$permissions ) {
                            return false;
                        }

                        return $magic->verify_rest_endpoint_permissions_on_post( $request );
                    },
                ],
            ]
        );
        register_rest_route(
            $namespace, '/' . $this->type . '/sort_post', [
                [
                    'methods'             => 'POST',
                    'callback'            => [ $this, 'sorted_list_posts' ],
                    'permission_callback' => function ( WP_REST_Request $request ) {
                        $magic = new DT_Magic_URL( $this->root );

                        $params = $request->get_params();

                        $permissions = $this->check_permissions( $params['parts']['post_id'], $params['post_id'] );
                        if ( !$permissions ) {
                            return false;
                        }

                        return $magic->verify_rest_endpoint_permissions_on_post( $request );
                    },
                ],
            ]
        );
    }

    public function check_permissions( $post_id, $connection_id ) {

        // if connection is actually the main post id, we're good
        if ( strval( $post_id ) === strval( $connection_id ) ) {
            return true;
        }

        //set query fields to search for our post_id
        $query_fields = [];
        //todo: based on list of posts that are accessible, verify current user
        // has permission to edit the given connection_id

        //get related records that have our query fields
        $this->items = DT_Posts::list_posts( $this->record_post_type, [
            'limit' => 1000,
            'fields' => [
                $query_fields
            ]
        ], false );

        //return true if the post_id in the request is in the list
        foreach ( $this->items['posts'] as $item ) {
            if ( strval( $connection_id ) === strval( $item['ID'] ) ) {
                return true;
            }
        }
        return false;
    }

    public function get_post( WP_REST_Request $request ){
        $params = $request->get_params();
        if ( !isset( $params['post_type'], $params['post_id'], $params['parts'], $params['action'], $params['comment_count'] ) ){
            return new WP_Error( __METHOD__, 'Missing parameters', [ 'status' => 400 ] );
        }

        // Sanitize and fetch user/post id
        $params = dt_recursive_sanitize_array( $params );

        // Update logged-in user state if required accordingly, based on their sys_type
        if ( !is_user_logged_in() ) {
            DT_ML_Helper::update_user_logged_in_state();
        }

        return [
            'success' => true,
            'post' => $this->items['posts'][0],
            'comments' => [],
        ];
    }

    public function update_record( WP_REST_Request $request ){
        $params = $request->get_params();
        if ( !isset( $params['post_id'], $params['post_type'], $params['parts'], $params['action'], $params['fields'] ) ){
            return new WP_Error( __METHOD__, 'Missing core parameters', [ 'status' => 400 ] );
        }

        // Sanitize and fetch user id
        $params = dt_recursive_sanitize_array( $params );

        // Update logged-in user state, if required
        if ( !is_user_logged_in() ){
            DT_ML_Helper::update_user_logged_in_state();
        }

        $updates = [];

        foreach ( $params['fields']['dt'] ?? [] as $field ) {
            //todo: handle all input fields
        }

        // Update specified post record
        if ( empty( $params['post_id'] ) ) {
            // if ID is empty ("0", 0, or generally falsy), create a new post
            $updates['type'] = 'access';

            $updated_post = DT_Posts::create_post( $params['post_type'], $updates, false, false );
        } else {
            // dt_write_log( json_encode( $updates ) );
            $updated_post = DT_Posts::update_post( $params['post_type'], $params['post_id'], $updates, false, false );
        }

        if ( empty( $updated_post ) || is_wp_error( $updated_post ) ) {
            dt_write_log( $updated_post );
            return [
                'success' => false,
                'message' => 'Unable to update/create contact record details!'
            ];
        }

        // Next, any identified custom fields, are to be added as comments
        foreach ( $params['fields']['custom'] ?? [] as $field ) {
            $field = dt_recursive_sanitize_array( $field );
            if ( ! empty( $field['value'] ) ) {
                $updated_comment = DT_Posts::add_post_comment( $updated_post['post_type'], $updated_post['ID'], $field['value'], 'comment', [], false );
                if ( empty( $updated_comment ) || is_wp_error( $updated_comment ) ) {
                    return [
                        'success' => false,
                        'message' => 'Unable to add comment to record details!'
                    ];
                }
            }
        }

        // Next, dispatch submission notification, accordingly; always send by default.
        if ( isset( $params['send_submission_notifications'] ) && $params['send_submission_notifications'] && isset( $updated_post['assigned_to'], $updated_post['assigned_to']['id'], $updated_post['assigned_to']['display'] ) ) {
            $default_comment = sprintf( __( '%s Updates Submitted', 'disciple_tools' ), $params['template_name'] );
            $submission_comment = '@[' . $updated_post['assigned_to']['display'] . '](' . $updated_post['assigned_to']['id'] . ') ' . $default_comment;
            DT_Posts::add_post_comment( $updated_post['post_type'], $updated_post['ID'], $submission_comment, 'comment', [], false );
        }

        // Finally, return successful response
        return [
            'success' => true,
            'message' => '',
            'post' => $updated_post,
        ];
    }

    public function new_comment( WP_REST_Request $request ){
        $params = $request->get_params();
        if ( !isset( $params['post_type'], $params['post_id'], $params['parts'], $params['action'] ) ){
            return new WP_Error( __METHOD__, 'Missing parameters', [ 'status' => 400 ] );
        }

        // Sanitize and fetch user id
        $params = dt_recursive_sanitize_array( $params );

        // Update logged-in user state, if required
        if ( !is_user_logged_in() ){
            DT_ML_Helper::update_user_logged_in_state();
        }

        $post = DT_Posts::get_post( $params['post_type'], $params['post_id'], false, false );
        //$params['comment']
        DT_Posts::add_post_comment( $post['post_type'], $post['ID'], $params['comment'], 'comment', [], false );

        return [
            'success' => true,
            'message' => '',
            'post' => $post,
        ];
    }

    public function sorted_list_posts( WP_REST_Request $request ){
        $params = $request->get_params();
        if ( !isset( $params['post_type'], $params['post_id'], $params['parts'], $params['action'] ) ){
            return new WP_Error( __METHOD__, 'Missing parameters', [ 'status' => 400 ] );
        }

        // Sanitize and fetch user id
        $params = dt_recursive_sanitize_array( $params );

        // Update logged-in user state, if required
        if ( !is_user_logged_in() ){
            DT_ML_Helper::update_user_logged_in_state();
        }

        //todo: get sorted items using DT_Posts::list_posts()

        return $this->items;
    }
}
