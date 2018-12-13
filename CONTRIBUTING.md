# Contributing Guidelines

Please create an issue for feature requests and found bugs.  
Also if you are going to fix it directly, create an issue and a pull request,
rearding this issue.

## Code
- UTF-8
- tabs to indent
- class names as `<class>.php`
- camelCase class, variable and method names
- constants ALL_CAPS
- using JavaDoc like PHPDoc
- comments should be english, a bit german is ok

### Example file header

```php
<?php
/** 
 * KIMB-Forms-Project
 * https://github.com/KIMB-technologies/KIMB-Forms-Project
 * 
 * (c) 2018 KIMB-technologies 
 * https://github.com/KIMB-technologies/
 * 
 * released under the terms of GNU Public License Version 3
 * https://www.gnu.org/licenses/gpl-3.0.txt
 */
defined( 'KIMB-FORMS-PROJECT' ) or die('Invalid Endpoint!');

// code

?>
```

## Structure
- normal System classes placed under `/core/`
- libraries under `/core/external`
- api classes under `/core/api`
- init php files for autoloaders and setup, only small letters
- ressources to be loaded by clients under `/load/`
    - if library has multiple files, own folder
- list libraries in `NOTICE.md`
    - add license there and license file near library
    
## Remember
- lightweight
    - don't add a library if you can write your own (not huge) function
    - uses PHP 7, if a function does not exist in older versions, us it nevertheless
        - and add a userland/ fallback implementation in `/core/external/`
- poll submissions have to be possible without javascript
    - they can be less user friendly
- always validate the input
- think about creating new classes and methods instead of adding the same code twice
