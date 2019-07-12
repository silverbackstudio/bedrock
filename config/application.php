<?php

use Svbk\WP\Helpers;

/** @var string Directory containing all of the site's files */
$root_dir = dirname(__DIR__);

/** @var string Document Root */
$webroot_dir = $root_dir . '/web';

define( 'WP_ROOT_DIR', $root_dir );
define( 'WP_WEBROOT', $webroot_dir );

/**
 * Expose global env() function from oscarotero/env
 */
Env::init();

/**
 * Use Dotenv to set required environment variables and load .env file in root
 */
$dotenv = new Dotenv\Dotenv($root_dir);
if (file_exists($root_dir . '/.env')) {
    $dotenv->load();
    $dotenv->required(['DB_NAME', 'DB_USER', 'DB_PASSWORD', 'WP_HOME', 'WP_SITEURL']);
}

/**
 * Setup Logger
 */
\Monolog\Registry::addLogger( new \Monolog\Logger( 'wordpress' ) );

/**
 * Set up our global environment constant and load its config first
 * Default: production
 */
define('WP_ENV', env('WP_ENV') ?: 'production');

$env_config = __DIR__ . '/environments/' . WP_ENV . '.php';

if (file_exists($env_config)) {
    require_once $env_config;
}

/**
 * URLs
 */

// Determine HTTP or HTTPS, then set WP_SITEURL and WP_HOME
if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) {
    $protocol_to_use = 'https://';
} else {
    $protocol_to_use = 'http://';
}

if (isset($_SERVER['HTTP_HOST'])) {
    define('HTTP_HOST', $_SERVER['HTTP_HOST']);
} else {
    define('HTTP_HOST', 'localhost');
}

define('WP_HOME', env('WP_HOME') ?: $protocol_to_use . HTTP_HOST);
define('WP_SITEURL',  env('WP_SITEURL') ?: $protocol_to_use . HTTP_HOST . '/wp' );

/**
 * Custom Content Directory
 */
define('CONTENT_DIR', '/app');
define('WP_CONTENT_DIR', $webroot_dir . CONTENT_DIR);
define('WP_CONTENT_URL', WP_HOME . CONTENT_DIR);



/**
 * DB settings
 */
define('DB_NAME', env('DB_NAME'));
define('DB_USER', env('DB_USER'));
define('DB_PASSWORD', env('DB_PASSWORD_FILE') ? file_get_contents( env('DB_PASSWORD_FILE') ) : env('DB_PASSWORD') );
define('DB_HOST', env('DB_HOST') ?: 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
$table_prefix = env('DB_PREFIX') ?: 'wp_';

/**
 * Authentication Unique Keys and Salts
 */
define('AUTH_KEY', env('AUTH_KEY'));
define('SECURE_AUTH_KEY', env('SECURE_AUTH_KEY'));
define('LOGGED_IN_KEY', env('LOGGED_IN_KEY'));
define('NONCE_KEY', env('NONCE_KEY'));
define('AUTH_SALT', env('AUTH_SALT'));
define('SECURE_AUTH_SALT', env('SECURE_AUTH_SALT'));
define('LOGGED_IN_SALT', env('LOGGED_IN_SALT'));
define('NONCE_SALT', env('NONCE_SALT'));

/**
 * Custom Settings
 */
define('AUTOMATIC_UPDATER_DISABLED', true);
define('DISABLE_WP_CRON', env('DISABLE_WP_CRON') ?: false);
define('DISALLOW_FILE_EDIT', true);

/**
 * Multisite options
 */
define( 'WP_ALLOW_MULTISITE', true );

if( env('DOMAIN_CURRENT_SITE')  ) {
    
    define( 'MULTISITE', true );
    define( 'SUBDOMAIN_INSTALL', true );
    $base = '/';
    define( 'DOMAIN_CURRENT_SITE', env('DOMAIN_CURRENT_SITE') );
    define( 'PATH_CURRENT_SITE', '/' );
    define( 'SITE_ID_CURRENT_SITE', 1 );
    define( 'BLOG_ID_CURRENT_SITE', 1 );
    
    define('PLUGINDIR', 'app/plugin' );
    define('MUPLUGINDIR', 'app/plugin' );
        
    if( file_exists( CONTENT_DIR . '/sunrise.php' ) ) {
        define('SUNRISE', 'on' );
    }

    if ( env('COOKIE_DOMAIN') !== null ) {
        define( 'COOKIE_DOMAIN', env('COOKIE_DOMAIN') );
    }

    define( 'NOBLOGREDIRECT', env('NOBLOGREDIRECT') ?: WP_HOME );
}

//Helpers\Config::load( __DIR__ . '/iubenda.json',  'iubenda' );
//Helpers\Config::load( __DIR__ . '/googlemaps.json',  'googlemaps' );

if ( env( 'SENDINBLUE_APIKEY' ) ) {
	SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey( 'api-key', env( 'SENDINBLUE_APIKEY' ) );
}

/**
 * Bootstrap WordPress
 */
if (!defined('ABSPATH')) {
    define('ABSPATH', $webroot_dir . '/wp/');
}
