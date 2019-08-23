<?php
/**
 * Configuration overrides for WP_ENV === 'development'
 */
use Roots\WPConfig\Config;

//Config::define('SAVEQUERIES', true);
Config::define('WP_DEBUG', true);
Config::define('WP_DEBUG_DISPLAY', true);
Config::define('WP_DISABLE_FATAL_ERROR_HANDLER', true);
Config::define('SCRIPT_DEBUG', true);

ini_set('display_errors', '1');

// Allow Docker Secret password file
Config::define('DB_PASSWORD', env('DB_PASSWORD_FILE') ? file_get_contents( env('DB_PASSWORD_FILE') ) : env('DB_PASSWORD') );

// Enable plugin and theme updates and installation from the admin
Config::define('DISALLOW_FILE_MODS', false);

// Put Jetpack in DEV mode
Config::define('JETPACK_DEV_DEBUG', true);

// Limit post revisions to 10
Config::define('WP_POST_REVISIONS', 10 );

/**
 * Setup Dev Handlers
 * Remove the Production Handler and push a file-based one
 */
Monolog\Registry::getInstance( 'wordpress' )->setHandlers( 
    [ new Monolog\Handler\StreamHandler( Config::get('WP_ROOT_DIR') . '/log/wordpress.log', Monolog\Logger::DEBUG ) ]
);