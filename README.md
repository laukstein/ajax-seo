# [Ajax SEO v3](http://lab.laukstein.com/ajax-seo/)
**Extend user experience**

Ajax SEO is crawlable webapp framework for outstanding UX.<br>
Demo in <http://lab.laukstein.com/ajax-seo/>.


## Quick start

1. [Download the recent code](https://github.com/laukstein/ajax-seo/archive/master.zip)
2. Extract on compatible Web server
3. Open in browser and setup settings

Use by adding `class=x` to any API compatible hyperlink.<br>
Here, `<a class=x href=history>href=history</a>` requires API Ajax request `api/history`

Minimum server requirements Apache 2 + mod_rewrite, MySQL 5, PHP 5.2.<br>
Apache, MySQL and PHP recommended settings in [/config](config).<br>
Place robots.txt and humans.txt in website root.


## Benifits
**Have one work for everyone**

* Cross-platform
* W3C cutting-edge standards
* Native JavaScript, HTML5.1 APIs
* SEO accessible, crawlable and indexable
* Grade-A performance, security and usability
* Simple, responsive, intuitive, maintainable


## Techniques
**Think hard to make life easy**

* Less dependencies
* Use [no-www](http://no-www.org) domain
* Protocol-relative URL [RFC 3986](http://tools.ietf.org/html/rfc3986#section-4.2)
* URL lowercasing and gibberish cleanup
* Performance tuning on Apache, MySQL and PHP
* Avoid outdated browser support

Legacy browser support in [earlier releases](releases).


## License

Released under [the MIT License](LICENSE).