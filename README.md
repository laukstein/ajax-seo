# [AJAX SEO](http://lab.laukstein.com/ajax-seo/)

AJAX SEO is crawlable framework for AJAX applications that applies the latest SEO standards, Page Speed and YSlow rules, Google HTML/CSS Style Guide, etc. to improve maximal performance, speed, accessibility and usability.
The source code is build on latest Web technology, HTML Living Standard - HTML5, CSS3, Microdata, etc.

[See the demo](<http://lab.laukstein.com/ajax-seo/>)


## How to use

* Server requirements: Apache 2, PHP 5.3, MySQL 5
* [Download the source code](https://github.com/laukstein/ajax-seo/zipball/master) and extract on your web server
* Apply Apache settings from content/httpd.conf
* Apply PHP settings from content/php.ini or uncomment .htaccess `php_flag` and `php_value`
* Apply MySQL settings from content/connect.php
* Add robots.txt and humans.txt in website root


## Search engine optimization

* Schema.org Microdata markup
* HTML5 history API with crawlable SEO fallback for < IE10
* AJAX crawling scheme with Apache Rewrite <https://developers.google.com/webmasters/ajax-crawling/docs/getting-started>
* Rewrite www to no-www domain <http://no-www.org>
* Handle HTTP/HTTPS protocol
* Slash and backslash issues
* Rewrite uppercase URLs to lowercase
* Rewrite space and underscore with dash
* Remove index.php and .php extension
* Remove dot and comma
* Custom 404 error page


## Speed performance

* Performance tuning on Apache, PHP, MySQL
* Relative URL [RFC 3986](http://tools.ietf.org/html/rfc3986#section-4.2)
* [jsPerf](http://jsperf.com/jquery-ajax-jsonp-timeout-performormance) `jQuery $.ajax() timeout` vs `window.setTimeout()`
* [jsPerf](http://jsperf.com/ajax-jsonp-vs-ajax-json) `AJAX JSONP` vs `AJAX JSON`
* [jsPerf](http://jsperf.com/getjson-vs-ajax-json) `$.ajax() json` vs `$.getJSON()`
* [jsPerf](http://jsperf.com/rename-title) `document.title=data.title` vs `$('title').html(data.title)`
* [jsPerf](http://jsperf.com/encodeuri-vs-encodeuricomponent) `encodeURIComponent()` vs `encodeURI()`
* [jsPerf](http://jsperf.com/decodeuri-vs-decodeuricomponent) `decodeURI()` vs `decodeURIComponent()`


## License

AJAX SEO released under MIT license.
jQuery and jQuery Address Plugin dual licensed under the MIT and GPL licenses.