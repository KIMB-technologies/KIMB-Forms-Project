# KIMB-Forms-Project Install

## Server
- Make sure to lock the following folders for HTTP-Requests:
	- `/core/*`
	- `/data/*`
- Make sure the server can write the following dirs:
	- `/data/*`
- Point the errors to
	- `/?task=error`

## Configuration
- Fill the values in:
	- `/data/config.json`

### URL Rewrite
Will be supported in future.

> Format: `/<task>/<poll|admin>/[<param>/<value>]`
