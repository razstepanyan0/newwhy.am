<?php
defined( 'ABSPATH' ) || exit;

class AgamaTemplates {
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
	 * Constructor
	 */
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomy'), 0 );
        add_filter('cmb2_meta_boxes', array($this, 'add_template') );
        add_filter('manage_edit-agamatemplates_columns', array($this, 'admin_column'), 5);
        add_action('manage_posts_custom_column', array($this, 'admin_row'), 5, 2);
        add_action('palleon_after_save', array($this, 'save_field'), 9);
        add_action('wp_ajax_saveAGtemplate', array($this, 'save_template'));
    }

    /**
	 * Register Post Type
	 */
    public function register_post_type() {
        $labels = array(
            'name'              => esc_html__( 'Agama Templates', 'agama' ),
            'singular_name'     => esc_html__( 'Template', 'agama' ),
            'add_new'           => esc_html__( 'Add new template', 'agama' ),
            'add_new_item'      => esc_html__( 'Add new template', 'agama' ),
            'edit_item'         => esc_html__( 'Edit template', 'agama' ),
            'new_item'          => esc_html__( 'New template', 'agama' ),
            'view_item'         => esc_html__( 'View template', 'agama' ),
            'search_items'      => esc_html__( 'Search templates', 'agama' ),
            'not_found'         => esc_html__( 'No template found', 'agama' ),
            'not_found_in_trash'=> esc_html__( 'No template found in trash', 'agama' ),
            'parent_item_colon' => esc_html__( 'Parent template:', 'agama' ),
            'menu_name'         => esc_html__( 'AG Templates', 'agama' )
        );
    
        $taxonomies = array();
     
        $supports = array('title', 'thumbnail');
     
        $post_type_args = array(
            'labels'            => $labels,
            'singular_label'    => esc_html__('Template', 'agama'),
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
        register_post_type('agamatemplates',$post_type_args);
    }

    /**
	 * Register Taxonomy
	 */
    public function register_taxonomy() {
        register_taxonomy(
            'agamatemplatetags',
            'agamatemplates',
            array(
                'labels' => array(
                    'name' => esc_html__( 'Categories', 'agama' ),
                    'add_new_item' => esc_html__( 'Add new category', 'agama' ),
                    'new_item_name' => esc_html__( 'New category', 'agama' )
                ),
                'show_ui' => true,
                'show_tagcloud' => false,
                'show_admin_column' => true,
                'show_in_nav_menus' => false,
                'hierarchical' => true,
                'query_var' => true
            )
        );
    }

    /**
	 * Add Template
	 */
    public function add_template( $meta_boxes ) {
        $meta_boxes['agama_template'] = array(
            'id' => 'agama_template',
            'title' => esc_html__( 'Template', 'agama'),
            'object_types' => array('agamatemplates'),
            'context' => 'normal',
            'priority' => 'default',
            'show_names' => true,
            'fields' => array(
                array(
                    'name'    => esc_html__( 'Template File URL', 'agama' ),
                    'description' => esc_html__( 'The only way to get a valid JSON file is using Palleon image editor. For more information please read the help documentation.', 'agama'),
                    'id'      => 'agama_custom_template',
                    'type'    => 'file',
                    'options' => array(
                        'url' => true
                    ),
                    'query_args' => array(
                        'type' => 'application/json'
                    )
                )
            ),
        );
    
        return $meta_boxes;
    }

    /**
	 * Add custom admin table column
	 */
    public function admin_column($defaults){
        $defaults['agama_template_preview'] = esc_html__( 'Preview', 'agama' );
        return $defaults;
    }

    /**
	 * Add custom admin table row
	 */
    public function admin_row($column_name, $post_id){
        global $post;
        if($column_name === 'agama_template_preview'){
            echo the_post_thumbnail( 'thumbnail');
        }    
    }

    /**
	 * Add save button to Palleon
	 */
    public function save_field(){
        if(is_admin() && (current_user_can('administrator') || current_user_can('editor'))) { ?>
        <div id="palleon-save-as-agtemplate">
            <div class="palleon-block-50">
                <div>
                    <label><?php echo esc_html__('Name', 'agama'); ?></label>
                    <input id="palleon-agtemplate-save-name" class="palleon-form-field palleon-file-name" type="text" value="" autocomplete="off" data-default="">
                </div>
                <button id="palleon-agtemplate-save" type="button" class="palleon-btn primary"><span class="material-icons">save</span><?php echo esc_html__('Save As AG Template', 'agama'); ?></button>
            </div>
            <div class="palleon-block-100">
                <label><?php echo esc_html__('Category', 'agama'); ?></label>
                <select id="palleon-agtemplate-save-tag" class="palleon-select palleon-select2" autocomplete="off" multiple="multiple">
                    <?php
                    $terms = get_terms( 'agamatemplatetags', array(
                        'hide_empty' => false,
                    ) );
                    foreach( $terms as $term ) {
                        echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
        <?php }
    }

    /**
	 * Save Template
	 */
    public function save_template(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'palleon-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'agama'));
        }

        // Insert Post
        $post_id = wp_insert_post(array (
            'post_title' => esc_html($_POST['name']),
            'post_type' => 'agamatemplates',
            'post_status' => 'publish'
        ));

        if (isset($_POST['tag']) && $_POST['tag'] !== 0 && !empty($_POST['tag'])) {
            $tags = json_decode( stripslashes( $_POST['tag'] ), true );
            wp_set_post_terms( $post_id, $tags, 'agamatemplatetags');
        }
        unset($_POST['tag']);

        // Upload dir.
        $upload_dir  = wp_upload_dir();
        $upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;

        // SAVE IMAGE
        $img = str_replace( 'data:' . $_POST['type'] . ';base64,', '', $_POST['thumb'] );
        $img = str_replace( ' ', '+', $img );
        $img = base64_decode( $img );
        $filename = $_POST['filename'] . '.png';

        $upload_file = file_put_contents( $upload_path . $filename, $img );

        $attachment = array(
            'post_mime_type' => $_POST['type'],
            'post_title'     => esc_html($_POST['name']),
            'post_content'   => '',
            'post_status'    => 'inherit',
            'post_parent'    => $post_id,
            'guid'           => $upload_dir['url'] . '/' . basename( $filename ),
            'meta_input'   => array(
                'palleon_hide' => true
            ),
        );

        $attachment_id = wp_insert_attachment( $attachment, $upload_dir['path'] . '/' . $filename );
        wp_update_attachment_metadata(
            $attachment_id,
            wp_generate_attachment_metadata( $attachment_id, $upload_dir['path'] . '/' . $filename )
        );

        set_post_thumbnail( $post_id, $attachment_id );

        unset($_POST['thumb']);
        unset($_POST['type']);

        // SAVE TEMPLATE
        $filename_json = $_POST['filename'] . '.json';
        $json = stripslashes($_POST['json']);
        $upload_json_file = file_put_contents( $upload_path . $filename_json, $json );

        $attachment_json = array(
            'post_mime_type' => 'application/json',
            'post_title'     => esc_html($_POST['name']),
            'post_content'   => '',
            'post_status'    => 'inherit',
            'post_parent'    => $post_id,
            'guid'           => $upload_dir['url'] . '/' . basename( $filename_json ),
            'meta_input'   => array(
                'palleon_hide' => true
            ),
        );

        $attachment_json_id = wp_insert_attachment( $attachment_json, $upload_dir['path'] . '/' . $filename_json );
        wp_update_attachment_metadata(
            $attachment_json_id,
            wp_generate_attachment_metadata( $attachment_json_id, $upload_dir['path'] . '/' . $filename_json )
        );

        $attachment_json_url = wp_get_attachment_url($attachment_json_id);
        update_post_meta($post_id, 'agama_custom_template', esc_url($attachment_json_url));

        unset($_POST['json']);

        wp_die();
    }
}

/**
 * Returns the main instance of the class
 */
function AgamaTemplates() {  
	return AgamaTemplates::instance();
}
// Global for backwards compatibility
$GLOBALS['AgamaTemplates'] = AgamaTemplates();
