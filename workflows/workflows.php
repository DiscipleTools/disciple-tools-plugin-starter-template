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

    private static $action_custom_people_group_connections = [
        'id'    => 'starter_groups_00001_custom_action_people_group_connections',
        'label' => 'Auto-Add People Group Connections'
    ];

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
        add_filter( 'dt_workflows_custom_actions', function ( $actions ) {
            $actions[] = (object) [
                'id'        => self::$action_custom_people_group_connections['id'],
                'name'      => self::$action_custom_people_group_connections['label'],
                'displayed' => true // Within admin workflow builder view?
            ];

            return $actions;
        }, 10, 1 );

        add_action( self::$action_custom_people_group_connections['id'], [
            $this,
            'custom_action_people_group_connections'
        ], 10, 1 );
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
        }

        return $workflows;
    }

    private function build_default_workflows_contacts( &$workflows ) {
    }

    private function build_default_workflows_groups( &$workflows ) {
        $dt_fields = DT_Posts::get_post_field_settings( 'groups' );

        $workflows[] = (object) [
            'id'         => 'starter_groups_00001',
            'name'       => '[Starter] Auto-Adding People Group Connections',
            'enabled'    => false, // Can be enabled via admin view
            'trigger'    => Disciple_Tools_Workflows_Defaults::$trigger_updated['id'],
            'conditions' => [
                Disciple_Tools_Workflows_Defaults::new_condition(
                    Disciple_Tools_Workflows_Defaults::$condition_is_set,
                    [
                        'id'    => 'members',
                        'label' => $dt_fields['members']['name']
                    ], [
                        'id'    => '',
                        'label' => ''
                    ]
                )
            ],
            'actions'    => [
                Disciple_Tools_Workflows_Defaults::new_action(
                    Disciple_Tools_Workflows_Defaults::$action_custom,
                    [
                        'id'    => 'members', // Field to be updated or an arbitrary selection!
                        'label' => $dt_fields['members']['name']
                    ], [
                        'id'    => self::$action_custom_people_group_connections['id'], // Action Hook
                        'label' => self::$action_custom_people_group_connections['label']
                    ]
                )
            ]
        ];
    }

    /**
     * Workflow custom action self-contained function to handle following
     * use case:
     *
     * When adding a contact to a group, if the contact has people group
     * connections, also add those connections to the group.
     *
     * @var    object
     * @access public
     * @since  1.11.0
     */
    public function custom_action_people_group_connections( $post ) {
        // Ensure post is a valid group type
        if ( ! empty( $post ) && ( $post['post_type'] === 'groups' ) ) {

            $new_members                      = [];
            $new_members['members']['values'] = [];

            // Iterate over group members in search of member connections to add to group
            $members = $post['members'] ?? [];
            foreach ( $members as $member ) {

                if ( ! empty( $member ) && $member['post_type'] === 'contacts' ) {

                    // Fetch member contacts record and any associated people group connections
                    $member_post = DT_Posts::get_post( $member['post_type'], $member['ID'], false, false, false );
                    if ( ! empty( $member_post ) && ! is_wp_error( $member_post ) && isset( $member_post['relation'] ) ) {

                        foreach ( $member_post['relation'] as $connection ) {

                            // Ensure connection is not already a group member -> safeguard against infinite post update loops!
                            if ( ! $this->is_group_member( $members, $connection['ID'] ) ) {

                                // Prepare non-group member connection for group addition
                                $new_members['members']['values'][] = [ "value" => $connection['ID'] ];
                            }
                        }
                    }
                }
            }

            // Assuming we have updated fields, proceed with post update!
            if ( ! empty( $new_members['members']['values'] ) ) {
                DT_Posts::update_post( $post['post_type'], $post['ID'], $new_members, false, false );
            }
        }
    }

    private function is_group_member( $members, $id ): bool {
        foreach ( $members as $member ) {
            if ( intval( $member['ID'] ) === intval( $id ) ) {
                return true;
            }
        }

        return false;
    }
}

Disciple_Tools_Plugin_Starter_Template_Workflows::instance();
