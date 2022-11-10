<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

/**
 * Adds a magic link home page to the Disciple Tools system.
 * @usage This could be used to add a microsite in front of the Disciple Tools system. Or used to hide the
 * Disciple Tools login behind a false store front. Or used to extend an entire application to the public out
 * in front of the Disciple Tools system.
 *
 * @example https://yoursite.com/(empty)
 *
 * @see https://disciple.tools/plugins/porch/
 * @see https://disciple.tools/plugins/disciple-tools-porch-template/
 */
class Disciple_Tools_Plugin_Starter_Template_Magic_Home_App extends DT_Magic_Url_Base
{
    public $magic = false;
    public $parts = false;
    public $page_title = 'Title';
    public $root = 'starter_app';
    public $type = 'home';
    public static $token = 'starter_app_home';

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
        if ( empty( $url ) && ! dt_is_rest() ) { // this filter is looking for the root site url without params.

            // register url and access
            add_action( 'template_redirect', [ $this, 'theme_redirect' ] );
            add_filter( 'dt_blank_access', function (){ return true;
            }, 100, 1 ); // allows non-logged in visit
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
            add_action( 'dt_blank_body', [ $this, 'body' ] );

            add_filter( 'dt_magic_url_base_allowed_css', [ $this, 'dt_magic_url_base_allowed_css' ], 10, 1 );
            add_filter( 'dt_magic_url_base_allowed_js', [ $this, 'dt_magic_url_base_allowed_js' ], 10, 1 );

        }
    }

    public function dt_magic_url_base_allowed_js( $allowed_js ) {
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
        <div id="content" style="">Insert Your Home Page Here</div>
        <?php
    }
}
Disciple_Tools_Plugin_Starter_Template_Magic_Home_App::instance();
