FROM ubuntu:bionic

ARG source_dir="/srv/src/vinyl"
ENV source_dir="$source_dir"

WORKDIR "$source_dir"

ARG docker_dir
ENV docker_dir="$docker_dir"

ENTRYPOINT [ "/bin/bash", "-c", "$docker_dir/init \"$@\" ", "$docker_dir/init" ]

ENV container=docker

ARG DEBIAN_FRONTEND=noninteractive

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
        php-mbstring \
        php-mysql \
        php-pgsql \
        php-sqlite3 \
        php-xml \
        php-zip \
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
    su developer -c "composer install" &&\
    :

# prep MySQL Vinyl user access
RUN set -x &&\
    service mysql start &&\
    mysql -e 'create user vinyl ; grant all on `vinyl\_%`.* to vinyl ' &&\
    :

# Copy in the rest of source tree    
COPY ./  ./


CMD [ "vendor/bin/phpunit" ]

RUN "$docker_dir"/finalize
