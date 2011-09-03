# [Ajax SEO maximized performance - speed, accessibility, user-friendly](//lab.laukstein.com/jsonp-ajax-seo/)
Ajax SEO is based on latest Web Technology (HTML5, Microdata, JSON, jQuery, CSS3). Server requirements: PHP 5, MySQL 5, Apache 2.
    
    
    var nav=$('#nav a');
    $.address.crawlable(1).state('<?php if(strlen(utf8_decode($path))>1){echo substr($path,0,-1);}else{echo$path;}?>').init(function(){
        nav.address();
    }).change(function(e){
        var content=$('#content');
        var timer=window.setTimeout(function(){ // Implement for timeout
            content.html('Loading seems to be taking a while.');
        },3800),clearTimeout=window.clearTimeout(timer);
        $.ajax({
            type:"GET",
            url:/*'http://lab.laukstein.com/ajax-seo/'+*/'api'+(e.path.length!=1 ? '/'+encodeURIComponent(e.path.toLowerCase().substr(1)) : ''),
            dataType:'json',        // jsonp
            cache:true,
            //jsonpCallback:'i',    // JSONP cache issue
            beforeSend:function(){
                document.title='Loading...';
                content.fadeTo(200,0.33);
            },
            success:function(data,textStatus,jqXHR){
                clearTimeout;
                nav.each(function(){
                    if($(this).attr('href')==(($.address.state()+decodeURI(e.path)).replace(/\/\//,'/'))){
                        $(this).addClass('selected').focus();
                    }else{
                        $(this).removeAttr('class');
                    }
                });
                document.title=data.title+'<?php echo$additional_title?>';
                content.fadeTo(20,1).removeAttr('style').html(data.content);
                if($.browser.msie){content.removeAttr('filter');}
            },
            error:function(jqXHR,textStatus,errorThrown){
                clearTimeout;
                nav.each(function(){
                    $(this).removeAttr('class');
                });
                document.title='404 Page not found';
                content.fadeTo(20,1).removeAttr('style').html('<h1>404 Page not found</h1>\r<p>Sorry, this page cannot be found.</p>\r');
                if($.browser.msie){content.removeAttr('filter');}
            }
        });
    });
    
    
### Search engine optimization

 -  Schema.org Microdata markup
 -  HTML5 `pushState` and `replaceState` with crawlable fallback
 -  Rewrite query string, [Ajax crawling](//code.google.com/web/ajaxcrawling/docs/getting-started.html)
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

 -  jQuery Address - browsers that does not support `pushState` like IE must rewrite `/#/url` to `/#!/url`
 -  jQuery Address - browsers that does not support `pushState`: if you'll try to refresh [page](//lab.laukstein.com/ajax-seo/#!/contact), you'll notice *jumping* content from 'Home' to 'Contact' in the title and content
 -  jQuery Address - browsers that supports `pushState` **(Chrome 10, Firefox 4, Safari 5, Opera 11.5)**: `/#/url` and `/#!/url` *jumps* from `/` to `/url`
 -  jQuery Address - browsers that supports `pushState`: avoid `$.ajax` request from the first open url
 -  jQuery Address - if `$.ajax` requested content is not modificeted - avoid `fadeTo()` and use browser cached data without repeated `$.ajax` request
 -  W3C - [CSS3 standards does not accept vendor-specific prefixes, like -webkit-, -moz-, -o-, -khtml-, -ms-](//www.w3.org/Bugs/Public/show_bug.cgi?id=11989)
 -  Apache and IE - domain.com//контакты rewrited to urlencode domain.com/%D0%BA%D0%BE%D0%BD%D1%82%D0%B0%D0%BA%D1%82%D1%8B


### How to use

 -  Apache settings in content/httpd.conf
 -  PHP settings in content/php.ini or php_flag, php_value's in .htaccess
 -  MySQL settings in content/connect.php
 -  **For MySQL UPDATE use SET `pubdate=NOW()` to affected cache!**
 -  Add humans.txt and robots.txt in website root


> jQuery Address Plugin based on [github.com/asual/jquery-address](//github.com/asual/jquery-address)