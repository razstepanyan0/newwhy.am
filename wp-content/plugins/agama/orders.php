<?php
class AgamaOrders {
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
        add_action('add_meta_boxes', array($this, 'info'));
        add_action('add_meta_boxes', array($this, 'attachments'));
        add_filter('post_row_actions', array($this, 'remove_row_actions'), 10, 1 );
        add_filter('manage_edit-agamaorders_columns', array($this, 'admin_column'), 5);
        add_filter('manage_edit-agamaorders_sortable_columns', array($this, 'admin_column_sortable') );
        add_filter('request', array($this, 'admin_column_orderby') );
        add_action('manage_posts_custom_column', array($this, 'admin_row'), 5, 2);
    }

    /**
	 * Register Post Type
	 */
    public function register_post_type() {
        $labels = array(
            'name'              => esc_html__( 'AG Orders', 'agama' ),
            'singular_name'     => esc_html__( 'Order', 'agama' ),
            'add_new'           => esc_html__( 'Add new order', 'agama' ),
            'add_new_item'      => esc_html__( 'Add new order', 'agama' ),
            'edit_item'         => esc_html__( 'Edit order', 'agama' ),
            'new_item'          => esc_html__( 'New order', 'agama' ),
            'view_item'         => esc_html__( 'View order', 'agama' ),
            'search_items'      => esc_html__( 'Search orders', 'agama' ),
            'not_found'         => esc_html__( 'No order found', 'agama' ),
            'not_found_in_trash'=> esc_html__( 'No order found in trash', 'agama' ),
            'parent_item_colon' => esc_html__( 'Parent order:', 'agama' ),
            'menu_name'         => esc_html__( 'AG Orders', 'agama' )
        );
    
        $taxonomies = array();
     
        $supports = array('title');
     
        $post_type_args = array(
            'labels'            => $labels,
            'singular_label'    => esc_html__('AG Order', 'agama'),
            'public'            => false,
            'exclude_from_search' => true,
            'show_ui'           => true,
            'show_in_nav_menus' => false,
            'publicly_queryable'=> true,
            'query_var'         => true,
            'capability_type'   => 'post',
            'capabilities' => array(
                'create_posts' => false,
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
            'menu_icon'         => 'dashicons-cart',
            'taxonomies'        => $taxonomies
        );
        register_post_type('agamaorders',$post_type_args);	
    }

    /**
	 * Attachments
	 */
    public function info( $meta_boxes ) {
        add_meta_box( 'agama_order_info', esc_html__( 'Order Details', 'agama' ), function( $post ) {
            if (isset($_GET['post']) && !empty(isset($_GET['post']))) {
                $order_id = get_post_meta( $_GET['post'], 'agama_cmb2_order_number', true );
                $user_id = get_post_meta( $_GET['post'], 'agama_cmb2_user_id', true );
                $product_id = get_post_meta( $_GET['post'], 'agama_cmb2_product_id', true );
                $variation = '';
                $variation_id = get_post_meta( $_GET['post'], 'agama_cmb2_variation_id', true );
                if (!empty($variation_id)) {
                    $variation = wc_get_product($variation_id);
                }
                $additional_fee = get_post_meta( $_GET['post'], 'agama_cmb2_additional_fee', true);
                if (empty($additional_fee)) {
                    $additional_fee = 0;
                }
                if (empty($order_id)) {
                    echo '<div class="agama-order-info">';
                    echo '<div class="agama-order-info-list">';
                    echo '<div><span>' . esc_html__('Status:', 'agama') . '</span> <strong>' . esc_html__('Not ordered yet', 'agama') . '</strong></div>';
                    if (!empty($product_id)) {
                        echo '<div><span>' . esc_html__('Product ID:', 'agama') . '</span> <strong><a href="' . get_the_permalink($product_id) . '" target="_blank">' . esc_html($product_id) . '</a></strong></div>';
                    }
                    if (!empty($variation_id)) {
                        echo '<div><span>' . esc_html__('Variation:', 'agama') . '</span><strong>';
                        foreach ($variation->get_variation_attributes() as $attr) {
                            echo '<span class="agama-attr-label">' . esc_html(wc_attribute_label($attr)) . '</span>';
                        }
                        echo '</strong></div>';
                    }
                    if (!empty($user_id)) {
                        $user_info = get_userdata($user_id);
                        echo '<div><span>' . esc_html__('User name:', 'agama') . '</span> <strong>' . esc_html($user_info->display_name) . '</strong></div>';
                        echo '<div><span>' . esc_html__('User email:', 'agama') . '</span> <strong><a href="mailto:' . esc_attr($user_info->user_email) . '">' . $user_info->user_email . '</a></strong></div>';
                    } else {
                        echo '<div><span>' . esc_html__('User:', 'agama') . '</span> <strong>' . esc_html__('Anonymous', 'agama') . '</strong></div>';
                    }
                    echo '</div>';
                    echo '</div>';
                } else {
                    $order = wc_get_order( $order_id );
                    $edit_order_link = get_edit_post_link($order_id);
                    $edit_product_link = get_edit_post_link($product_id);
                    $thumb = get_the_post_thumbnail_url($product_id, 'large'); 
                    if (!empty($variation)) {
                        $thumb_id = $variation->get_image_id();
                        $thumb_array = wp_get_attachment_image_src($thumb_id, 'large');
                        $thumb = $thumb_array[0];
                    }
                    echo '<div class="agama-order-info-thumb"><img src="' . $thumb . '" /></div>';
                    echo '<div class="agama-order-info">';
                    echo '<div class="agama-order-info-list">';
                    if (!empty($product_id)) {
                        echo '<div><span>' . esc_html__('Product:', 'agama') . '</span> <strong><a href="' . esc_url($edit_product_link) . '" target="_blank">' . esc_html(get_the_title($product_id)) . '</a></strong></div>';
                    }
                    if (!empty($variation_id) && !empty($variation)) {
                        echo '<div><span>' . esc_html__('Variation:', 'agama') . '</span> <strong>';
                        foreach ($variation->get_variation_attributes() as $attr) {
                            echo ' <span class="agama-attr-label">' . esc_html(wc_attribute_label($attr)) . '</span>';
                        }
                        echo '</strong></div>';
                    }
                    echo '<div><span>' . esc_html__('Order ID:', 'agama') . '</span> <strong>#' . $order_id . '</strong></div>';
                    echo '<div><span>' . esc_html__('Order Status:', 'agama') . '</span> <strong>' . wc_get_order_status_name($order->get_status()) . '</strong></div>';
                    echo '</div>';
                    echo '<div class="agama-order-info-view"><a class="button button-large" href="' . esc_url($edit_order_link) . '" target="_blank">' . esc_html__('View Order Details', 'agama') . '</a></div>';
                    echo '</div>';
                }
            }
        }, 'agamaorders', 'side','high' );
    }

    /**
	 * Attachments
	 */
    public function attachments( $meta_boxes ) {
        add_meta_box( 'agama_order_templates', esc_html__( 'Palleon Templates', 'agama' ), function( $post ) {
            $jsonArgs = array(
                'post_type' => 'attachment',
                'post_mime_type' => 'application/json',
                'post_parent' => $post->ID
            );
            echo '<div class="agama-attachments">';
            foreach( get_posts( $jsonArgs ) as $json) {
                echo '<div class="agama-attachment">';
                echo '<h4>' . esc_html(get_the_title($json->ID)) . '</h4>';
                echo '<div class="agama-attachment-edit"><a class="button button-primary button-large" href="' . admin_url('admin.php?page=palleon&attachment_id=' . $json->ID) . '" target="_blank">' . esc_html__( 'Edit & Download With Palleon', 'agama' ) . '</a></div>';
                echo '</div>';
            }
            echo '</div>';
        }, 'agamaorders' );



        add_meta_box( 'agama_order_images', esc_html__( 'Images', 'agama' ), function( $post ) {
            $imgArgs = array(
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'post_parent' => $post->ID
            );
            echo '<div class="agama-attachments">';
            foreach( get_posts($imgArgs) as $img) {
                echo '<div class="agama-attachment">';
                echo '<h4>' . esc_html(get_the_title($img->ID)) . '</h4>';
                echo '<div class="agama-attachment-view"><a class="button button-large" href="' . get_edit_post_link($img->ID) . '" target="_blank">' . esc_html__('View Image', 'agama') . '</a></div>';
                echo '<div class="agama-attachment-download"><a class="button button-large" href="' . wp_get_attachment_image_url($img->ID, 'full') . '" download>' . esc_html__('Download Image', 'agama') . '</a></div>';
                echo '</div>';
            }
            echo '</div>';
        }, 'agamaorders' );
    }

	/**
	 * Add custom admin table row
	 */
    public function admin_row($column_name, $post_id){
        if (get_post_type($post_id) == 'agamaorders') {
            $order_id = get_post_meta( get_the_ID(), 'agama_cmb2_order_number', true );
            $product_id = get_post_meta( get_the_ID(), 'agama_cmb2_product_id', true );
            if($column_name === 'custom_order_id'){
                if (!empty($order_id)) {
                    $edit_link = get_edit_post_link($order_id);
                    echo '<a href="' . $edit_link . '" target="_blank">' . $order_id . '</a>';
                } else {
                    echo '<span class="dashicons dashicons-minus"></span>';
                }
            }   
            if($column_name === 'custom_order_status'){
                if (empty($order_id)) {
                    echo '<mark class="agama-order-status status-not-completed tips"><span>' . esc_html__('Not ordered yet', 'agama') . '</span></mark>';
                } else {
                    $order = wc_get_order( $order_id );
                    echo '<mark class="agama-order-status status-' . esc_html($order->get_status()) . ' tips"><span>' . esc_html(wc_get_order_status_name($order->get_status())) . '</span></mark>';
                }
            }
            if($column_name === 'custom_order_product'){
                $thumb = get_the_post_thumbnail_url( $product_id, 'thumbnail');
                $edit_product_link = get_edit_post_link($product_id);
                $variation_id = get_post_meta( get_the_ID(), 'agama_cmb2_variation_id', true );
                if (!empty($variation_id)) {
                    $variation = wc_get_product($variation_id);
                    if ($variation) {
                        $thumb_id = $variation->get_image_id();
                        $thumb_array = wp_get_attachment_image_src($thumb_id, 'thumbnail');
                        $thumb = $thumb_array[0];
                    }      
                }
                if (!empty($thumb)) {
                    echo '<a href="' . esc_url($edit_product_link) . '" target="_blank" title="' . esc_attr(get_the_title($product_id)) . '"><img src="' . $thumb . '" /></a>';
                }
            }
            if($column_name == 'custom_date'){
                echo human_time_diff( get_the_time('U', $post_id), current_time( 'timestamp' ) ).' '. esc_html__( 'ago', 'agama');
            }
            if($column_name === 'ID'){
                echo get_the_ID();
            }
        }
    }

	/**
	 * Add custom admin table column
	 */
    public function admin_column($defaults){
        $new = array();
        unset( $defaults['date'] );
        $defaults['custom_date'] = esc_html__( 'Date', 'agama' );
        $defaults['custom_order_id'] = esc_html__( 'Order', 'agama' );
        $defaults['custom_order_status'] = esc_html__( 'Status', 'agama' );
		$defaults['custom_order_product'] = esc_html__( 'Product', 'agama' );
        $defaults['ID'] = esc_html__( 'Design ID', 'agama' );

        $customOrder = array('cb', 'title', 'ID', 'custom_date', 'custom_order_id', 'custom_order_status', 'custom_order_product');
        foreach ($customOrder as $colname) {
            $new[$colname] = $defaults[$colname];    
        }
        return $new;
    }

    /**
	 * Custom sortable columns
	 */
    public function admin_column_sortable( $columns ) {
        $columns['custom_date'] = 'custom_date';
        return $columns;
    }
    
    /**
	 * Custom sortable columns orderby
	 */
    public function admin_column_orderby( $vars ) {
        if ( isset( $vars['orderby'] ) && 'custom_date' == $vars['orderby'] ) {
            $vars = array_merge( $vars, array(
                'orderby' => 'date'
            ) );
        }
        return $vars;
    }

    /**
	 * Remove row actions
	 */
    public function remove_row_actions( $actions ) {
        if( get_post_type() === 'agamaorders' ) {
            unset( $actions['view'] );
        }
        return $actions;
    }
}

/**
 * Returns the main instance of the class
 */
function AgamaOrders() {  
	return AgamaOrders::instance();
}
// Global for backwards compatibility
$GLOBALS['AgamaOrders'] = AgamaOrders();