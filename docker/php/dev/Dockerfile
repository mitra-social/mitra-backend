FROM php:7.3-cli-alpine

ARG HOST_USER_ID
ARG HOST_USER

# SYS: Install required packages
RUN apk --no-cache upgrade && \
    apk add autoconf bash gcc g++ git make nodejs npm postgresql-dev shadow sudo

# NODE: install nodemon
RUN npm install -g nodemon --no-optional

COPY dev/files/php/ /phpIni
COPY prod/files/bin/ /usr/local/bin/

# PHP: Install php extensions
RUN pecl channel-update pecl.php.net && \
    pecl install xdebug pcov && \
    docker-php-ext-install pdo_pgsql opcache posix pcntl > /dev/null 2>&1 && \
    docker-php-ext-enable pdo_pgsql opcache posix pcntl xdebug pcov > /dev/null 2>&1

# USER: change user id if provided
RUN /bin/bash -c 'if [ -n $HOST_USER_ID ] && [ $HOST_USER_ID -lt 60000 ]; then \
        usermod -u ${HOST_USER_ID} www-data; \
    fi'

# USER: set /bin/sh and allow www-data to become root
RUN usermod -s /bin/bash www-data && \
    echo 'www-data ALL=(ALL) NOPASSWD: ALL' > '/etc/sudoers.d/www-data'

# SYS: add ssh config (for GitHub)
COPY prod/files/.ssh/config /home/$HOST_USER/.ssh/config
COPY prod/files/startup.sh /usr/bin/startup.sh

RUN ln -s /run/secrets/ssh_host_key /home/$HOST_USER/.ssh/id_rsa && \
    chown -R $HOST_USER:www-data /home/$HOST_USER && \
    chmod 700 /home/$HOST_USER/.ssh && \
    chmod 400 /home/$HOST_USER/.ssh/config

# SYS: LOG & Permissions
RUN mkdir -p /var/log/php && \
    chown -R www-data:www-data /var/log/* && \
    chmod g+w /var/log/php && \
    chmod +x /usr/bin/startup.sh && \
    chmod +x /usr/local/bin/php-ext-disable && \
    chmod +x /usr/local/bin/php-ext-enable && \
    chmod 777 /tmp

USER $HOST_USER

WORKDIR /var/www/html

# ENTRYPOINT
ENTRYPOINT ["/usr/bin/startup.sh"]
