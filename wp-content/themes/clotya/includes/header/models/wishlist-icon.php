<?php
if ( ! function_exists( 'clotya_wishlist_icon' ) ) {
	function clotya_wishlist_icon(){
	?>

	<?php $wishlistheader = get_theme_mod('clotya_header_wishlist',0); ?>
	<?php if($wishlistheader == 1){ ?>

		<?php if ( function_exists( 'tinv_url_wishlist_default' ) ) { ?>
            <div class="header-button wishlist-icon">
				<div class="button-icon">
					<a href="<?php echo tinv_url_wishlist_default(); ?>" class="wishlist-button">
						<i class="klbth-icon-heart"></i>
					</a>
					<div class="count"><?php echo do_shortcode('[ti_wishlist_products_counter]'); ?></div>
				</div><!-- button-icon -->
            </div><!-- header-button -->
		<?php } ?>

	<?php } ?>
	
	<?php 
	
	}
}