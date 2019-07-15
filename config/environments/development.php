<?php

/** Development */
define('WP_DEBUG', true);
define('SCRIPT_DEBUG', true);
define('JETPACK_DEV_DEBUG', true);
define('WP_POST_REVISIONS', 10 );
define('WP_DISABLE_FATAL_ERROR_HANDLER', true);

/**
 * Setup Log Handlers
 */
Monolog\Registry::getInstance( 'wordpress' )->pushHandler( 
    new Monolog\Handler\StreamHandler( WP_ROOT_DIR . '/log/wordpress.log', Monolog\Logger::DEBUG ) 
);
