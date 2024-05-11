FROM php:fpm-alpine
RUN apk update
# ENV MAIL_AUTH_PASS=$MAIL_AUTH_PASS
# RUN adduser $USER video
# RUN chmod a+rwx /dev/video0
# ENV POSTGRES_PASSFILE=$POSTGRES_PASSFILE
# ENV POSTGRES_HOST=$POSTGRES_HOST
# ENV POSTGRES_USER=$POSTGRES_USER
# ENV POSTGRES_DATABASE=$POSTGRES_DATABASE
# ENV POSTGRES_PASSWORD=$POSTGRES_PASSWORD
# ENV POSTGRES_PORT=$POSTGRES_PORT
# RUN apk add msmtp
RUN apk add php83-imap
# COPY msmtprc /etc/msmtprc
# RUN adduser -D -g 'msmtp' msmtp
# RUN chmod 600 /etc/msmtprc && chown -R msmtp:msmtp /etc/msmtprc
# COPY msmtp-sendmail.start /etc/local.d/msmtp-sendmail.start
# RUN chmod +x /etc/local.d/msmtp-sendmail.start
# RUN apk add eudev-libs libgcc libintl libstdc++ musl v4l-utils-libs v4l-utils ffmpeg
RUN apk add libpq musl php83-common php83-gd freetype libavif libjpeg-turbo libpng libwebp libxpm zlib
RUN apk add php83-pdo php83-pdo_pgsql php83-pgsql
RUN apk add postgresql postgresql postgresql-client postgresql-contrib
RUN echo -e 'catch_workers_output = yes\nphp_flag[display_errors] = on\nphp_admin_value[error_log] = /var/log/fpm-php.www.log\nphp_admin_flag[log_errors] = on' >> /usr/local/etc/php-fpm.d/www.conf
RUN touch /var/log/fpm-php.www.log && chmod 666 /var/log/fpm-php.www.log
# RUN sed -i 's/;extension=pgsql/extension=pgsql/g' /etc/php83/php.ini
# RUN sed -i 's/;extension=pdo_pgsql/extension=pdo_pgsql/g' /etc/php83/php.ini
# RUN sed -i 's/;extension=imap/extension=imap/g' /etc/php83/php.ini
RUN sed -i 's/extension=\/path\/to\/extension\/mysqli.so/extension=\/path\/to\/extension\/mysqli.so\nextension=\/usr\/lib\/php83\/modules\/pdo.so\nextension=\/usr\/lib\/php83\/modules\/pdo_pgsql.so\nextension=\/usr\/lib\/php83\/modules\/gd.so\nextension=\/usr\/lib\/php83\/modules\/imap.so/g' /etc/php83/php.ini
RUN sed -i 's/memory_limit/memory_limit=1024M;/g' /etc/php83/php.ini
RUN sed -i 's/SMTP =/;SMTP =/g' /etc/php83/php.ini
RUN sed -i 's/smtp_port =/;smtp_port =/g' /etc/php83/php.ini
RUN sed -i 's/mail.add_x_header = Off/mail.add_x_header=On/g' /etc/php83/php.ini
# RUN sed -i 's/;sendmail_from/sendmail_from = \/usr\/sbin\/imapd;/g' /etc/php83/php.ini
CMD ["php-fpm", "-c", "/etc/php83/php.ini"]