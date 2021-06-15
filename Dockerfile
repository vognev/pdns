FROM debian:buster-slim as runtime

SHELL ["/bin/bash", "-c"]

RUN apt-get update -qq && apt-get install -y curl gnupg apt-transport-https\
 && curl -s https://packages.sury.org/php/apt.gpg | apt-key add -\
 && echo "deb https://packages.sury.org/php/ buster main" > /etc/apt/sources.list.d/php.list \
 && apt-get update -qq && apt-get install -y curl php8.1-{cli,mbstring,intl}\
 && apt-get autoremove -y && rm -rf /var/lib/apt/lists/*

FROM runtime as develop

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_HOME=/tmp
ENV COMPOSER_CACHE_DIR=/tmp

RUN curl -s https://packages.sury.org/php/apt.gpg | apt-key add -\
 && apt-get update -qq && apt-get install -o Dpkg::Options::="--force-confold" -y php8.1-{xdebug,pcov}\
 && phpdismod -v 8.1 -s cli xdebug\
 && rm -rf /var/lib/apt/lists/*
