FROM php:8.1.3-fpm
RUN apt-get update && apt-get install -y \
		libfreetype-dev \
		libjpeg62-turbo-dev \
		libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd

WORKDIR /var/www

# Update packages
RUN apt-get update \
    && apt-get install -y gnupg zip nginx libzip-dev unzip libmcrypt-dev libbz2-dev nodejs git supervisor cron \
    && apt-get clean

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


### ----------------------------------------------------------
### Part 1 of Nginx Dockerfile source https://github.com/nginxinc/docker-nginx/blob/4785a604aa40e0b0a69047a61e28781a2b0c2069/stable/alpine-slim/Dockerfile
### ----------------------------------------------------------

# Install nginx
RUN apt install -y nginx

RUN  ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log \
# create a docker-entrypoint.d directory
    && mkdir /docker-entrypoint.d

COPY .docker/nginx/scripts/docker-entrypoint.sh /
RUN chmod +x /docker-entrypoint.sh
ENTRYPOINT ["/docker-entrypoint.sh"]

EXPOSE 80

STOPSIGNAL SIGQUIT



### ----------------------------------------------------------
### Install php dependency
### ----------------------------------------------------------
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-configure zip && docker-php-ext-install zip
RUN curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer

ARG APP_DEBUG=false
ENV APP_DEBUG ${APP_DEBUG}
RUN if [ ${APP_DEBUG} = true ]; then \
    pecl install xdebug \
    && \
    docker-php-ext-enable xdebug \
    ;fi


### ----------------------------------------------------------
### PHP, Nginx, Supervisor configuration
### ----------------------------------------------------------
COPY .docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY .docker/php/supervisor/supervisord.conf /etc/supervisord.conf

COPY .docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY .docker/nginx/default.conf /etc/nginx/sites-available/default

COPY .docker/php/php.ini /usr/local/etc/php/conf.d/php.ini

WORKDIR /var/www

### ----------------------------------------------------------
### Laravel
### ----------------------------------------------------------

COPY public /usr/share/nginx/html
COPY . /var/www

RUN set -x && \
    touch /var/log/cron.log && \
    chown -R www-data:www-data /usr/share/nginx/html && \
    chown -R www-data:www-data /var/www && \
    find /var/www/storage -type f -exec chmod 664 {} \; && \
    find /var/www/storage -type d -exec chmod 770 {} \;

ARG APP_ENV=production
ENV APP_ENV ${APP_ENV}

RUN set -x && \
    if [ "${APP_ENV}" = "local" ] || [ "${APP_ENV}" = "development" ] || [ "${APP_ENV}" = "dev" ]; then \
        composer install ; \
    else \
        composer install --no-dev ; \
    fi
CMD ["nginx", "-g", "daemon off;"]
