# [Ajax SEO maximized performance - speed, availability, user-friendly](http://lab.laukstein.com/jsonp-ajax-seo/)
Ajax SEO is based on latest Web Technology (HTML5, JSONP, jQuery, CSS3). Web server requirements: PHP, MySQL, Apache.
    
    $.ajax({
        type:"GET",
        url:event.path+'.json',
        dataType:'jsonp',
        success:function(data){
            document.title=data.title;
            $('#content').html(data.content);
        }
    });
    

### Search engine optimization

 -  HTML5 tags, `pushState` with crawlable fallback
 -  [Ajax crawling](http://code.google.com/web/ajaxcrawling/docs/getting-started.html) with `?_escaped_fragment_=/url` 301 redirect to `url`
 -  Trailing slashes issues
 -  Rewrite uppercase letter URL to lowercase
 -  Rewrite space and underscore with dash
 -  Remove .php extension
 -  Remove comma


### Speed Performance

 -  `$.ajax() json` vs `$.getJSON()` <http://jsperf.com/getjson-vs-ajax-json>
 -  `document.title=data.title` vs `$('title').html(data.title)` <http://jsperf.com/rename-title>
 -  `encodeURIComponent()` vs `encodeURI()` <http://jsperf.com/encodeuri-vs-encodeuricomponent>
 -  `decodeURI()` vs `decodeURIComponent()` <http://jsperf.com/decodeuri-vs-decodeuricomponent>


### Known bugs

 -  For browsers that does not support `pushState` (IE, Firefox > 4, Opera) if you'll try to refresh [page](http://lab.laukstein.com/ajax-seo/#!/contact), you'll notice *jumping* content from 'Home' to 'Contact' in browser title and in page content
 -  For browsers that does not support `pushState` (IE, Firefox > 4, Opera) must be redirect from /#/url to /#!/url
 -  IE7 browser refresh rewrites non Latin character URL to gibberish, [#!/контакты](http://lab.laukstein.com/ajax-seo/#!/контакты) becomes `#!/ÐºÐ¾Ð½ÑÐ°ÐºÑÑ` and `#!/ÃÂºÃÂ¾ÃÂ½ÃÂÃÂ°ÃÂºÃÂÃÂ` etc.
 -  Crome 8.0.552.224 links like `/#/url` or `/#!/url` *jumps* from `/` to `/url`
 -  Apache rewrite /контакты// redirects to /%2525d0%2525ba%2525d0%2525be%2525d0%2525bd%2525d1%252582%2525d0%2525b0%2525d0%2525ba%2525d1%252582%2525d1%25258b


### Installation

 -  Add your MySQL settings in connect.php
 -  Run ajax_seo.sql SQL queries on your database (through phpMyAdmin)


> jQuery Address Plugin based on <https://github.com/asual/jquery-address>