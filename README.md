# [Ajax SEO v3](http://lab.laukstein.com/ajax-seo/)
**Extend user experience**

Ajax SEO is Webpp framework to deliver outstanding UX.<br>
Demo in <http://lab.laukstein.com/ajax-seo/>.


## Quick start

1. [Download the recent code](https://github.com/laukstein/ajax-seo/archive/master.zip)
2. Extract on compatible Web server
3. Open in browser and setup settings

```html
<!-- Apply Ajax SEO by adding class "x" to any hyperlink -->
<a class=x href=request>Example</a>
```

Minimum server requirements Apache 2 + mod_rewrite, MySQL 5, PHP 5.2.<br>
[Apache httpd.conf](config/httpd.conf), [MySQL my.cnf](config/my.cnf) and [PHP php.ini](config/php.ini) recommended settings.<br>
Place robots.txt and humans.txt in website root.


## Goals
**Make one work for everyone**

* Cross-platform compatibility
* W3C cutting-edge standards
* Native JavaScript, HTML5 APIs
* SEO crawlable and indexable, Microdata
* Grade-A performance, security and usability
* Simple, responsive, intuitive, maintainable


## Techniques
**Think hard to make life easy**

* Less dependencies
* Use [no-www](http://no-www.org) domain
* Protocol-relative URL [RFC 3986](http://tools.ietf.org/html/rfc3986#section-4.2)
* URL lowercasing and gibberish cleanup
* Schema.org Microdata markup and Open Graph protocol
* Optimized Google Universal Analytics
* Performance tuning on Apache, MySQL and PHP
* Avoid outdated browser support

Legacy browser support available in [earlier releases](releases).


## License

Released under [the MIT License](LICENSE).