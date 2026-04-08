FROM debian:bookworm-slim

RUN apt-get update \
    && apt-get install -y --no-install-recommends apache2 \
    && rm -rf /var/lib/apt/lists/*

RUN a2dismod mpm_event \
    && a2enmod proxy_fcgi ssl rewrite proxy proxy_http mpm_worker \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && rm -f /run/apache2/apache2.pid

EXPOSE 80 443

CMD ["apache2ctl", "-D", "FOREGROUND"]
