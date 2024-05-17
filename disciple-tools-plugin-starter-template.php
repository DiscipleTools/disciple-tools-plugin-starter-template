<?php
/**
 * Plugin Name: Disciple.Tools - Plugin Starter Template
 * Plugin URI: https://github.com/DiscipleTools/disciple-tools-plugin-starter-template
 * Description: Disciple.Tools - Plugin Starter Template is intended to help developers and integrator jumpstart their extension of the Disciple.Tools system.
 * Text Domain: disciple-tools-plugin-starter-template
 * Domain Path: /languages
 * Version:  0.1
 * Author URI: https://github.com/DiscipleTools
 * GitHub Plugin URI: https://github.com/DiscipleTools/disciple-tools-plugin-starter-template
 * Requires at least: 4.7.0
 * (Requires 4.7+ because of the integration of the REST API at 4.7 and the security requirements of this milestone version.)
 * Tested up to: 5.6
 *
 * @package Disciple_Tools
 * @link    https://github.com/DiscipleTools
 * @license GPL-2.0 or later
 *          https://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Refactoring (renaming) this plugin as your own:
 * 1. @todo Rename the `disciple-tools-plugin-starter-template.php file.
 * 2. @todo Refactor all occurrences of the name Disciple_Tools_Plugin_Starter_Template, disciple_tools_plugin_starter_template, disciple-tools-plugin-starter-template, starter_post_type, and "Plugin Starter Template"
 * 3. @todo Update the README.md and LICENSE
 * 4. @todo Update the default.pot file if you intend to make your plugin multilingual. Use a tool like POEdit
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

add_action( 'disciple_tools_load_plugins', function (){
    Disciple_Tools_Plugin_Starter_Template::instance();
} );
/**
 * Singleton class for setting up the plugin.
 *
 * @since  0.1
 * @access public
 */
class Disciple_Tools_Plugin_Starter_Template {
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private static $required_theme_version = '1.63.0';
    public static $name = 'Disciple.Tools - Plugin Starter Template';
    public static $key = 'disciple-tools-plugin-starter-template';

    private function __construct(){
        //check theme version number
        $dt_theme_version = disciple_tools()->version;
        if ( version_compare( $dt_theme_version, self::$required_theme_version, '<' ) ){
            add_action( 'admin_notices', [ $this, 'admin_notice' ] );
            add_action( 'wp_ajax_dismissed_notice_handler', 'dt_hook_ajax_notice_handler' );

            return;
        }
        add_filter( 'dt_plugins', [ $this, 'register_plugin' ] );
        $this->load_plugin();
    }

    /**
     * Register the plugin with Disciple.Tools
     */
    public function register_plugin(){
        $plugin_data = get_file_data( __FILE__, [ 'Version' => 'Version', 'Plugin Name' => 'Plugin Name' ], false );
        $plugins['disciple-tools-plugin-starter-template'] = [
            'plugin_url' => trailingslashit( plugin_dir_url( __FILE__ ) ),
            'version' => $plugin_data['Version'] ?? null,
            'name' => $plugin_data['Plugin Name'] ?? null,
        ];
        return $plugins;
    }

    /**
     * Loads the plugin.
     */
    public function load_plugin(){
        $is_rest = dt_is_rest();
        /**
         * @todo Decide if you want to use the REST API example
         * To remove: delete this following line and remove the folder named /rest-api
         */
        require_once( 'rest-api/rest-api.php' ); // adds starter rest api class

        /**
         * @todo Decide if you want to create a new post type
         * To remove: delete the line below and remove the folder named /post-type
         */
        require_once( 'post-type/loader.php' ); // add starter post type extension to Disciple.Tools system

        /**
         * @todo Decide if you want to create a custom site-to-site link
         * To remove: delete the line below and remove the folder named /site-link
         */
        require_once( 'site-link/custom-site-to-site-links.php' ); // add site to site link class and capabilities

        /**
         * @todo Decide if you want to add new charts to the metrics section
         * To remove: delete the line below and remove the folder named /charts
         */
        if ( strpos( dt_get_url_path(), 'metrics' ) !== false || ( $is_rest && strpos( dt_get_url_path(), 'disciple-tools-plugin-starter-template-metrics' ) !== false ) ){
            require_once( 'charts/charts-loader.php' );  // add custom charts to the metrics area
        }

        /**
         * @todo Decide if you want to add a custom tile or settings page tile
         * To remove: delete the lines below and remove the folder named /tile
         */
        require_once( 'tile/custom-tile.php' ); // add custom tile
        if ( 'settings' === dt_get_url_path() && ! $is_rest ) {
            require_once( 'tile/profile-settings-tile.php' ); // add custom settings page tile
        }

        /**
         * @todo Decide if you want to create a magic link
         * To remove: delete the line below and remove the folder named /magic-link
         */
        require_once( 'magic-link/post-type-magic-link/magic-link-post-type.php' );
        require_once( 'magic-link/magic-link-user-app.php' );
        require_once( 'magic-link/magic-link-login-user-app.php' );
        require_once( 'magic-link/magic-link-non-object.php' );
        require_once( 'magic-link/magic-link-map.php' );
//        require_once( 'magic-link/magic-link-home.php' );

        /**
         * @todo Decide if you want to add a custom admin page in the admin area
         * To remove: delete the 3 lines below and remove the folder named /admin
         */
        if ( is_admin() ) {
            require_once( 'admin/admin-menu-and-tabs.php' ); // adds starter admin page and section for plugin
        }

        /**
         * @todo Decide if you want to support localization of your plugin
         * To remove: delete the line below and remove the folder named /languages
         */
        $this->i18n();

        /**
         * @todo Decide if you want to customize links for your plugin in the plugin admin area
         * To remove: delete the lines below and remove the function named "plugin_description_links"
         */
        if ( is_admin() ) { // adds links to the plugin description area in the plugin admin list.
            add_filter( 'plugin_row_meta', [ $this, 'plugin_description_links' ], 10, 4 );
        }

        /**
         * @todo Decide if you want to create default workflows
         * To remove: delete the line below and remove the folder named /workflows
         */
        require_once( 'workflows/workflows.php' );

    }

    /**
     * Filters the array of row meta for each/specific plugin in the Plugins list table.
     * Appends additional links below each/specific plugin on the plugins page.
     */
    public function plugin_description_links( $links_array, $plugin_file_name, $plugin_data, $status ) {
        if ( strpos( $plugin_file_name, basename( __FILE__ ) ) ) {
            // You can still use `array_unshift()` to add links at the beginning.

            $links_array[] = '<a href="https://disciple.tools">Disciple.Tools Community</a>'; // @todo replace with your links.
            // @todo add other links here
        }

        return $links_array;
    }

    /**
     * Method that runs only when the plugin is activated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function activation() {
        // add elements here that need to fire on activation
    }

    /**
     * Method that runs only when the plugin is deactivated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function deactivation() {
        // add functions here that need to happen on deactivation
        delete_option( 'dismissed-disciple-tools-plugin-starter-template' );
    }

    /**
     * Loads the translation files.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function i18n() {
        $domain = 'disciple-tools-plugin-starter-template';
        load_plugin_textdomain( $domain, false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ). 'languages' );
    }

    /**
     * Magic method to output a string if trying to use the object as a string.
     *
     * @since  0.1
     * @access public
     * @return string
     */
    public function __toString() {
        return 'disciple-tools-plugin-starter-template';
    }

    /**
     * Magic method to keep the object from being cloned.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, 'Whoah, partner!', '0.1' );
    }

    /**
     * Magic method to keep the object from being unserialized.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, 'Whoah, partner!', '0.1' );
    }

    /**
     * Magic method to prevent a fatal error when calling a method that doesn't exist.
     *
     * @param string $method
     * @param array $args
     * @return null
     * @since  0.1
     * @access public
     */
    public function __call( $method = '', $args = array() ) {
        _doing_it_wrong( 'disciple_tools_plugin_starter_template::' . esc_html( $method ), 'Method does not exist.', '0.1' );
        unset( $method, $args );
        return null;
    }

    public function admin_notice(){
        $message = self::$name . ' is not compatible with the current version of Disciple.Tools ('. disciple_tools()->version . '). Please update Disciple.Tools to version ' . self::$required_theme_version . ' or higher.';

        if ( !get_option( 'dismissed-' . self::$key, false ) ) { ?>
            <div class="notice notice-error notice-<?php echo esc_html( self::$key ); ?> is-dismissible dt-admin-notice" data-notice="<?php echo esc_html( self::$key ); ?>">
                <p><?php echo esc_html( $message );?></p>
            </div>
            <script>
                jQuery(function($) {
                    $( document ).on( 'click', '.notice-disciple-tools-plugin-starter-template .notice-dismiss', function () {
                        const key = $(this).parent().data('notice');
                            $.ajax( ajaxurl, {
                            type: 'POST',
                            data: {
                                action: 'dismissed_notice_handler',
                                type: key,
                                security: '<?php echo esc_html( wp_create_nonce( 'wp_rest_dismiss' ) ) ?>'
                            },
                        })
                    });
                });
            </script>
        <?php }
    }
}

// Register activation hook.
register_activation_hook( __FILE__, [ 'Disciple_Tools_Plugin_Starter_Template', 'activation' ] );
register_deactivation_hook( __FILE__, [ 'Disciple_Tools_Plugin_Starter_Template', 'deactivation' ] );


/**
 * AJAX handler to store the state of dismissible notices.
 */
if ( !function_exists( 'dt_hook_ajax_notice_handler' ) ){
    function dt_hook_ajax_notice_handler(){
        check_ajax_referer( 'wp_rest_dismiss', 'security' );
        if ( isset( $_POST['type'] ) ){
            $type = sanitize_text_field( wp_unslash( $_POST['type'] ) );
            update_option( 'dismissed-' . $type, true );
        }
    }
}

/**
 * Plugin Releases and updates
 * @todo Uncomment and change the url if you want to support remote plugin updating with new versions of your plugin
 * To remove: delete the section of code below and delete the file called version-control.json in the plugin root
 *
 * This section runs the remote plugin updating service, so you can issue distributed updates to your plugin
 *
 * @note See the instructions for version updating to understand the steps involved.
 * @link https://github.com/DiscipleTools/disciple-tools-plugin-starter-template/wiki/Configuring-Remote-Updating-System
 *
 * @todo Enable this section with your own hosted file
 * @todo An example of this file can be found in (version-control.json)
 * @todo Github is a good option for delivering static json.
 */
/**
 * Check for plugin updates even when the active theme is not Disciple.Tools
 *
 * Below is the publicly hosted .json file that carries the version information. This file can be hosted
 * anywhere as long as it is publicly accessible. You can download the version file listed below and use it as
 * a template.
 * Also, see the instructions for version updating to understand the steps involved.
 * @see https://github.com/DiscipleTools/disciple-tools-version-control/wiki/How-to-Update-the-Starter-Plugin
 */
//add_action( 'plugins_loaded', function (){
//    if ( is_admin() && !( is_multisite() && class_exists( "DT_Multisite" ) ) || wp_doing_cron() ){
//        // Check for plugin updates
//        if ( ! class_exists( 'Puc_v4_Factory' ) ) {
//            if ( file_exists( get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' )){
//                require( get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' );
//            }
//        }
//        if ( class_exists( 'Puc_v4_Factory' ) ){
//            Puc_v4_Factory::buildUpdateChecker(
//                'https://raw.githubusercontent.com/DiscipleTools/disciple-tools-plugin-starter-template/master/version-control.json',
//                __FILE__,
//                'disciple-tools-plugin-starter-template'
//            );
//
//        }
//    }
//} );
