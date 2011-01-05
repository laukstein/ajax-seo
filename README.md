# [JSONP Ajax SEO maximized performance](http://lab.laukstein.com/jsonp-ajax-seo/) - speed, availability, user-friendly
JSONP Ajax SEO is based on Latest Web Technology (HTML5, JSONP, jQuery, CSS3); Web server requirements: PHP, MySQL, Apache.
    
    $.ajax({
        type:"GET",
        url:encodeURIComponent(event.path.substr(1))+'.json',
        dataType:'jsonp',
        jsonpCallback:'a',
        success:function(data){
            document.title=data.title;
            $('#content').html(data.content);
        }
    });
    

### Search engine optimization

 -  HTML5 tags, pushState with crawlable fallback
 -  [Ajax crawling](http://code.google.com/web/ajaxcrawling/docs/getting-started.html) with `?_escaped_fragment_=/friendly-url` 301 redirect to `friendly-url`
 -  no multiple trailing slashes
 -  no index.php


### Speed Performance

 -  `$.ajax() json` vs `$.getJSON()` <http://jsperf.com/getjson-vs-ajax-json>
 -  `document.title=data.title` vs `$('title').html(data.title)` <http://jsperf.com/rename-title>
 -  `encodeURIComponent()` vs `encodeURI()` <http://jsperf.com/encodeuri-vs-encodeuricomponent>
 -  `decodeURI()` vs `decodeURIComponent()` <http://jsperf.com/decodeuri-vs-decodeuricomponent>


### Known bugs

 -  For browsers that does not support `pushState` (IE, >Firefox 4, Opera) if you'll try to refresh [page](http://lab.laukstein.com/address-json/#!/contact), you'll notice *jumping* content from 'Home' to 'Contact' in browser title and in page content
 -  IE7 browser refresh address changes from [#!/контакты](http://lab.laukstein.com/address-json/#!/контакты) to `#!/ÐºÐ¾Ð½ÑÐ°ÐºÑÑ` and `#!/ÃÂºÃÂ¾ÃÂ½ÃÂÃÂ°ÃÂºÃÂÃÂ`
 -  Crome 8.0.552.224 links like `/#/url` or `/#!/url` *jumps* from `/` to `/url`


### Installation

 -  Change your server `$path` in `index.php`
 -  Add your MySQL settings in `connect.php`
 -  Run `jsonp_ajax_seo.sql` SQL queries on your database (through phpMyAdmin)


> jQuery Address Plugin based on <https://github.com/asual/jquery-address>