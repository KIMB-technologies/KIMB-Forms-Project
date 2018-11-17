# KIMB-Forms-Project Install

## Server Requirements
- Webserver running PHP **7**
    - PHP 5.6 should work as well, but is not really supported
- PHP-GD for Captchas

## Server Setup
- Make sure to lock the following folders for HTTP-Requests:
	- `/core/*`
	- `/data/*`
- Make sure the server can write the following dirs:
	- `/data/*`
- Point the errors to
	- `/?task=error404` for HTTP 404 and other
    - `/?task=error403` for HTTP 403

## Configuration
Edit the file `/data/config.json` as follows:

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
    "urlrewrite": true|false // enable or disable speaking urls, server has to query index.php and set uri to get uri param
}

```
