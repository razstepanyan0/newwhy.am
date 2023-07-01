<?php
defined( 'ABSPATH' ) || exit;

class Palleon {
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
        add_action('admin_init', array($this, 'page_check'), 1);
        add_action('init', array($this, 'init'), 1);
        add_action('init', array($this, 'page_check_frontend'), 2);
        add_action('admin_menu', array($this, 'register_page'));
        add_filter('media_row_actions', array($this, 'edit_links'), 10, 2);
        add_action('wp_ajax_loadmyimgs', array($this, 'load_my_images'));
        add_action('wp_ajax_loadmytemplates', array($this, 'load_my_templates'));
        add_action('wp_ajax_loadallimgs', array($this, 'load_all_images'));
        add_action('wp_ajax_uploadImgToLibrary', array($this, 'upload_img_to_library'));
        add_action('wp_ajax_uploadSVGToLibrary', array($this, 'upload_svg_to_library'));
        add_action('wp_ajax_deleteImgFromLibrary', array($this, 'delete_file_from_library'));
        add_action('wp_ajax_deleteJsonFromLibrary', array($this, 'delete_file_from_library'));
        add_action('wp_ajax_favElement', array($this, 'favorite_element'));
        add_action('wp_ajax_favframe', array($this, 'favorite_frame'));
        add_action('wp_ajax_favTemplate', array($this, 'favorite_template'));
        add_action('wp_ajax_savePreferences', array($this, 'save_preferences'));
        add_action('wp_ajax_saveImage', array($this, 'save_image'));
        add_action('wp_ajax_saveJson', array($this, 'save_json'));
        add_action('palleon_head', array($this, 'styles'));
        add_action('palleon_head', 'wp_site_icon');
        add_action('palleon_body_end', array($this, 'scripts'));
        add_filter('upload_mimes', array($this, 'mime_types'));
        add_filter('plugin_row_meta', array($this, 'plugin_links'), 10, 4 );
        add_action('wp_ajax_templateSearch', array($this, 'template_search'));
        add_action('wp_ajax_nopriv_templateSearch', array($this, 'template_search'));
        add_action('palleon_backend', array($this, 'pmpro'));
        add_action('palleon_backend', array($this, 'swpm'));
        add_action('palleon_backend', array($this, 'rcpro'));
    }

    /**
	 * Init
	 */
    public function init() {
        load_plugin_textdomain( 'palleon', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        register_nav_menus(
            array(
                'palleon-be-menu' => esc_html__( 'Palleon Back-end Menu', 'palleon' ),
                'palleon-fe-menu' => esc_html__( 'Palleon Front-end Menu', 'palleon' )
            )
        );
    }

    /**
	 * Add plugin links to plugins page on the admin dashboard
	 */
    public function plugin_links($links_array, $plugin_file_name, $plugin_data, $status) {
        if ( strpos( $plugin_file_name, 'palleon.php' ) !== false ) {
            $links_array[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=palleon_options') ) .'">' . esc_html__( 'Settings', 'palleon' ) . '</a>';
            $links_array[] = '<a href="https://palleon.website/documentation/" target="_blank">' . esc_html__( 'Documentation', 'palleon' ) . '</a>';
        }
        return $links_array;
    }

    /**
	 * Allowed additional mime types (Optional)
	 */
    public function mime_types($mimes) {
        $allowSVG =  PalleonSettings::get_option('allow_svg', 'enable');
        $allowJSON =  PalleonSettings::get_option('allow_json', 'enable');
        if ($allowSVG == 'enable') {
            $mimes['svg'] = 'image/svg+xml';
        }
        if ($allowJSON == 'enable') {
            $mimes['json'] = 'application/json';
        }
        return $mimes;
    }

    /**
	 * Register Admin Page
	 */
    public function register_page(){
        add_menu_page( 
            esc_html__( 'Palleon', 'palleon' ),
            esc_html__( 'Palleon', 'palleon' ),
            'upload_files',
            'palleon',
            array($this, 'page_output'),
            'dashicons-format-image',
            10
        ); 
    }

    /**
	 * Get Template Parts
	 */
    public static function get_template_parts(){
        $template_parts = apply_filters('palleonTemplateParts',array(
            "header" => "template-parts/header.php",
            "main-loader" => "template-parts/main-loader.php",
            "top-bar" => "template-parts/top-bar.php",
            "icon-menu" => "template-parts/icon-menu.php",
            "panel" => "template-parts/panel.php",
            "canvas" => "template-parts/canvas.php",
            "layers" => "template-parts/layers.php",
            "add-new" => "template-parts/add-new.php",
            "save" => "template-parts/save.php",
            "media-library" => "template-parts/media-library.php",
            "svg-library" => "template-parts/svg-library.php",
            "history" => "template-parts/history.php",
            "footer" => "template-parts/footer.php"
        ));
        return $template_parts;
    }

    /**
	 * Get Panel Parts
	 */
    public static function get_panel_parts(){
        $frames = PalleonSettings::get_option('module_frames', 'enable');
        $text = PalleonSettings::get_option('module_text', 'enable');
        $image = PalleonSettings::get_option('module_image', 'enable');
        $shapes = PalleonSettings::get_option('module_shapes', 'enable');
        $elements = PalleonSettings::get_option('module_elements', 'enable');
        $qrcode = PalleonSettings::get_option('module_qrcode', 'enable');
        $brushes = PalleonSettings::get_option('module_brushes', 'enable');

        $panel_parts = apply_filters('palleonPanelParts',array(
            "adjust" => "panel-parts/adjust.php",
            "text" => "panel-parts/text.php",
            "image" => "panel-parts/image.php",
            "frames" => "panel-parts/frames.php",
            "shapes" => "panel-parts/shapes.php",
            "elements" => "panel-parts/elements.php",
            "icons" => "panel-parts/icons.php",
            "qrcode" => "panel-parts/qrcode.php",
            "brushes" => "panel-parts/brushes.php",
            "settings" => "panel-parts/settings.php"
        ));

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

        return $panel_parts;
    }

    /**
	 * Get Panel Parts
	 */
    public static function palleon_get_menu_icons(){
        $frames = PalleonSettings::get_option('module_frames', 'enable');
        $text = PalleonSettings::get_option('module_text', 'enable');
        $image = PalleonSettings::get_option('module_image', 'enable');
        $shapes = PalleonSettings::get_option('module_shapes', 'enable');
        $elements = PalleonSettings::get_option('module_elements', 'enable');
        $qrcode = PalleonSettings::get_option('module_qrcode', 'enable');
        $brushes = PalleonSettings::get_option('module_brushes', 'enable');

        $icons = apply_filters('palleonMenuIcons',array(
            'adjust' => array('tune', esc_html__('Adjust', 'palleon')),
            'text' => array('title', esc_html__('Text', 'palleon')),
            'image' => array('add_photo_alternate', esc_html__('Image', 'palleon')),
            'frames' => array('wallpaper', esc_html__('Frames', 'palleon')),
            'shapes' => array('category', esc_html__('Shapes', 'palleon')),
            'elements' => array('star', esc_html__('Elements', 'palleon')),
            'icons' => array('place', esc_html__('Icons', 'palleon')),
            'qrcode' => array('qr_code', esc_html__('QR Code', 'palleon')),
            'draw' => array('brush', esc_html__('Brushes', 'palleon')),
            'settings' => array('settings', esc_html__('Settings', 'palleon'))
        ));

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

        return $icons;
    }

    /**
	 * HTML Compress
	 */
    public static function ob_html_compress($buf){
        return preg_replace(array('/<!--(.*)-->/Uis',"/[[:blank:]]+/"),array('',' '),str_replace(array("\n","\r","\t"),'',$buf));
    }

    /**
	 * Page Output
	 */
    public static function page_output(){
        $template_parts = self::get_template_parts();

        if (!is_admin()) {
            unset($template_parts["media-library"]);
            unset($template_parts["svg-library"]);
        }

        foreach ( $template_parts as $template) {
            include_once($template);
        }
    }

    /**
     * Backend
     * Catches our query variable. If it’s there, we’ll stop the
     * rest of WordPress from loading and load the photo editor
     */
    public function page_check() {
        global $pagenow;
        if(!isset($_GET['page']) || empty($_GET['page']) || $pagenow != 'admin.php' || wp_doing_ajax()) {
            return;
        } else {
            if($_GET['page'] == 'palleon' && isset($_GET['attachment_id'])) {
                do_action('palleon_backend');
                if (empty($_GET['attachment_id'])) {
                    wp_die(esc_html__('Attachment ID is required.', 'palleon'));
                } else {
                    $author = get_post_field('post_author', $_GET['attachment_id']);
                    if (current_user_can('administrator') || current_user_can('editor') || $author == get_current_user_id()) {
                        ob_start(array($this,'ob_html_compress'));
                        $this->page_output();
                        ob_end_flush();
                        exit();
                    } else {
                        wp_die(esc_html__('You are not allowed to edit this image.', 'palleon'));
                    }
                }
            } else if($_GET['page'] == 'palleon') {
                do_action('palleon_backend');
                ob_start(array($this,'ob_html_compress'));
                $this->page_output();
                ob_end_flush();
                exit();
            } else {
                return;
            }
        }
    }

    /**
     * Frontend
     * Catches our query variable. If it’s there, we’ll stop the
     * rest of WordPress from loading and load the photo editor
     */
    public function page_check_frontend() {
        $editor = PalleonSettings::get_option('fe_editor','disable');
        $slug =  PalleonSettings::get_option('fe_slug', 'palleon');
        if(isset($_GET['page']) && !empty($_GET['page']) && $_GET['page'] == $slug && !is_admin() && !wp_doing_ajax()) {
            if ($editor == 'enable') {
                do_action('palleon_frontend');
                ob_start(array($this,'ob_html_compress'));
                $this->page_output();
                ob_end_flush();
                exit();
            } else {
                wp_die(esc_html__('Front-end photo editor is disabled.', 'palleon'));
            }

        } else {
            return;
        }
    }

    /**
	 * Scripts and Styles.
     * Since we've created a bare-bones separate admin page for the photo editor, there is no point to use "admin_enqueue_scripts".
     * Please see "page_check" function above.
	 */

    /**
	 * Get Stylesheets
	 */
    public static function get_stylesheets(){
        $suffix = ( defined( 'PALLEON_SCRIPT_DEBUG' ) && PALLEON_SCRIPT_DEBUG ) ? '' : '.min';
        $default_theme = PalleonSettings::get_option('default_theme','dark');
        $custom_theme = Palleon::get_user_option('custom-theme', get_current_user_id(), $default_theme);

        $styles = apply_filters('palleonStylesheets',array(
            'material-icons' => array('https://fonts.googleapis.com/icon?family=Material+Icons', ''),
            'plugins-css' => array(PALLEON_PLUGIN_URL . 'css/plugins.min.css', PALLEON_VERSION),
            'style' => array(PALLEON_PLUGIN_URL . 'css/style'. $suffix . '.css', PALLEON_VERSION),
            'theme-link' => array(PALLEON_PLUGIN_URL . 'css/' . $custom_theme  . $suffix . '.css', PALLEON_VERSION)
        ));

        return $styles;
    }

    /**
	 * Print Styles
	 */
    public function styles(){
        $stylesheets = self::get_stylesheets();
        foreach($stylesheets as $id => $key) { 
            $ver = '';
            if (!empty($key[1])) {
                $ver = '?ver=' . $key[1];
            }
            echo '<link id="palleon-' . esc_attr($id) . '" href="' . esc_url($key[0] . $ver) . '" rel="stylesheet" type="text/css">';
        }
        $default_background = PalleonSettings::get_option('default_background','#212121');
        $primary_color = PalleonSettings::get_option('primary_color','#6658ea');
        $secondary_color = PalleonSettings::get_option('secondary_color','#5546e8');
        $custom_css = PalleonSettings::get_option('custom_css','');
        $font_size = $this->get_user_option('custom-font-size', get_current_user_id(), 14); 
        $bg_color = $this->get_user_option('custom-background', get_current_user_id(), $default_background); 
        $guide_color = Palleon::get_user_option('ruler-guide-color', get_current_user_id(), '#6658ea');
        $guide_size = Palleon::get_user_option('ruler-guide-size', get_current_user_id(), '1');
        $inline_style = '';

        if (!empty($font_size)) {
            $inline_style .= 'html {font-size:' . $font_size . 'px;}';
        }
        if (!empty($bg_color)) {
            $inline_style .= '#palleon-content {background-color:' . $bg_color . ';}';
        }
        if (!empty($guide_color) && $guide_color != '#6658ea') {
            $inline_style .= '.guide.h, .guide.v {border-color:' . $guide_color . ';}';
        }
        if (!empty($guide_size) && $guide_size != '1') {
            $inline_style .= '.guide.h, .guide.v {border-width:' . $guide_size . 'px;}';
        }
        if (!empty($primary_color) && $primary_color != '#6658ea') {
            $inline_style .= '.palleon-loader {border-top-color: ' . $primary_color . ';}';
            $inline_style .= '.notice {border-left-color: ' . $primary_color . ';}';
            $inline_style .= '.palleon-img-loader {border-top-color: ' . $primary_color . ';}';
            $inline_style .= '#palleon-toggle-left:hover .material-icons,#palleon-toggle-right:hover .material-icons {color: ' . $primary_color . ';}';
            $inline_style .= 'ul.palleon-dropdown li a .material-icons {color: ' . $primary_color . ';}';
            $inline_style .= '#palleon-layers>li>.material-icons {color: ' . $primary_color . ';}';
            $inline_style .= '#palleon-no-layer a {color: ' . $primary_color . ';}';
            $inline_style .= '.palleon-frame:hover,.palleon-element:hover {color: ' . $primary_color . ';}';
            $inline_style .= '.grid-icon-item:hover {border-color: ' . $primary_color . ';}';
            $inline_style .= '.grid-icon-item .material-icons {color: ' . $primary_color . ';}';
            $inline_style .= '.palleon-btn.active,.palleon-btn.active:hover,.palleon-btn.primary {background: ' . $primary_color . ';}';
            $inline_style .= '.palleon-pagination ul li.active,.palleon-pagination ul li.active:hover {border-color: ' . $primary_color . ';background: ' . $primary_color . ';}';
            $inline_style .= '.palleon-pagination ul li:hover {border-color: ' . $primary_color . ';}';
            $inline_style .= '.palleon-accordion .palleon-pagination ul li.active,.palleon-accordion .palleon-pagination ul li.active:hover {background: ' . $primary_color . ';}';
            $inline_style .= 'ul.palleon-accordion > li.opened {border-color: ' . $primary_color . ';}';
            $inline_style .= 'ul.palleon-accordion > li > a >.data-count {background: ' . $primary_color . ';}';
            $inline_style .= '.palleon-slider::-webkit-slider-thumb{background: ' . $primary_color . ';}';
            $inline_style .= '.palleon-slider::-moz-range-thumb {background: ' . $primary_color . ';}';
            $inline_style .= '.palleon-form-field:focus {border-color: ' . $primary_color . ';}';
            $inline_style .= '.sp-container button.sp-choose {background: ' . $primary_color . ';}';
            $inline_style .= '.palleon-select:focus {border-color: ' . $primary_color . ';}';
            $inline_style .= '.select2-container--dark .select2-results__option--highlighted[aria-selected] {background: ' . $primary_color . ' !important;}';
            $inline_style .= '.select2-container--dark .select2-results__option[aria-selected=true] {background: ' . $primary_color . ';}';
            $inline_style .= '.select2-container--dark .select2-search input:focus {border-color: ' . $primary_color . ';}';
            $inline_style .= '.palleon-tabs-menu li.active {background: ' . $primary_color . ';}';
            $inline_style .= '.palleon-toggle-checkbox:checked+.palleon-toggle-switch {background: ' . $primary_color . ';}';
            $inline_style .= '.palleon-checkbox input:checked~.palleon-checkmark {background-color: ' . $primary_color . ';border-color: ' . $primary_color . ';}';
            $inline_style .= '#palleon-history-list li .info .material-icons {color: ' . $primary_color . ';}';
            $inline_style .= 'a.pexels-url {color: ' . $primary_color . ';}';
            $inline_style .= '.menu-btn {background-color: ' . $primary_color . ';}';
        }
        if (!empty($secondary_color) && $secondary_color != '#5546e8') {
            $inline_style .= '.palleon-btn:hover,.palleon-btn.primary:hover {background: ' . $secondary_color . ';}';
            $inline_style .= '.sp-container button.sp-choose:hover,.sp-container button.sp-choose:focus {background: ' . $secondary_color . ';}';
        }

        if (!empty($custom_css)) {
            $inline_style .= $custom_css;
        }
        echo '<style>' . $inline_style . '</style>';
    }

    /**
	 * Get Scripts
	 */
    public static function get_scripts(){
        $suffix = ( defined( 'PALLEON_SCRIPT_DEBUG' ) && PALLEON_SCRIPT_DEBUG ) ? '' : '.min';
        $ruler = PalleonSettings::get_option('module_ruler', 'enable');
        $qrcode = PalleonSettings::get_option('module_qrcode', 'enable');
        $slug =  PalleonSettings::get_option('fe_slug', 'palleon');

        $scripts = array(
            'jquery' => array(PALLEON_PLUGIN_URL . 'js/jquery.min.js', '3.6.3'),
            'fabric' => array(PALLEON_PLUGIN_URL . 'js/fabric.min.js', '5.3.0'),
            'plugins-js' => array(PALLEON_PLUGIN_URL . 'js/plugins.min.js', PALLEON_VERSION),
            'jquery-ui' => array(PALLEON_PLUGIN_URL . 'js/jquery-ui.min.js', '1.13.2'),
            'palleon-js' => array(PALLEON_PLUGIN_URL . 'js/palleon' . $suffix . '.js', PALLEON_VERSION)
        );

        if ($ruler == 'enable') {
            $scripts['ruler'] = array(PALLEON_PLUGIN_URL . 'js/rulers-guides.min.js', PALLEON_VERSION);
        }

        if ($qrcode == 'enable') {
            $scripts['qrcode'] = array(PALLEON_PLUGIN_URL . 'js/qrcode.min.js', PALLEON_VERSION);
        }

        if(isset($_GET['page']) && !empty($_GET['page']) && ($_GET['page'] == $slug || $_GET['page'] == 'palleon') && !wp_doing_ajax()) { 
            $scripts['init'] = array(PALLEON_PLUGIN_URL . 'js/init'.$suffix.'.js', PALLEON_VERSION);
        }

        $scripts = apply_filters('palleonScripts', $scripts);

        return $scripts;
    }

    /**
	 * Print Scripts
	 */
    public function scripts(){
        $scripts = self::get_scripts();
        foreach($scripts as $id => $key) { 
            $ver = '';
            if (!empty($key[1])) {
                $ver = '?ver=' . $key[1];
            }
            echo '<script id="palleon-' . esc_attr($id) . '" type="text/javascript" src="' . esc_url($key[0] . $ver) . '"></script>';
        }

        $version = ( is_admin()) ? 'backend' : 'frontend';
        $proCheck = ( Palleon::can_use_template()) ? 'yes' : 'no';
        $suffix = ( defined( 'PALLEON_SCRIPT_DEBUG' ) && PALLEON_SCRIPT_DEBUG ) ? '' : '.min';
        $enableGLFiltering = PalleonSettings::get_option('webgl_filtering','true');  
        $textureSize = PalleonSettings::get_option('texture_size',4096);
        $proInfoTitle = PalleonSettings::get_option('fe_pro_info_title',esc_html__( 'Login Required', 'palleon' ));
        $proInfo = PalleonSettings::get_option('fe_pro_info',esc_html__( 'You must login to use PRO templates.', 'palleon' ));
        $upgradeInfoTitle = PalleonSettings::get_option('be_pro_info_title',esc_html__( 'Login Required', 'palleon' ));
        $upgradeInfo = PalleonSettings::get_option('be_pro_info',esc_html__( 'You need to upgrade your membership level to use this template.', 'palleon' ));
        $alpha_color = PalleonSettings::get_option('alpha_color','true');
        $watermark = PalleonSettings::get_option('watermark','none');
        $watermarkText = PalleonSettings::get_option('watermark_text',esc_html__( 'palleon.website', 'palleon' ));
        $watermarkFontFamily = PalleonSettings::get_option('watermark_font_family','Georgia, serif');
        $watermarkFontColor = PalleonSettings::get_option('watermark_font_color','#000000');
        $watermarkFontSize = PalleonSettings::get_option('watermark_font_size',40);
        $watermarkFontWeight = PalleonSettings::get_option('watermark_font_weight','normal');
        $watermarkStrokeColor = PalleonSettings::get_option('watermark_stroke_color','#FFFFFF');
        $watermarkFontStyle = PalleonSettings::get_option('watermark_font_style','normal');
        $watermarkLocation = PalleonSettings::get_option('watermark_location','bottom-right');
        ?>

        <script>
        /* <![CDATA[ */
        var palleonParams = {
            "baseURL": "<?php echo PALLEON_PLUGIN_URL; ?>",
            "sourceURL": "<?php echo PALLEON_SOURCE_URL; ?>",
            "suffix": "<?php echo esc_js($suffix); ?>",
            "version": "<?php echo esc_js($version); ?>",
            "userid": "<?php echo esc_js(get_current_user_id()); ?>",
            "ajaxurl":"<?php echo admin_url( 'admin-ajax.php' ); ?>",
            "nonce":"<?php echo wp_create_nonce('palleon-nonce'); ?>",
            "textbox":"<?php echo esc_html__('Enter Your Text Here', 'palleon'); ?>",
            "object":"<?php echo esc_html__('Object', 'palleon'); ?>",
            "loading":"<?php echo esc_html__('Loading...', 'palleon'); ?>",
            "loadmore":"<?php echo esc_html__('Load More', 'palleon'); ?>",
            "refresh":"<?php echo esc_html__('Refresh', 'palleon'); ?>",
            "wrong":"<?php echo esc_html__('Something went wrong.', 'palleon'); ?>",
            "settingsaved":"<?php echo esc_html__('Settings Saved!', 'palleon'); ?>",
            "saved":"<?php echo esc_html__('Saved!', 'palleon'); ?>",
            "imgsaved":"<?php echo esc_html__('Image is saved.', 'palleon'); ?>",
            "tempsaved":"<?php echo esc_html__('Template is saved.', 'palleon'); ?>",
            "freeDrawing":"<?php echo esc_html__('Free drawing', 'palleon'); ?>",
            "frame":"<?php echo esc_html__('Frame', 'palleon'); ?>",
            "image":"<?php echo esc_html__('Image', 'palleon'); ?>",
            "circle":"<?php echo esc_html__('Circle', 'palleon'); ?>",
            "square":"<?php echo esc_html__('Square', 'palleon'); ?>",
            "rectangle":"<?php echo esc_html__('Rectangle', 'palleon'); ?>",
            "triangle":"<?php echo esc_html__('Triangle', 'palleon'); ?>",
            "ellipse":"<?php echo esc_html__('Ellipse', 'palleon'); ?>",
            "trapezoid":"<?php echo esc_html__('Trapezoid', 'palleon'); ?>",
            "pentagon":"<?php echo esc_html__('Pentagon', 'palleon'); ?>",
            "octagon":"<?php echo esc_html__('Octagon', 'palleon'); ?>",
            "emerald":"<?php echo esc_html__('Emerald', 'palleon'); ?>",
            "star":"<?php echo esc_html__('Star', 'palleon'); ?>",
            "element":"<?php echo esc_html__('Element', 'palleon'); ?>",
            "customSvg":"<?php echo esc_html__('Custom SVG', 'palleon'); ?>",
            "success":"<?php echo esc_html__('Success', 'palleon'); ?>",
            "error":"<?php echo esc_html__('Error', 'palleon'); ?>",
            "uploaded":"<?php echo esc_html__('The file is uploaded.', 'palleon'); ?>",
            "deleted":"<?php echo esc_html__('The file is deleted.', 'palleon'); ?>",
            "favorited":"<?php echo esc_html__('The item has been favorited.', 'palleon'); ?>",
            "unfavorited":"<?php echo esc_html__('The item has been unfavorited.', 'palleon'); ?>",
            "delete":"<?php echo esc_html__('Delete', 'palleon'); ?>",
            "duplicate":"<?php echo esc_html__('Duplicate', 'palleon'); ?>",
            "showhide":"<?php echo esc_html__('Show/Hide', 'palleon'); ?>",
            "lockunlock":"<?php echo esc_html__('Lock/Unlock', 'palleon'); ?>",
            "enableGLFiltering": <?php echo esc_js($enableGLFiltering); ?>,
            "textureSize": <?php echo esc_js($textureSize); ?>,
            "nothing":"<?php echo esc_html__('Nothing Found.', 'palleon'); ?>",
            "text":"<?php echo esc_html__('Text', 'palleon'); ?>",
            "started":"<?php echo esc_html__('Editing started.', 'palleon'); ?>",
            "added":"<?php echo esc_html__('added.', 'palleon'); ?>",
            "removed":"<?php echo esc_html__('removed.', 'palleon'); ?>",
            "resized":"<?php echo esc_html__('resized.', 'palleon'); ?>",
            "edited":"<?php echo esc_html__('edited.', 'palleon'); ?>",
            "replaced":"<?php echo esc_html__('replaced.', 'palleon'); ?>",
            "rotated":"<?php echo esc_html__('rotated.', 'palleon'); ?>",
            "moved":"<?php echo esc_html__('moved.', 'palleon'); ?>",
            "scaled":"<?php echo esc_html__('scaled.', 'palleon'); ?>",
            "flipped":"<?php echo esc_html__('flipped.', 'palleon'); ?>",
            "cropped":"<?php echo esc_html__('cropped.', 'palleon'); ?>",
            "bg":"<?php echo esc_html__('Canvas', 'palleon'); ?>",
            "filter":"<?php echo esc_html__('filter', 'palleon'); ?>",
            "answer1":"<?php echo esc_html__('Are you sure you want to delete the template permanently?', 'palleon'); ?>",
            "answer2":"<?php echo esc_html__('Are you sure you want to delete the image permanently?', 'palleon'); ?>",
            "answer3":"<?php echo esc_html__('Are you sure you want clear the history?', 'palleon'); ?>",
            "answer4":"<?php echo esc_html__('Are you sure you want to delete the layers?', 'palleon'); ?>",
            "answer5":"<?php echo esc_html__('Are you sure you want to crop the image?', 'palleon'); ?>",
            "answer6":"<?php echo esc_html__('Are you sure you want to resize the image?', 'palleon'); ?>",
            "watermark": "<?php echo esc_js($watermark); ?>",
            "watermarkText": "<?php echo esc_js($watermarkText); ?>",
            "watermarkFontFamily": "<?php echo esc_js($watermarkFontFamily); ?>",
            "watermarkFontColor": "<?php echo esc_js($watermarkFontColor); ?>",
            "watermarkFontSize": "<?php echo esc_js($watermarkFontSize); ?>",
            "watermarkStrokeColor": "<?php echo esc_js($watermarkStrokeColor); ?>",
            "watermarkFontStyle": "<?php echo esc_js($watermarkFontStyle); ?>",
            "watermarkLocation": "<?php echo esc_js($watermarkLocation); ?>",
            "watermarkFontWeight": "<?php echo esc_js($watermarkFontWeight); ?>",
            "proInfoTitle": "<?php echo esc_js($proInfoTitle); ?>",
            "upgradeInfoTitle": "<?php echo esc_js($upgradeInfoTitle); ?>",
            "proInfo": '<?php echo addcslashes(wp_kses_post($proInfo), "'"); ?>',
            "upgradeInfo": '<?php echo addcslashes(wp_kses_post($upgradeInfo), "'"); ?>',
            "proCheck": "<?php echo esc_js($proCheck); ?>",
            "showRulers": "<?php echo esc_html__('Show rulers', 'palleon'); ?>",
            "hideRulers": "<?php echo esc_html__('Hide rulers', 'palleon'); ?>",
            "showGuides": "<?php echo esc_html__('Show guides', 'palleon'); ?>",
            "hideGuides": "<?php echo esc_html__('Hide guides', 'palleon'); ?>",
            "clearAllGuides": "<?php echo esc_html__('Clear all guides', 'palleon'); ?>",
            "printArea": "<?php echo esc_html__('Print Area', 'palleon'); ?>",
            "alphaColor": "<?php echo esc_js($alpha_color); ?>",
            "maxUploadSize":"<?php echo esc_html__('File is too big! Maximum image size allowed is', 'palleon'); ?>",
            "noDuplicate":"<?php echo esc_html__('You can not duplicate multiple objects. Please select single object to duplicate.', 'palleon'); ?>",
            "warning": "<?php echo esc_html__('Warning', 'palleon'); ?>",
            "qrCode": "<?php echo esc_html__('QR Code', 'palleon'); ?>",
        };
        /* ]]> */
        </script>
        <?php
    }

    /**
	 * Add custom edit links to the media library
	 */
    public function edit_links($actions, $post) {
        if (current_user_can('administrator') || current_user_can('editor') || (get_the_author_meta( 'ID' ) == get_current_user_id())) {
            if ( 'image/png' == $post->post_mime_type || 'image/jpeg' == $post->post_mime_type || 'image/jpg' == $post->post_mime_type || 'image/webp' == $post->post_mime_type || 'application/json' == $post->post_mime_type) {
                $actions['palleon'] = '<a href="' . admin_url('admin.php?page=palleon&attachment_id=' . $post->ID) . '">' . esc_html__('Edit with Palleon', 'palleon') . '</a>';
            }
        }
        return $actions;    
    }

    /**
	 * Upload Img to media library
	 */
    public function upload_img_to_library(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'palleon-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'palleon'));
        }
        $arr_img_ext = array('image/png', 'image/jpeg', 'image/jpg', 'image/webp');
        if (in_array($_FILES['file']['type'], $arr_img_ext)) {
            $upload = wp_upload_bits($_FILES["file"]["name"], null, file_get_contents($_FILES["file"]["tmp_name"]));
            $path_parts = pathinfo($_FILES["file"]["name"]);
            $post_title = $path_parts['filename'];
            $info = wp_check_filetype( $upload['file'] );
            $post = [
                'post_title' => $post_title,
                'guid' => $upload['url'],
                'post_mime_type' => $info['type']
            ];
            $post_id = wp_insert_attachment( $post, $upload['file'] );
            wp_update_attachment_metadata(
                $post_id,
                wp_generate_attachment_metadata( $post_id, $upload['file'] )
            );
        } else {
            wp_send_json_error(esc_html__('This file type is not allowed.', 'palleon'));
        }
    }

    /**
	 * Upload SVG to media library
	 */
    public function upload_svg_to_library(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'palleon-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'palleon'));
        }
        $arr_img_ext = array('image/svg+xml');
        if (in_array($_FILES['file']['type'], $arr_img_ext)) {
            $upload = wp_upload_bits($_FILES["file"]["name"], null, file_get_contents($_FILES["file"]["tmp_name"]));
            $path_parts = pathinfo($_FILES["file"]["name"]);
            $post_title = $path_parts['filename'];
            $info = wp_check_filetype( $upload['file'] );
            $post = [
                'post_title' => $post_title,
                'guid' => $upload['url'],
                'post_mime_type' => $info['type']
            ];
            $post_id = wp_insert_attachment( $post, $upload['file'] );
            wp_update_attachment_metadata(
                $post_id,
                wp_generate_attachment_metadata( $post_id, $upload['file'] )
            );
        } else {
            wp_send_json_error(esc_html__('This file type is not allowed.', 'palleon'));
        }
    }

    /**
	 * Save Image
	 */
    public function save_image(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'palleon-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'palleon'));
        }

        // Upload dir.
        $upload_dir  = wp_upload_dir();
        $upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;

        $img = '';

        if ($_POST['format'] == 'svg') {
            $img = str_replace('\"', '"', $_POST['url']);
            $img = str_replace("\'", "'", $img);
        } else {
            $img = str_replace( 'data:' . $_POST['type'] . ';base64,', '', $_POST['url'] );
            $img = str_replace( ' ', '+', $img );
            $img = base64_decode( $img );
        }
        
        $filename        = $_POST['filename'] . '.' . $_POST['format'];

        // Save the image in the uploads directory.
        $upload_file = file_put_contents( $upload_path . $filename, $img );

        $attachment = array(
            'post_mime_type' => $_POST['type'],
            'post_title'     => esc_html($_POST['name']),
            'post_content'   => '',
            'post_status'    => 'inherit',
            'guid'           => $upload_dir['url'] . '/' . basename( $filename )
        );

        if (!empty($_POST['id']) && $_POST['mode'] == 'image' && get_post_mime_type($_POST['id']) != 'application/json') {
            update_attached_file( $_POST['id'], $upload_dir['path'] . '/' . $filename );
            wp_update_attachment_metadata(
                $_POST['id'],
                wp_generate_attachment_metadata( $_POST['id'], $upload_dir['path'] . '/' . $filename )
            );
        } else {
            $attachment_id = wp_insert_attachment( $attachment, $upload_dir['path'] . '/' . $filename );
            wp_update_attachment_metadata(
                $attachment_id,
                wp_generate_attachment_metadata( $attachment_id, $upload_dir['path'] . '/' . $filename )
            );
        }

        wp_die();
    }

    /**
	 * Save JSON
	 */
    public function save_json(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'palleon-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'palleon'));
        }

        // Upload dir.
        $upload_dir  = wp_upload_dir();
        $upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;
        $filename = $_POST['filename'] . '.json';

        $json = stripslashes($_POST['json']);

        // Save the file in the uploads directory.
        $upload_file = file_put_contents( $upload_path . $filename, $json );

        $attachment = array(
            'post_mime_type' => 'application/json',
            'post_title'     => esc_html($_POST['name']),
            'post_content'   => '',
            'post_status'    => 'inherit',
            'guid'           => $upload_dir['url'] . '/' . basename( $filename )
        );

        if (!empty($_POST['id']) && $_POST['mode'] == 'json' && get_post_mime_type($_POST['id']) == 'application/json') {
            update_attached_file( $_POST['id'], $upload_dir['path'] . '/' . $filename );
            wp_update_attachment_metadata(
                $_POST['id'],
                wp_generate_attachment_metadata( $_POST['id'], $upload_dir['path'] . '/' . $filename )
            );
        } else {
            $attachment_id = wp_insert_attachment( $attachment, $upload_dir['path'] . '/' . $filename );
            wp_update_attachment_metadata(
                $attachment_id,
                wp_generate_attachment_metadata( $attachment_id, $upload_dir['path'] . '/' . $filename )
            );
        }
        wp_die();
    }

    /**
	 * Load My Templates
	 */
    public function load_my_templates(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'palleon-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'palleon'));
        }
        $args = json_decode( stripslashes( $_POST['query'] ), true );
        $my_temps = new WP_Query( $args );
        foreach ( $my_temps->posts as $temp ) {
            $temp_ID = $temp->ID;
            $temp_url = wp_get_attachment_url($temp_ID);
            $temp_title = get_the_title($temp_ID);
            ?>
            <li data-keyword="<?php echo esc_attr($temp_title); ?>">
                <div><?php echo esc_html($temp_title); ?></div>
                <div>
                    <button type="button" class="palleon-btn primary palleon-select-template" data-json="<?php echo esc_url($temp_url); ?>"><span class="material-icons">check</span><?php echo esc_html__('Select', 'palleon'); ?></button>
                    <button type="button" class="palleon-btn danger palleon-template-delete" data-target="<?php echo esc_attr($temp_ID); ?>"><span class="material-icons">clear</span><?php echo esc_html__('Delete', 'palleon'); ?></button>
                </div>
            </li>
            <?php
        }
        wp_die();
    }

    /**
	 * Load My Images
	 */
    public function load_my_images(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'palleon-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'palleon'));
        }
        $args = json_decode( stripslashes( $_POST['query'] ), true );
        $my_images = new WP_Query( $args );
        foreach ( $my_images->posts as $image ) { 
            $id = $image->ID;
            $thumb = wp_get_attachment_image_url($id, 'thumbnail', false);
            $full = wp_get_attachment_image_url($id, 'full', false);
            $title = get_the_title($id);
        ?>
        <div class="palleon-masonry-item" data-keyword="<?php echo esc_attr($title); ?>">
            <div class="palleon-library-delete" data-target="<?php echo esc_attr($id); ?>"><span class="material-icons">remove</span></div>
            <div class="palleon-masonry-item-inner">
                <div class="palleon-img-wrap">
                    <div class="palleon-img-loader"></div>
                    <img class="lazy" data-src="<?php echo esc_url($thumb); ?>" data-full="<?php echo esc_url($full); ?>" data-id="<?php echo esc_attr($id); ?>" data-filename="<?php echo esc_attr($title); ?>" alt="<?php echo esc_attr($title); ?>" />
                </div>
                <?php if (!empty($title)) { ?>
                <div class="palleon-masonry-item-desc">
                    <?php echo esc_html($title); ?>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php }
        wp_die();
    }

    /**
	 * Load All Images
	 */
    public function load_all_images(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'palleon-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'palleon'));
        }
        $args = json_decode( stripslashes( $_POST['query'] ), true );
        $all_images = new WP_Query( $args );
        foreach ( $all_images->posts as $image ) { 
            $id = $image->ID;
            $thumb = wp_get_attachment_image_url($id, 'thumbnail', false);
            $full = wp_get_attachment_image_url($id, 'full', false);
            $title = get_the_title($id);
        ?>
        <div class="palleon-masonry-item" data-keyword="<?php echo esc_attr($title); ?>">
            <div class="palleon-masonry-item-inner">
                <div class="palleon-img-wrap">
                    <div class="palleon-img-loader"></div>
                    <img class="lazy" data-src="<?php echo esc_url($thumb); ?>" data-full="<?php echo esc_url($full); ?>" data-id="<?php echo esc_attr($id); ?>" data-filename="<?php echo esc_attr($title); ?>" alt="<?php echo esc_attr($title); ?>" />
                </div>
                <?php if (!empty($title)) { ?>
                <div class="palleon-masonry-item-desc">
                    <?php echo esc_html($title); ?>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php }
        wp_die();
    }

    /**
	 * Delete File from media library
	 */
    public function delete_file_from_library(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'palleon-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'palleon'));
        }
        wp_delete_attachment($_POST['target'], true);
    }

    /**
	 * Favorite Frame
	 */
    public function favorite_frame(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'palleon-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'palleon'));
        }
        $output = '';
        $source_url = PALLEON_SOURCE_URL . 'frames/';
        $frames = get_user_meta(get_current_user_id(), 'palleon_frame_fav',true);
        if (empty($frames)) {
            add_user_meta(get_current_user_id(), 'palleon_frame_fav', array(), true);
            $frames = array();
        }
        if ($_POST['mode'] == 'remove') {
            $remove = array($_POST['frameid']);
            $frames = array_diff( $frames, $remove);
        } else {
            array_unshift($frames, $_POST['frameid']); 
        }
        update_user_meta(get_current_user_id(),'palleon_frame_fav', $frames, false);
        if ($frames == array()) {
            echo '<div class="notice notice-info"><h6>' . esc_html__( 'No favorites yet', 'palleon' ) . '</h6>' . esc_html__('Click the star icon on any frame, and you will see it here next time you visit.', 'palleon') . '</div>';
        } else {
            foreach($frames as $frame) { ?>
                <div class="palleon-frame" data-elsource="<?php echo esc_url($source_url . $frame . '.svg'); ?>">
                <div class="palleon-img-wrap">
                    <div class="palleon-img-loader"></div>
                    <img class="lazy" data-src="<?php echo esc_url($source_url . $frame . '.jpg'); ?>" />
                    <div class="frame-favorite"><button type="button" class="palleon-btn-simple star favorited" data-frameid="<?php echo esc_attr($frame); ?>"><span class="material-icons">star</span></button></div>
                </div>
                </div>
            <?php }
            }
        wp_die();
    }

    /**
	 * Favorite Element
	 */
    public function favorite_element(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'palleon-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'palleon'));
        }
        $output = '';
        $source_url = PALLEON_SOURCE_URL . 'elements/';
        $elements = get_user_meta(get_current_user_id(), 'palleon_element_fav',true);
        if (empty($elements)) {
            add_user_meta(get_current_user_id(), 'palleon_element_fav', array(), true);
            $elements = array();
        }
        if ($_POST['mode'] == 'remove') {
            $remove = array($_POST['elementid']);
            $elements = array_diff( $elements, $remove);
        } else {
            array_unshift($elements, $_POST['elementid']); 
        }
        update_user_meta(get_current_user_id(),'palleon_element_fav', $elements, false); 
        if ($elements == array()) {
            echo '<div class="notice notice-info"><h6>' . esc_html__( 'No favorites yet', 'palleon' ) . '</h6>' . esc_html__('Click the star icon on any element, and you will see it here next time you visit.', 'palleon') . '</div>';
        } else {
            foreach($elements as $element) { 
                if (substr( $element, 0, 4 ) === "http") {  
                    $url_query = parse_url($element, PHP_URL_QUERY);
                    if (!empty($url_query)) {
                        parse_str($url_query, $output);
                    } else {
                        $output = array("bg"=>"dark","loader"=>"yes");
                    }
                    ?>
                    <div class="palleon-element <?php echo esc_attr($output['bg']); ?>" data-elsource="<?php echo esc_url($element); ?>" data-loader="<?php echo esc_attr($output['loader']); ?>">
                    <img class="lazy" data-src="<?php echo esc_url($element); ?>" />
                    <div class="element-favorite"><button type="button" class="palleon-btn-simple star favorited" data-elementid="<?php echo esc_attr($element); ?>"><span class="material-icons">star</span></button></div>
                    </div>
                <?php } else {
                    $tag = strtok($element, '/');
                    $elementTags = palleon_get_element_tags();
                    ?>
                    <div class="palleon-element <?php echo esc_attr($elementTags[$tag][2]); ?>" data-elsource="<?php echo esc_url($source_url . $element . '.svg'); ?>" data-loader="<?php echo esc_attr($elementTags[$tag][3]); ?>">
                    <img class="lazy" data-src="<?php echo esc_url($source_url . $element . '.svg'); ?>" />
                    <div class="element-favorite"><button type="button" class="palleon-btn-simple star favorited" data-elementid="<?php echo esc_attr($element); ?>"><span class="material-icons">star</span></button></div>
                    </div>
                    <?php
                }
            }
        }
        wp_die();
    }

    /**
	 * Favorite Template
	 */
    public function favorite_template(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'palleon-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'palleon'));
        }
        $output = '';
        $templates = palleon_get_templates(false);
        $selected_templates = get_user_meta(get_current_user_id(), 'palleon_template_fav',true);
        if (empty($selected_templates)) {
            add_user_meta(get_current_user_id(), 'palleon_template_fav', array(), true);
            $selected_templates = array();
        }
        if ($_POST['mode'] == 'remove') {
            $remove = array($_POST['templateid']);
            $selected_templates = array_diff( $selected_templates, $remove);
        } else {
            array_unshift($selected_templates, $_POST['templateid']); 
        }
        update_user_meta(get_current_user_id(),'palleon_template_fav', $selected_templates, false); 
        if ($selected_templates == array()) {
            echo '<div class="notice notice-info"><h6>' . esc_html__( 'No favorites yet', 'palleon' ) . '</h6>' . esc_html__('Click the star icon on any template, and you will see it here next time you visit.', 'palleon') . '</div>';
        } else {
            foreach($templates as $template) { 
                if (in_array($template[0], $selected_templates)) { 
                    $template_version = 'free';
                    if (isset($template[5])) {
                        $template_version = $template[5]; 
                    }
                    ?>
                <div class="grid-item">
                    <?php if ($template_version == 'pro') { ?>
                    <div class="template-pro"><span class="material-icons">workspace_premium</span></div>
                    <?php } ?>
                    <div class="template-favorite"><button type="button" class="palleon-btn-simple star favorited" data-templateid="<?php echo esc_attr($template[0]); ?>"><span class="material-icons">star</span></button></div>
                    <div class="palleon-masonry-item-inner palleon-select-template" data-json="<?php echo esc_url($template[3]); ?>" data-version="<?php echo esc_attr($template_version); ?>">
                        <div class="palleon-img-wrap">
                            <div class="palleon-img-loader"></div>
                            <img class="lazy" data-src="<?php echo esc_url($template[2]); ?>" alt="<?php echo esc_attr($template[1]); ?>" />
                        </div>
                        <div class="palleon-masonry-item-desc">
                        <?php echo esc_html($template[1]); ?>
                        </div>
                    </div>
                </div>  
            <?php }
            }
        }
        wp_die();
    }

    /**
	 * Template Search
	 */
    public function template_search(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'palleon-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'palleon'));
        }
        $user_fav = get_user_meta(get_current_user_id(), 'palleon_template_fav',true);
        if (empty($user_fav)) {
            $user_fav = array();
        }
        $random =  PalleonSettings::get_option('template_order', 'random');
        $templates = palleon_templates();
        if ($random == 'random') {
            shuffle($templates);
        } else if ($random == 'new') {
            $templates = array_reverse($templates);
        }
        $keyword = $_POST['keyword'];
        $category = $_POST['category'];
        if (!empty($category) && $category != 'all') {
            $filteredArray = array();
            foreach($templates as $template) {
                if (in_array($category, $template[4])) {
                    $filteredArray[] = $template;
                }
            }
            $templates = $filteredArray;
        }
        if (!empty($keyword)) {
            $filteredArray = array();
            foreach($templates as $template) {
                if (stripos($template[1], $keyword) !== false) {
                    $filteredArray[] = $template;
                }
            }
            $templates = $filteredArray;
        }
        if ($templates == array()) {
            echo '<div class="notice notice-warning">' . esc_html__( 'No results found.', 'palleon' ) . '</div>';
        } else {
        foreach($templates as $template) { 
            $btn_class = '';
            $icon = 'star_border';
            if (in_array($template[0], $user_fav)) {
                $btn_class = 'favorited';
                $icon = 'star';
            }
            $template_version = 'free';
            if (isset($template[5])) {
                $template_version = $template[5]; 
            }
            ?>
            <div class="grid-item">
                <?php if ($template_version == 'pro') { ?>
                <div class="template-pro"><span class="material-icons">workspace_premium</span></div>
                <?php } ?>
                <div class="template-favorite"><button type="button" class="palleon-btn-simple star <?php echo esc_attr($btn_class); ?>" data-templateid="<?php echo esc_attr($template[0]); ?>"><span class="material-icons"><?php echo esc_html($icon); ?></span></button></div>
                <div class="palleon-masonry-item-inner palleon-select-template" data-json="<?php echo esc_url($template[3]); ?>" data-version="<?php echo esc_attr($template_version); ?>">
                    <div class="palleon-img-wrap">
                        <div class="palleon-img-loader"></div>
                        <img class="lazy" data-src="<?php echo esc_url($template[2]); ?>" title="<?php echo esc_attr($template[1]); ?>" />
                    </div>
                    <div class="palleon-masonry-item-desc">
                    <?php echo esc_html($template[1]); ?>
                    </div>
                </div>
            </div>
            <?php }}
        wp_die();
    }

    /**
	 * Save Preferences
	 */
    public function save_preferences(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'palleon-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'palleon'));
        }
        $preferences = $_POST['preferences'];
        $meta = get_user_meta(get_current_user_id(), 'palleon_preferences',true);
        if (empty($meta)) {
            add_user_meta(get_current_user_id(), 'palleon_preferences', $preferences, true);
        } else {
            update_user_meta(get_current_user_id(),'palleon_preferences', $preferences, false);
        }
        wp_die();
    }

    /* ---------------------------------------------------------
    INTEGRATIONS
    ----------------------------------------------------------- */

    /**
     * Paid Membership Pro
     */
    public function pmpro(){
        if (function_exists('pmpro_hasMembershipLevel')) {
            $levels = PalleonSettings::get_option('pmpro_levels','');
            $redirect = PalleonSettings::get_option('pmpro_redirect', get_home_url());
            if(!empty($levels) && !pmpro_hasMembershipLevel($levels) && !current_user_can('administrator')) {
                wp_redirect($redirect);
                exit;
            }
        }
    }

    /**
     * Simple Membership
     */
    public function swpm(){
        if (class_exists('SwpmMemberUtils')) {
            $member_level = SwpmMemberUtils::get_logged_in_members_level();
            $levels = PalleonSettings::get_option('swpm_levels','');
            $redirect = PalleonSettings::get_option('swpm_redirect', get_home_url());
            if(!empty($levels) && !in_array($member_level, $levels) && !current_user_can('administrator')) {
                wp_redirect($redirect);
                exit;
            }
        }
    }

    /**
     * Restrict Content PRO
     */
    public function rcpro(){
        if (function_exists('rcp_get_customer_by_user_id')) {
            $levels = PalleonSettings::get_option('rcpro_levels','');
            $redirect = PalleonSettings::get_option('rcpro_redirect', get_home_url());
            if (!empty($levels)) {
                $customer = rcp_get_customer_by_user_id(get_current_user_id());
                if (empty($customer) && !current_user_can('administrator')) {
                    wp_redirect($redirect);
                    exit;
                } else if (!current_user_can('administrator')) {
                    $memberships = $customer->get_memberships();
                    $memberlevels = array();
                    foreach ( $memberships as $membership ) {
                        $membership_level_id = $membership->get_object_id();
                        array_push($memberlevels, $membership_level_id);
                    }
                    $result = array_intersect($levels, $memberlevels);
                    if(empty($result)) {
                        wp_redirect($redirect);
                        exit;
                    }
                }
            }
        }
    }

    /* ---------------------------------------------------------
    HELPER FUNCTIONS
    ----------------------------------------------------------- */

    /**
     * PRO templates and elements
     */
    static function can_use_template(){
        $pro = true;

        if (function_exists('pmpro_hasMembershipLevel')) {
            $levels = PalleonSettings::get_option('pmpro_template_levels','');
            if(!empty($levels) && !pmpro_hasMembershipLevel($levels) && !current_user_can('administrator')) {
                $pro = false;
            } else {
                $pro = true;
            }
        }

        if (class_exists('SwpmMemberUtils')) {
            $member_level = SwpmMemberUtils::get_logged_in_members_level();
            $levels = PalleonSettings::get_option('swpm_template_levels','');
            if(!empty($levels) && !in_array($member_level, $levels) && !current_user_can('administrator')) {
                $pro = false;
            } else {
                $pro = true;
            }
        }

        if (function_exists('rcp_get_customer_by_user_id')) {
            $levels = PalleonSettings::get_option('rcpro_template_levels','');
            $redirect = PalleonSettings::get_option('rcpro_redirect', get_home_url());
            if (!empty($levels)) {
                $customer = rcp_get_customer_by_user_id(get_current_user_id());
                if (empty($customer) && !current_user_can('administrator')) {
                    $pro = false;
                } else if (!current_user_can('administrator')) {
                    $memberships = $customer->get_memberships();
                    $memberlevels = array();
                    foreach ( $memberships as $membership ) {
                        $membership_level_id = $membership->get_object_id();
                        array_push($memberlevels, $membership_level_id);
                    }
                    $result = array_intersect($levels, $memberlevels);
                    if(empty($result)) {
                        $pro = false;
                    } else {
                        $pro = true;
                    }
                }
            }
        }

        return $pro;
    }

    // Get websafe fonts
    static function get_websafe_fonts() {
        $fonts = apply_filters('palleononts',array(
            'Impact, Charcoal, sans-serif' => esc_html__( 'Impact', 'palleon' ),
            "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif" => esc_html__( 'Helvetica Neue', 'palleon' ),
            'Georgia, serif' => esc_html__( 'Georgia', 'palleon' ),
            "'Palatino Linotype', 'Book Antiqua', Palatino, serif" => esc_html__( 'Palatino Linotype', 'palleon' ),
            "'Times New Roman', Times, serif" => esc_html__( 'Times New Roman', 'palleon' ),
            'Arial, Helvetica, sans-serif' => esc_html__( 'Arial', 'palleon' ),
            "'Arial Black', Gadget, sans-serif" => esc_html__( 'Arial Black', 'palleon' ),
            "'Comic Sans MS', cursive, sans-serif" => esc_html__( 'Comic Sans', 'palleon' ),
            "'Lucida Sans Unicode', 'Lucida Grande', sans-serif" => esc_html__( 'Lucida Sans', 'palleon' ),
            'Tahoma, Geneva, sans-serif' => esc_html__( 'Tahoma', 'palleon' ),
            "'Trebuchet MS', Helvetica, sans-serif" => esc_html__( 'Trebuchet', 'palleon' ),
            'Verdana, Geneva, sans-serif' => esc_html__( 'Verdana', 'palleon' ),
            "'Courier New', Courier, monospace" => esc_html__( 'Courier New', 'palleon' ),
            "'Lucida Console', Monaco, monospace" => esc_html__( 'Lucida Console', 'palleon' )
        ));
        return $fonts;
    }

    // Get User Menu
    static function user_menu(){
        $menu = apply_filters('palleonUserMenu',array(
            array(esc_html__('Documentation', 'palleon'), 'https://themes.thememasters.club/plugin-docs/palleon/index.html', 'text_snippet'),
            array(esc_html__('Return to Dashboard', 'palleon'), get_dashboard_url(), 'wordpress'),
        ));
        if (current_user_can('manage_options')) {
            array_unshift($menu, array(esc_html__('Settings', 'palleon'), admin_url('admin.php?page=palleon_options'), 'settings'));
        }
        $i = 1;
        foreach($menu as $item) {
            echo '<li id="palleon-user-menu-item-' . $i . '"><a href="' . $item[1] . '"><span class="material-icons">' . $item[2] . '</span>' . $item[0] . '</a></li>';
            $i++;
        }
    }

    // Print Filters
    static function print_filters(){
        $filters = array(
            'grayscale' => esc_html__('Grayscale', 'palleon'),
            'sepia' => esc_html__('Sepia', 'palleon'),
            'blackwhite' => esc_html__('Black/White', 'palleon'),
            'brownie' => esc_html__('Brownie', 'palleon'),
            'vintage' => esc_html__('Vintage', 'palleon'),
            'kodachrome' => esc_html__('Kodachrome', 'palleon'),
            'technicolor' => esc_html__('Technicolor', 'palleon'),
            'polaroid' => esc_html__('Polaroid', 'palleon'),
            'shift' => esc_html__('Shift', 'palleon'),
            'invert' => esc_html__('Invert', 'palleon'),
            'sharpen' => esc_html__('Sharpen', 'palleon'),
            'emboss' => esc_html__('Emboss', 'palleon'),
            'sobelX' => esc_html__('SobelX', 'palleon'),
            'sobelY' => esc_html__('SobelY', 'palleon')
        );
        foreach($filters as $id => $name) {
            echo '<div class="grid-item">';
            echo '<input type="checkbox" name="palleon-filter" id="' . $id . '" autocomplete="off" class="input-hidden" />';
            echo '<label for="' . $id . '">';
            echo '<img class="lazy" data-src="'. PALLEON_PLUGIN_URL . 'assets/filters/' . $id . '.png" />';
            echo '<span>' . $name . '</span>';
            echo '</label>';
            echo '</div>';
        }
    }

    // Get User Meta
    static function get_user_option($key, $user_id, $default){
        $options = get_user_meta($user_id, 'palleon_preferences',true);
        if (!empty($options)) {
            $options = json_decode($options, true);
            foreach($options as $option => $value) {
                if ($option == $key) {
                    return $value;
                }
            }
        } else {
            return $default;
        }
    }
}

/**
 * Returns the main instance of the class
 */
function Palleon() {  
	return Palleon::instance();
}
// Global for backwards compatibility
$GLOBALS['Palleon'] = Palleon();