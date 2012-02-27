# [AJAX SEO](http://lab.laukstein.com/ajax-seo/)
AJAX SEO is highly optimized AJAX crawlable framework that delivers you maximized SEO and speed performance, accessibility and usability. <http://lab.laukstein.com/ajax-seo/> based on latest Web technology, HTML5, CSS3, Microdata, JSON, jQuery.

Server requirements: PHP 5, MySQL 5, Apache 2.
Clone `git clone git@github.com:laukstein/ajax-seo.git` or [download it](https://github.com/laukstein/ajax-seo/zipball/master).


## How to use

* Apply Apache settings from content/httpd.conf
* Apply PHP settings from content/php.ini or uncomment .htaccess `php_flag` and `php_value`
* Apply MySQL settings from content/connect.php
* For MySQL UPDATE use SET `pubdate=NOW()` to affected cache
* Add humans.txt and robots.txt in website root


## Search engine optimization

* Schema.org Microdata markup
* HTML5 history API for Chrome 10, FF4, IE10, Safari 5, Opera 11.5 with crawlable SEO fallback for older browsers
* AJAX crawling scheme with Apache Rewrite, <https://developers.google.com/webmasters/ajax-crawling/docs/getting-started>
* Rewrite www to no-www domain, <http://no-www.org>
* Slash and backslash issues
* Rewrite uppercase letter URLs to lowercase
* Rewrite space and underscore with dash
* Remove .php extension
* Remove dot and comma
* 404 error page


## Speed performance

* Performance tuning on Apache, PHP, MySQL
* Protocol-relative URL, <http://paulirish.com/2010/the-protocol-relative-url/>
* [jsPerf](http://jsperf.com/jquery-ajax-jsonp-timeout-performormance) `jQuery $.ajax() timeout` vs `window.setTimeout()`
* [jsPerf](http://jsperf.com/ajax-jsonp-vs-ajax-json) `AJAX JSONP` vs `AJAX JSON`
* [jsPerf](http://jsperf.com/getjson-vs-ajax-json) `$.ajax() json` vs `$.getJSON()`
* [jsPerf](http://jsperf.com/rename-title) `document.title=data.title` vs `$('title').html(data.title)`
* [jsPerf](http://jsperf.com/encodeuri-vs-encodeuricomponent) `encodeURIComponent()` vs `encodeURI()`
* [jsPerf](http://jsperf.com/decodeuri-vs-decodeuricomponent) `decodeURI()` vs `decodeURIComponent()`


## Known bugs

* Apache - domain.com/ajax-seo/path/index.php has redirect 301 to domain.com/path/
* jQuery Address - rewrite `/#/url` to `/#!/url`
* jQuery Address - avoid `$.ajax()` when content is cached, not modified
* W3C - CSS3 standards does not accept [vendor prefixes](//www.w3.org/Bugs/Public/show_bug.cgi?id=11989)


jQuery Address plugin based on <https://github.com/asual/jquery-address>.