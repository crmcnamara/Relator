FROM ubuntu:trusty

WORKDIR /srv/src
ENV container=docker

# Install Ubuntu packages
RUN set -x \
    && apt-get update \
    && apt-get upgrade -y \
    && apt-get install -y \
        ca-certificates \
        php5 \
        php5-sqlite \
        wget 

# Install Composer
RUN set -x \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === '$(wget -q -O - https://composer.github.io/installer.sig)') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" 

# Create non-root user to install dependencies
RUN set -x \
    && adduser --disabled-password --gecos '' developer \
    && mkdir -p vendor \
    && chown developer -R vendor

# Install composer php dependencies
COPY composer.*  ./
RUN set -x \
    && su developer -c " composer update && composer install " 

# Copy in the rest of source tree    
COPY ./  ./

ENTRYPOINT [ "/srv/src/docker/php5/init" ]
CMD [ "vendor/bin/phpunit", "tests/" ]

RUN /srv/src/docker/php5/finalize
