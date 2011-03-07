# [Ajax SEO maximized performance - speed, availability, user-friendly](//lab.laukstein.com/jsonp-ajax-seo/)
Ajax SEO is based on latest Web Technology (HTML5, JSON, jQuery, CSS3). Web server requirements: PHP 5.3, MySQL 5, Apache 2.
    
    
    var timer=window.setTimeout(function(){
        $('#content').html('Loading seems to be taking a while.');
    },3800),clearTimeout=window.clearTimeout(timer);
    $.ajax({
        type:"GET",
        url:encodeURIComponent(event.path.substr(1))+'.json',
        dataType:'json',
        cache:true,
        jsonpCallback:'i',
        beforeSend:function(){
            document.title='Loading...';
            $('#content').fadeTo(200,0.33);
        },
        success:function(data){
            clearTimeout;
            document.title=data.fn+'<?php echo$title?>';
            $('#content').fadeTo(20,1).html(data.content);
        },
        error:function(){
            clearTimeout;
            document.title='404 Page not found';
            $('#content').fadeTo(20,1).removeAttr('style').html('<h1>404 Page not found</h1>\r<p>Sorry, this page cannot be found.</p>\r');
        }
    });
    
    
### Search engine optimization

 -  HTML5, `pushState` with crawlable fallback
 -  [Ajax crawling](//code.google.com/web/ajaxcrawling/docs/getting-started.html) with `?_escaped_fragment_=/$` 301 redirect to `/$`
 -  Rewrite www to no-www domain
 -  Slash and backslash issues
 -  Rewrite uppercase letter URL to lowercase
 -  Rewrite space and underscore with dash
 -  Remove .php extension
 -  Remove comma
 -  404 error page


### Speed Performance

 -  `jQuery $.ajax() timeout` vs `window.setTimeout()` [jsperf](//jsperf.com/jquery-ajax-jsonp-timeout-performormance)
 -  `Ajax JSONP` vs `Ajax JSON` [jsperf](//jsperf.com/ajax-jsonp-vs-ajax-json)
 -  `$.ajax() json` vs `$.getJSON()` [jsperf](//jsperf.com/getjson-vs-ajax-json)
 -  `document.title=data.title` vs `$('title').html(data.title)` [jsperf](//jsperf.com/rename-title)
 -  `encodeURIComponent()` vs `encodeURI()` [jsperf](//jsperf.com/encodeuri-vs-encodeuricomponent)
 -  `decodeURI()` vs `decodeURIComponent()` [jsperf](//jsperf.com/decodeuri-vs-decodeuricomponent)


### Known bugs

 -  jQuery Address - on browsers that does not support `pushState` (IE, FF > 4, Opera) if you'll try to refresh [page](//lab.laukstein.com/ajax-seo/#!/contact), you'll notice *jumping* content from 'Home' to 'Contact' in the title and content
 -  jQuery Address - on browsers that supports `pushState` (Crome, Safari. FF 4) `/#/url` and `/#!/url` *jumps* from `/` to `/url`
 -  jQuery Address - browsers that does not support `pushState` must have redirect from `/#/url` to `/#!/url`
 -  jQuery Address - jQuery Address breaks on IE6 click to non Latin character URLs, it *jumping* from `/` to `/#!/url`
 -  jQuery Address - for request `/url` or `/#!/url` (not click event) do not load/refresh `$.ajax` any content `DOM`, just change address url (if needed), add history
 -  jQuery - bug for $.ajax() ifModified cache
 -  jQuery - bug for .fadeTo() on IE
 -  W3C - [Not validated CSS3 vendor-specific prefixes, like -webkit-, -moz-, -o- etc.](//www.w3.org/Bugs/Public/show_bug.cgi?id=11989)
 -  W3C - [border-radius throws Parse Error [empty string]](//www.w3.org/Bugs/Public/show_bug.cgi?id=11975)
 -  Apache and IE - domain.com//контакты rewrited to urlencode domain.com/%D0%BA%D0%BE%D0%BD%D1%82%D0%B0%D0%BA%D1%82%D1%8B
 -  Apache - rewrite bug for -d and QUERY_STRING
 -  Apache - HTTP Header validation bug `The 304 response is not allowed to have a body.` for [robots.txt](http://redbot.org/?uri=http://lab.laukstein.com/ajax-seo/robots.txt) and [sitemap.xml](http://redbot.org/?uri=http://lab.laukstein.com/ajax-seo/sitemap.xml)


### Installation

 -  Use Apache settings from content/httpd.conf
 -  Use PHP settings from content/php.ini or uncomment php_flag, php_value from .htaccess
 -  Add your MySQL settings in content/connect.php
 -  Run content/ajax-seo.sql SQL queries on your database (through phpMyAdmin)


> jQuery Address Plugin based on [github.com/asual/jquery-address](//github.com/asual/jquery-address)