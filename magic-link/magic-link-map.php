<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

/**
 * Loading the Mapbox Mapping system into a Magic Link is a little tricky, so this starter class helps put in place
 * the key js and css resources needed to do that.
 *
 * @see https://zume.vision/maps/
 * These Zume maps are driven via a magic link from a Disciple Tools system.
 */
class Disciple_Tools_Plugin_Starter_Template_Magic_Map_App extends DT_Magic_Url_Base
{
    public $magic = false;
    public $parts = false;
    public $page_title = 'Starter Map';
    public $root = 'starter_app';
    public $type = 'map';
    public $type_name = 'Starter App Map';
    public static $token = 'starter_app_map';
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
            add_action( 'template_redirect', [ $this, 'theme_redirect' ] );
            add_filter( 'dt_blank_access', function (){ return true;
            }, 100, 1 );
            add_filter( 'dt_allow_non_login_access', function (){ return true;
            }, 100, 1 );
            add_filter( 'dt_override_header_meta', function (){ return true;
            }, 100, 1 );

            // header content
            add_filter( 'dt_blank_title', [ $this, 'page_tab_title' ] ); // adds basic title to browser tab
            add_action( 'wp_print_scripts', [ $this, 'print_scripts' ], 1500 ); // authorizes scripts
            add_action( 'wp_print_styles', [ $this, 'print_styles' ], 1500 ); // authorizes styles


            // page content
            add_action( 'dt_blank_head', [ $this, '_header' ] );
            add_action( 'dt_blank_footer', [ $this, '_footer' ] );
            add_action( 'dt_blank_body', [ $this, 'body' ] ); // body for no post key

            add_filter( 'dt_magic_url_base_allowed_css', [ $this, 'dt_magic_url_base_allowed_css' ], 10, 1 );
            add_filter( 'dt_magic_url_base_allowed_js', [ $this, 'dt_magic_url_base_allowed_js' ], 10, 1 );
            add_action( 'wp_enqueue_scripts', [ $this, '_wp_enqueue_scripts' ], 100 );
        }

        if ( dt_is_rest() ) {
            add_action( 'rest_api_init', [ $this, 'add_endpoints' ] );
            add_filter( 'dt_allow_rest_access', [ $this, 'authorize_url' ], 10, 1 );
        }
    }

    public function dt_magic_url_base_allowed_js( $allowed_js ) {
        $allowed_js[] = 'jquery-touch-punch';
        $allowed_js[] = 'mapbox-gl';
        $allowed_js[] = 'jquery-cookie';
        $allowed_js[] = 'mapbox-cookie';
        $allowed_js[] = 'heatmap-js';
        return $allowed_js;
    }

    public function dt_magic_url_base_allowed_css( $allowed_css ) {
        $allowed_css[] = 'mapbox-gl-css';
        $allowed_css[] = 'introjs-css';
        $allowed_css[] = 'heatmap-css';
        $allowed_css[] = 'site-css';
        return $allowed_css;
    }

    public function header_javascript(){
        ?>
        <script>
            let jsObject = [<?php echo json_encode([
                'map_key' => DT_Mapbox_API::get_key(),
                'ipstack' => DT_Ipstack_API::get_key(),
                'mirror_url' => dt_get_location_grid_mirror( true ),
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'parts' => $this->parts,
                'translations' => [
                    'add' => __( 'Add Magic', 'disciple-tools-plugin-starter-template' ),
                ],
            ]) ?>][0]

            jQuery(document).ready(function(){
                window.load_map()
            })


            window.load_map = () => {
                let spinner = jQuery('.loading-spinner')

                /* set vertical size the form column*/
                jQuery('#custom-style').append(`
                  <style>
                      #map-wrapper {
                          height: ${window.innerHeight}px !important;
                      }
                      #map {
                          height: ${window.innerHeight}px !important;
                      }
                  </style>`)


                window.get_geojson().then(function(data){

                    mapboxgl.accessToken = jsObject.map_key;
                    var map = new mapboxgl.Map({
                        container: 'map',
                        style: 'mapbox://styles/mapbox/light-v10',
                        center: [0, 0],
                        minZoom: 0,
                        zoom: 0
                    });

                    map.dragRotate.disable();
                    map.touchZoomRotate.disableRotation();

                    map.on('load', function() {
                        map.addSource('layer-source', {
                            type: 'geojson',
                            data: data
                        });

                        map.addLayer({
                            id: 'circle-layer',
                            type: 'circle',
                            source: 'layer-source',
                            paint: {
                                'circle-color': '#00d9ff',
                                'circle-radius':12,
                                'circle-stroke-width': 1,
                                'circle-stroke-color': '#fff'
                            }
                        });

                       // @see https://docs.mapbox.com for all the capacity of mapbox mapping.

                        spinner.removeClass('active')

                        var bounds = new mapboxgl.LngLatBounds();
                        data.features.forEach(function(feature) {
                            bounds.extend(feature.geometry.coordinates);
                        });
                        map.fitBounds(bounds, { padding: {top: 20, bottom:20, left: 20, right: 20 } });

                    });
                })
            }

            window.get_geojson = () => {
                return jQuery.ajax({
                    type: "POST",
                    data: JSON.stringify({ action: 'geojson', parts: jsObject.parts }),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: jsObject.root + jsObject.parts.root + '/v1/' + jsObject.parts.type,
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', jsObject.nonce )
                    }
                })
                    .fail(function(e) {
                        console.log(e)
                        jQuery('#error').html(e)
                    })
            }

        </script>
        <?php
    }

    public function header_style() {
        ?>
        <style>
            body {
                background: white !important;
            }
            #initialize-screen {
                width: 100%;
                height: 2000px;
                z-index: 100;
                background-color: white;
                position: absolute;
            }
            #initialize-spinner-wrapper{
                position:relative;
                top:45%;
            }
            progress {
                top: 50%;
                margin: 0 auto;
                height:50px;
                width:300px;
            }
        </style>
        <?php
    }

    public function footer_javascript(){
    }

    public function body(){
        DT_Mapbox_API::geocoder_scripts();
        ?>
        <style id="custom-style"></style>
        <div id="map-wrapper">
            <div id='map'></div>
        </div>
        <?php
    }

    public static function _wp_enqueue_scripts(){
        DT_Mapbox_API::load_mapbox_header_scripts();
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
            case 'geojson':
                return $this->endpoint_geojson( $params['parts'] );
            default:
                return new WP_Error( __METHOD__, 'Missing valid action parameters', [ 'status' => 400 ] );
        }
    }

    public function endpoint_geojson( $parts ) {
        global $wpdb;

        $results = $wpdb->get_results(
        "SELECT * FROM $wpdb->dt_location_grid WHERE level = 0", ARRAY_A );

        if ( empty( $results ) ) {
            return $this->_empty_geojson();
        }

        $features = [];
        foreach ( $results as $result ) {
            $features[] = array(
                'type' => 'Feature',
                'properties' => array(
                    'grid_id' => $result['grid_id'],
                    'name' => $result['name'],
                    'value' => rand( 1, 10 ) // random value
                ),
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array(
                        (float) $result['longitude'],
                        (float) $result['latitude'],
                        1
                    ),
                ),
            );
        }

        $geojson = array(
            'type' => 'FeatureCollection',
            'features' => $features,
        );

        return $geojson;
    }

    private function _empty_geojson() {
        return array(
            'type' => 'FeatureCollection',
            'features' => array()
        );
    }



}
Disciple_Tools_Plugin_Starter_Template_Magic_Map_App::instance();
