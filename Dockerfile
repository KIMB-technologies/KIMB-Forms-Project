FROM kimbtechnologies/php_smtp_nginx:latest

# add gd for captcha
RUN apk add --update --no-cache freetype libpng libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev && \
  docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg && \
  NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) && \
  docker-php-ext-install -j${NPROC} gd && \
  apk del --no-cache freetype-dev libpng-dev libjpeg-turbo-dev

# copy sourcecode
COPY --chown=www-data:www-data ./ /php-code/

# place config and translation outside, config done by env
RUN mkdir /sysdata/ \
	&& mv /php-code/data/config.json /sysdata/ \
	&& mv /php-code/data/translation_*.json /sysdata/ \
	&& chown -R www-data:www-data /sysdata/ \
	&& echo $' \n\
	# url rewriting error pages \n\
	error_page 404 /index.php?uri=err404; \n\
	error_page 403 /index.php?uri=err403; \n\
	# protect private directories \n\
	location ~ ^/(data|core){ \n\
		deny all; \n\
		return 403; \n\
	} \n\
	# first try to serve as file or folder, if no file, pass to php \n\
	location / { \n\
		try_files $uri $uri/ @nofile; \n\
	} \n\
	# pass to php incl. request string \n\
	location @nofile { \n\
		rewrite ^(.*)$ /index.php?uri=$1 last; \n\
	} \n\
	' > /etc/nginx/more-server-conf.conf \
	&& rm -rf /php-code/Dockerfile /php-code/.travis.yml /php-code/dockerpublish.sh \
	&& echo "chown -R www-data:www-data /php-code/data/" > /startup-before.sh

# tell the system that it runs in docker container
ENV DOCKERMODE=true \
	CONF_urlrewrite=true
