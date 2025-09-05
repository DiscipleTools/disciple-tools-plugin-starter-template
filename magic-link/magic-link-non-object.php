<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

/**
 * Adds a non-object (neither post or user) magic link page.
 *
 * @example https://yoursite.com/starter_app/page/
 *
 * @use-case You could use a page like this as a registration page, a landing page for a campaign, a map page.
 * Basically you can create a publically accessible page that can display data from inside Disciple Tools to a public
 * audience. I.E public maps or statistics on the DT system.
 * @see https://zume.vision/maps for a public map link example
 */
class Disciple_Tools_Plugin_Vue_App_Magic_Non_Object_App extends DT_Magic_Url_Base
{
    public $magic = false;
    public $parts = false;
    public $page_title = 'Vue App';
    public $root = 'vue_app';
    public $type = 'page';
    public $type_name = 'Vue App';
    public static $token = 'vue_app_page';
    public $post_type = 'contacts'; // This can be supplied or not supplied. It does not influence the url verification.

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        parent::__construct();

        $url = dt_get_url_path();
        if ( ( $this->root . '/' . $this->type ) === $url ) {

            $this->magic = new DT_Magic_URL( $this->root );
            $this->parts = $this->magic->parse_url_parts();


            // register url and access
            add_filter( 'dt_override_header_meta', '__return_true', 100, 1 );

            // page content
            add_action( 'dt_blank_body', [ $this, 'body' ] ); // body for no post key

            add_filter( 'dt_magic_url_base_allowed_css', [ $this, 'dt_magic_url_base_allowed_css' ], 10, 1 );
            add_filter( 'dt_magic_url_base_allowed_js', [ $this, 'dt_magic_url_base_allowed_js' ], 10, 1 );
            add_action( 'wp_enqueue_scripts', [ $this, '_wp_enqueue_scripts' ], 100 );
        }

        if ( dt_is_rest() ) {
            add_action( 'rest_api_init', [ $this, 'add_endpoints' ] );
        }
    }

    public function dt_magic_url_base_allowed_js( $allowed_js ) {
        $allowed_js[] = 'vue';
        return $allowed_js;
    }

    public function dt_magic_url_base_allowed_css( $allowed_css ) {
        return $allowed_css;
    }

    public function header_style(){
        ?>
        <style>
            body {
                background-color:white;
            }
            #content {
                margin-left: auto;
                margin-right: auto;
                max-width: 1440px;
                font-size: 5rem;
                margin-top: 30%;
                text-align: center;
            }
        </style>
        <?php
    }

    public function body(){
        ?>
        <div id="content">
            <span class="first" v-if="loading"><i class="loading-spinner active"></i></span>
            <span class="first" v-if="!loading">{{ message1 }}</span>
            <span class="second">{{ message2 }}</span>
            <div id="error" v-if="error">{{ error }}</div>
        </div>
        <?php
    }

    public function footer_javascript(){
        ?>
        <script>
            const jsObject = <?php echo json_encode([
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'parts' => $this->parts,
                'translations' => [
                    'add' => __( 'Add Magic', 'disciple-tools-plugin-starter-template' ),
                ],
            ]) ?>;

            const { createApp } = Vue

            createApp({
                data() {
                    return {
                        loading: true,
                        message1: '',
                        message2: '',
                        error: '',
                    }
                },
                mounted() {
                    this.get_magic()
                },
                methods: {
                    get_magic() {
                        jQuery.ajax({
                            type: "POST",
                            data: JSON.stringify( { action: 'get', parts: jsObject.parts } ),
                            contentType: "application/json; charset=utf-8",
                            dataType: "json",
                            url: jsObject.root + jsObject.parts.root + '/v1/' + jsObject.parts.type,
                            beforeSend: (xhr) => {
                                xhr.setRequestHeader('X-WP-Nonce', jsObject.nonce )
                            }
                        })
                        .done((data) => {
                            this.message1 = 'I\'m a page with Vue.js';
                            this.loading = false;
                        })
                        .fail((e) => {
                            console.log(e);
                            this.error = e.responseText;
                        });

                        jQuery.ajax({
                            type: "POST",
                            data: JSON.stringify( { action: 'excited', parts: jsObject.parts } ),
                            contentType: "application/json; charset=utf-8",
                            dataType: "json",
                            url: jsObject.root + jsObject.parts.root + '/v1/' + jsObject.parts.type,
                            beforeSend: (xhr) => {
                                xhr.setRequestHeader('X-WP-Nonce', jsObject.nonce )
                            }
                        })
                        .done((data) => {
                            this.message2 = '!!';
                        })
                        .fail((e) => {
                            console.log(e);
                            this.error = this.error + ' ' + e.responseText;
                        });
                    }
                }
            }).mount('#content')

        </script>
        <?php
        return true;
    }

    public function _wp_enqueue_scripts(){
        wp_enqueue_script( 'vue', 'https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js', [], null, true );
    }

    /**
     * Register REST Endpoints
     * @link https://github.com/DiscipleTools/disciple-tools-theme/wiki/Site-to-Site-Link for outside of wordpress authentication
     */
    public function add_endpoints() {
        $namespace = $this->root . '/v1';
        register_rest_route(
            $namespace,
            '/'.$this->type,
            [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'endpoint' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );
    }

    public function endpoint( WP_REST_Request $request ) {
        $params = $request->get_params();

        if ( ! isset( $params['parts'], $params['action'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters', [ 'status' => 400 ] );
        }

        $params = dt_recursive_sanitize_array( $params );

        switch ( $params['action'] ) {
            case 'get':
                // do something
                return true;
            case 'excited':
                // do something else
            default:
                return true;
        }
    }
}
Disciple_Tools_Plugin_Vue_App_Magic_Non_Object_App::instance();
