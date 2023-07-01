<?php
defined( 'ABSPATH' ) || exit;

class PalleonSettings {
    /* The single instance of the class */
	protected static $_instance = null;

    /* Main Instance */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    /* Constructor */
    public function __construct() {
        add_action( 'cmb2_admin_init', array($this, 'register_metabox') );
        add_action( 'admin_enqueue_scripts',array($this, 'colorpicker_labels'), 99 );
        add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts') );
    }

    /* Admin Scripts */
    public function admin_scripts($hook){
        $suffix = ( defined( 'PALLEON_SCRIPT_DEBUG' ) && PALLEON_SCRIPT_DEBUG ) ? '' : '.min';
        if ('palleon_page_palleon_options' == $hook)  {
            wp_enqueue_style('palleon-admin', PALLEON_PLUGIN_URL . 'css/admin' . $suffix . '.css', false, '1.0');
            wp_enqueue_script('palleon-admin', PALLEON_PLUGIN_URL . 'js/admin' . $suffix . '.js', array( 'jquery' ), '1.0', true);
        } else {
            wp_enqueue_style('palleon-admin-general', PALLEON_PLUGIN_URL . 'css/admin-general' . $suffix . '.css', false, '1.0');
        }
    }

    /**
    * Hook in and register a metabox to handle a plugin options page and adds a menu item.
    */
    public function register_metabox() {
        $args = array(
            'id'           => 'palleon_options',
            'title'        => esc_html__('Palleon Settings', 'palleon') . ' <span>v' . PALLEON_VERSION . '</span>',
            'menu_title'   => esc_html__('Settings', 'palleon'),
            'object_types' => array( 'options-page' ),
            'option_key'   => 'palleon_options',
            'parent_slug'     => 'palleon',
            'capability'      => 'manage_options',
            'save_button'     => esc_html__( 'Save Settings', 'palleon' )
        );

        $options = new_cmb2_box( $args );

        /* TABS */
        $options->add_field( array(
            'name' => esc_html__( 'General', 'palleon' ),
            'id'   => 'general_title',
            'classes'   => array('active'),
            'type' => 'title',
            'before_row' => '<div id="palleon-tabs">',
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Back-end Version', 'palleon' ),
            'id'   => 'be_editor_title',
            'type' => 'title'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Front-end Version', 'palleon' ),
            'id'   => 'fe_editor_title',
            'type' => 'title'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Modules', 'palleon' ),
            'id'   => 'modules_title',
            'type' => 'title'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Pagination', 'palleon' ),
            'id'   => 'pagination_title',
            'type' => 'title'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Watermark', 'palleon' ),
            'id'   => 'watermark_title',
            'type' => 'title'
        ) );

        if (function_exists('pmpro_getAllLevels')) {
            $options->add_field( array(
                'name' => esc_html__( 'Paid Memberships Pro', 'palleon' ),
                'id'   => 'pmpro_title',
                'type' => 'title'
            ) );
        }

        if (class_exists('SwpmMembershipLevelUtils')) {
            $options->add_field( array(
                'name' => esc_html__( 'Simple Membership', 'palleon' ),
                'id'   => 'swpm_title',
                'type' => 'title'
            ) );
        }

        if (function_exists('rcp_get_membership_levels')) {
            $options->add_field( array(
                'name' => esc_html__( 'Restrict Content PRO', 'palleon' ),
                'id'   => 'rcpro_title',
                'type' => 'title'
            ) );
        }

        do_action('palleon_add_setting_tab', $options);

        $options->add_field( array(
            'name' => esc_html__( 'Pexels', 'palleon' ),
            'id'   => 'pexels_title',
            'type' => 'title',
            'after_row' => '</div><div id="palleon-tab-boxes">',
        ) );

        /* GENERAL */
        $options->add_field( array(
            'name'    => esc_html__( 'Logo', 'palleon' ),
            'id'      => 'logo',
            'type'    => 'file',
            'query_args' => array(
                'type' => array(
                    'image/jpeg',
                    'image/png',
                ),
            ),
            'preview_size' => 'medium',
            'default' => PALLEON_PLUGIN_URL . 'assets/logo.png',
            'before_row' => '<div class="palleon-tab-content active" data-id="general-title">',
        ) );

        $options->add_field( array(
            'name'    => esc_html__( 'Mobile Logo', 'palleon' ),
            'id'      => 'logo_small',
            'type'    => 'file',
            'query_args' => array(
                'type' => array(
                    'image/jpeg',
                    'image/png',
                ),
            ),
            'preview_size' => 'medium',
            'default' => PALLEON_PLUGIN_URL . 'assets/logo-small.png'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Default Theme', 'palleon' ),
            'id'   => 'default_theme',
            'type' => 'radio_inline',
            'options' => array(
                'dark' => esc_html__( 'Dark', 'palleon' ),
                'light'   => esc_html__( 'Light', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'dark'
        ) );

        $options->add_field( array(
            'name'    => esc_html__( 'Primary Color', 'palleon' ),
            'id'      => 'primary_color',
            'type'    => 'colorpicker',
            'default' => '#6658ea'
        ) );

        $options->add_field( array(
            'name'    => esc_html__( 'Secondary Color', 'palleon' ),
            'id'      => 'secondary_color',
            'type'    => 'colorpicker',
            'default' => '#5546e8'
        ) );

        $options->add_field( array(
            'name'    => esc_html__( 'Default Background', 'palleon' ),
            'id'      => 'default_background',
            'type'    => 'colorpicker',
            'default' => '#212121'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Allow Transparency', 'palleon' ),
            'description' => esc_html__( 'Alpha transparency selection on the color pickers.', 'palleon' ),
            'id'   => 'alpha_color',
            'type' => 'radio_inline',
            'options' => array(
                'true' => esc_html__( 'Enable', 'palleon' ),
                'false'   => esc_html__( 'Disable', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'true'
        ) );

        $options->add_field(
            array(
                'name' => esc_html__( 'WEBGL Filtering', 'palleon'),
                'description'    => esc_html__( 'Image filtering engine can work on both WEBGL or plain CPU javascript. WEBGL may not work properly on older browsers or hardware. If you are experiencing issues on image filters, you can disable WEBGL.', 'palleon'),
                'id' => 'webgl_filtering',
                'type' => 'radio_inline',
                'options' => array(
                    'true' => esc_html__( 'Enable', 'palleon' ),
                    'false'   => esc_html__( 'Disable', 'palleon' )
                ),
                'attributes' => array(
                    'autocomplete' => 'off'
                ),
                'default' => 'true'
            )
        );

        $options->add_field( array(
            'name' => esc_html__( 'Max. Texture Size (WEBGL)', 'palleon' ),
            'id'   => 'texture_size',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 4096
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Custom CSS', 'palleon' ),
            'id' => 'custom_css',
            'type' => 'textarea_code',
            'attributes' => array(
                'data-codeeditor' => json_encode( array(
                    'codemirror' => array(
                        'mode' => 'css'
                    ),
                ) ),
            ),
            'after_row' => '</div>',
        ) );
        
        /* BACK-END VERSION */
        $options->add_field( array(
            'name' => esc_html__( 'Allow SVG Upload', 'palleon' ),
            'description' => esc_html__( 'Allow users to upload SVG files to the media library.', 'palleon' ),
            'id'   => 'allow_svg',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'palleon' ),
                'disable'   => esc_html__( 'Disable', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable',
            'before_row' => '<div class="palleon-tab-content" data-id="be-editor-title">',
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Allow JSON Upload', 'palleon' ),
            'description' => esc_html__( 'Allow users to upload JSON files (templates) to the media library.', 'palleon' ),
            'id'   => 'allow_json',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'palleon' ),
                'disable'   => esc_html__( 'Disable', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Other Images', 'palleon' ),
            'description' => esc_html__( 'Allow users to use all images in the media library. If you disable it, users will only able to use their own images.', 'palleon' ),
            'id'   => 'other_images',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'palleon' ),
                'disable'   => esc_html__( 'Disable', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'PRO Template Title', 'palleon' ),
            'id'   => 'be_pro_info_title',
            'type' => 'text',
            'default' => esc_html__( 'Premium Content', 'palleon' )
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'PRO Template Info', 'palleon' ),
            'description' => esc_html__( 'If you are using one of the supported Memberships plugins, Members which are not allowed to use the template, will see this message.', 'palleon' ),
            'id'   => 'be_pro_info',
            'type'    => 'wysiwyg',
            'options' => array(
                'wpautop' => true,
                'media_buttons' => false,
                'teeny' => true,
                'quicktags' => false,
                'textarea_rows' => 2
            ),
            'default' => esc_html__( 'You need to upgrade your membership level to use this template.', 'palleon' ),
            'after_row' => '</div>',
        ) );

        /* FRONT-END VERSION */
        $slug =  PalleonSettings::get_option('fe_slug', 'palleon');

        $options->add_field( array(
            'name' => esc_html__( 'Front-end Version', 'palleon' ),
            'description' => esc_html__( 'If you enable this, all site visitors can use the editor without login from the following link;', 'palleon' ) . '<br><a href="' . get_site_url() . '?page=' . $slug . '" target="_blank">' . get_site_url() . '?page=' . $slug . '</a>',
            'id'   => 'fe_editor',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'palleon' ),
                'disable'   => esc_html__( 'Disable', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'disable',
            'before_row' => '<div class="palleon-tab-content" data-id="fe-editor-title">',
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Custom URL Slug', 'palleon' ),
            'description' => esc_html__( 'Default slug is "palleon".', 'palleon' ),
            'id'   => 'fe_slug',
            'type' => 'text',
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => ''
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Description (Meta tag)', 'palleon' ),
            'id'   => 'fe_desc',
            'type' => 'textarea_small',
            'default' => ''
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Keywords (Meta tag)', 'palleon' ),
            'id'   => 'fe_keywords',
            'type' => 'textarea_small',
            'default' => ''
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'PRO Template Title', 'palleon' ),
            'id'   => 'fe_pro_info_title',
            'type' => 'text',
            'default' => esc_html__( 'Login Required', 'palleon' )
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'PRO Template Info', 'palleon' ),
            'description' => esc_html__( 'The info for PRO templates which are not accessible on frontend version.', 'palleon' ),
            'id'   => 'fe_pro_info',
            'type'    => 'wysiwyg',
            'options' => array(
                'wpautop' => true,
                'media_buttons' => false,
                'teeny' => true,
                'quicktags' => false,
                'textarea_rows' => 2
            ),
            'default' => esc_html__( 'You must login to use PRO templates.', 'palleon' ),
            'after_row' => '</div>',
        ) );

        /* MODULES */
        $options->add_field( array(
            'name' => esc_html__( 'Basic Adjusts', 'palleon' ),
            'id'   => 'module_basic_adjust',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'palleon' ),
                'disable'   => esc_html__( 'Disable', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable',
            'before_row' => '<div class="palleon-tab-content" data-id="modules-title">',
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Image Filters', 'palleon' ),
            'id'   => 'module_filters',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'palleon' ),
                'disable'   => esc_html__( 'Disable', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Frames', 'palleon' ),
            'id'   => 'module_frames',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'palleon' ),
                'disable'   => esc_html__( 'Disable', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Text', 'palleon' ),
            'id'   => 'module_text',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'palleon' ),
                'disable'   => esc_html__( 'Disable', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Image', 'palleon' ),
            'id'   => 'module_image',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'palleon' ),
                'disable'   => esc_html__( 'Disable', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Shapes', 'palleon' ),
            'id'   => 'module_shapes',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'palleon' ),
                'disable'   => esc_html__( 'Disable', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Elements & Icons', 'palleon' ),
            'id'   => 'module_elements',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'palleon' ),
                'disable'   => esc_html__( 'Disable', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Default Elements', 'palleon' ),
            'description' => esc_html__( 'If you want to show only your own elements, disable.', 'palleon' ),
            'id'   => 'default_elements',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'palleon' ),
                'disable'   => esc_html__( 'Disable', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'QR Code', 'palleon' ),
            'id'   => 'module_qrcode',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'palleon' ),
                'disable'   => esc_html__( 'Disable', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Brushes', 'palleon' ),
            'id'   => 'module_brushes',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'palleon' ),
                'disable'   => esc_html__( 'Disable', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Templates', 'palleon' ),
            'id'   => 'module_templates',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'palleon' ),
                'disable'   => esc_html__( 'Disable', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Default Templates', 'palleon' ),
            'description' => esc_html__( 'If you want to show only your own templates and tags on template library, disable.', 'palleon' ),
            'id'   => 'default_temp',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'palleon' ),
                'disable'   => esc_html__( 'Disable', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Order of the Templates', 'palleon' ),
            'id'   => 'template_order',
            'type' => 'select',
            'options' => array(
                'random' => esc_html__( 'Random', 'palleon' ),
                'new' => esc_html__( 'Newest First', 'palleon' ),
                'old' => esc_html__( 'Oldest First', 'palleon' ),
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'random'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Canvas Ruler', 'palleon' ),
            'id'   => 'module_ruler',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'palleon' ),
                'disable'   => esc_html__( 'Disable', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'History', 'palleon' ),
            'id'   => 'history',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'palleon' ),
                'disable'   => esc_html__( 'Disable', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name'    => esc_html__( 'Max. history log', 'palleon' ),
            'description'    => esc_html__( 'Maximum history log to store. Big numbers may slow down your browser.', 'palleon' ),
            'id'      => 'max_history_log',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 40,
            'after_row' => '</div>'
        ) );

        /* PAGINATION */
        $options->add_field( array(
            'name' => esc_html__( 'Media Library', 'palleon' ),
            'description' => esc_html__( 'Max. number of images to show.', 'palleon' ),
            'id'   => 'ml_pagination',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 18,
            'before_row' => '<div class="palleon-tab-content" data-id="pagination-title">',
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Templates', 'palleon' ),
            'description' => esc_html__( 'Max. number of templates to show.', 'palleon' ),
            'id'   => 'tp_pagination',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 21
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'My Templates', 'palleon' ),
            'description' => esc_html__( 'Max. number of my templates to show.', 'palleon' ),
            'id'   => 'mytp_pagination',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 10
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Elements', 'palleon' ),
            'description' => esc_html__( 'Max. number of elements to show in a category.', 'palleon' ),
            'id'   => 'el_pagination',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 12
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Frames', 'palleon' ),
            'description' => esc_html__( 'Max. number of frames to show in a category.', 'palleon' ),
            'id'   => 'fr_pagination',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 4,
            'after_row' => '</div>',
        ) );

        /* WATERMARK */
        $options->add_field(
            array(
                'name' => esc_html__( 'Watermark', 'palleon'),
                'description'    => esc_html__( 'If enabled, watermark is added to the image when user clicks save or download.', 'palleon'),
                'id' => 'watermark',
                'type' => 'radio_inline',
                'options' => array(
                    'none' => esc_html__( 'Disable', 'palleon' ),
                    'frontend'   => esc_html__( 'Front-end only', 'palleon' ),
                    'both'   => esc_html__( 'Front-end & Back-end', 'palleon' )
                ),
                'attributes' => array(
                    'autocomplete' => 'off'
                ),
                'default' => 'none',
                'before_row' => '<div class="palleon-tab-content" data-id="watermark-title">',
            )
        );

        $options->add_field(
            array(
                'name' => esc_html__( 'Location', 'palleon'),
                'id' => 'watermark_location',
                'type' => 'radio_inline',
                'options' => array(
                    'top-left' => esc_html__( 'Top Left', 'palleon' ),
                    'top-right'   => esc_html__( 'Top Right', 'palleon' ),
                    'bottom-left'   => esc_html__( 'Bottom Left', 'palleon' ),
                    'bottom-right'   => esc_html__( 'Bottom Right', 'palleon' )
                ),
                'attributes' => array(
                    'autocomplete' => 'off'
                ),
                'default' => 'bottom-right',
            )
        );

        $options->add_field( array(
            'name'    => esc_html__( 'Watermark Text', 'palleon' ),
            'id'      => 'watermark_text',
            'type'    => 'text',
            'default' => esc_html__( 'palleon.website', 'palleon' )
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Font Family', 'palleon' ),
            'id'   => 'watermark_font_family',
            'type' => 'select',
            'options' => Palleon::get_websafe_fonts(),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'Georgia, serif'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Font Size', 'palleon' ),
            'id'   => 'watermark_font_size',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 40
        ) );

        $options->add_field(
            array(
                'name' => esc_html__( 'Font Style', 'palleon'),
                'id' => 'watermark_font_style',
                'type' => 'radio_inline',
                'options' => array(
                    'normal' => esc_html__( 'Normal', 'palleon' ),
                    'italic'   => esc_html__( 'Italic', 'palleon' )
                ),
                'attributes' => array(
                    'autocomplete' => 'off'
                ),
                'default' => 'normal'
            )
        );

        $options->add_field(
            array(
                'name' => esc_html__( 'Font Weight', 'palleon'),
                'id' => 'watermark_font_weight',
                'type' => 'radio_inline',
                'options' => array(
                    'normal' => esc_html__( 'Normal', 'palleon' ),
                    'bold'   => esc_html__( 'Bold', 'palleon' )
                ),
                'attributes' => array(
                    'autocomplete' => 'off'
                ),
                'default' => 'bold'
            )
        );

        $options->add_field( array(
            'name'    => esc_html__( 'Font Color', 'palleon' ),
            'id'      => 'watermark_font_color',
            'type'    => 'colorpicker',
            'default' => '#000000'
        ) );

        $options->add_field( array(
            'name'    => esc_html__( 'Background Color', 'palleon' ),
            'id'      => 'watermark_stroke_color',
            'type'    => 'colorpicker',
            'default' => '#FFFFFF',
            'after_row' => '</div>'
        ) );

        /* PAID MEMBERSHIPS PRO */
        if (function_exists('pmpro_getAllLevels')) {

            $pmpro_levels = pmpro_getAllLevels( true, true );
            $pmpro_levels = pmpro_sort_levels_by_order( $pmpro_levels );

            foreach( $pmpro_levels as $level ) {
                $pmpro_levels_array[ $level->id ] = $level->name;
            }

            $options->add_field( array(
                'name'    => esc_html__( 'Membership Levels', 'palleon' ),
                'desc'    => esc_html__( 'Select membership levels which will be able to access backend photo editor.', 'palleon' ),
                'id'      => 'pmpro_levels',
                'type'    => 'multicheck',
                'options' => $pmpro_levels_array,
                'before_row' => '<div class="palleon-tab-content" data-id="pmpro-title">',
            ) );

            $options->add_field( array(
                'name' => esc_html__( 'Redirect URL', 'palleon' ),
                'desc'   => esc_html__( 'As default, non-memberships redirect to the homepage if they try to access backend photo editor.', 'palleon' ),
                'id'      => 'pmpro_redirect',
                'type' => 'text_url'
            ) );

            $options->add_field( array(
                'name'    => esc_html__( 'PRO Templates', 'palleon' ),
                'desc'    => esc_html__( 'Select membership levels which will be able to access PRO templates.', 'palleon' ),
                'id'      => 'pmpro_template_levels',
                'type'    => 'multicheck',
                'options' => $pmpro_levels_array,
                'after_row' => '</div>',
            ) );

        }

        /* SIMPLE MEMBERSHIP */
        if (class_exists('SwpmMembershipLevelUtils')) {
            $swpm_levels = SwpmMembershipLevelUtils::get_all_membership_levels_in_array();

            $options->add_field( array(
                'name'    => esc_html__( 'Membership Levels', 'palleon' ),
                'desc'    => esc_html__( 'Select membership levels which will be able to access backend photo editor.', 'palleon' ),
                'id'      => 'swpm_levels',
                'type'    => 'multicheck',
                'options' => $swpm_levels,
                'before_row' => '<div class="palleon-tab-content" data-id="swpm-title">',
            ) );

            $options->add_field( array(
                'name' => esc_html__( 'Redirect URL', 'palleon' ),
                'desc'   => esc_html__( 'As default, non-memberships redirect to the homepage if they try to access backend photo editor.', 'palleon' ),
                'id'      => 'swpm_redirect',
                'type' => 'text_url'
            ) );

            $options->add_field( array(
                'name'    => esc_html__( 'PRO Templates', 'palleon' ),
                'desc'    => esc_html__( 'Select membership levels which will be able to access PRO templates.', 'palleon' ),
                'id'      => 'swpm_template_levels',
                'type'    => 'multicheck',
                'options' => $swpm_levels,
                'after_row' => '</div>',
            ) );
        }

        /* RESTRICT CONTENT PRO */
        if (function_exists('rcp_get_membership_levels')) {

            $rcpro_levels = rcp_get_membership_levels(array('status' => 'active','number' => 999));
            $rcpro_array = array();
            foreach ( $rcpro_levels as $level ) {
                $rcpro_array[$level->get_id()] = $level->get_name();
            }

            $options->add_field( array(
                'name'    => esc_html__( 'Membership Levels', 'palleon' ),
                'desc'    => esc_html__( 'Select membership levels which will be able to access backend photo editor.', 'palleon' ),
                'id'      => 'rcpro_levels',
                'type'    => 'multicheck',
                'options' => $rcpro_array,
                'before_row' => '<div class="palleon-tab-content" data-id="rcpro-title">',
            ) );

            $options->add_field( array(
                'name' => esc_html__( 'Redirect URL', 'palleon' ),
                'desc'   => esc_html__( 'As default, non-memberships redirect to the homepage if they try to access backend photo editor.', 'palleon' ),
                'id'      => 'rcpro_redirect',
                'type' => 'text_url'
            ) );

            $options->add_field( array(
                'name'    => esc_html__( 'PRO Templates', 'palleon' ),
                'desc'    => esc_html__( 'Select membership levels which will be able to access PRO templates.', 'palleon' ),
                'id'      => 'rcpro_template_levels',
                'type'    => 'multicheck',
                'options' => $rcpro_array,
                'before_row' => '</div>',
            ) );
        }

        do_action('palleon_add_settings', $options);

        /* PEXELS */
        $options->add_field( array(
            'name'    => esc_html__( 'API Key (Required)', 'palleon' ),
            'description' => esc_html__( 'You must get a free API key from Pexels to use this feature. For more information, please read the documentation.', 'palleon' ),
            'id'      => 'pexels',
            'type'    => 'text',
            'before_row' => '<div class="palleon-tab-content" data-id="pexels-title">',
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Language', 'palleon' ),
            'description' => esc_html__( 'The language of the search you are performing.', 'palleon' ),
            'id'   => 'pexels_lang',
            'type' => 'select',
            'options' => array(
                'en-US' => esc_html__( 'English', 'palleon' ),
                'pt-BR' => esc_html__( 'Portuguese (Brazil)', 'palleon' ),
                'es-ES' => esc_html__( 'Spanish', 'palleon' ),
                'ca-ES' => esc_html__( 'Catalan (Spanish)', 'palleon' ),
                'de-DE' => esc_html__( 'German', 'palleon' ),
                'it-IT' => esc_html__( 'Italian', 'palleon' ),
                'fr-FR' => esc_html__( 'French', 'palleon' ),
                'sv-SE' => esc_html__( 'Swedish', 'palleon' ),
                'pl-PL' => esc_html__( 'Polish', 'palleon' ),
                'nl-NL' => esc_html__( 'Dutch', 'palleon' ),
                'hu-HU' => esc_html__( 'Hungarian', 'palleon' ),
                'cs-CZ' => esc_html__( 'Czech', 'palleon' ),
                'da-DK' => esc_html__( 'Danish', 'palleon' ),
                'fi-FI' => esc_html__( 'Finnish', 'palleon' ),
                'nb-NO' => esc_html__( 'Norwegian', 'palleon' ),
                'uk-UA' => esc_html__( 'Ukrainian', 'palleon' ),
                'tr-TR' => esc_html__( 'Turkish', 'palleon' ),
                'el-GR' => esc_html__( 'Greek', 'palleon' ),
                'ro-RO' => esc_html__( 'Romanian', 'palleon' ),
                'sk-SK' => esc_html__( 'Slovak', 'palleon' ),
                'ru-RU' => esc_html__( 'Russian', 'palleon' ),
                'ja-JP' => esc_html__( 'Japanese', 'palleon' ),
                'zh-TW' => esc_html__( 'Chinese (T)', 'palleon' ),
                'zh-CN' => esc_html__( 'Chinese (S)', 'palleon' ),
                'ko-KR' => esc_html__( 'Korean', 'palleon' ),
                'th-TH' => esc_html__( 'Thai', 'palleon' ),
                'id-ID' => esc_html__( 'Indonesian', 'palleon' ),
                'vi-VN' => esc_html__( 'Vietnamese', 'palleon' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'en-US'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Image Size', 'palleon' ),
            'description' => esc_html__( 'Original is not recommended. By default WordPress does not allow images bigger than 2500px to save.', 'palleon' ),
            'id'   => 'pexels_img_size',
            'type' => 'select',
            'options' => array(
                'large2x' => esc_html__( 'Large 2x', 'palleon' ),
                'large' => esc_html__( 'Large', 'palleon' ),
                'medium' => esc_html__( 'Medium', 'palleon' ),
                'portrait' => esc_html__( 'Portrait', 'palleon' ),
                'landscape' => esc_html__( 'Landscape', 'palleon' ),
                'tiny' => esc_html__( 'Tiny', 'palleon' ),
                'original' => esc_html__( 'Original', 'palleon' ),
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'large2x'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Pagination', 'palleon' ),
            'description' => esc_html__( 'Max. number of images to show.', 'palleon' ),
            'id'   => 'pexels_pagination',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 15
        ) );

        $options->add_field( array(
            'name'    => esc_html__( 'Caching (hour)', 'palleon' ),
            'id'      => 'pexels_caching',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 24,
            'after_row' => '</div></div>',
        ) );
    }
    /**
    * Colorpicker Labels
    */
    public function colorpicker_labels( $hook ) {
        global $wp_version;
        if( version_compare( $wp_version, '5.4.2' , '>=' ) ) {
            wp_localize_script(
            'wp-color-picker',
            'wpColorPickerL10n',
            array(
                'clear'            => esc_html__( 'Clear', 'palleon' ),
                'clearAriaLabel'   => esc_html__( 'Clear color', 'palleon' ),
                'defaultString'    => esc_html__( 'Default', 'palleon' ),
                'defaultAriaLabel' => esc_html__( 'Select default color', 'palleon' ),
                'pick'             => esc_html__( 'Select Color', 'palleon' ),
                'defaultLabel'     => esc_html__( 'Color value', 'palleon' )
            )
            );
        }
    }

    /**
    * Palleon get option
    */
    static function get_option( $key = '', $default = false ) {
        if ( function_exists( 'cmb2_get_option' ) ) {
            return cmb2_get_option( 'palleon_options', $key, $default );
        }
        $opts = get_option( 'palleon_options', $default );
        $val = $default;
        if ( 'all' == $key ) {
            $val = $opts;
        } elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
            $val = $opts[ $key ];
        }
        return $val;
    }

}

/**
 * Returns the main instance of the class.
 */
function PalleonSettings() {  
	return PalleonSettings::instance();
}
// Global for backwards compatibility.
$GLOBALS['PalleonSettings'] = PalleonSettings();