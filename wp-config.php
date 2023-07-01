<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'newwhy' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );


/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'KtfpKn<vhOIcf|uT)~3BPgvlY!4na=HDW:c}-x/Ir@:1QG`S!%*Me`yA}GhDu9i%' );
define( 'SECURE_AUTH_KEY',  ',1JvO_U-gqVwHt@|.BI>V$ff,%!X}r*_*NQLqfRnHzN_uH{>|cm}&U7,0Y?=6`sM' );
define( 'LOGGED_IN_KEY',    'mWFi*|^3[QDF`Ean7*&fJ?QEY+Kl!/at0n%B#Hg7@_:dj061QIw5R{]Cx.)NYB!Q' );
define( 'NONCE_KEY',        'r=ZE}18e&S{5[[pUpMK:GcDy|;D3$+DP>kN .Yq}8TzgdQfm>z!mv(;PhmPKcTuF' );
define( 'AUTH_SALT',        'qq[%ZAHB|4F<>y)%Hn )f^}7mig~.&;*I3mbMA,]wIu:x7Qy<1pCIR}3i#P(QFFO' );
define( 'SECURE_AUTH_SALT', 'gK,MiBB>@.msk33z{m=%-kO}L+8ejtP$_^l<r{0^fN`^~uUCgN(LbosHQ}3P>O!h' );
define( 'LOGGED_IN_SALT',   'SUgl79rOdfT>3yu p#6ZCf@[Lz)G@y]m<IXz]v&$0]e.`o oG1bwPI8X)d{hqE6{' );
define( 'NONCE_SALT',       'WH~PY}7QH4U. .:1Y-PV[XlK,^fH_t?s8r rq4:otvhGTO)sd]0{e._,J.{kCVE6' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'nw_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */


@ini_set('upload_max_size' , '256M' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';