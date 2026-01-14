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

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

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
define( 'AUTH_KEY',          'zUK 6:rf(Xp.;TO<&EYGzIi_cl$Oq![D8&*n{F0+{onYRHzd)a57O/0]|<.<T]To' );
define( 'SECURE_AUTH_KEY',   '!fw(R)l[aKdq-.h@+vv0NT`Qdz:[L;K3rCG|c-#P=0yG>|w2ARH1Wsw<.9m)jqs|' );
define( 'LOGGED_IN_KEY',     'wCRTXU,0yUT>Z<nO}~6/j/^&$L*AUi=[4^*uRe@6;q/b2G$xde6V.4~yX}lzeu{C' );
define( 'NONCE_KEY',         ']R($ccP4tcYU^_:3l_uj=3;^9hyB+3^epnunCs+))7t$?)<eoNE]or<&U)E^}glc' );
define( 'AUTH_SALT',         '`o_M+L&am.dHJ/i)nCB0*$*^:*9pnu){A~(/et<7K:I_5>s7s+o~JR{C=MuOR$U8' );
define( 'SECURE_AUTH_SALT',  ' &h*#}_oCwc(D3u.o5B) iL;qM@ChQ$Ih@~?BV8H0qVk$,RoFE<!I{_l4FXI<<xS' );
define( 'LOGGED_IN_SALT',    'RWtBcY{fei1qC,ZB8.dH.)9]MJ2spM<5{nUa3rr!}+]O(S`ikNLAG@RyMj<LX,h%' );
define( 'NONCE_SALT',        ' :jw-5l1Fg0?YJkm(Xx`Yf.M7;`5]P=l07BUYK!VWLNO/[nE|{_M#0r[:Xl DI}X' );
define( 'WP_CACHE_KEY_SALT', '08 smj]!:|BRU~8!?~$)4b(EfsrC7)|s=dD,1ki3FDdKCup_3mWCUr 21eSn8(`a' );


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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
