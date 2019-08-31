FROM ubuntu:xenial

ARG source_dir="/srv/src/vinyl"
ENV source_dir="$source_dir"

WORKDIR "$source_dir"

ARG docker_dir
ENV docker_dir="$docker_dir"

ENTRYPOINT [ "/bin/bash", "-c", "$docker_dir/init \"$@\" ", "$docker_dir/init" ]

ENV container=docker

ARG DEBIAN_FRONTEND=noninteractive

RUN set -x &&\
    apt-get update &&\
    apt-get install -y software-properties-common language-pack-en-base &&\
    LC_ALL=en_US.UTF-8 add-apt-repository -y ppa:ondrej/php &&\
    :

# Install Ubuntu packages
RUN set -x &&\
    apt-get update &&\
    apt-get upgrade -y &&\
    apt-get dist-upgrade -y &&\
    :
    
RUN set -x &&\
    apt-get update &&\
    apt-get install -y \
        composer \
        mysql-client \
        mysql-server \
        php7.1-cli \
        php7.1-mbstring \
        php7.1-mysql \
        php7.1-pgsql \
        php7.1-sqlite3 \
        php7.1-xml \
        php7.1-zip \
        postgresql \
        sqlite \
        ;

# Create non-root user to install dependencies
RUN set -x && adduser --disabled-password --gecos '' developer 
    

# Install composer php dependencies
COPY composer.*  ./
RUN set -x &&\
    mkdir -p vendor &&\
    chown developer -R composer.json composer.lock vendor &&\
    su developer -c "php7.1 /usr/bin/composer install" &&\
    :

# Copy in the rest of source tree    
COPY ./  ./


CMD [ "vendor/bin/phpunit" ]

RUN "$docker_dir"/finalize
