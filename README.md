# [Ajax SEO](http://lab.laukstein.com/ajax-seo/)

Bring stable, aesthetic, fast and secure application experience with Ajax SEO framework. Ajax SEO based on bleeding edge W3C standards and SEO guides to maximize crawlability, accessibility, usability, performance and security.

Based on W3C, Google, Yahoo, etc. rules to deliver innovative user experience.

See the demo <http://lab.laukstein.com/ajax-seo/>


## How to use

* Minimum server requirements: Apache 2 with mod_rewrite, PHP 5.2, MySQL 5
* [Download the source code](https://github.com/laukstein/ajax-seo/zipball/master) and extract on your web server
* Apply Apache settings from content/httpd.conf
* Apply PHP settings from content/php.ini or uncomment .htaccess `php_flag` and `php_value`
* Apply MySQL settings from content/connect.php
* Add robots.txt and humans.txt in website root
* In order to disable Ajax cache, set `cache: false` in **index.php**


## Search engine optimization

* Schema.org Microdata markup
* HTML5 history API with crawlable SEO fallback for < IE10
* Ajax crawling scheme with Apache Rewrite <https://developers.google.com/webmasters/ajax-crawling/docs/getting-started>
* Rewrite www to no-www domain <http://no-www.org>
* Handle HTTP/HTTPS protocol, protocol-less URL
* Slash and backslash issues
* Rewrite uppercase URLs to lowercase
* Rewrite space and underscore with dash
* Remove index.php and .php extension
* Remove dot and comma
* Custom 404 error page
* Google Universal Analytics


## Speed performance

* Performance tuning on Apache, PHP, MySQL
* Relative URL [RFC 3986](http://tools.ietf.org/html/rfc3986#section-4.2)
* [jsPerf](http://jsperf.com/jquery-ajax-jsonp-timeout-performormance) `jQuery $.ajax() timeout` vs `window.setTimeout()`
* [jsPerf](http://jsperf.com/ajax-jsonp-vs-ajax-json) `Ajax JSONP` vs `Ajax JSON`
* [jsPerf](http://jsperf.com/getjson-vs-ajax-json) `$.ajax() json` vs `$.getJSON()`
* [jsPerf](http://jsperf.com/rename-title) `document.title=data.title` vs `$('title').html(data.title)`
* [jsPerf](http://jsperf.com/encodeuri-vs-encodeuricomponent) `encodeURIComponent()` vs `encodeURI()`
* [jsPerf](http://jsperf.com/decodeuri-vs-decodeuricomponent) `decodeURI()` vs `decodeURIComponent()`


## License

Ajax SEO released under MIT license.
jQuery and jQuery Address Plugin dual licensed under the MIT and GPL licenses.