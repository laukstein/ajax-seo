# [Ajax SEO maximized performance - speed, availability, user-friendly](//lab.laukstein.com/jsonp-ajax-seo/)
Ajax SEO is based on latest Web Technology (HTML5, JSONP, jQuery, CSS3). Web server requirements: PHP 5, MySQL 5, Apache 2.
    
    
    var timer=window.setTimeout(function(){
        $('#content').fadeTo(110,1).html('Loading seems to be taking a while.');
    },3800);
    $.ajax({
        type:"GET",
        url:encodeURIComponent(event.path.substr(1))+'.json',
        dataType:'jsonp',
        cache:true,
        jsonpCallback:'i',
        beforeSend:function(){
            document.title='Loading...';
            $('#content').html('Loading...');
        },
        success:function(data){
            window.clearTimeout(timer);
            document.title=data.title;
            $('#content').html(data.content);
        },
        error:function(){
            window.clearTimeout(timer);
            $('#content').html('The request failed.');
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

 -  `$.ajax() json` vs `$.getJSON()` [jsperf](//jsperf.com/getjson-vs-ajax-json)
 -  `document.title=data.title` vs `$('title').html(data.title)` [jsperf](//jsperf.com/rename-title)
 -  `encodeURIComponent()` vs `encodeURI()` [jsperf](//jsperf.com/encodeuri-vs-encodeuricomponent)
 -  `decodeURI()` vs `decodeURIComponent()` [jsperf](//jsperf.com/decodeuri-vs-decodeuricomponent)


### Known bugs

 -  jQuery Address - on browsers that does not support `pushState` (IE, FF > 4, Opera) if you'll try to refresh [page](//lab.laukstein.com/ajax-seo/#!/contact), you'll notice *jumping* content from 'Home' to 'Contact' in the title and content
 -  jQuery Address - on browsers that supports `pushState` (Crome, Safari. FF 4) `/#/$` and `/#!/$` *jumps* from `/` to `/$`
 -  jQuery Address - browsers that does not support `pushState` must have redirect from `/#/$` to `/#!/$`
 -  jQuery Address - jQuery Address breaks on IE6 click to non Latin character URLs, it *jumping* from `/` to `/#!/$`
 -  W3C - [Not validated CSS3 vendor-specific prefixes, like -webkit-, -moz-, -o- etc.](//www.w3.org/Bugs/Public/show_bug.cgi?id=11989)
 -  W3C - [border-radius throws Parse Error [empty string]](//www.w3.org/Bugs/Public/show_bug.cgi?id=11975)
 -  Apache and IE - domain.com//контакты rewrited to urlencode domain.com/%D0%BA%D0%BE%D0%BD%D1%82%D0%B0%D0%BA%D1%82%D1%8B


### Installation

 -  Add your MySQL settings in connect.php
 -  Run ajax_seo.sql SQL queries on your database (through phpMyAdmin)


> jQuery Address Plugin based on [github.com/asual/jquery-address](//github.com/asual/jquery-address)