<?php
define('WP_CACHE', true); // WP-Optimize Cache
define('WP_MEMORY_LIMIT', '512M');

// Debug settings — display errors on site
define('WP_DEBUG', false);
define('WP_DEBUG_DISPLAY', false); // show errors on site
define('WP_DEBUG_LOG', false);    // we won't write to debug.log

// Force PHP to display errors
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0);

// Force SSL for admin pages
define('FORCE_SSL_ADMIN', true);

// Recognize HTTPS behind a reverse proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

/** Database settings */
define('DB_NAME', 'oras_db');
define('DB_USER', 'oras_user');
define('DB_PASSWORD', 'Rocco2508!');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

/** Authentication keys and salts */
define('AUTH_KEY', 'put your unique phrase here');
define('SECURE_AUTH_KEY', 'put your unique phrase here');
define('LOGGED_IN_KEY', 'put your unique phrase here');
define('NONCE_KEY', 'put your unique phrase here');
define('AUTH_SALT', 'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT', 'put your unique phrase here');
define('NONCE_SALT', 'put your unique phrase here');

/** Table prefix */
$table_prefix = 'wp_';

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
