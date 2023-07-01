<?php
defined( 'ABSPATH' ) || exit;

class PalleonElements {
    /**
	 * The single instance of the class
	 */
	protected static $_instance = null;

    /**
	 * Main Instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    /**
	 * Palleon Constructor
	 */
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_filter( 'cmb2_meta_boxes', array($this, 'add_element') );
        add_action('admin_head', array($this, 'admin_style'));
    }

    /**
	 * Register Post Type
	 */
    public function register_post_type() {
        $labels = array(
            'name'              => esc_html__( 'Palleon Elements', 'palleon' ),
            'singular_name'     => esc_html__( 'Element', 'palleon' ),
            'add_new'           => esc_html__( 'Add new group', 'palleon' ),
            'add_new_item'      => esc_html__( 'Add new group', 'palleon' ),
            'edit_item'         => esc_html__( 'Edit group', 'palleon' ),
            'new_item'          => esc_html__( 'New group', 'palleon' ),
            'view_item'         => esc_html__( 'View group', 'palleon' ),
            'search_items'      => esc_html__( 'Search groups', 'palleon' ),
            'not_found'         => esc_html__( 'No group found', 'palleon' ),
            'not_found_in_trash'=> esc_html__( 'No group found in trash', 'palleon' ),
            'parent_item_colon' => esc_html__( 'Parent group:', 'palleon' ),
            'menu_name'         => esc_html__( 'PE Elements', 'palleon' )
        );
    
        $taxonomies = array();
     
        $supports = array('title');
     
        $post_type_args = array(
            'labels'            => $labels,
            'singular_label'    => esc_html__('Element Group', 'palleon'),
            'public'            => false,
            'exclude_from_search' => true,
            'show_ui'           => true,
            'show_in_nav_menus' => false,
            'publicly_queryable'=> true,
            'query_var'         => true,
            'capability_type'   => 'post',
            'capabilities' => array(
                'edit_post'          => 'update_core',
                'read_post'          => 'update_core',
                'delete_post'        => 'update_core',
                'edit_posts'         => 'update_core',
                'edit_others_posts'  => 'update_core',
                'delete_posts'       => 'update_core',
                'publish_posts'      => 'update_core',
                'read_private_posts' => 'update_core'
            ),
            'has_archive'       => false,
            'hierarchical'      => false,
            'supports'          => $supports,
            'menu_position'     => 10,
            'menu_icon'         => 'dashicons-format-gallery',
            'taxonomies'        => $taxonomies
        );
        register_post_type('palleonelements',$post_type_args);
    }

    /**
	 * Add Element
	 */
    public function add_element( $meta_boxes ) {
        $prefix = 'palleon_cmb2';
        $meta_boxes['palleon_element'] = array(
            'id' => 'palleon_element',
            'title' => esc_attr__( 'Elements', 'palleon'),
            'object_types' => array('palleonelements'),
            'context' => 'normal',
            'priority' => 'default',
            'show_names' => false,
            'fields' => array(
                array(
                    'name'    => esc_html__( 'Element File', 'palleon' ),
                    'id'      => $prefix  . '_custom_elements',
                    'type' => 'file_list',
                    'preview_size' => array( 100, 100 ), // Default: array( 50, 50 )
                    'query_args' => array(
                        'type' => 'image/svg+xml'
                    )
                ),
            ),
        );

        $meta_boxes['palleon_element_bg'] = array(
            'id' => 'palleon_element_bg',
            'title' => esc_attr__( 'Background', 'palleon'),
            'object_types' => array('palleonelements'),
            'context' => 'side',
            'priority' => 'high',
            'show_names' => false,
            'fields' => array(
                array(
                    'name'    => esc_html__( 'Background', 'palleon' ),
                    'id'      => $prefix  . '_custom_elements_bg',
                    'type'    => 'radio_inline',
                    'options' => array(
                        'dark' => esc_html__( 'Dark', 'palleon' ),
                        'light'   => esc_html__( 'Light', 'palleon' )
                    ),
                    'default' => 'dark',
                ),
            ),
        );

        $meta_boxes['palleon_element_loader'] = array(
            'id' => 'palleon_element_loader',
            'title' => esc_attr__( 'Loader', 'palleon'),
            'object_types' => array('palleonelements'),
            'context' => 'side',
            'priority' => 'high',
            'show_names' => false,
            'fields' => array(
                array(
                    'name'    => esc_html__( 'Loader', 'palleon' ),
                    'id'      => $prefix  . '_custom_elements_loader',
                    'type'    => 'radio_inline',
                    'options' => array(
                        'yes' => esc_html__( 'Enable', 'palleon' ),
                        'no'   => esc_html__( 'Disable', 'palleon' )
                    ),
                    'default' => 'yes',
                ),
            ),
        );
    
        return $meta_boxes;
    }

    /**
	 * Admin Style
	 */
    public function admin_style() {
        echo '<style>
        #palleon_cmb2_custom_elements-status li img {
            width:100px;
            height:auto;
        }
        </style>';
    }

}

/**
 * Returns the main instance of the class
 */
function PalleonElements() {  
	return PalleonElements::instance();
}
// Global for backwards compatibility
$GLOBALS['PalleonElements'] = PalleonElements();
