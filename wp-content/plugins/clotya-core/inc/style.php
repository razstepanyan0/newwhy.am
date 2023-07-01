<?php 
/*************************************************
## Clotya Typography
*************************************************/

function clotya_custom_styling() { ?>

<style type="text/css">

<?php if (get_theme_mod( 'clotya_mobile_sticky_header',0 ) == 1) { ?>
@media(max-width:64rem){
	header.sticky-header {
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
	}	
}
<?php } ?>

<?php if (get_theme_mod( 'clotya_sticky_header',0 ) == 1) { ?>
.sticky-header .header-main {
    position: fixed;
    left: 0;
    right: 0;
    top: 0;
    z-index: 9;
    border-bottom: 1px solid #e3e4e6;
    padding-top: 15px;
    padding-bottom: 15px;
}
<?php } ?>

<?php if (get_theme_mod( 'clotya_mobile_single_sticky_cart',0 ) == 1) { ?>
@media(max-width:64rem){
	.single .product-type-simple form.cart {
	    position: fixed;
	    bottom: 0;
	    right: 0;
	    z-index: 9999;
	    background: #fff;
	    margin-bottom: 0;
	    padding: 15px;
	    -webkit-box-shadow: 0 -2px 5px rgb(0 0 0 / 7%);
	    box-shadow: 0 -2px 5px rgb(0 0 0 / 7%);
	    justify-content: space-between;
		width: 100%;
	}

	.single .woocommerce-variation-add-to-cart {
	    display: -webkit-box;
	    display: -ms-flexbox;
	    display: flex;
	    position: fixed;
	    bottom: 0;
	    right: 0;
	    z-index: 9999;
	    background: #fff;
	    margin-bottom: 0;
	    padding: 15px;
	    -webkit-box-shadow: 0 -2px 5px rgb(0 0 0 / 7%);
	    box-shadow: 0 -2px 5px rgb(0 0 0 / 7%);
	    justify-content: space-between;
    	width: 100%;
		flex-wrap: wrap;
		width: 100%; 
	}
}
<?php } ?>

<?php if (get_theme_mod( 'clotya_main_color' )) { ?>
:root {
    --color-primary: <?php echo esc_attr(get_theme_mod( 'clotya_main_color' ) ); ?>;
}
<?php } ?>

<?php if (get_theme_mod( 'clotya_color_danger' )) { ?>
:root {
	--color-danger: <?php echo esc_attr(get_theme_mod( 'clotya_color_danger' ) ); ?>;
}
<?php } ?>

<?php if (get_theme_mod( 'clotya_color_success' )) { ?>
:root {
	--color-success: <?php echo esc_attr(get_theme_mod( 'clotya_color_success' ) ); ?>;
}
<?php } ?>

<?php if (get_theme_mod( 'clotya_color_success_dark' )) { ?>
:root {
	--color-success-dark: <?php echo esc_attr(get_theme_mod( 'clotya_color_success_dark' ) ); ?>;
}
<?php } ?>

<?php if (get_theme_mod( 'clotya_color_success_light' )) { ?>
:root {
	--color-success-light: <?php echo esc_attr(get_theme_mod( 'clotya_color_success_light' ) ); ?>;
}
<?php } ?>

<?php if (get_theme_mod( 'clotya_color_warning' )) { ?>
:root {
	--color-warning: <?php echo esc_attr(get_theme_mod( 'clotya_color_warning' ) ); ?>;
}
<?php } ?>

<?php if (get_theme_mod( 'clotya_color_warning_light' )) { ?>
:root {
	--color-warning-light: <?php echo esc_attr(get_theme_mod( 'clotya_color_warning_light' ) ); ?>;
}
<?php } ?>

<?php if(function_exists('dokan')){ ?>

	input[type='submit'].dokan-btn-theme,
	a.dokan-btn-theme,
	.dokan-btn-theme {
		background-color: <?php echo esc_attr(get_theme_mod( 'clotya_main_color' ) ); ?>;
		border-color: <?php echo esc_attr(get_theme_mod( 'clotya_main_color' ) ); ?>;
	}
	input[type='submit'].dokan-btn-theme .badge,
	a.dokan-btn-theme .badge,
	.dokan-btn-theme .badge {
		color: <?php echo esc_attr(get_theme_mod( 'clotya_main_color' ) ); ?>;
	}
	.dokan-announcement-uread {
		border: 1px solid <?php echo esc_attr(get_theme_mod( 'clotya_main_color' ) ); ?> !important;
	}
	.dokan-announcement-uread .dokan-annnouncement-date {
		background-color: <?php echo esc_attr(get_theme_mod( 'clotya_main_color' ) ); ?> !important;
	}
	.dokan-announcement-bg-uread {
		background-color: <?php echo esc_attr(get_theme_mod( 'clotya_main_color' ) ); ?>;
	}
	.dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li:hover {
		background: <?php echo esc_attr(get_theme_mod( 'clotya_main_color' ) ); ?>;
	}
	.dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li.dokan-common-links a:hover {
		background: <?php echo esc_attr(get_theme_mod( 'clotya_main_color' ) ); ?>;
	}
	.dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li.active {
		background: <?php echo esc_attr(get_theme_mod( 'clotya_main_color' ) ); ?>;
	}
	.dokan-product-listing .dokan-product-listing-area table.product-listing-table td.post-status label.pending {
		background: <?php echo esc_attr(get_theme_mod( 'clotya_main_color' ) ); ?>;
	}
	.product-edit-container .dokan-product-title-alert,
	.product-edit-container .dokan-product-cat-alert {
		color: <?php echo esc_attr(get_theme_mod( 'clotya_main_color' ) ); ?>;
	}
	.product-edit-container .dokan-product-less-price-alert {
		color: <?php echo esc_attr(get_theme_mod( 'clotya_main_color' ) ); ?>;
	}
	.dokan-store-wrap {
	    margin-top: 3.5rem;
	}
	.dokan-widget-area ul {
	    list-style: none;
	    padding-left: 0;
	    font-size: .875rem;
	    font-weight: 400;
	}
	.dokan-widget-area ul li a {
	    text-decoration: none;
	    color: var(--color-text-lighter);
	    margin-bottom: .625rem;
	    display: inline-block;
	}
	form.dokan-store-products-ordeby:before, 
	form.dokan-store-products-ordeby:after {
		content: '';
		display: table;
		clear: both;
	}
	.dokan-store-products-filter-area .orderby-search {
	    width: auto;
	}
	input.search-store-products.dokan-btn-theme {
	    border-top-left-radius: 0;
	    border-bottom-left-radius: 0;
	}
	.dokan-pagination-container .dokan-pagination li a {
	    display: -webkit-inline-box;
	    display: -ms-inline-flexbox;
	    display: inline-flex;
	    -webkit-box-align: center;
	    -ms-flex-align: center;
	    align-items: center;
	    -webkit-box-pack: center;
	    -ms-flex-pack: center;
	    justify-content: center;
	    font-size: .875rem;
	    font-weight: 600;
	    width: 2.25rem;
	    height: 2.25rem;
	    border-radius: 50%;
	    color: currentColor;
	    text-decoration: none;
	    border: none;
	}
	.dokan-pagination-container .dokan-pagination li.active a {
	    color: #fff;
	    background-color: var(--color-secondary) !important;
	}
	.dokan-pagination-container .dokan-pagination li:last-child a, 
	.dokan-pagination-container .dokan-pagination li:first-child a {
	    width: auto;
	}

	.vendor-customer-registration label {
	    margin-right: 10px;
	}

	.woocommerce-mini-cart dl.variation {
	    display: none;
	}

	.product-name dl.variation {
	    display: none;
	}

	.seller-rating .star-rating span.width + span {
	    display: none;
	}
	
	.seller-rating .star-rating {width: 70px;display: block;}

<?php } ?>

<?php if (function_exists('get_wcmp_vendor_settings') && is_user_logged_in()) {
	if(is_vendor_dashboard()){	
} ?>

.woosc-popup, div#woosc-area {
    display: none;
}
	
.select-location {
    display: none;
}
	
<?php } ?>

.site-header.header-type1 .global-notification  {
	background-color: <?php echo esc_attr(get_theme_mod( 'clotya_header1_top1_bg_color' ) ); ?>;
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header1_top1_notification_color' ) ); ?>;
}

.site-header.header-type1 .header-topbar.border-full  {
	background-color: <?php echo esc_attr(get_theme_mod( 'clotya_header1_top2_bg_color' ) ); ?>;
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header1_top2_notification_color' ) ); ?>;
	border-bottom-color: <?php echo esc_attr(get_theme_mod( 'clotya_header1_top2_border_color' ) ); ?>;
}

.site-header.header-type1 .header-main,
.site-header.header-type1 .header-mobile{
	background-color: <?php echo esc_attr(get_theme_mod( 'clotya_header1_main_bg_color' ) ); ?>;
	border-bottom-color: <?php echo esc_attr(get_theme_mod( 'clotya_header1_main_border_color' ) ); ?>;
}

.site-header.header-type1 .site-nav.primary .menu > li > a,
.site-header.header-type1 .site-nav.primary .menu .sub-menu li a {
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header1_main_font_color' ) ); ?>;
}

.site-header.header-type1 .site-nav.primary .menu > li > a:hover,
.site-header.header-type1 .site-nav.primary .menu .sub-menu li a:hover {
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header1_main_font_hvrcolor' ) ); ?>;
}

.site-header.header-type1 .site-nav.horizontal > .menu .mega-menu > .sub-menu > li > a{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header1_main_submenu_font_color' ) ); ?>;
}

.site-header.header-type1 .header-button i{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header1_main_icon_color' ) ); ?>;
}

.site-header.header-type1 .header-button i:hover{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header1_main_icon_hvrcolor' ) ); ?>;
}

.site-header.header-type2 .global-notification  {
	background-color: <?php echo esc_attr(get_theme_mod( 'clotya_header2_top1_bg_color' ) ); ?>;
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header2_top1_notification_color' ) ); ?>;
}

.site-header.header-type2 .header-topbar.border-full  {
	background-color: <?php echo esc_attr(get_theme_mod( 'clotya_header2_top2_bg_color' ) ); ?>;
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header2_top2_notification_color' ) ); ?>;
	border-bottom-color: <?php echo esc_attr(get_theme_mod( 'clotya_header2_top2_border_color' ) ); ?>;
}

.site-header.header-type2 .header-main,
.site-header.header-type2 .header-row.header-navbar,
.site-header.header-type2 .header-mobile {
	background-color: <?php echo esc_attr(get_theme_mod( 'clotya_header2_main_bg_color' ) ); ?>;
	border-bottom-color: <?php echo esc_attr(get_theme_mod( 'clotya_header2_main_border_color' ) ); ?>;
	
}

.site-header.header-type2 .site-departments .dropdown-toggle{
	border-right-color: <?php echo esc_attr(get_theme_mod( 'clotya_header2_main_border_color' ) ); ?>;
}

.site-header.header-type2 .site-nav.primary .menu > li > a,
.site-header.header-type2 .site-nav.primary .menu .sub-menu li a,
.site-header.header-type2 .site-departments {
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header2_main_font_color' ) ); ?>;
}

.site-header.header-type2 .site-nav.primary .menu > li > a:hover,
.site-header.header-type2 .site-nav.primary .menu .sub-menu li a:hover,
.site-header.header-type2 .site-departments:hover {
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header2_main_font_hvrcolor' ) ); ?>;
}

.site-header.header-type2 .site-nav.horizontal > .menu .mega-menu > .sub-menu > li > a{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header2_main_submenu_font_color' ) ); ?>;
}

.site-header.header-type2 .header-button i{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header2_main_icon_color' ) ); ?>;
}

.site-header.header-type2 .header-button i:hover{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header2_main_icon_hvrcolor' ) ); ?>;
}

.site-header.header-type2 .site-departments .departments-menu .menu > .menu-item > a,
.site-header.header-type2 .site-departments .departments-menu .menu .sub-menu li a{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header2_category_font_color' ) ); ?>;
}

.site-header.header-type2 .site-departments .departments-menu .menu > .menu-item > a:hover,
.site-header.header-type2 .site-departments .departments-menu .menu .sub-menu li a:hover{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header2_category_font_hvrcolor' ) ); ?>;
}

.site-header.header-type3 .global-notification{
	background-color: <?php echo esc_attr(get_theme_mod( 'clotya_header3_top1_bg_color' ) ); ?>;
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header3_top1_notification_color' ) ); ?>;
}

.site-header.header-type3 .header-mobile{
	background-color: <?php echo esc_attr(get_theme_mod( 'clotya_header3_main_bg_color' ) ); ?>;
}

.site-header.transparent.header-type3 .header-main{
	background-color: <?php echo esc_attr(get_theme_mod( 'clotya_header3_main_bg_color' ) ); ?>;
	border-color: <?php echo esc_attr(get_theme_mod( 'clotya_header3_main_border_color' ) ); ?>;
}

.site-header.transparent.header-type3 .header-main .header-wrapper{
	border-color: <?php echo esc_attr(get_theme_mod( 'clotya_header3_main_border_color' ) ); ?>;
}

.site-header.header-type3 .site-nav.primary .menu > li > a {
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header3_main_font_color' ) ); ?>;
}

.site-header.header-type3 .site-nav.primary .menu > li > a:hover {
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header3_main_font_hvrcolor' ) ); ?>;
}

.site-header.header-type3 .site-nav.horizontal > .menu .mega-menu > .sub-menu > li > a{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header3_main_submenu_font_color' ) ); ?>;
}

.site-header.header-type3 .site-nav.primary .menu .sub-menu li a{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header3_main_submenu_subtitle_font_color' ) ); ?>;
}

.site-header.header-type3 .header-button i{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header3_main_icon_color' ) ); ?>;
}

.site-header.header-type3 .header-button i:hover{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header3_main_icon_hvrcolor' ) ); ?>;
}

.site-header.header-type4 .global-notification  {
	background-color: <?php echo esc_attr(get_theme_mod( 'clotya_header4_top1_bg_color' ) ); ?>;
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header4_top1_notification_color' ) ); ?>;
}

.site-header.header-type4 .header-topbar.border-full  {
	background-color: <?php echo esc_attr(get_theme_mod( 'clotya_header4_top2_bg_color' ) ); ?>;
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header4_top2_notification_color' ) ); ?>;
	border-bottom-color: <?php echo esc_attr(get_theme_mod( 'clotya_header4_top2_border_color' ) ); ?>;
}

.site-header.header-type4 .header-main,
.site-header.header-type4 .header-mobile{
	background-color: <?php echo esc_attr(get_theme_mod( 'clotya_header4_main_bg_color' ) ); ?>;
}

.site-header.header-type4 .site-nav.primary .menu > li > a,
.site-header.header-type4 .site-nav.primary .menu .sub-menu li a {
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header4_main_font_color' ) ); ?>;
}

.site-header.header-type4 .site-nav.primary .menu > li > a:hover,
.site-header.header-type4 .site-nav.primary .menu .sub-menu li a:hover {
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header4_main_font_hvrcolor' ) ); ?>;
}

.site-header.header-type4 .site-nav.horizontal > .menu .mega-menu > .sub-menu > li > a{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header4_main_submenu_font_color' ) ); ?>;
}

.site-header.header-type4 .header-button i{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header4_main_icon_color' ) ); ?>;
}

.site-header.header-type4 .header-button i:hover{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header4_main_icon_hvrcolor' ) ); ?>;
}

.site-header.header-type5 .global-notification{
	background-color: <?php echo esc_attr(get_theme_mod( 'clotya_header5_top1_bg_color' ) ); ?>;
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header5_top1_notification_color' ) ); ?>;
}

.site-header.header-type5 .header-main,
.site-header.header-type5 .header-mobile{
	background-color: <?php echo esc_attr(get_theme_mod( 'clotya_header5_main_bg_color' ) ); ?>;
}

.site-header.header-type5 .header-row.border-container .header-wrapper{
	border-bottom-color: <?php echo esc_attr(get_theme_mod( 'clotya_header5_main_border_color' ) ); ?>; 
}


.site-header.header-type5 .site-nav.primary .menu > li > a,
.site-header.header-type5 .site-nav.primary .menu .sub-menu li a {
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header5_main_font_color' ) ); ?>;
}

.site-header.header-type5 .site-nav.primary .menu > li > a:hover,
.site-header.header-type5 .site-nav.primary .menu .sub-menu li a:hover {
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header5_main_font_hvrcolor' ) ); ?>;
}

.site-header.header-type5 .site-nav.horizontal > .menu .mega-menu > .sub-menu > li > a{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header5_main_submenu_font_color' ) ); ?>;
}

.site-header.header-type5 .header-button i{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header5_main_icon_color' ) ); ?>;
}

.site-header.header-type5 .header-button i:hover{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_header5_main_icon_hvrcolor' ) ); ?>;
}

.site-offcanvas{
	background-color: <?php echo esc_attr(get_theme_mod( 'clotya_mobile_menu_bg_color' ) ); ?>;
}

.site-offcanvas .offcanvas-heading{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_mobile_menu_title_color' ) ); ?>;	
}

.site-offcanvas .site-nav .menu a{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_mobile_menu_subtitle_color' ) ); ?>;	
}

.site-offcanvas .site-nav + .offcanvas-heading{
	border-top-color: <?php echo esc_attr(get_theme_mod( 'clotya_mobile_menu_border_color' ) ); ?>;
}

.site-offcanvas .site-copyright p {
	color: <?php echo esc_attr(get_theme_mod( 'clotya_mobile_menu_copyright_font_color' ) ); ?>;	
}

.mobile-bottom-menu{
	background-color: <?php echo esc_attr(get_theme_mod( 'clotya_mobile_bottom_menu_bg_color' ) ); ?>;
}

.mobile-bottom-menu .mobile-menu ul li a i,
.mobile-bottom-menu .mobile-menu ul li a svg{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_mobile_bottom_menu_icon_color' ) ); ?>;
}

.mobile-bottom-menu .mobile-menu ul li a span {
	color: <?php echo esc_attr(get_theme_mod( 'clotya_mobile_bottom_menu_font_color' ) ); ?>;
}

.site-footer .footer-row.subscribe-row.black{
	background-color: <?php echo esc_attr(get_theme_mod( 'clotya_subscribe_bg_color' ) ); ?>;
}

.site-footer .subscribe-row .footer-subscribe-wrapper .entry-title{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_subscribe_title_color' ) ); ?>;
}

.site-footer .subscribe-row .footer-subscribe-wrapper .entry-description p{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_subscribe_subtitle_color' ) ); ?>;
}

.site-footer .subscribe-row .footer-contact-wrapper .entry-title{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_subscribe_contact_title_color' ) ); ?>;
}

.site-footer .subscribe-row .footer-contact-wrapper .entry-description p{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_subscribe_contact_subtitle_color' ) ); ?>;
}

.site-footer .subscribe-row .footer-contact-wrapper > span{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_subscribe_contact_desc_color' ) ); ?>;
}

.site-footer .footer-row.widgets-row{
	background-color: <?php echo esc_attr(get_theme_mod( 'clotya_footer_top_bg_color' ) ); ?>;
}

.site-footer .widgets-row .widget .widget-title{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_footer_title_color' ) ); ?>;
}

.site-footer .widgets-row .widget .brand-info p,
.site-footer .widgets-row .klbfooterwidget ul li a{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_footer_subtitle_color' ) ); ?>;
}

.site-footer .footer-row.border-boxed .footer-row-wrapper{
	border-bottom-color: <?php echo esc_attr(get_theme_mod( 'clotya_footer_border_color' ) ); ?>;
}

.site-footer .footer-row.footer-copyright{
	background-color: <?php echo esc_attr(get_theme_mod( 'clotya_footer_bottom_bg_color' ) ); ?>;
}

.site-footer .footer-copyright .site-copyright p{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_footer_copyright_color' ) ); ?>;
}

.site-footer .footer-copyright .footer-menu ul li a{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_footer_bottom_menu_color' ) ); ?>;
}

.shop-page-banner .banner .banner-content .entry-title{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_shop_banner_title_color' ) ); ?>;
}

.shop-page-banner .banner .banner-content .entry-description p{
	color: <?php echo esc_attr(get_theme_mod( 'clotya_shop_banner_subtitle_color' ) ); ?>;
}
</style>
<?php }
add_action('wp_head','clotya_custom_styling');

?>