FROM php:7.2-apache

# Configure apt
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update \
    && apt-get -y install --no-install-recommends apt-utils 2>&1

# install the PHP extensions we need (https://make.wordpress.org/hosting/handbook/handbook/server-environment/#php-extensions)
RUN set -ex; \
	\
	savedAptMark="$(apt-mark showmanual)"; \
	\
	apt-get update; \
	apt-get install -y --no-install-recommends \
		libjpeg-dev \
		libmagickwand-dev \
		libpng-dev \
	; \
	\
	docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr; \
	docker-php-ext-install \
		bcmath \
		exif \
		gd \
		mysqli \
		opcache \
		zip \
	; \
	pecl install imagick-3.4.4; \
	docker-php-ext-enable imagick; \
	\
# reset apt-mark's "manual" list so that "purge --auto-remove" will remove all build dependencies
	apt-mark auto '.*' > /dev/null; \
	apt-mark manual $savedAptMark; \
	ldd "$(php -r 'echo ini_get("extension_dir");')"/*.so \
		| awk '/=>/ { print $3 }' \
		| sort -u \
		| xargs -r dpkg-query -S \
		| cut -d: -f1 \
		| sort -u \
		| xargs -rt apt-mark manual; \
	\
	apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
		echo 'opcache.memory_consumption=128'; \
		echo 'opcache.interned_strings_buffer=8'; \
		echo 'opcache.max_accelerated_files=4000'; \
		echo 'opcache.revalidate_freq=2'; \
		echo 'opcache.fast_shutdown=1'; \
		echo 'opcache.enable_cli=1'; \
	} > /usr/local/etc/php/conf.d/opcache-recommended.ini
# https://codex.wordpress.org/Editing_wp-config.php#Configure_Error_Logging
RUN { \
		echo 'error_reporting = 4339'; \
		echo 'display_errors = On'; \
		echo 'display_startup_errors = On'; \
		echo 'log_errors = On'; \
		echo 'error_log = /var/log/php/error.log'; \
		echo 'log_errors_max_len = 1024'; \
		echo 'ignore_repeated_errors = On'; \
		echo 'ignore_repeated_source = Off'; \
		echo 'html_errors = On'; \
	} > /usr/local/etc/php/conf.d/error-logging.ini

# Install required packages and tools
RUN apt-get update; \
	apt-get -y install git procps lsb-release dialog unzip nano mariadb-client ssl-cert

# Install NodeJS 10
RUN curl -sL https://deb.nodesource.com/setup_10.x | bash - \
	&& apt-get install -y nodejs

# Install Google Cloud SDK
RUN echo "deb [signed-by=/usr/share/keyrings/cloud.google.gpg] https://packages.cloud.google.com/apt cloud-sdk main" |  tee -a /etc/apt/sources.list.d/google-cloud-sdk.list \
	&& apt-get -y install apt-transport-https ca-certificates gnupg \
	&& curl https://packages.cloud.google.com/apt/doc/apt-key.gpg | apt-key --keyring /usr/share/keyrings/cloud.google.gpg add - \
	&& apt-get update \ 
	&& apt-get -y install google-cloud-sdk 

# Install Gulp NPM Package Globally
RUN npm install -g gulp

# Install composer and wp-cli from factoery images
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY --from=wordpress:cli-php7.2 /usr/local/bin/wp /usr/local/bin/wp

# Setup PHP as development environment
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Enable required Apache Modules
RUN a2enmod rewrite expires headers

# Change Apache Root Folder
RUN sed -ri -e 's!/var/www/html!/var/www/html/web!g' /etc/apache2/sites-available/*.conf

# Change Apache HTTP ports to avoid the needs to be root
RUN sed -ri -e 's!<VirtualHost \*:80>!<VirtualHost *:8080>!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!Listen 80!Listen 8080!g' /etc/apache2/ports.conf 
RUN sed -ri -e 's!<VirtualHost _default_:443>!<VirtualHost _default_:8443>!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!Listen 443!Listen 8443!g' /etc/apache2/ports.conf 

# Enable Apache SSL
RUN a2enmod ssl
RUN a2ensite default-ssl
# Apache boots as unprivileged user, snakeoil cert key file need to readable. 
RUN chmod 755 /etc/ssl/private
RUN chmod 644 /etc/ssl/private/ssl-cert-snakeoil.key

# Configure new user
ARG USERNAME=developer
RUN useradd -m $USERNAME
ENV HOME /home/$USERNAME

# [Optional] Add sudo support
RUN apt-get install -y sudo \
    && echo $USERNAME ALL=\(root\) NOPASSWD:ALL > /etc/sudoers.d/$USERNAME \
    && chmod 0440 /etc/sudoers.d/$USERNAME

# Clean up
RUN apt-get autoremove -y \
    && apt-get clean -y \
    && rm -rf /var/lib/apt/lists/*
ENV DEBIAN_FRONTEND=dialog

# Set the default shell to bash instead of sh
ENV SHELL /bin/bash

# Set the default user
USER $USERNAME