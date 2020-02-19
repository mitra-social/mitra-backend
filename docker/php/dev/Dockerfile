FROM php:7.3-cli-alpine

ARG HOST_USER_ID
ARG HOST_USER

# SYS: Install required packages
RUN apk --no-cache upgrade && \
    apk add autoconf bash gcc g++ git make nodejs npm shadow sudo

# NODE: install nodemon
RUN npm install -g nodemon --no-optional

COPY prod/files/bin/ /usr/local/bin/

# PHP: Install php extensions
RUN pecl channel-update pecl.php.net && \
    pecl install xdebug && \
    docker-php-ext-install pdo_mysql opcache > /dev/null 2>&1 && \
    docker-php-ext-enable pdo_mysql opcache xdebug > /dev/null 2>&1

RUN adduser -u $HOST_USER_ID -D -H $HOST_USER

# USER: set /bin/bash and allow www-data to become root
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
    chmod 777 /tmp

USER $HOST_USER

WORKDIR /var/www/html

# ENTRYPOINT
ENTRYPOINT ["/usr/bin/startup.sh"]
