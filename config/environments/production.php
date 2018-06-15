<?php
/** Production */
ini_set('display_errors', 0);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', false);
/** Disable all file modifications including updates and update notifications */
define('DISALLOW_FILE_MODS', true);

/**
 * Setup Log Handlers
 */
Monolog\Registry::getInstance( 'wordpress' )->pushHandler( 
    new Monolog\Handler\PsrHandler( Google\Cloud\Logging\LoggingClient::psrBatchLogger('wp-website') )
);

if( env('SENDINBLUE_APIKEY') ) {
    Monolog\Registry::getInstance( 'wordpress' )->pushHandler( 
        new Svbk\Monolog\Sendinblue\Handler( 
            env('SENDINBLUE_APIKEY'),
            'tatap@silverbackstudio.it', 
            'webmaster@silverbackstudio.it', 
            'Tatap Website Log',
            Monolog\Logger::ERROR
        )
    );
}