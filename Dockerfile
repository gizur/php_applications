# Apache2
#
# VERSION               0.0.1

FROM     base
MAINTAINER Prabhat Khera "prabhat.khera@gmail.com"

RUN apt-get -y update

RUN dpkg-divert --local --rename --add /sbin/initctl
RUN ln -s /bin/true /sbin/initctl

RUN apt-get install -y apache2 php5 php5-common php5-mysql php5-curl php5-dev bzip2 wget openssl ssl-cert git build-essential

#PHP-REDIS
RUN git clone git://github.com/nicolasff/phpredis.git
RUN cd phpredis
RUN phpize
RUN ./configure
RUN make
RUN make install
RUN echo "extension=redis.so > /etc/php5/conf.d/redis.ini"

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2

# Enable mode-rewrite
RUN a2enmod rewrite
# Restart after enabling mode rewrite
RUN service apache2 restart
# Remove default html file
RUN rm -f /var/www/index.html
# Add everything containing folder to /var/www
ADD . /var/www
# Update the default server file & restart the apache
RUN cp -f ./instance-configuration/docker/apache2/sites-available/default /etc/apache2/sites-available/
RUN service apache2 restart

EXPOSE 80

CMD ["/usr/sbin/apache2", "-D", "FOREGROUND"]
