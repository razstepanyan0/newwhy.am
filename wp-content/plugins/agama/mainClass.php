<?php
defined( 'ABSPATH' ) || exit;

class Agama extends Palleon {
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
	 * Agama Constructor
	 */
    public function __construct() {
		add_action('init', array($this, 'init'), 1);
        add_filter('plugin_row_meta', array($this, 'agama_plugin_links'), 10, 4);
		add_action('template_redirect', array($this, 'agama_redirect') );
		add_action('wp_loaded', array($this, 'agama_check'), 1);
        add_filter('palleonTemplateParts', array($this, 'agama_template_parts'), 10, 2);
        add_filter('palleonPanelParts', array($this, 'agama_panel_parts'), 10, 2);
        add_filter('palleonMenuIcons', array($this, 'agama_menu_icon'), 10, 2);
        add_action('wp_enqueue_scripts', array($this, 'agama_frontend_styles'), 99);
		add_filter('palleonStylesheets', array($this, 'agama_styles'), 20);
        add_action('admin_enqueue_scripts', array($this, 'agama_admin_scripts'));
        add_filter('palleonScripts', array($this, 'agama_scripts'), 20);
        add_action('palleon_body_end', array($this, 'agama_custom_scripts'), 99);
        add_action('palleon_select_shape', array($this, 'agama_add_shape') );
        add_action('palleon_after_layers', array($this, 'agama_print_area_color'));
		add_action('palleon_body_end', array($this, 'agama_loader'), 1);
        add_action('palleon_add_setting_tab', array($this, 'agama_setting_tab'));
		add_action('palleon_add_settings', array($this, 'agama_settings'));
        add_action('cmb2_admin_init', array($this, 'agama_meta_box'));
        // WooCommerce
        $color_field =  PalleonSettings::get_option('agama_color', '');
        if(!empty($color_field)) {
            add_action('pa_' . $color_field . '_edit_form_fields', array($this, 'woo_color_edit_field'), 10, 2);
            add_action('edited_pa_' . $color_field, array($this, 'woo_color_edited'));
            add_action('created_pa_' . $color_field, array($this, 'woo_color_edited'));
            add_action('pa_' . $color_field . '_add_form_fields', array($this, 'woo_color_add_field'));
            add_action('admin_enqueue_scripts', array($this, 'woo_color_script'));
        }
        add_filter('woocommerce_get_item_data', array($this, 'woo_add_item_meta'),10,2);
        add_filter('woocommerce_display_item_meta', array($this, 'woo_display_item_meta'),10,3);
        add_action('woocommerce_cart_calculate_fees', array($this, 'woo_add_fee'));
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'woo_order_line_item'),10,4);
        add_action('woocommerce_remove_cart_item', array($this, 'woo_remove_cart_item'), 10, 2);
        add_action('woocommerce_cart_item_restored', array($this, 'woo_restore_cart_item'), 10, 2);
        add_filter('woocommerce_loop_add_to_cart_link', array($this, 'woo_add_to_cart'), 98, 2);
        add_action('before_delete_post', array($this, 'woo_delete_attachment'), 10, 2);
        add_action('woocommerce_thankyou', array($this, 'woo_thank_you'), 10, 1);
        add_filter('woocommerce_product_export_meta_value',  array($this, 'woo_handle_export'), 10, 4); 
        add_filter('woocommerce_product_importer_parsed_data',  array($this, 'woo_handle_import'), 10, 2);
        add_action('wp_ajax_agamaMakeOrder', array($this, 'woo_make_order'));
        add_action('wp_ajax_nopriv_agamaMakeOrder', array($this, 'woo_make_order'));
    }

    /**
	 * Init
	 */
    public function init() {
        // Load text domain
        load_plugin_textdomain( 'agama', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        // Register user menu
        register_nav_menus(
            array(
                'agama-menu' => esc_html__( 'Agama Menu', 'agama' )
            )
        );
    }

    /**
	 * Add plugin links to plugins page on the admin dashboard
	 */
    public function agama_plugin_links($links_array, $plugin_file_name, $plugin_data, $status) {
        if ( strpos( $plugin_file_name, 'agama.php' ) !== false ) {
            $links_array[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=palleon_options') ) .'">' . esc_html__( 'Settings', 'agama' ) . '</a>';
            $links_array[] = '<a href="https://palleon.website/agama/documentation/" target="_blank">' . esc_html__( 'Documentation', 'agama' ) . '</a>';
        }
        return $links_array;
    }

    /**
	 * Check if the current page is reserved for Agama
	 */
    public static function is_agama() {
        $slug =  PalleonSettings::get_option('agama_slug', 'product-designer');
        if(isset($_GET['page']) && !empty($_GET['page']) && $_GET['page'] == $slug && !is_admin() && !wp_doing_ajax()) { 
            return true;
        } else {
            return false;
        }
    }

	/**
     * Template redirect
     */
	public function agama_redirect() {
        if (is_product()) {
            global $post;
            $slug =  PalleonSettings::get_option('agama_slug', 'product-designer');
            $url = get_site_url() . '?page=' . $slug . '&product_id=' . $post->ID;
			$template_enable = get_post_meta( $post->ID, 'agama_cmb2_custom_product', true );
            if ( $template_enable == 'enable' ) {
                wp_redirect( $url );
                exit();
            } else {
                return;
            }
        }
    }

	/**
     * Catches our query variable. If it’s there, we’ll stop the
     * rest of WordPress from loading and load the product designer
     */
    public function agama_check() {
        $slug =  PalleonSettings::get_option('agama_slug', 'product-designer');
        if($this->is_agama()) {
            if (isset($_GET['product_id']) && !empty($_GET['product_id'])) {
                $guest_order =  PalleonSettings::get_option('agama_guest_order', 'enable');
                $guest_order_msg =  PalleonSettings::get_option('agama_guest_order_msg', esc_html__( 'Please login or register to continue.', 'agama' ));
				$template_enable = get_post_meta( $_GET['product_id'], 'agama_cmb2_custom_product', true );
				if ( $template_enable != 'enable' ) {
					wp_die(esc_html__('This product is not customizable.', 'agama'));
				}
                if ($guest_order != 'enable') {
                    $user_id = get_current_user_id();
                    if ($user_id === 0) {
                        wp_die(wp_kses_post(wpautop($guest_order_msg)));
                    }
                }
                ob_start(array($this,'ob_html_compress'));
                $this->page_output();
                ob_end_flush();
			} else {
				wp_die(esc_html__('Product ID is required.', 'agama'));
			}
			exit();
        } else {
            return;
        }
    }

    /**
	 * Modify template parts
	 */
    public function agama_template_parts($template_parts) {
        $history = PalleonSettings::get_option('agama_history', 'enable');
        if($this->is_agama()) {
            $template_parts["header"] = AGAMA_PLUGIN_PATH . "template-parts/header.php";
            $template_parts["top-bar"] = AGAMA_PLUGIN_PATH . "template-parts/top-bar.php";
            $template_parts["canvas"] = AGAMA_PLUGIN_PATH . "template-parts/canvas.php";
            $template_parts["agama-media-library"] = AGAMA_PLUGIN_PATH . "template-parts/media-library.php";
            unset($template_parts["add-new"]);
            unset($template_parts["save"]);
            if ($history != 'enable') {
                unset($template_parts["history"]);
            }
        }
        return $template_parts;
    }

    /**
	 * Modify panel parts
	 */
    public function agama_panel_parts($panel_parts) {
        if($this->is_agama()) {
            $templates =  PalleonSettings::get_option('agama_module_templates', 'enable');
            $frames =  PalleonSettings::get_option('agama_module_frames', 'enable');
            $text =  PalleonSettings::get_option('agama_module_text', 'enable');
            $image =  PalleonSettings::get_option('agama_module_image', 'enable');
            $shapes =  PalleonSettings::get_option('agama_module_shapes', 'enable');
            $elements =  PalleonSettings::get_option('agama_module_elements', 'enable');
            $qrcode =  PalleonSettings::get_option('agama_module_qr_code', 'enable');
            $brushes =  PalleonSettings::get_option('agama_module_brushes', 'enable');
            $panel_parts["adjust"] = AGAMA_PLUGIN_PATH . "template-parts/panel-parts/product.php";
            if ($templates == 'enable') {
                $val = array('agama-templates' => AGAMA_PLUGIN_PATH . "template-parts/panel-parts/templates.php");
                $panel_parts = array_slice($panel_parts, 0, 1) + $val + array_slice($panel_parts, 1);
            }
            $panel_parts["image"] = AGAMA_PLUGIN_PATH . "template-parts/panel-parts/image.php";
            $panel_parts["settings"] = AGAMA_PLUGIN_PATH . "template-parts/panel-parts/settings.php";
            if ($frames != 'enable') {
                unset($panel_parts['frames']);
            }
            if ($text != 'enable') {
                unset($panel_parts['text']);
            }
            if ($image != 'enable') {
                unset($panel_parts['image']);
            }
            if ($shapes != 'enable') {
                unset($panel_parts['shapes']);
            }
            if ($elements != 'enable') {
                unset($panel_parts['elements']);
                unset($panel_parts['icons']);
            }
            if ($qrcode != 'enable') {
                unset($panel_parts['qrcode']);
            }
            if ($brushes != 'enable') {
                unset($panel_parts['brushes']);
            }
        }
        return $panel_parts;
    }

    /**
	 * Modify Menu Icons
	 */
    public function agama_menu_icon($icons) {
        if($this->is_agama()) {
            $templates =  PalleonSettings::get_option('agama_module_templates', 'enable');
            $frames =  PalleonSettings::get_option('agama_module_frames', 'enable');
            $text =  PalleonSettings::get_option('agama_module_text', 'enable');
            $image =  PalleonSettings::get_option('agama_module_image', 'enable');
            $shapes =  PalleonSettings::get_option('agama_module_shapes', 'enable');
            $elements =  PalleonSettings::get_option('agama_module_elements', 'enable');
            $qrcode =  PalleonSettings::get_option('agama_module_qr_code', 'enable');
            $brushes =  PalleonSettings::get_option('agama_module_brushes', 'enable');
            $icons['adjust'] = array('storefront', esc_html__('Product', 'agama'));
            if ($templates == 'enable') {
                $val = array('agama-templates' => array('article', esc_html__('Templates', 'agama')));
                $icons = array_slice($icons, 0, 1) + $val + array_slice($icons, 1);
            }
            if ($frames != 'enable') {
                unset($icons['frames']);
            }
            if ($text != 'enable') {
                unset($icons['text']);
            }
            if ($image != 'enable') {
                unset($icons['image']);
            }
            if ($shapes != 'enable') {
                unset($icons['shapes']);
            }
            if ($elements != 'enable') {
                unset($icons['elements']);
                unset($icons['icons']);
            }
            if ($qrcode != 'enable') {
                unset($icons['qrcode']);
            }
            if ($brushes != 'enable') {
                unset($icons['draw']);
            }
        }
        return $icons;
    }

    /**
	 * Frontend styles
	 */
    public function agama_frontend_styles() {
        $suffix = ( defined( 'PALLEON_SCRIPT_DEBUG' ) && PALLEON_SCRIPT_DEBUG ) ? '' : '.min';
        wp_enqueue_style('agama-site', AGAMA_PLUGIN_URL . 'css/site'. $suffix . '.css', false, AGAMA_VERSION);
    }

    /* Admin Scripts */
    public function agama_admin_scripts(){
        $suffix = ( defined( 'PALLEON_SCRIPT_DEBUG' ) && PALLEON_SCRIPT_DEBUG ) ? '' : '.min';
        if(get_post_type() == 'agamaorders') {
            wp_enqueue_style('agama-orders', AGAMA_PLUGIN_URL . 'css/orders'. $suffix . '.css', false, AGAMA_VERSION);
            // Disable auto draft
            wp_deregister_script( 'autosave' );
        }
        if(get_post_type() == 'agamatemplates') {
            wp_enqueue_style('agama-templates', AGAMA_PLUGIN_URL . 'css/templates'. $suffix . '.css', false, AGAMA_VERSION);
        }
        if(get_post_type() == 'product') {
            wp_enqueue_style('agama-product', AGAMA_PLUGIN_URL . 'css/product'. $suffix . '.css', false, AGAMA_VERSION);
            wp_enqueue_script('agama-product', AGAMA_PLUGIN_URL . 'js/product'. $suffix . '.js', array( 'jquery' ), AGAMA_VERSION, true );
        }
    }

    /**
	 * Palleon styles
	 */
    public function agama_styles($styles) {
        $suffix = ( defined( 'PALLEON_SCRIPT_DEBUG' ) && PALLEON_SCRIPT_DEBUG ) ? '' : '.min';
        if($this->is_agama()) { 
            $styles['featherlight-css'] = array(AGAMA_PLUGIN_URL . 'css/featherlight'. $suffix . '.css', AGAMA_VERSION);
            $styles['agama-css'] = array(AGAMA_PLUGIN_URL . 'css/style'. $suffix . '.css', AGAMA_VERSION);
        } else if (is_admin()) {
            $styles['agama-admin-css'] = array(AGAMA_PLUGIN_URL . 'css/admin'. $suffix . '.css', AGAMA_VERSION);
        }
        return $styles;
    }
    
	/**
	 * Palleon scripts
	 */

     public function agama_scripts($scripts) {
        $suffix = ( defined( 'PALLEON_SCRIPT_DEBUG' ) && PALLEON_SCRIPT_DEBUG ) ? '' : '.min';
        if($this->is_agama()) { 
            unset($scripts["ruler"]);
            $scripts['featherlight-js'] = array(AGAMA_PLUGIN_URL . 'js/featherlight'. $suffix . '.js', AGAMA_VERSION);
            $scripts['agama-init'] = array(AGAMA_PLUGIN_URL . 'js/init'. $suffix . '.js', AGAMA_VERSION);
        }
        return $scripts;
     }


    /**
	 * Palleon custom scripts
	 */

    public function agama_custom_scripts() {
        $multiplier =  PalleonSettings::get_option('agama_multiplier', '1');
        $dpi =  PalleonSettings::get_option('agama_dpi', '72');
        if($this->is_agama()) { 
            if (isset($_GET['product_id'])) {
                $product_id = $_GET['product_id'];
                $product = wc_get_product( $product_id ); 
                if ($product->is_type( 'variable' )) {
                    $variations = json_encode($product->get_available_variations());
                    echo '<script>/* <![CDATA[ */ var agamaVariations =' . $variations . ' /* ]]> */</script>';
                } 
            } 
        ?>
        <script>
        /* <![CDATA[ */
        var agamaParams = {
            "cartUrl": "<?php echo esc_url(wc_get_cart_url()); ?>",
            "multiplier": "<?php echo esc_js($multiplier); ?>",
            "dpi": "<?php echo esc_js($dpi); ?>",
            "outside": "<?php echo esc_html__('All objects must be inside the print area.', 'agama'); ?>",
            "changeProduct": "<?php echo esc_html__('Are you sure you want to change the product?', 'agama'); ?>",
            "outofstock": "<?php echo esc_html__('Out of stock!', 'agama'); ?>",
            "addedToCart": "<?php echo esc_html__('The product is added to your cart.', 'agama'); ?>",
            "viewCart": "<?php echo esc_html__('View Cart', 'agama'); ?>",
            "outofstock": "<?php echo esc_html__('Out of stock!', 'agama'); ?>",
            "currencySymbol": "<?php echo esc_js(get_woocommerce_currency_symbol()); ?>",
            "decimalSeparator": "<?php echo esc_js(wc_get_price_decimal_separator()); ?>",
            "thousandSeparator": "<?php echo esc_js(wc_get_price_thousand_separator()); ?>",
            "currencyPosition": "<?php echo esc_js(get_option( 'woocommerce_currency_pos' )); ?>",
            "numberOfDecimals": "<?php echo esc_js(get_option( 'woocommerce_price_num_decimals' )); ?>",
            "areYouSure": "<?php echo esc_html__('Are you sure you finished the customization?', 'agama'); ?>",
            "nothingToPrint": "<?php echo esc_html__('There is nothing on the print area(s). Please add something to the print area before adding the product to your cart.', 'agama'); ?>",
        };
        /* ]]> */
        </script>
        <?php }
    }

    /**
	 * Add custom shape
	 */
    public function agama_add_shape() {
        if(is_admin() && (current_user_can('administrator') || current_user_can('editor'))) { 
            echo '<option value="printarea">' . esc_html__('Print Area', 'agama') . '</option>';
        }
    }

    /**
	 * Add colorpicker for print area object
	 */
    public function agama_print_area_color() {
        if($this->is_agama()) {
            echo '<div class="agama-printarea-settings"><input id="agama-printarea-color" type="text" class="palleon-colorpicker allow-empty" autocomplete="off" value="" /></div>';
        }
    }

    /**
	 * Add custom loader
	 */
    public function agama_loader() {
        if($this->is_agama()) {
            echo '<div id="agama-main-loader" class="palleon-loader-wrap"><div class="palleon-loader-inner"><div class="palleon-loader"></div></div></div>';
        }
    }

    /**
	 * Add Setting Tab
	 */
    public function agama_setting_tab($options) {
        $options->add_field( array(
            'name' => '<span class="dashicons dashicons-admin-plugins"></span>' . esc_html__( 'Agama', 'agama' ),
            'id'   => 'agama_title',
            'type' => 'title'
        ) );
    }

	/**
	 * Add Settings
	 */
    public function agama_settings($options) {

		$options->add_field( array(
            'name' => esc_html__( 'Custom URL Slug', 'agama' ),
            'description' => esc_html__( 'The default slug is "product-designer".', 'agama' ),
            'id'   => 'agama_slug',
            'type' => 'text',
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => '',
            'before_row' => '<div class="palleon-tab-content" data-id="agama-title">',
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Guest Order', 'agama' ),
            'description' => esc_html__( 'Allow customers to access the product designer without an account.', 'agama' ),
            'id'   => 'agama_guest_order',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Yes', 'agama' ),
                'disable'   => esc_html__( 'No', 'agama' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Guest Order Message', 'agama' ),
            'description' => esc_html__( 'If you disable "Guest Order", this message will be shown to non-login users.', 'agama' ),
            'id'   => 'agama_guest_order_msg',
            'type'    => 'wysiwyg',
            'options' => array(
                'wpautop' => true,
                'media_buttons' => false,
                'teeny' => true,
                'quicktags' => false,
                'textarea_rows' => 2
            ),
            'default' => esc_html__( 'Please login or register to continue.', 'agama' )
        ) );

        $attributes_tax_slugs = array_keys( wc_get_attribute_taxonomy_labels() );
        $attributes_options = array();
        foreach( $attributes_tax_slugs as $slug ) {
            $attributes_options[ $slug ] = $slug;
        }

        $options->add_field( array(
            'name'    => esc_html__( 'Color Attribute Slug', 'agama' ),
            'desc'    => esc_html__( 'Choose which product attribute will be used for color selection. This attribute will also change the background of the canvas. For more information please read the documentation.', 'agama' ),
            'id'      => 'agama_color',
            'type'    => 'select',
            'show_option_none' => true,
            'options' => $attributes_options,
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => ''
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Upload/Download Template', 'agama' ),
            'description' => esc_html__( 'If enabled, users can download the design to upload later using buttons on the top bar.', 'agama' ),
            'id'   => 'agama_upload_download',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'agama' ),
                'disable'   => esc_html__( 'Disable', 'agama' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Templates Module', 'agama' ),
            'id'   => 'agama_module_templates',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'agama' ),
                'disable'   => esc_html__( 'Disable', 'agama' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Frames Module', 'agama' ),
            'id'   => 'agama_module_frames',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'agama' ),
                'disable'   => esc_html__( 'Disable', 'agama' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Text Module', 'agama' ),
            'id'   => 'agama_module_text',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'agama' ),
                'disable'   => esc_html__( 'Disable', 'agama' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Image Module', 'agama' ),
            'id'   => 'agama_module_image',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'agama' ),
                'disable'   => esc_html__( 'Disable', 'agama' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Max. Upload Size', 'agama' ),
            'description' => esc_html__( 'Maximum image size allowed to upload (MB).', 'agama' ),
            'id'   => 'agama_max_upload_size',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 8
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Shapes Module', 'agama' ),
            'id'   => 'agama_module_shapes',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'agama' ),
                'disable'   => esc_html__( 'Disable', 'agama' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Elements & Icons Module', 'agama' ),
            'id'   => 'agama_module_elements',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'agama' ),
                'disable'   => esc_html__( 'Disable', 'agama' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'QR Code Module', 'agama' ),
            'id'   => 'agama_module_qr_code',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'agama' ),
                'disable'   => esc_html__( 'Disable', 'agama' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Brushes', 'agama' ),
            'id'   => 'agama_module_brushes',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'agama' ),
                'disable'   => esc_html__( 'Disable', 'agama' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'History', 'agama' ),
            'id'   => 'agama_history',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'agama' ),
                'disable'   => esc_html__( 'Disable', 'agama' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Reviews', 'agama' ),
            'description' => esc_html__( 'Max. number of reviews to show.', 'agama' ),
            'id'   => 'agama_reviews_pagination',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 5
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Image Multiplier', 'agama' ),
            'description' => esc_html__( 'Warning! Changing this setting may cause overload on your server. For more information, please read the help documentation.', 'agama' ),
            'id'   => 'agama_multiplier',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '[0-9]+([\.,][0-9]+)?',
                "step" => '0.1',
                'autocomplete' => 'off'
            ),
            'default' => 0.5
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Image DPI', 'agama' ),
            'id'   => 'agama_dpi',
            'type' => 'select',
            'options' => array(
                '72' => '72 DPI - ' . esc_html__('Monitor Resolution', 'agama'),
                '96'   => '96 DPI',
                '144'   => '144 DPI',
                '300'   => '300 DPI - ' . esc_html__('Printer Resolution', 'agama'),
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable',
            'after_row' => '</div>'
        ) );
    }

    /**
	 * Custom product meta box
	 */
    public function agama_meta_box() {
        $prefix = 'agama_cmb2';
        $cmb = new_cmb2_box( array(
            'id' => $prefix . '_custom_product_meta_box',
            'title' => esc_html__( 'Product designer', 'agama'),
            'object_types' => array('product'),
            'context' => 'normal',
            'priority' => 'default',
            'show_names' => true,
        ));

        $cmb->add_field( array(
            'name'    => esc_html__( 'Product designer', 'agama'),
            'desc'    => '',
            'id'      => $prefix . '_custom_product',
            'type'    => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'agama' ),
                'disable'   => esc_html__( 'Disable', 'agama' ),
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'disable'
        ));

        $cmb->add_field( array(
            'name'    => esc_html__( 'Name (Required)', 'agama'),
            'id'   => $prefix . '_template_name',
            'type' => 'text',
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => esc_html__( 'Front', 'agama' )
        ));

        $cmb->add_field( array(
            'name'    => esc_html__( 'Product Template (Required)', 'agama' ),
            'description'    => esc_html__( 'You can create product templates only using Palleon. For more information, please read the help documentation.', 'agama' ),
            'id'      => $prefix . '_template',
            'type'    => 'file',
            'options' => array(
                'url' => true
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'query_args' => array(
                'type' => 'application/json'
            )
        ));

        $cmb->add_field( array(
            'name'    => esc_html__( 'Additional Options', 'agama'),
            'description'    => esc_html__('Choose "Print Areas" if the product has multiple printable areas. For example for a t-shirt, you can add front, back, left sleeve and right sleeve print areas.', 'agama' ) . '<br/><br/> ' . esc_html__('Choose "Variants" if the product has different types. For example for a phone case; You can add iPhone14, iPhone13 etc.', 'agama' ),
            'id'      => $prefix . '_additional_options',
            'type'    => 'radio_inline',
            'options' => array(
                'printareas' => esc_html__( 'Print Areas', 'agama' ),
                'variants'   => esc_html__( 'Variants', 'agama' ),
                'disable'   => esc_html__( 'Disable', 'agama' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'disable'
        ));

        $group_field_id = $cmb->add_field( array(
            'id'          => $prefix . '_print_areas',
            'type'        => 'group',
            'options'     => array(
                'group_title'       => esc_html__( 'Option {#}', 'agama' ),
                'add_button'        => esc_html__( 'Add Another Option', 'agama' ),
                'remove_button'     => esc_html__( 'Remove Option', 'agama' ),
                'sortable'          => true,
                'closed'         => true
            )
        ) );

        $cmb->add_group_field( $group_field_id, array(
            'name' => esc_html__( 'Name (Required)', 'agama' ),
            'id'   => 'name',
            'type' => 'text',
            'attributes' => array(
                'autocomplete' => 'off'
            ),
        ) );

        $cmb->add_group_field( $group_field_id, array(
            'name'    => esc_html__( 'Product Template (Required)', 'agama' ),
            'id'      => 'template',
            'type'    => 'file',
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'options' => array(
                'url' => true
            ),
            'query_args' => array(
                'type' => 'application/json'
            )
        ));

        $cmb->add_group_field( $group_field_id, array(
            'name' => esc_html__( 'Additional Fee (Optional)', 'agama' ),
            'id'   => 'additional_price',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '[0-9]+([\.,][0-9]+)?',
                "step" => '0.1',
                'autocomplete' => 'off'
            ),
        ) );
    }

    /**
	 * Add custom WooCommerce color fields
	 */
    public function woo_color_edit_field( $term, $taxonomy ){
        $color = get_term_meta( $term->term_id, 'agama_color', true );
        ?>
            <tr class="form-field">
                <th><label for="term-agama_color"><?php echo esc_html__('Agama Color', 'agama'); ?></label></th>
                <td><input type="text" id="term-agama_color" name="agama_color" value="<?php echo esc_attr( $color ) ?>" autocomplete="off" /></td>
            </tr>
        <?php
    }

    public function woo_color_add_field( $taxonomy ){
        ?>
        <div class="form-field">
			<label for="term-agama_color"><?php echo esc_html__('Agama Color', 'agama'); ?></label>
            <input type="text" id="term-agama_color" name="agama_color" value="" autocomplete="off" />
		</div>
        <?php
    }

    /**
	 * Custom WooCommerce color field save function
	 */
    public function woo_color_edited( $term_id ) {	
        $agama_color = ! empty( $_POST[ 'agama_color' ] ) ? $_POST[ 'agama_color' ] : '';
        update_term_meta( $term_id, 'agama_color', sanitize_hex_color( $agama_color ) );    
    }

    /**
	 * Enqueue custom WooCommerce colorpicker scripts
	 */
    public function woo_color_script( $hook ) {  
        if ('term.php' == $hook || 'edit-tags.php' == $hook) { 
            $suffix = ( defined( 'PALLEON_SCRIPT_DEBUG' ) && PALLEON_SCRIPT_DEBUG ) ? '' : '.min';
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_script('agama-colorpicker', plugins_url('js/colorpicker' . $suffix . '.js', __FILE__), array('jquery', 'wp-color-picker'), AGAMA_VERSION, true);
        }
    }

    /**
     * Add item meta data
     */
    public function woo_add_item_meta($item_data, $cart_item) {
        if(array_key_exists('design_id', $cart_item)){
            $design_id = $cart_item['design_id'];
            $item_data[] = array(
                'key'   => esc_html__( 'Design ID', 'agama' ),
                'value' => '<strong>#' . $design_id . '</strong>'
            );
            $args = array(
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'post_parent' => $design_id
            );
            $output = '<div class="agama-item-meta">';
            foreach( get_posts( $args ) as $image) {
                $output .= '<a href="' . esc_url(wp_get_attachment_image_url( $image->ID, 'full' )) . '" target="_blank" title="' . esc_html(get_the_title($image->ID)) . '"><img src="' . esc_url(wp_get_attachment_image_url( $image->ID, 'medium' )) . '" alt="' . esc_html(get_the_title($image->ID)) . '" /></a>';
            }
            $output .= '</div>';
            $item_data[] = array(
                'key'   => esc_html__( 'Attachments', 'agama' ),
                'value' => $output
            );
        }
        return $item_data;
    }

    /**
     * Display item meta data
     */
    public function woo_display_item_meta($html, $item, $args) {
        foreach ($item->get_meta_data() as $meta_item ) {
            if ($meta_item->key == '_design_id') {
                $design_id = $meta_item->value;
                echo '<div class="agama-item-meta-label">' . esc_html__( 'Design ID', 'agama' ) . '<strong> #' . $design_id . '</strong></div>';
                $args = array(
                    'post_type' => 'attachment',
                    'post_mime_type' => 'image',
                    'post_parent' => $design_id
                );
                echo '<div class="agama-item-meta-label">' . esc_html__( 'Attachments', 'agama' ) . '</div>';
                echo '<div class="agama-item-meta">';
                foreach( get_posts( $args ) as $image) {
                    echo '<a href="' . esc_url(wp_get_attachment_image_url( $image->ID, 'full' )) . '" target="_blank" title="' . esc_html(get_the_title($image->ID)) . '"><img src="' . esc_url(wp_get_attachment_image_url( $image->ID, 'medium' )) . '" alt="' . esc_html(get_the_title($image->ID)) . '" /></a>';
                }
                echo '</div>';
            }
        }
        return $html;
    }

    /**
     * Add additional fee
     */

    public function woo_add_fee( $cart ) {
        foreach ( $cart->get_cart() as $cart_item_key => $value ) {
            if ($value['design_id'] ?? null) {
                if ( 0 != ( $fee = get_post_meta( $value['design_id'], 'agama_cmb2_additional_fee', true ) ) ) {
                    $name      = esc_html__( 'Extra print fee', 'agama') . ' (#' . esc_html( $value['design_id'] . ')' );
                    $amount    = $fee;
                    if ( $value["quantity"] > 1 ) {
                        $amount = $fee * $value["quantity"];
                    }
                    $taxable   = true;
                    $tax_class = '';
                    $cart->add_fee( $name, $amount, $taxable, $tax_class );
                }
            }
        }
    }

    /**
     * Add custom meta data to admin page
     */
    public function woo_order_line_item($item, $cart_item_key, $values, $order) {
        if(array_key_exists('design_id', $values)) {
            $design_id = $values['design_id'];
            $post = get_post($design_id);
            if ($post) {
                $output = '<a class="button" href="' . get_edit_post_link($post->ID) . '" target="_blank">' . esc_html__( 'View Attachments', 'agama') . '</a>';
            }
            $item->add_meta_data('_design_id', $design_id);
            $item->add_meta_data('_attachments', $output);
        }
    }

    /**
     * Remove cart item hook  
     */
    public function woo_remove_cart_item( $cart_item_key, $cart ){
        $item = $cart->cart_contents[ $cart_item_key ];
        if ($item['design_id'] ?? null) {
            $new_post_data = array(
                'ID' => $item['design_id'],
                'post_status' => 'trash'
            );
            wp_update_post( $new_post_data );
        }
    }

    /**
     * Undo cart item hook  
     */
    public function woo_restore_cart_item( $cart_item_key, $cart ){
        $item = $cart->cart_contents[ $cart_item_key ];
        if ($item['design_id'] ?? null) {
            $new_post_data = array(
                'ID' =>  $item['design_id'],
                'post_status' => 'draft'
            );
            wp_update_post( $new_post_data );
        }
    }

    /**
     * Delete custom design attachments
     */
    public function woo_delete_attachment($post_id) {
        if ( get_post_type( $post_id ) == 'agamaorders' || get_post_type( $post_id ) == 'agamatemplates' ) {
            $attachments = get_attached_media( '', $post_id );
            foreach ($attachments as $attachment) {
                wp_delete_attachment($attachment->ID, true);
            }
        }
    }
		
    /**
     * Customize add to cart button
     */
    public function woo_add_to_cart( $add_to_cart_html, $product ) {
        $custom_product = get_post_meta( $product->get_id(), 'agama_cmb2_custom_product', true );
        if ( $custom_product == 'enable' ) {
            return '<a class="button" href="' . esc_url(get_the_permalink($product->get_id())) . '">' . esc_html__( 'Customize', 'agama' ) . '</a>';
        }    
    }

    /**
     * Thank you page hook
     */
    public function woo_thank_you( $order_id ) {
        if (!$order_id) {
            return;
        }
        if(!get_post_meta( $order_id, '_thankyou_action_done', true) ) {
            $order = wc_get_order( $order_id );
            $order_key = $order->get_order_number();

            foreach ( $order->get_items() as $item_id => $item ) {
                $design_id = $item->get_meta( '_design_id', true );
                if (!empty($design_id)) {
                    $new_post_data = array(
                        'ID' => $design_id,
                        'post_status' => 'publish'
                    );
                    wp_update_post( $new_post_data );
                    update_post_meta( $design_id, 'agama_cmb2_order_number', $order_key );
                }
            }
            $order->update_meta_data( '_thankyou_action_done', true );
            $order->save();
        }
    }

    /**
	 * Serialize "agama_cmb2_print_areas" meta data on product export (CSV)
	 */
    public function woo_handle_export($value, $meta, $product, $row) {
        if ($meta->key == 'agama_cmb2_print_areas') {
            return serialize($value);
        } else {
			return $value;
		}
    }

    /**
	 * Unserialize "agama_cmb2_print_areas" meta data on product import (CSV)
	 */
    public function woo_handle_import( $data, $d_data ) {
        foreach ( $data[ 'meta_data' ] as $mkey => $mvalue ) {
            if ( $mvalue[ 'key' ] == 'agama_cmb2_print_areas' ) {
                $printAreas = unserialize($mvalue['value']);
                unset($data[ 'meta_data' ]['agama_cmb2_print_areas']);
                $data['meta_data']['agama_cmb2_print_areas']['key'] = 'agama_cmb2_print_areas';
                $data['meta_data']['agama_cmb2_print_areas']['value'] = $printAreas;
            }
        }
        return $data;
    }

    /**
	 * Make order
	 */
    public function woo_make_order(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'palleon-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'agama'));
        }
        $rand = rand();
        $title = $rand;
        $slug = $rand;
        if (isset($_POST['title'])) {
            $title = esc_html($_POST['title']);
        }
        unset($_POST['title']);
        if (isset($_POST['slug'])) {
            $slug = esc_html($_POST['slug']);
        }
        unset($_POST['slug']);
        $upload_dir  = wp_upload_dir();
        $upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;

        // Insert Post
        $post_id = wp_insert_post(array (
            'post_title' => $title,
            'post_type' => 'agamaorders',
            'post_status' => 'draft'
        ));

        if(!is_wp_error($post_id)){
            // Save Images
            if (isset($_POST['images'])) {
                $images = json_decode( stripslashes( $_POST['images'] ), true );
                foreach ($images as $image) {
                    $img = str_replace( 'data:' . $image["type"] . ';base64,', '', $image["img"] );
                    $img = str_replace( ' ', '+', $img );
                    $decoded = base64_decode( $img );
                    $image_area = esc_html($image["area"]);
                    $image_area_slug = str_replace( ' ', '-', $image_area );
                    $image_filename = $image_area_slug . '-' . $slug . '-' . $rand . '.png';

                    file_put_contents( $upload_path . $image_filename, $decoded );

                    $image_attachment = array(
                        'post_mime_type' => esc_attr($image["type"]),
                        'post_title'     => $image_area,
                        'post_content'   => '',
                        'post_parent' => $post_id,
                        'post_status'    => 'inherit',
                        'guid'           => $upload_dir['url'] . '/' . basename( $image_filename ),
                        'meta_input'   => array(
                            'palleon_hide' => true
                        ),
                    );
                    $image_attachment_id = wp_insert_attachment( $image_attachment, $upload_dir['path'] . '/' . $image_filename );
                    wp_update_attachment_metadata(
                        $image_attachment_id,
                        wp_generate_attachment_metadata( $image_attachment_id, $upload_dir['path'] . '/' . $image_filename )
                    );
                }
                unset($_POST['images']);
            }

            // Save Designs
            if (isset($_POST['designs'])) {
                $designs = json_decode( stripslashes( $_POST['designs'] ), true );
                foreach ($designs as $design) {
                    $design_area = esc_html($design["area"]);
                    $design_area_slug = str_replace( ' ', '-', $design_area );
                    $design_filename  = $design_area_slug . '-' . $slug . '-' . $rand . '.json';
                    $design_json = stripslashes($design['json']);

                    file_put_contents( $upload_path . $design_filename, $design_json );

                    $design_attachment = array(
                        'post_mime_type' => 'application/json',
                        'post_title'     => $design_area,
                        'post_content'   => '',
                        'post_parent' => $post_id,
                        'post_status'    => 'inherit',
                        'guid'           => $upload_dir['url'] . '/' . basename( $design_filename ),
                        'meta_input'   => array(
                            'palleon_hide' => true
                        ),
                    );
                    $design_attachment_id = wp_insert_attachment( $design_attachment, $upload_dir['path'] . '/' . $design_filename );
                    wp_update_attachment_metadata(
                        $attachment_id,
                        wp_generate_attachment_metadata( $design_attachment_id, $upload_dir['path'] . '/' . $design_filename )
                    );
                }
                unset($_POST['designs']);
            }

            // Update Post
            $new_post_data = array(
                'ID' => $post_id,
                'post_title' => $title
            );
            wp_update_post( $new_post_data );

            if (isset($_POST['fee']) && $_POST['fee'] !== 0) {
                update_post_meta( $post_id, 'agama_cmb2_additional_fee', esc_html($_POST['fee']) );
            }
            unset($_POST['fee']);

            if (isset($_POST['userid']) && $_POST['userid'] !== 0) {
                update_post_meta( $post_id, 'agama_cmb2_user_id', esc_html($_POST['userid']) );
            }
            unset($_POST['userid']);

            // Add To Cart
            if (isset($_POST['product']) && !empty($_POST['product'])) {
                update_post_meta( $post_id, 'agama_cmb2_product_id', esc_html($_POST['product']) );
                if (isset($_POST['variation']) && !empty($_POST['variation'])) {
                    update_post_meta( $post_id, 'agama_cmb2_variation_id', esc_html($_POST['variation']) );
                    $product_id = WC()->cart->add_to_cart( $_POST['product'], $_POST['quantity'], $_POST['variation'], array(), array( 'design_id' => $post_id )  );  
                } else {
                    $product_id = WC()->cart->add_to_cart( $_POST['product'], $_POST['quantity'], '', array(), array( 'design_id' => $post_id )  );  
                }
                echo esc_html(count(WC()->cart->get_cart()));
            }
            unset($_POST['product']);
            unset($_POST['variation']);
            unset($_POST['quantity']);
            wp_die();

        } else {
            wp_die($post_id->get_error_message());
        }

    }
}

/**
 * Returns the main instance of the class
 */
function Agama() {  
	return Agama::instance();
}
// Global for backwards compatibility
$GLOBALS['Agama'] = Agama();