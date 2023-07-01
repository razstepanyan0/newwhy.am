<?php
/**
 * Plugin Name: Palleon
 * Plugin URI: https://palleon.website
 * Description: Image Editor For WordPress
 * Version: 2.8.4
 * Author: ThemeMasters
 * Author URI: http://codecanyon.net/user/egemenerd
 * License: http://codecanyon.net/licenses
 * Text Domain: palleon
 * Domain Path: /languages
 *
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'PALLEON_PLUGIN_URL' ) ) {
	define( 'PALLEON_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'PALLEON_SOURCE_URL' ) ) {
	define( 'PALLEON_SOURCE_URL', 'https://palleon.website/js-version/demo/files/' );
}

if ( ! defined( 'PALLEON_VERSION' ) ) {
    define ('PALLEON_VERSION', '2.8.4');
}

/* ---------------------------------------------------------
Include files
----------------------------------------------------------- */

$palleondir = ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 ) ? __DIR__ : dirname( __FILE__ );

if ( file_exists(  $palleondir . '/cmb2/init.php' ) ) {
    require_once($palleondir . '/cmb2/init.php');
} elseif ( file_exists(  $palleondir . '/CMB2/init.php' ) ) {
    require_once($palleondir . '/CMB2/init.php');
}

include_once('settingsClass.php');
include_once('library.php');
include_once('mainClass.php');
include_once('pexels.php');

/* ---------------------------------------------------------
Plugins Loaded
----------------------------------------------------------- */
function palleon_plugins_loaded() {
    include_once('customTemplates.php');
    include_once('customElements.php');
    include_once('customFonts.php');
}
add_action('plugins_loaded', 'palleon_plugins_loaded');