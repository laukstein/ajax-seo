# [Ajax SEO v5](https://lab.laukstein.com/ajax-seo)

**Ajax SEO** crawlable webapp framework with boosted UX.<br>
Demo <https://lab.laukstein.com/ajax-seo>

    as { // Readable API
        // The focused DOM Element based on as.url
        activeElement: a.active,

        // Google Analytics ID (optional)
        analytics: "UA-XXXX-Y",

        // Respect user agent DNT
        dnt: true,

        // Google Analytics domain (optional)
        domain: "laukstein.com",

        // Detect if shown error page
        error: false,

        // Project root
        origin: "https://lab.laukstein.com/ajax-seo",

        // Current page title
        title: "History",

        // Current page URL
        url: "https://lab.laukstein.com/ajax-seo/history",

        // Ajax SEO version
        version: "5.3.0",

        // Maximal width of device adaptation
        viewportWidth: 720
    }


## Quick start

1. [Download the recent code](https://github.com/laukstein/ajax-seo/archive/master.zip)
2. Extract on compatible Web server
3. Open in browser and setup settings

Here, `<a href=history>History</a>` requests API `api/history`.

Minimum server requirements Apache 2 + mod_rewrite, MySQL 5, PHP 5.2.<br>
Recommended settings in [`~config`](~config).<br>
Place robots.txt and humans.txt in website root.


## Benifits

* Cross-platform
* W3C cutting-edge standards
* Native HTML5.1 APIs, Microdata, JavaScript
* SEO accessible, crawlable and indexable
* HTML auto minify, Grade-A performance, security and usability
* Simple, responsive, intuitive, maintainable
* [Future plans](https://github.com/laukstein/ajax-seo/wiki/Plans)


## Techniques

* No-dependency
* [no-www](http://no-www.org) domain
* [HTTPS-Only Standard](https://https.cio.gov)
* Respect DNT (Do Not Track)
* Protocol-relative URL [RFC 3986](http://tools.ietf.org/html/rfc3986#section-4.2)
* SEO URLs, lowercasing and gibberish cleanup
* Performance tuning in front end and back end
* [Avoid](http://dowebsitesneedtolookexactlythesameineverybrowser.com) outdated browser support

Legacy browser support in [earlier releases](https://github.com/laukstein/ajax-seo/releases).


## License

Released under the [ISC License](LICENSE).
