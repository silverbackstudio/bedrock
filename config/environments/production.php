<?php
/** Production */
ini_set('display_errors', 0);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', false);
/** Disable all file modifications including updates and update notifications */
define('DISALLOW_FILE_MODS', true);
/** Set the port for AppEngine internal reverse-proxy */
define( 'JETPACK_SIGNATURE__HTTPS_PORT', 8080 );


/**
 * Setup Log Handlers
 */
$metadataProvider = new Google\Cloud\Core\Report\SimpleMetadataProvider([], '', 'website', '1.0');
$loggingClient = new Google\Cloud\Logging\LoggingClient();
$psrLogger = $loggingClient->psrLogger('wp-website', [
    'batchEnabled' => true,
    'metadataProvider' => $metadataProvider,
]);
Google\Cloud\ErrorReporting\Bootstrap::init($psrLogger); 
Monolog\Registry::getInstance( 'wordpress' )->pushHandler( new Monolog\Handler\PsrHandler( $psrLogger ) );