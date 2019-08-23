<?php
/**
 * Your base production configuration goes in this file. Environment-specific
 * overrides go in their respective config/environments/{{WP_ENV}}.php file.
 *
 * A good default policy is to deviate from the production config as little as
 * possible. Try to define as much of your configuration in this file as you
 * can.
 */

use Svbk\WP\Helpers;
use Roots\WPConfig\Config;

/** @var string Directory containing all of the site's files */
$root_dir = dirname(__DIR__);

/** @var string Document Root */
$webroot_dir = $root_dir . '/web';

Config::define( 'WP_ROOT_DIR', $root_dir );
Config::define( 'WP_WEBROOT', $webroot_dir );

/**
 * Expose global env() function from oscarotero/env
 */
Env::init();

/**
 * Use Dotenv to set required environment variables and load .env file in root
 */
$dotenv = Dotenv\Dotenv::create($root_dir);
if (file_exists($root_dir . '/.env')) {
    $dotenv->load();
    $dotenv->required(['WP_HOME', 'WP_SITEURL']);
    if (!env('DATABASE_URL')) {
        $dotenv->required(['DB_NAME', 'DB_USER', 'DB_PASSWORD']);
    }
}

/**
 * Set up our global environment constant and load its config first
 * Default: production
 */
define('WP_ENV', env('WP_ENV') ?: 'production');

/**
 * URLs
 */
if (isset($_SERVER['HTTP_HOST'])) {
    $http_host = $_SERVER['HTTP_HOST'];
} else {
    $http_host = 'localhost';
}

// Determine HTTP or HTTPS, then set WP_SITEURL and WP_HOME
if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) {
    $protocol_to_use = 'https://';
} else {
    $protocol_to_use = 'http://';
}

Config::define('WP_HOME', env('WP_HOME') ?: $protocol_to_use . $http_host);
Config::define('WP_SITEURL', env('WP_SITEURL') ?: $protocol_to_use . $http_host . '/wp');

/**
 * Custom Content Directory
 */
Config::define('CONTENT_DIR', '/app');
Config::define('WP_CONTENT_DIR', $webroot_dir . Config::get('CONTENT_DIR'));
Config::define('WP_CONTENT_URL', Config::get('WP_HOME') . Config::get('CONTENT_DIR'));

/**
 * DB settings
 */
Config::define('DB_NAME', env('DB_NAME'));
Config::define('DB_USER', env('DB_USER'));
Config::define('DB_PASSWORD', env('DB_PASSWORD') );
Config::define('DB_HOST', env('DB_HOST') ?: 'localhost');
Config::define('DB_CHARSET', 'utf8mb4');
Config::define('DB_COLLATE', '');
$table_prefix = env('DB_PREFIX') ?: 'wp_';

if (env('DATABASE_URL')) {
    $dsn = (object) parse_url(env('DATABASE_URL'));

    Config::define('DB_NAME', substr($dsn->path, 1));
    Config::define('DB_USER', $dsn->user);
    Config::define('DB_PASSWORD', isset($dsn->pass) ? $dsn->pass : null);
    Config::define('DB_HOST', isset($dsn->port) ? "{$dsn->host}:{$dsn->port}" : $dsn->host);
}

/**
 * Authentication Unique Keys and Salts
 */
Config::define('AUTH_KEY', env('AUTH_KEY'));
Config::define('SECURE_AUTH_KEY', env('SECURE_AUTH_KEY'));
Config::define('LOGGED_IN_KEY', env('LOGGED_IN_KEY'));
Config::define('NONCE_KEY', env('NONCE_KEY'));
Config::define('AUTH_SALT', env('AUTH_SALT'));
Config::define('SECURE_AUTH_SALT', env('SECURE_AUTH_SALT'));
Config::define('LOGGED_IN_SALT', env('LOGGED_IN_SALT'));
Config::define('NONCE_SALT', env('NONCE_SALT'));

/**
 * Custom Settings
 */
Config::define('AUTOMATIC_UPDATER_DISABLED', true);
Config::define('DISABLE_WP_CRON', env('DISABLE_WP_CRON') ?: false);
// Disable the plugin and theme file editor in the admin
Config::define('DISALLOW_FILE_EDIT', true);
// Disable plugin and theme updates and installation from the admin
Config::define('DISALLOW_FILE_MODS', true);

/**
 * Debugging Settings
 */
Config::define('WP_DEBUG_DISPLAY', false);
Config::define('SCRIPT_DEBUG', false);
ini_set('display_errors', '0');

/**
 * Allow WordPress to detect HTTPS when used behind a reverse proxy or a load balancer
 * See https://codex.wordpress.org/Function_Reference/is_ssl#Notes
 */
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

/**
 * Setup Logging
 */
\Monolog\Registry::addLogger( new \Monolog\Logger( 'wordpress' ) );

// Add Google Logging Client if is available
$metadataProvider = \Google\Cloud\Core\Report\MetadataProviderUtils::autoSelect($_SERVER);
$loggingClient = new \Google\Cloud\Logging\LoggingClient();
$psrLogger = $loggingClient->psrLogger('wordpress', [
    'batchEnabled' => true,
    'metadataProvider' => $metadataProvider,
]);
\Google\Cloud\ErrorReporting\Bootstrap::init($psrLogger); 
\Monolog\Registry::getInstance( 'wordpress' )->pushHandler( new Monolog\Handler\PsrHandler( $psrLogger ) );

/**
 * Multisite options
 */
Config::define( 'WP_ALLOW_MULTISITE', true );

if( env('DOMAIN_CURRENT_SITE')  ) {
    
    Config::define( 'MULTISITE', true );
    Config::define( 'SUBDOMAIN_INSTALL', true );
    $base = '/';
    Config::define( 'DOMAIN_CURRENT_SITE', env('DOMAIN_CURRENT_SITE') );
    Config::define( 'PATH_CURRENT_SITE', '/' );
    Config::define( 'SITE_ID_CURRENT_SITE', 1 );
    Config::define( 'BLOG_ID_CURRENT_SITE', 1 );
    
    Config::define('PLUGINDIR', 'app/plugin' );
    Config::define('MUPLUGINDIR', 'app/plugin' );
        
    if( file_exists( CONTENT_DIR . '/sunrise.php' ) ) {
        Config::define('SUNRISE', 'on' );
    }

    if ( env('COOKIE_DOMAIN') !== null ) {
        Config::define( 'COOKIE_DOMAIN', env('COOKIE_DOMAIN') );
    }

    Config::define( 'NOBLOGREDIRECT', env('NOBLOGREDIRECT') ?: WP_HOME . '#noblog' );
}

/**
 * Import Global Configs
 */
//Helpers\Config::load( __DIR__ . '/iubenda.json',  'iubenda' );
//Helpers\Config::load( __DIR__ . '/googlemaps.json',  'googlemaps' );

/**
 * Jetpack Compatibility
 */

// Set the Jetpack Signature port to Google AppEngine internal reverse-proxy 
Config::define( 'JETPACK_SIGNATURE__HTTPS_PORT', 8080 );

/**
 * Set Sendinblue APi KEY
 */
if ( env( 'SENDINBLUE_APIKEY' ) ) {
	SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey( 'api-key', env( 'SENDINBLUE_APIKEY' ) );
}

$env_config = __DIR__ . '/environments/' . WP_ENV . '.php';

if (file_exists($env_config)) {
    require_once $env_config;
}

Config::apply();

/**
 * Bootstrap WordPress
 */
if (!defined('ABSPATH')) {
    define('ABSPATH', $webroot_dir . '/wp/');
}
