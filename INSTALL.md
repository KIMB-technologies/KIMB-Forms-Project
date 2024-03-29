# Installation
> KIMB-Forms-Project 

- [Docker Install Guide](#docker)
- [Classic Install Guide](#install)
- [Classic Update Guide](#update)

## Notes

### Server Requirements
- Webserver running PHP **7+**
    - PHP 5.6 should work as also, but is not recommended
- PHP-GD for captchas
- Filesystem which supports filelocking `flock()`

### Server Recommendation
- Webserver which allows URL-Rewriting, for speaking urls
	- Apache
	- NGINX Webserver
- SSL-Certificate

## Docker
1. Get the [docker-compose.yml](https://github.com/KIMB-technologies/KIMB-Forms-Project/blob/master/docker-compose.yml).
2. Change the configuation parameter, done via `environment`.  
	In most cases these values need to be changed:
	- `CONF_site_hosturl`, the url where the system can be reached (without `/` at the end)
	- `CONF_site_pagename`, the name of this page
	- `CONF_site_footercontent` some additional text in the footer (Imprint, Terms of Service, etc.)
	- `CONF_texts_*`, change the texts a user has to check before he can participate or disable them
	- `CONF_newpollmailto`, give your admin email adress, you will be informed when a poll is created
3. *If you need one: Create a `CookieBanner.php` and link it into the container.*  
	*Use `/core/external/CookieBanner.php` as template.*
4. KIMB-Forms-Project should be reachable at the published port. 
5. Update to **new versions** can be done by pulling a the new image.

## Install

1. Download the latest release [here](https://github.com/KIMB-technologies/KIMB-Forms-Project/releases/latest)
2. Unpack the archive on the webserver
	- one may remove the folder `/.github/*`
	- one may remove all in Markdown files `/*.md`
	- if not using Apache, the `.htaccess` can be removed
	- remove the `updateLibraries.php`
3. Make sure the follwing directories can not be accessed 
	- if using Apache done by the `/core/.htaccess`, `/data/.htaccess` provided
	- if using NGINX, see the configuation below [&darr; NGINX Configuration](#nginx-configuration)
	- `/core/*`
	- `/data/*`
4. Make sure the server can create and write files in
	- `/data/*`
5. Set up URL-Rewriting, if desired
	- all Requests have to go to `index.php`
		- route to `/index.php?uri=$`, where `$` ist the query path
		- or set the `$_SERVER['REQUEST_URI']` of PHP
	- files in `/load/*` and the `api.php` have to stay accessible
	- if using Apache done by the `/.htaccess` provided
6. Set up the Error Documents (HTTP `404` and `403`)
	- point the errors to
		- `/?task=error404` (`/error404` if using URL-Rewriting) 
		- `/?task=error403` (`/error403` if using URL-Rewriting)
	- if using Apache see the `/.htaccess` provided
7. Edit the system configuration
	- JSON file at `/data/config.json`
	- normally one has to edit
		- `site.hosturl`, the url where the system can be reached (without `/` at the end)
		- `site.pagename`, the name of this page
		- `site.footercontent` some additional text in the footer (Imprint, Terms of Service, etc.)
		- `urlrewrite`, enable URL-Rewriting if set up
		- `texts.*`, change the texts a user has to check before he can participate
			- or disable them
		- `newpollmailto`, give your admin email adress, you will be informed when a poll is created
	- the file:
```javascript
{
    "site": {
        "hosturl": "string", // the (base) URL to the system, without / at the end
        "pagename": "string", // the name of the website
        "footercontent": "string, html" // the content of the footer
    },
    "captcha": {
        "poll": true|false, // enable or disable Captchas when submitting answers
        "new": true|false // enable or disable Captchas when creating new polls
    },
    "texts": {
        "enablePoll": true|false, // enable a text the user has to accept before submitting answers
        "enableNew": true|false, // enable a text the user has to accept before creating polls
        "textPoll": "string, html", // the text, submitting polls
        "textNew": "string, html" // the text, creating polls
    },
    "urlrewrite": true|false // enable or disable speaking urls,
    "cookiebanner": true|false // display a custom cookie banner, /core/external/CookieBanner.php has to be edited!
    "newpollmailto": "string, mail address| empty" // notification when new poll is created
}

```
8. Setup done
	- visit the page and try it out

## Update
1. Download the latest release [here](https://github.com/KIMB-technologies/KIMB-Forms-Project/releases/latest)
2. Backup the `/data/` directory
	- also backup custom `.htaccess` etc.
	- backup your custom `/core/external/CookieBanner.php` if you have one
3. Unpack the new release into the folder
	- can also be done by deleting all files and unpacking the new release
4. Remove the files `translation_de.json` and `translation_en.json` in your *backup* of `/data/`
5. Move your backup back to `/data/`
	- overwrite existsing files, typically
		- `config.json`, `polls.json` and `admincodes.json`
6. Normally there are no changes necessary in the configuration and other `json` files
	- if necessary the release will contain a description what todo
		- e.g. provide an update script


### NGINX Configuration

Example for an NGINX Configuration enabling URL-Rewriting and protecting system directories. (Does what the `.htaccess` do for Apache.)

```nginx

server {
	# listen on port 80 for http
	listen [::]:80;
	listen 80;  
	# add https support if possible!

	# set the url/ server name here
	server_name forms.<<mydomain.tld>>;

	# folder where kimb-forms is located
	root /var/www/kimb-forms/;

	index index.php index.html;

	# url rewriting error pages
	error_page 404 /index.php?uri=err404;
	error_page 403 /index.php?uri=err403;

	# protect private directories
	location ~ ^/(data|core){
		deny all;
		return 403;
	}

	# first try to serve as file or folder, if no file, pass to php
	location / {
		try_files $uri $uri/ @nofile;
	}

	# pass to php incl. request string
	location @nofile {
		rewrite ^(.*)$ /index.php?uri=$1 last;
	}

	# normal php handling
	location ~ \.php$ {
		include snippets/fastcgi-php.conf;
		fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
	}
}


```
