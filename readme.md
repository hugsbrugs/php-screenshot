# php-screenshot

This librairy provides utilities function to ease screenshot generation with different providers in order to always get a fallback in case first provider fails to generate screenshot.

[![Build Status](https://travis-ci.org/hugsbrugs/php-screenshot.svg?branch=master)](https://travis-ci.org/hugsbrugs/php-screenshot)
[![Coverage Status](https://coveralls.io/repos/github/hugsbrugs/php-screenshot/badge.svg?branch=master)](https://coveralls.io/github/hugsbrugs/php-screenshot?branch=master)

## Notes

Currently 3 providers are available :
[Manet](https://github.com/vbauer/manet)
[Apercite](http://www.apercite.fr/)
[PagePeeker](http://pagepeeker.com/)

You're welcome to suggest others providers.

## Install

Install package with composer
```
composer require hugsbrugs/php-screenshot
```

In your PHP code, load library
```php
require_once __DIR__ . '/../vendor/autoload.php';
use Hug\Screenshot\Screenshot as Screenshot;
```

You first have to define 3 constants
```php
define('HUG_SCREENSHOT_SAVE_PATH', '/path/to/screenshots/');
define('HUG_SCREENSHOT_PROVIDERS', '/path/to/providers.json');
define('HUG_SCREENSHOT_CACHE', 'P1W');
```

HUG_SCREENSHOT_SAVE_PATH should be writable by webserver user

HUG_SCREENSHOT_PROVIDERS is a json file like following

```json
{
	"1": {
		"provider":"Manet",
		"basic_auth_user":"USERNAME",
		"basic_auth_pass":"PASSWORD",
		"scheme":"http OR https",
		"host":"HOSTNAME_OR_IP",
		"port":"PORT",
		"params":{
			"engine":"slimerjs|phantomjs",
			"format":"jpg|png|gif",
			"width":"1024",
			"delay":"3000",
			"quality":"0.9"
		}
	},
	"2":{
		"provider":"Apercite"
	},
	"3":{
		"provider":"PagePeeker",
		"code":"YOUR_CODE",
		"entrypoint":"free",
		"size":"x",
		"refresh":"",
		"wait":""
	}
}
```

Please referer to each provider documentation for option details.

## Usage

```php
$Screenshot = new Screenshot();

$url = 'https://hugo.maugey.fr';
$widths = ['1024', '768', '480'];
$screenshot = $Screenshot->shot($url, $widths);
error_log(print_r($screenshot, true));
```
with outputs
```php
[status] => success
[message] => 
[images] => Array
    (
        [1024] => hugo.maugey.fr-1024xauto-1492020456.jpg
        [768] => hugo.maugey.fr-768xauto-1492020467.jpg
        [480] => hugo.maugey.fr-480xauto-1492020475.jpg
    )
[details] => Array
```

Screenshot filenames are generated with following conventions :
- URL
- width x height
- timestamp


## Unit Tests

```
composer exec phpunit
```

## Author

Hugo Maugey [visit my website ;)](https://hugo.maugey.fr)

