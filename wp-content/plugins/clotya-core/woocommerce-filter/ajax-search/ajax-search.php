<?php
/*************************************************
## Ajax Search Scripts
*************************************************/ 
function clotya_ajax_search_scripts() {
	wp_enqueue_style( 'clotya-ajax-search',    plugins_url( 'css/ajax-search.css', __FILE__ ), false, '1.0');
	wp_enqueue_script( 'clotya-ajax-search',    plugins_url( 'js/ajax-search.js', __FILE__ ), false, '1.0');
	wp_localize_script( 'clotya-ajax-search', 'clotyasearch', array(
		'ajaxurl' => esc_url(admin_url( 'admin-ajax.php' )),
	));
}
add_action( 'wp_enqueue_scripts', 'clotya_ajax_search_scripts' );


/*************************************************
## Ajax Login CallBack
*************************************************/ 
add_action( 'wp_ajax_nopriv_ajax_search', 'clotya_ajax_search_callback' );
add_action( 'wp_ajax_ajax_search', 'clotya_ajax_search_callback' );
function clotya_ajax_search_callback() {
	$keyword        = esc_html( $_POST['keyword'] );

	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		's'              => $keyword,
		'posts_per_page' => 7,
	);
	
	if($_POST['selected_cat'] != null){
		$args['tax_query'][] = array(
			'taxonomy' 	=> 'product_cat',
			'field' 	=> 'slug',
			'terms' 	=> $_POST['selected_cat'],
		);
	}

	$args = new WP_Query( $args );

	if ( $args->have_posts() ) {
		echo '<ul>';

		while ( $args->have_posts() ) : $args->the_post();
			$product = wc_get_product( get_the_ID() );
			
			$title = $product->get_name();
			$price = $product->get_price_html();

			if ( ! $product || ( 'trash' === $product->get_status() ) ) {
				continue;
			}
			
			echo '<li>';
			echo '<div class="search-img">';
			echo $product->get_image('thumbnail');
			echo '</div>';
			echo '<div class="search-content">';
			echo '<h1 class="product-title"><a href="'.get_permalink().'" title="'.the_title_attribute( 'echo=0' ).'">'.get_the_title().'</a></h1>';
			echo '<span class="price">'.$price.'</span>';
			echo '</div>';
			echo '</li>';
		endwhile;
		
		if($args->found_posts > 7){
			$searchall = add_query_arg(
				array(
					's' => $keyword, 
					'post_type' => 'product'
				),
				site_url() 
			);
					
			echo '<li class="search-more">';
			echo '<a href="'.esc_url($searchall).'"><span>'.esc_html__('See all products...','clotya-core').'</span> <span>('.esc_html($args->found_posts).')</span></a>';
			echo '</li>';
		}
		
		echo '</ul>';
		wp_reset_postdata();
	} else {
		echo '<ul><li><span>'.sprintf(esc_html__( 'No results found for "%s"', 'clotya-core' ), esc_html( $keyword )).'</span></li></ul>';
	}

	wp_die();

}