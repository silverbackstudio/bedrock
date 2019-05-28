# Silverback WP+Bedrock > Google App Engine Migration

* Update Google APT key `curl https://packages.cloud.google.com/apt/doc/apt-key.gpg | sudo apt-key add -`
* Install / Update `Gogole Cloud SDK` via `sudo apt-get update && sudo apt-get install google-cloud-sdk`
* Backup or rename any `php.ini` files in project root
* Unzip the file in project root
    `unzip gae_migrate.zip`
* Copy environment variables to from production `.env` file `env_variables.yaml` 
    * Use this search and replace `=(.*[^'])$` => `: '$1'` regex to reformat the file
* Count files in the `/web` folder with `find ./web/ -type f | wc -l  ` and verify that are less than `~9000`. If more try to delete unnecessary plugins
* Open the Cloud IAM console and add following roles to GCP user that will deploy the app:
    * `Editor` 
    * `AppEngine Deployment`
    * `Storage Object Administrator` 
* Perform the following replacement in `config/application.php`:

```php
/**
 * URLs
 */
define('WP_HOME', env('WP_HOME'));
define('WP_SITEURL', env('WP_SITEURL'));
```
with  

```php
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
```

* Perform the following replacement in `config/environments/production.php`:
```php
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
```
with
```php
/**
 * Setup Log Handlers
 */
Monolog\Registry::getInstance( 'wordpress' )->pushHandler( new Monolog\Handler\ErrorLogHandler() );
```
* Set the project 
* Choose the app region from the Console (requires admin privileges)
* Deploy the app `gcloud app deploy app.yaml cron.yaml` and check if navigating the `.appspot.com` domain redirects to the main domain
* Add and verify all the main domains to AppEngine Settings
* Disable the cert auto management for all domains
* Remove `php.ini` from .gitignore file
* Remove the `Editor` role for the deploy user
* Remove all outadate GAE versions

## Improovements

* Update WP and deps to the latest version with `composer update --prefer-dist --no-dev`
* Deploy again

