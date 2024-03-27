

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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */


 define('FS_METHOD', 'direct' );
// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'astronet' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'f1o5KUzdLRD7uD' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          'Q3j@]jl$lb dx6}V7V~>sdWLZ}X>cql4/d7|)k3>Njbro?G@z0l&sSQ%F]NDOkz|' );
define( 'SECURE_AUTH_KEY',   'kv3GT/YID22Vp<iy~Ft*8D%|- |(ZuOr&@2XwI;F^CJ,]Rtd=N:XOn`c,@YjS~s>' );
define( 'LOGGED_IN_KEY',     'WQv#:T]|K:!ayV:e(<=dzSv?s_e!_7 3MmwqfFvu6.;$%S6eBLN}cq_F_n+MW08u' );
define( 'NONCE_KEY',         'qEtB+uDf1,I)V7nEv1>HKZ!ok:0b*adn@2I9D{8/]<zt<A3cCmw^+zRHie%y4ZB4' );
define( 'AUTH_SALT',         '|.#[5a5Y`!vpHAA)yY CPW{4?g!#!dB+i=W?~{brp8{vVi;?#?~VlmwyB.4E4K,o' );
define( 'SECURE_AUTH_SALT',  'VAjmY_t&)CS{2W&{[h$;V2.x_`_As-(Ev35N$I8CMrYi!&_p~M[SF{R>d)c -Fpk' );
define( 'LOGGED_IN_SALT',    'a`*DS(55OJ-^U5p1z+=a3&huCBLtxP`Y>LReW>JQOP<>w,7R^*EO@A!wh^n5[3Vm' );
define( 'NONCE_SALT',        '-UfJFz^k!_|*{kb^n(]LBJ&hbC4c@_$?}#}Db*2^<2Hqi,PdhF}SAu2qQ*S5kWd ' );
define( 'WP_CACHE_KEY_SALT', '*<D,mg{|+LmKDd4WZf.,f({U4K_|#x3u)BDG7B[B#[^`}&RL9rL(yL,Hx$H CPHT' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );

define( 'WP_DEBUG_DISPLAY', false );

define( 'WP_DEBUG_LOG', true );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

define('FTP_USER', 'username');define('FTP_PASS', 'password');define('FTP_HOST', 'host');