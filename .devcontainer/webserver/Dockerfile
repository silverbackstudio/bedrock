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
		echo 'display_errors = Off'; \
		echo 'display_startup_errors = Off'; \
		echo 'log_errors = On'; \
		echo 'error_log = /dev/stderr'; \
		echo 'log_errors_max_len = 1024'; \
		echo 'ignore_repeated_errors = On'; \
		echo 'ignore_repeated_source = Off'; \
		echo 'html_errors = Off'; \
	} > /usr/local/etc/php/conf.d/error-logging.ini

RUN a2enmod rewrite expires ssl

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Change Apache Root Folder
RUN sed -ri -e 's!/var/www/html!/var/www/html/web!g' /etc/apache2/sites-available/*.conf

# Change Apache HTTP ports to avoid the needs to be root
RUN sed -ri -e 's!<VirtualHost \*:80>!<VirtualHost *:8080>!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!Listen 80!Listen 8080!g' /etc/apache2/ports.conf 
RUN sed -ri -e 's!<VirtualHost _default_:443>!<VirtualHost _default_:8443>!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!Listen 443!Listen 8443!g' /etc/apache2/ports.conf 

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure new user
ARG USERNAME=developer
RUN useradd -m $USERNAME
ENV HOME /home/$USERNAME

# [Optional] Add sudo support
RUN apt-get install -y sudo \
    && echo $USERNAME ALL=\(root\) NOPASSWD:ALL > /etc/sudoers.d/$USERNAME \
    && chmod 0440 /etc/sudoers.d/$USERNAME

RUN apt-get -y install git procps lsb-release dialog unzip nano

# Install Google Cloud SDK
RUN echo "deb [signed-by=/usr/share/keyrings/cloud.google.gpg] https://packages.cloud.google.com/apt cloud-sdk main" | sudo tee -a /etc/apt/sources.list.d/google-cloud-sdk.list \
	&& apt-get -y install apt-transport-https ca-certificates gnupg \
	&& curl https://packages.cloud.google.com/apt/doc/apt-key.gpg | sudo apt-key --keyring /usr/share/keyrings/cloud.google.gpg add - \
	&& apt-get update \ 
	&& apt-get -y install google-cloud-sdk 

# Clean up
RUN apt-get autoremove -y \
    && apt-get clean -y \
    && rm -rf /var/lib/apt/lists/*
ENV DEBIAN_FRONTEND=dialog

# Set the default shell to bash instead of sh
ENV SHELL /bin/bash

# Set the default user
USER $USERNAME
