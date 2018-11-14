# KIMB-Forms-Project Install

## Server Requirements
- Webserver running PHP **7**
- PHP-GD for Captchas

## Server Setup
- Make sure to lock the following folders for HTTP-Requests:
	- `/core/*`
	- `/data/*`
- Make sure the server can write the following dirs:
	- `/data/*`
- Point the errors to
	- `/?task=error`

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
    }
}

```

### URL Rewrite
Will be supported in future.

> Format: `/<task>/<poll|admin>/[<param>/<value>]`
