<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Disciple_Tools_Plugin_Starter_Template_Tile
{
    private static $_instance = null;
    public static function instance(){
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct(){
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 10, 2 );
        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields' ], 1, 2 );
        add_action( 'dt_details_additional_section', [ $this, 'dt_add_section' ], 30, 2 );
    }

    /**
     * This function registers a new tile to a specific post type
     *
     * @todo Set the post-type to the target post-type (i.e. contacts, groups, trainings, etc.)
     * @todo Change the tile key and tile label
     *
     * @param $tiles
     * @param string $post_type
     * @return mixed
     */
    public function dt_details_additional_tiles( $tiles, $post_type = '' ) {
        if ( $post_type === 'contacts' || $post_type === 'starter_post_type' ){
            $tiles['disciple_tools_plugin_starter_template'] = [ 'label' => __( 'Plugin Starter Template', 'disciple-tools-plugin-starter-template' ) ];
            $tiles['a_beautiful_tile'] = [ 'label' => __( 'A Beautiful Tile', 'disciple-tools-plugin-starter-template' ) ];
        }
        return $tiles;
    }

    /**
     * @param array $fields
     * @param string $post_type
     * @return array
     */
    public function dt_custom_fields( array $fields, string $post_type = '' ) {
        /**
         * @todo set the post type
         */
        if ( $post_type === 'contacts' || $post_type === 'starter_post_type' ){
            /**
             * @todo Add the fields that you want to include in your tile.
             *
             * Examples for creating the $fields array
             * Contacts
             * @link https://github.com/DiscipleTools/disciple-tools-theme/blob/256c9d8510998e77694a824accb75522c9b6ed06/dt-contacts/base-setup.php#L108
             *
             * Groups
             * @link https://github.com/DiscipleTools/disciple-tools-theme/blob/256c9d8510998e77694a824accb75522c9b6ed06/dt-groups/base-setup.php#L83
             */

            /**
             * This is an example of a text field
             */
            $fields['disciple_tools_plugin_starter_template_text'] = [
                'name'        => __( 'Text', 'disciple-tools-plugin-starter-template' ),
                'description' => _x( 'Text', 'Optional Documentation', 'disciple-tools-plugin-starter-template' ),
                'type'        => 'text',
                'default'     => '',
                'tile' => 'disciple_tools_plugin_starter_template',
                'icon' => get_template_directory_uri() . '/dt-assets/images/edit.svg',
            ];
            /**
             * This is an example of a multiselect field
             */
            $fields['disciple_tools_plugin_starter_template_multiselect'] = [
                'name' => __( 'Multiselect', 'disciple-tools-plugin-starter-template' ),
                'default' => [
                    'one' => [ 'label' => __( 'One', 'disciple-tools-plugin-starter-template' ) ],
                    'two' => [ 'label' => __( 'Two', 'disciple-tools-plugin-starter-template' ) ],
                    'three' => [ 'label' => __( 'Three', 'disciple-tools-plugin-starter-template' ) ],
                    'four' => [ 'label' => __( 'Four', 'disciple-tools-plugin-starter-template' ) ],
                ],
                'tile' => 'disciple_tools_plugin_starter_template',
                'type' => 'multi_select',
                'hidden' => false,
                'icon' => get_template_directory_uri() . '/dt-assets/images/edit.svg',
            ];
            /**
             * This is an example of a key select field
             */
            $fields['disciple_tools_plugin_starter_template_keyselect'] = [
                'name' => 'Key Select',
                'type' => 'key_select',
                'tile' => 'disciple_tools_plugin_starter_template',
                'default' => [
                    'first'   => [
                        'label' => _x( 'First', 'Key Select Label', 'disciple-tools-plugin-starter-template' ),
                        'description' => _x( 'First Key Description', 'Training Status field description', 'disciple-tools-plugin-starter-template' ),
                        'color' => '#ff9800'
                    ],
                    'second'   => [
                        'label' => _x( 'Second', 'Key Select Label', 'disciple-tools-plugin-starter-template' ),
                        'description' => _x( 'Second Key Description', 'Training Status field description', 'disciple-tools-plugin-starter-template' ),
                        'color' => '#4CAF50'
                    ],
                    'third'   => [
                        'label' => _x( 'Third', 'Key Select Label', 'disciple-tools-plugin-starter-template' ),
                        'description' => _x( 'Third Key Description', 'Training Status field description', 'disciple-tools-plugin-starter-template' ),
                        'color' => '#366184'
                    ],
                ],
                'icon' => get_template_directory_uri() . '/dt-assets/images/edit.svg',
                'default_color' => '#366184',
                'select_cannot_be_empty' => true
            ];

            //test fields
            $fields['number_test'] = [
                'name'        => __( 'Number field', 'disciple-tools-plugin-starter-template' ),
                'type'        => 'number',
                'default'     => 0,
                'tile'        => 'a_beautiful_tile',
                'min_option'  => '5',
            ];
            $fields['number_test_private'] = [
                'name'        => __( 'Number field private', 'disciple-tools-plugin-starter-template' ),
                'type'        => 'number',
                'default'     => 0,
                'tile'        => 'a_beautiful_tile',
                'private'     => true,
                'max_option'  => '200',
            ];
            $fields['text_test'] = [
                'name'        => __( 'Text', 'disciple-tools-plugin-starter-template' ),
                'type'        => 'text',
                'default'     => 0,
                'tile'      => 'a_beautiful_tile',
            ];
            $fields['text_test_private'] = [
                'name'        => __( 'Text', 'disciple-tools-plugin-starter-template' ),
                'type'        => 'text',
                'default'     => 0,
                'tile'      => 'a_beautiful_tile',
                'private'   => true
            ];
            $fields['contact_communication_channel_test'] = [
                'name'        => __( 'Communication Channel', 'disciple-tools-plugin-starter-template' ),
                'type'        => 'communication_channel',
                'default'     => 0,
                'tile'      => 'a_beautiful_tile',
            ];

            $fields['user_select_test'] = [
                'name'        => __( 'User Select', 'disciple-tools-plugin-starter-template' ),
                'type'        => 'user_select',
                'tile'        => 'a_beautiful_tile'
            ];
            $fields['array_test'] = [
                'name'        => __( 'Array', 'disciple-tools-plugin-starter-template' ),
                'type'        => 'array',
                'tile'        => 'a_beautiful_tile'
            ];
            $fields['location_test'] = [
                'name' => 'location field',
                'type' => 'location',
                'tile' => 'a_beautiful_tile'
            ];
            $fields['date_test'] = [
                'name'        => __( ' Date Field', 'disciple-tools-plugin-starter-template' ),
                'description' => '',
                'type'        => 'date',
                'default'     => '',
                'tile' => 'a_beautiful_tile'
            ];
            $fields['date_test_private'] = [
                'name'        => __( ' Date Field', 'disciple-tools-plugin-starter-template' ),
                'description' => '',
                'type'        => 'date',
                'default'     => '',
                'tile' => 'a_beautiful_tile',
                'private'   => true
            ];
            $fields['boolean_test'] = [
                'name'        => __( 'Boolean', 'disciple-tools-plugin-starter-template' ),
                'type'        => 'boolean',
                'default'     => false,
            ];
            $fields['boolean_test_private'] = [
                'name'        => __( 'Boolean', 'disciple-tools-plugin-starter-template' ),
                'type'        => 'boolean',
                'default'     => false,
                'private'   => true
            ];
            $fields['multi_select_test'] = [
                'name' => 'Random Options Multiselect',
                'type' => 'multi_select',
                'default' => [
                    'one' => [ 'label' => 'option 1' ],
                    'two' => [ 'label' => 'option 2' ],
                    'three' => [ 'label' => 'option 3' ],
                ],
                'tile' => 'a_beautiful_tile',
            ];
            $fields['multi_select_test_private'] = [
                'name' => 'Random Private Options',
                'type' => 'multi_select',
                'default' => [
                    'one_private' => [ 'label' => 'option 1' ],
                    'two_private' => [ 'label' => 'option 2' ],
                    'three_private' => [ 'label' => 'option 3' ],
                ],
                'tile' => 'a_beautiful_tile',
                'private'   => true
            ];
            $fields['key_select_test'] = [
                'name' => 'Random Options Key Select',
                'type' => 'key_select',
                'default' => [
                    'one' => [ 'label' => 'option 1' ],
                    'two' => [ 'label' => 'option 2' ],
                    'three' => [ 'label' => 'option 3' ],
                ],
                'tile' => 'a_beautiful_tile',
            ];
            $fields['key_select_test_private'] = [
                'name' => 'Random Private Key Select Options',
                'type' => 'key_select',
                'default' => [
                    'one_private' => [ 'label' => 'option 1' ],
                    'two_private' => [ 'label' => 'option 2' ],
                    'three_private' => [ 'label' => 'option 3' ],
                ],
                'tile' => 'a_beautiful_tile',
                'private'   => true
            ];
            $fields['tags_test'] = [
                'name' => 'Random Tags',
                'type' => 'tags',
                'default' => [
                    'one' => [ 'label' => 'option 1' ],
                    'two' => [ 'label' => 'option 2' ],
                    'three' => [ 'label' => 'option 3' ],
                ],
                'tile' => 'a_beautiful_tile',
            ];
            $fields['tags_test_private'] = [
                'name' => 'Random Tags Private',
                'type' => 'tags',
                'default' => [
                    'one' => [ 'label' => 'option 1' ],
                    'two' => [ 'label' => 'option 2' ],
                    'three' => [ 'label' => 'option 3' ],
                ],
                'tile' => 'a_beautiful_tile',
                'private'   => true
            ];

            $fields['links_test'] = [
                'name' => 'Links',
                'type' => 'link',
                'default' => [
                    'default' => [ 'label' => 'Default', ],
                    'one' => [ 'label' => 'option 1' ],
                    'two' => [ 'label' => 'option 2' ],
                    'three' => [ 'label' => 'option 3' ],
                ],
                'tile' => 'a_beautiful_tile',
            ];
        }
        return $fields;
    }

    public function dt_add_section( $section, $post_type ) {
        /**
         * @todo set the post type and the section key that you created in the dt_details_additional_tiles() function
         */
        if ( ( $post_type === 'contacts' || $post_type === 'starter_post_type' ) && $section === 'disciple_tools_plugin_starter_template' ){
            /**
             * These are two sets of key data:
             * $this_post is the details for this specific post
             * $post_type_fields is the list of the default fields for the post type
             *
             * You can pull any query data into this section and display it.
             */
            $this_post = DT_Posts::get_post( $post_type, get_the_ID() );
            $post_type_fields = DT_Posts::get_post_field_settings( $post_type );
            ?>

            <!--
            @todo you can add HTML content to this section.
            -->

            <div class="cell small-12 medium-4">
                <!-- @todo remove this notes section-->
                <strong>You can do a number of customizations here.</strong><br><br>
                See post types and field keys and values: <a href="<?php echo esc_html( admin_url( 'admin.php?page=dt_utilities&tab=fields' ) ); ?>" target="_blank">click here</a>
            </div>

        <?php }
    }
}
Disciple_Tools_Plugin_Starter_Template_Tile::instance();
