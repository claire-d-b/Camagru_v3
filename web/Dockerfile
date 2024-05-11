FROM alpine:latest
RUN apk update
# RUN chmod a+rwx /dev/video0
# ENV POSTGRES_PASSFILE=$POSTGRES_PASSFILE
# ENV POSTGRES_HOST=$POSTGRES_HOST
# ENV POSTGRES_USER=$POSTGRES_USER
# ENV POSTGRES_DATABASE=$POSTGRES_DATABASE
# ENV POSTGRES_PASSWORD=$POSTGRES_PASSWORD
# ENV POSTGRES_PORT=$POSTGRES_PORT
RUN apk add nginx

RUN mv etc/nginx/http.d/default.conf etc/nginx/http.d/default_old.conf
RUN rm /etc/nginx/nginx.conf
COPY nginx.conf /etc/nginx/nginx.conf

RUN mkdir -p var/www/html
COPY pages/ /var/www/html

RUN cd /var/www/html/ && mkdir data
# RUN chown -R www:www /var/lib/nginx && chown -R www:www /www

RUN CONTAINER_IP=$(hostname -i) && sed -i 's/proxy_pass/proxy_pass http\:\/\/'$CONTAINER_IP'\:8080;/g' /etc/nginx/nginx.conf
RUN sed -i 's/fastcgi_pass/fastcgi_pass php\:9000;/g' /etc/nginx/nginx.conf

COPY pages/ /var/www/html/
RUN mkdir -p /var/www/html/src/images/
COPY src/images/ /var/www/html/src/images/

# Set the correct permissions on directories and files. Directories should be 755
# (readable and executable by all users but only writable by the owner),
# and files should be 644 (readable by all users but only writable by the owner).
# This setup enhances security by limiting who can write to the files.
RUN find /var/www/html/ -type d -exec chmod 755 {} \;
RUN find /var/www/html/ -type f -exec chmod 644 {} \;

RUN chown -R nginx:nginx /var/www/html

RUN chmod u+rx /var/www/html/home/photo.js
EXPOSE 8080

CMD ["nginx", "-c", "/etc/nginx/nginx.conf", "-g", "daemon off;"]
# For Docker containers (or for debugging), the daemon off; directive tells Nginx to stay in the foreground.
# For containers this is useful as best practice is for one container = one process. One server (container) has only one service.