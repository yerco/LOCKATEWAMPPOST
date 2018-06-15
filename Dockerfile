FROM lock8/lock8-base:latest
MAINTAINER Yerco <yerco@hotmail.com>

# create a non-root user
RUN groupadd -g 999 appuser && \
    useradd -r -u 999 -g appuser appuser

# create workdir and assign permissions to non-root user
RUN set -xe && \
    mkdir /var/www/html/wamppost  && \
    chown -R appuser:appuser /var/www/html/wamppost

# zmq extension for php
RUN apt-get update && apt-get install -y zlib1g-dev libzmq-dev wget git lsof vim
#\
#    && pecl install zmq-beta \
#    && docker-php-ext-install zip \
#    && docker-php-ext-install pdo pdo_mysql

## http://zeromq.org/bindings:php
#RUN echo 'extension=zmq.so' >> /usr/local/etc/php/conf.d/docker-php-ext-zmq.ini

RUN mkdir -p /home/appuser/.composer && \
    chown -R appuser:appuser /home/appuser/.composer && \
    chmod +w -R /home/appuser/.composer

# chown included otherwise copied as root
COPY --chown=appuser:appuser composer.phar  /var/www/html/wamppost
RUN chmod +x /var/www/html/wamppost/composer.phar

# logs
COPY --chown=appuser:appuser wamppost_client_at_router.log /var/www/html/wamppost
COPY --chown=appuser:appuser lockate_wamppost_posts.log /var/www/html/wamppost

# copy PHP code
COPY --chown=appuser:appuser . /var/www/html/wamppost/

# switch from root to appuser
USER appuser

WORKDIR /var/www/html/wamppost

# symfony
RUN ./composer.phar install
RUN ./composer.phar dump-autoload

CMD ["php", "WampPostClient/WampPostClient.php"]
