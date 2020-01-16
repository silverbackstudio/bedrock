<?php
ignore_user_abort( true );

$multisite_domain = getenv('DOMAIN_CURRENT_SITE');

/**
 * If not a multisite install, run regular wp-cron.
 */
if ( !$multisite_domain ){
    require_once( __DIR__ . '/wp/wp-cron.php' );
    exit;
}

/**
 * We must force the WP main network domain if required. 
 * GAE runs the cron requests with the `service.project.appspot.com` HOST schema,
 * if your WP installation is on a custom domain the request will be redirected and will fail.
 */ 
if( $multisite_domain && $_SERVER['HTTP_HOST'] !== $multisite_domain ) {
    $_SERVER['HTTP_HOST'] = $multisite_domain;
}

if ( ! defined( 'ABSPATH' ) ) {
	/** Set up WordPress environment */
	require_once( __DIR__ . '/wp/wp-load.php' );
}

/**
 * Cycle all the network sites and spawn a cron job if required
 */ 
$sites =  get_sites();

foreach( $sites as $site ) {
    
    // Switch to the current blog/site
    switch_to_blog( $site->blog_id );
    
    // Check for due events and spawn the site specific cron
    $spawned = spawn_cron();

    do_action( 'log', 
        $spawned ? 'info' : 'debug', 
        $spawned ? 'Spawned cron for {cron_site_domain}' : 'No scheduled crons due now for {cron_site_domain}', 
        array( 'source' => 'wordpress.cron', 'cron_site_domain' => $site->domain, 'cron_site_id' => $site->blog_id ) 
    );
}