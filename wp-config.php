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
define( 'DB_NAME', 'webxiyjk_harryclarktemplate' );

/** Database username */
define( 'DB_USER', 'webxiyjk_admin' );

/** Database password */
define( 'DB_PASSWORD', '3scApyVV9YVYr@T' );

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
define( 'AUTH_KEY',         '$lQNS~~J*O).Ewc{cP~FgE`$J**p!:?7#|A!7sp<ACGVn#4L7CC/.66kGK@lepSG' );
define( 'SECURE_AUTH_KEY',  'v(d.2(L=?I+cYDZM }ST0}KYO$7bM8%YqQ*sygd;2W=(o4uj|di+q%,Rz<.dGTgq' );
define( 'LOGGED_IN_KEY',    'Pf8DnVd.,We9^.Uj7 Uq{rV/=B)u[zm5[Vzy]BFvkATyJBx&F,?DWY~IyD>LiX6a' );
define( 'NONCE_KEY',        '2l}:HZOg[]t[@|J,g6N^[NkIDHJk:D1B.u:xyy3*{lq~h*$WoT w.&hM0rRlw~|[' );
define( 'AUTH_SALT',        '/WlK:f.aL>xt]v;i-nFW=oVN N:H%vQPkHP?MnxJ[F81PipK](|Xk0P|?#sA!X`^' );
define( 'SECURE_AUTH_SALT', 't`Fq0lBh2jH7FKuV!h*x7?!,@L*hmAL|%vF-vgE{*8|KmP-FP~eZ/JcQ7N1LsS%(' );
define( 'LOGGED_IN_SALT',   'sb<?PlG?U2P+GHG<L-rt*>N1ZLS8K^9OBQA{CYkzJ3y6oQ}c4Q7+X8d(=2$q8[6r' );
define( 'NONCE_SALT',       ' Z/h+0/<<3>BG!@VFRh?}?d1J`_*e[CwL2-j4tOYGh_OrAOA)n`VBU&[X9U;W5{C' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
