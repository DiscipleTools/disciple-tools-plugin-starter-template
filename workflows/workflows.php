<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Plugin_Starter_Template_Workflows
 *
 * @since  1.11.0
 */
class Disciple_Tools_Plugin_Starter_Template_Workflows {

    /**
     * Disciple_Tools_Plugin_Starter_Template_Workflows The single instance of Disciple_Tools_Plugin_Starter_Template_Workflows.
     *
     * @var    object
     * @access private
     * @since  1.11.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Plugin_Starter_Template_Workflows Instance
     *
     * Ensures only one instance of Disciple_Tools_Plugin_Starter_Template_Workflows is loaded or can be loaded.
     *
     * @return Disciple_Tools_Plugin_Starter_Template_Workflows instance
     * @since  1.11.0
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Disciple_Tools_Plugin_Starter_Template_Workflows constructor.
     */
    public function __construct() {
        add_filter( 'dt_workflows', [ $this, 'fetch_default_workflows_filter' ], 10, 2 );
    }

    public function fetch_default_workflows_filter( $workflows, $post_type ) {
        /*
         * Please ensure workflow ids are both static and unique; as they
         * will be used further downstream within admin view and execution handler.
         * Dynamically generated timestamps will not work, as they will regularly
         * change. Therefore, maybe a plugin id prefix, followed by post type and then a constant: E.g: starter_groups_00001
         *
         * Also, review /themes/disciple-tools-theme/dt-core/admin/js/dt-utilities-workflows.js;
         * so, as to determine which condition and action event types can be assigned to which field type!
         */

        switch ( $post_type ) {
            case 'contacts':
                $this->build_default_workflows_contacts( $workflows );
                break;
            case 'groups':
                $this->build_default_workflows_groups( $workflows );
                break;
            case 'starter_post_type':
                $this->build_default_workflows_starter( $workflows );
                break;
        }

        return $workflows;
    }

    private function build_default_workflows_contacts( &$workflows ) {
    }

    private function build_default_workflows_groups( &$workflows ) {
    }

    private function build_default_workflows_starter( &$workflows ) {
        $dt_fields = DT_Posts::get_post_field_settings( 'starter_post_type' );

        $workflows[] = (object) [
            'id'         => 'starter_00001',
            'name'       => 'Starter Template Add Text On Creation',
            'enabled'    => false, // Can be enabled via admin view
            'trigger'    => Disciple_Tools_Workflows_Defaults::$trigger_created['id'],
            'conditions' => [
                Disciple_Tools_Workflows_Defaults::new_condition( Disciple_Tools_Workflows_Defaults::$condition_is_set,
                    [
                        'id'    => 'name',
                        'label' => $dt_fields['name']['name']
                    ], [
                        'id'    => '',
                        'label' => ''
                    ]
                )
            ],
            'actions'    => [
                Disciple_Tools_Workflows_Defaults::new_action( Disciple_Tools_Workflows_Defaults::$action_update,
                    [
                        'id'    => 'disciple_tools_plugin_starter_template_text',
                        'label' => $dt_fields['disciple_tools_plugin_starter_template_text']['name']
                    ], [
                        'id'    => 'Auto Filled By Workflow Engine',
                        'label' => 'Auto Filled By Workflow Engine'
                    ]
                )
            ]
        ];
    }
}

Disciple_Tools_Plugin_Starter_Template_Workflows::instance();
