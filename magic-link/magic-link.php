<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

Disciple_Tools_Plugin_Starter_Magic_Link::instance();

class Disciple_Tools_Plugin_Starter_Magic_Link extends DT_Magic_Url_Base {


    public $magic = false;
    public $parts = false;
    public $page_title = 'Magic';
    public $root = "magic_app"; // @todo define the root of the url {yoursite}/root/type/key/action
    public $type = 'magic_type'; // @todo define the type
    public $post_type = 'starter_post_type'; // @todo set the post type this magic link connects with.
    private $meta_key = '';

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        $this->meta_key = $this->root . '_' . $this->type . '_magic_key';
        parent::__construct();

        /**
         * post type and module section
         */
        add_action( 'dt_details_additional_section', [ $this, 'dt_details_additional_section' ], 30, 2 );
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 10, 2 );
        add_action( 'rest_api_init', [ $this, 'add_endpoints' ] );

        /**
         * Magic Url Section
         */
        //don't load the magic link page for other urls
        if ( !$this->check_parts_match() ){
            return;
        }

        // fail if not valid url
        $url = dt_get_url_path();
        if ( strpos( $url, $this->root . '/' . $this->type ) === false ) {
            return;
        }

        // load if valid url
        add_action( 'dt_blank_head', [ $this, '_header' ] );
        add_action( 'dt_blank_footer', [ $this, '_footer' ] );
        add_action( 'dt_blank_body', [ $this, 'body' ] ); // body for no post key

    }

    public function dt_details_additional_tiles( $tiles, $post_type = "" ) {
        if ( $post_type === $this->post_type ){
            $tiles["dt_starters_magic_url"] = [
                "label" => __( "Magic Url", 'disciple_tools' ),
                "description" => "The Magic URL sets up a page accessible without authentication, only the link is needed. Useful for small applications liked to this record, like quick surveys or updates."
            ];
        }
        return $tiles;
    }
    public function dt_details_additional_section( $section, $post_type ) {
        // test if campaigns post type and campaigns_app_module enabled
        if ( $post_type === $this->post_type ) {
            if ( 'dt_starters_magic_url' === $section ) {
                $record = DT_Posts::get_post( $post_type, get_the_ID() );
                if ( isset( $record[$this->meta_key] )) {
                    $key = $record[$this->meta_key];
                } else {
                    $key = dt_create_unique_key();
                    update_post_meta( get_the_ID(), $this->meta_key, $key );
                }
                $link = DT_Magic_URL::get_link_url( $this->root, $this->type, $key )
                ?>
                <p>See help <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/> for description.</p>
                <a class="button" href="<?php echo esc_html( $link ); ?>" target="_blank">Open magic link</a>
                <?php
            }
        }
    }


    public function _header(){
        wp_head();
        $this->header_style();
        $this->header_javascript();
    }
    public function _footer(){
        wp_footer();
    }

    public function header_style(){
        ?>
        <style>
            body {
                background-color: white;
                padding: 1em;
            }
        </style>
        <?php
    }
    public function header_javascript(){
        ?>
        <script>
            let jsObject = [<?php echo json_encode([
                'map_key' => DT_Mapbox_API::get_key(),
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'parts' => $this->parts,
                'translations' => [
                    'add' => __( 'Add Magic', 'disciple_tools' ),
                ],
            ]) ?>][0]

            jQuery(document).ready(function(){
                clearInterval(window.fiveMinuteTimer)
            })

            window.get_magic = () => {
                jQuery.ajax({
                    type: "POST",
                    data: JSON.stringify({ action: 'get', parts: jsObject.parts }),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: jsObject.root + jsObject.parts.root + '/v1/' + jsObject.parts.type,
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', jsObject.nonce )
                    }
                })
                    .done(function(data){
                        window.load_magic( data )
                    })
                    .fail(function(e) {
                        console.log(e)
                        jQuery('#error').html(e)
                    })
            }

            window.load_magic = ( data ) => {
                let content = jQuery('#content')
                let spinner = jQuery('.loading-spinner')

                content.empty()
                jQuery.each(data, function(i,v){
                    content.prepend(`
                         <div class="cell">
                             ${v.name}
                         </div>
                     `)
                })

                spinner.removeClass('active')

            }
        </script>
        <?php
        return true;
    }



    public function body(){
        ?>
        <div id="custom-style"></div>
        <div id="wrapper">
            <div class="grid-x">
                <div class="cell center">
                    <h2 id="title">Title</h2>
                </div>
            </div>
            <hr>
            <div class="grid-x" id="content"><span class="loading-spinner active"></span><!-- javascript container --></div>
        </div>
        <script>
            jQuery(document).ready(function($){
                window.get_magic()
            })
        </script>
        <?php
    }

    /**
     * Register REST Endpoints
     * @link https://github.com/DiscipleTools/disciple-tools-theme/wiki/Site-to-Site-Link for outside of wordpress authentication
     */
    public function add_endpoints() {
        $namespace = $this->root . '/v1';
        register_rest_route(
            $namespace, '/'.$this->type, [
                [
                    'methods'  => "POST",
                    'callback' => [ $this, 'endpoint' ],
                    'permission_callback' => function( WP_REST_Request $request ){
                        $magic = new DT_Magic_URL( $this->root );
                        return $magic->verify_rest_endpoint_permissions_on_post( $request );
                    },
                ],
            ]
        );
    }

    public function endpoint( WP_REST_Request $request ) {
        $params = $request->get_params();

        if ( ! isset( $params['parts'], $params['action'] ) ) {
            return new WP_Error( __METHOD__, "Missing parameters", [ 'status' => 400 ] );
        }

        $params = dt_recursive_sanitize_array( $params );
        $action = sanitize_text_field( wp_unslash( $params['action'] ) );
        $post_id = $params["parts"]["post_id"];

        switch ( $action ) {
            case 'get':
                return $this->endpoint_get();

            // add other cases

            default:
                return new WP_Error( __METHOD__, "Missing valid action", [ 'status' => 400 ] );
        }
    }

    public function endpoint_get() {
        $data = [];

        $data[] = [ 'name' => 'List item' ]; // @todo remove example
        $data[] = [ 'name' => 'List item' ]; // @todo remove example

        return $data;
    }
}

