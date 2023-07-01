<?php
/**
 * Plugin Name: Agama
 * Plugin URI: https://themeforest.net/user/egemenerd/portfolio
 * Description: Product designer for WooCommerce - Palleon Addon
 * Version: 1.1
 * Requires PHP: 7.0
 * Author: ThemeMasters
 * Author URI: http://codecanyon.net/user/egemenerd
 * License: http://codecanyon.net/licenses
 * Text Domain: agama
 * Domain Path: /languages
 *
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'AGAMA_PLUGIN_URL' ) ) {
	define( 'AGAMA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'AGAMA_PLUGIN_PATH' ) ) {
	define( 'AGAMA_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'AGAMA_VERSION' ) ) {
	define( 'AGAMA_VERSION', '1.1');
}

if ( ! defined( 'AGAMA_PALLEON_VERSION' ) ) {
	define( 'AGAMA_PALLEON_VERSION', '2.8.4');
}

/* Admin notices */
function agama_requires_palleon(){
    echo '<div class="notice notice-error"><p>' . esc_html__( 'Agama requires Palleon WordPress Image Editor plugin.', 'agama' ) . ' <a href="https://palleon.website" target="_blank">' . esc_html__( 'Buy now!', 'agama' ) . '</a></p></div>';
}

function agama_requires_palleon_version(){
    echo '<div class="notice notice-warning"><p>' . esc_html__( 'Agama requires Palleon version', 'agama' ) . ' ' . AGAMA_PALLEON_VERSION . ' ' . esc_html__( 'or greater. You can download the latest version from your Codecanyon account.', 'agama' ) . '</p></div>';
}

function agama_requires_woocommerce(){
    echo '<div class="notice notice-error"><p>' . esc_html__( 'Agama requires WooCommerce plugin.', 'agama' ) . ' <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">' . esc_html__( 'Download now!', 'agama' ) . '</a></p></div>';
}

/* Init */
function agama_plugins_loaded() {
    if ( !defined( 'PALLEON_PLUGIN_URL' )  ) {
        add_action('admin_notices', 'agama_requires_palleon');
        return;
    } 
    if ( !defined( 'PALLEON_VERSION' )  ) {
        add_action('admin_notices', 'agama_requires_palleon_version');
        return;
    } else {
        if ( !version_compare( PALLEON_VERSION, AGAMA_PALLEON_VERSION, '>=' ) ) {
            add_action('admin_notices', 'agama_requires_palleon_version');
        }
    }
    if ( !class_exists( 'woocommerce' ) ) {
        add_action('admin_notices', 'agama_requires_woocommerce');
        return;
    } 
    // If everything is OK, include required files
    include_once('mainClass.php');
    include_once('orders.php');
    include_once('templates.php');
    
}
add_action('plugins_loaded', 'agama_plugins_loaded', 11);