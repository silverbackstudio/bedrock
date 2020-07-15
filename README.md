# Silverback Wordpress Website Boilerplate

This bolerplate is based on Bedrock, a modern WordPress stack that helps you get started with the best development tools and project structure.

## Features

* Better folder structure
* Dependency management with [Composer](https://getcomposer.org)
* Easy WordPress configuration with environment specific files
* Environment variables with [Dotenv](https://github.com/vlucas/phpdotenv)
* Autoloader for mu-plugins (use regular plugins as mu-plugins)
* Enhanced security (separated web root and secure passwords with roots.io's [wp-password-bcrypt](https://github.com/roots/wp-password-bcrypt))

## Requirements

* PHP >= 7.2
* Composer - [Install](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)

## Installation

1. Create a new project:
    ```sh
    $ composer create-project silverback/wp-website
    ```
2. Update environment variables in the `.env` file:
  * Database variables
    * `DB_NAME` - Database name
    * `DB_USER` - Database user
    * `DB_PASSWORD` - Database password
    * `DB_HOST` - Database host
    * Optionally, you can define `DATABASE_URL` for using a DSN instead of using the variables above (e.g. `mysql://user:password@127.0.0.1:3306/db_name`)
  * `WP_ENV` - Set to environment (`development`, `staging`, `production`)
  * `WP_HOME` - Full URL to WordPress home (https://localhost)
  * `WP_SITEURL` - Full URL to WordPress including `/wp` subdirectory (https://localhost/wp)
  * `AUTH_KEY`, `SECURE_AUTH_KEY`, `LOGGED_IN_KEY`, `NONCE_KEY`, `AUTH_SALT`, `SECURE_AUTH_SALT`, `LOGGED_IN_SALT`, `NONCE_SALT`
    * Generate with [wp-cli-dotenv-command](https://github.com/aaemnnosttv/wp-cli-dotenv-command)
    * Generate with [Roots.io WordPress salts generator](https://roots.io/salts.html)
3. Add theme(s) via `composer require`
4. Set the document root on your webserver to app `web` folder: `/path/to/site/web/`
5. Access WordPress admin at `https://localhost/wp/wp-admin/`

## Development Environment with Docker

This boliperplate contains a pre-configured Docker environment with dedicated WP and MySQL containers.
You can find a Visual Studio Code development guide in the [/.devcontainer/README.md](.devcontainer) folder (recommended) or you can manually launch it via [`docker-compose.yaml`](/.devcontainer/docker-compose.yaml) file.

## Google App Engine

This boilerlate is made to be deployed to Google App Engine. Please read the [GAE_DEPLOY](/GAE_DEPLOY.md) guide.

## Bedrock

Bedrock documentation is available at [https://roots.io/bedrock/docs/](https://roots.io/bedrock/docs/).

