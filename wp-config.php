<?php

/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'kal_portfolio_fcok612ncm');

/** MySQL database username */
define('DB_USER', 'pj4c2HzL6o421a6');

/** MySQL database password */
define('DB_PASSWORD', 'qqr05PvirEbR3HIQ');

/** MySQL hostname */
define('DB_HOST', 'kasslenterscom.fatcowmysql.com');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 'l&|hH<pKNB/_mUVCuQp^Tnk+Gb[HX>EocngO$@w_<&<@p;N*kGkmKa-QLx_KkdDvY%GY@xU[v_f_=sg=QTX)wuR;BtOME_buatZYq!Hr(IKoG)(Yo-$PNMR@/xT;GGv*');
define('SECURE_AUTH_KEY', '*kpWBn_xe-F/c[EVa+ERk=(ivzj)izpTT@{^%ZzX[)Ge$LrliwxtLhlK}|dc</];ez=gb!f^QHQovmfXOfs(X@r^r]U*;tonT%_;Q*ri)d[l]nc*z^|hDlvYrhNh[$==');
define('LOGGED_IN_KEY', 'l=y<IMw&;(c(]rR)twuY;s?OJ]!WojMyYQ*{HqRgC>uczGz=ME(]/;Fweir)G=]V{/KFW(k+Q*y(otR+$MDtA[TN-/XQcI/k$frxg>xzD-agDmY]Qd>Qv(@Okq%IUYRQ');
define('NONCE_KEY', 'FYp^OETz<N+N|K_(iI>KJ[CWd!_Rve&wp&PSao};_ICMfa^T}O>Sod-ruv&YNca|$aaadwTw)&@WIt@PWe||c+sPaX>(gB?VS}^$s|]J[**Fd/&bKpxbu<@C+u]&MZRu');
define('AUTH_SALT', 'Ultd-{r>C_ECGRf@$^/e/AE=}&$(h^!OX_>ZQ>vMs*GqGP=u{iM@m<o^w[wuU*e(A_lwM<)xn[ol!AJsD^tU&YNJniVw|NtgWediVa>PiJpPt;?T>APXzC/Dz?}^avUw');
define('SECURE_AUTH_SALT', 'mD(sHq_^c(We<wHpP=PA[-KE*NrkK$<]f?cewnA[*<kwK]}bRjX+NK?fDEs[djY^{Sfy+*dJR/O;bveLp)IRK<iklT&EvvZ=re+uhWI{lt!oa_[*%Y?x<!@OYSllQpUJ');
define('LOGGED_IN_SALT', '{|O%A{qtG^S/Oihg&E<&!*-QoF&O[p$LR&pwsgQBpCiDZ];IN*;UdCXJgMjSPJ-cQw&)EYaB;)(_I*i=SiA%VMoGH/h;rgWWkCUznBs!E>SS)x>bPfECakS<IW%^f=s(');
define('NONCE_SALT', 'GRS>I<aN];s&P)g%iN^V(^>qDO%*HiDe)@fa)oPTG|qx*+aZGKe(Cl!XDpVO+>u/CNG}c^F;A@/_<G(cFGa@z<sPN?qERfSGrH&CuH/>sme>Lt;}d(Zxh{bELiziXpag');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_owsp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* Multisite */
define( 'WP_ALLOW_MULTISITE', false);

define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', true);
define('DOMAIN_CURRENT_SITE', 'www.kasslenters.com');
define('PATH_CURRENT_SITE', '/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
