FROM composer:latest

ARG HOST_USER_ID
ARG HOST_USER

# SYS: Install required packages
RUN apk --no-cache upgrade && \
    apk add bash

COPY php/prod/files/bin/ /usr/local/bin/

# PHP: Install php extensions
RUN docker-php-ext-install pdo_mysql opcache > /dev/null 2>&1 && \
    docker-php-ext-enable pdo_mysql opcache > /dev/null 2>&1

RUN adduser -u $HOST_USER_ID -D -H $HOST_USER

# SYS: add ssh config (for GitHub)
COPY php/prod/files/.ssh/config /home/$HOST_USER/.ssh/config

RUN ln -s /run/secrets/ssh_host_key /home/$HOST_USER/.ssh/id_rsa && \
    chown -R $HOST_USER:www-data /home/$HOST_USER && \
    chmod 700 /home/$HOST_USER/.ssh && \
    chmod 400 /home/$HOST_USER/.ssh/config

# SYS: LOG & Permissions
RUN mkdir -p /var/log/php && \
    chown -R www-data:www-data /var/log/* && \
    chmod g+w /var/log/php && \
    chmod 777 /tmp

USER $HOST_USER

# COMPOSER: add prestissimo for composer parallelism
RUN composer global require hirak/prestissimo

WORKDIR /app
