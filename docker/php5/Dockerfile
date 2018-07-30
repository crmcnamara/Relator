FROM ubuntu:trusty

WORKDIR /srv/src
ENV container=docker

RUN set -x \
    && apt-get update \
    && apt-get upgrade -y \
    && apt-get install -y \
        ca-certificates \
        php5 \
        php5-sqlite \
        wget 
    
RUN set -x \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === '$(wget -q -O - https://composer.github.io/installer.sig)') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" 
    
COPY composer.*  ./
RUN set -x && composer update && composer install 
    
COPY ./  ./

ENTRYPOINT /srv/src/docker/php5/init

RUN /srv/src/docker/php5/finalize
