FROM php:7.4-cli-alpine

ARG HOST_USER_ID
ARG HOST_USER

# SYS: Install required packages
RUN apk --no-cache upgrade && \
    apk add bash make nodejs npm

# NODE: install nodemon
RUN npm install -g nodemon --no-optional

COPY prod/files/bin/ /usr/local/bin/

# PHP: Install php extensions
RUN docker-php-ext-install pdo_mysql opcache > /dev/null 2>&1 && \
    docker-php-ext-enable pdo_mysql opcache > /dev/null 2>&1

RUN adduser -u $HOST_USER_ID -D -H $HOST_USER

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
