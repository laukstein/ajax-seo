# [Ajax SEO maximized performance - speed, accessibility, user-friendly](http://lab.laukstein.com/ajax-seo/)
Ajax SEO is based on latest Web Technology (HTML5, Microdata, JSON, jQuery, CSS3). Server requirements: PHP 5, MySQL 5, Apache 2.
    
    
    var nav=$('header nav a');
    $.address.crawlable(1).state('<?php if(strlen(utf8_decode($path))>1){echo substr($path,0,-1);}else{echo$path;}?>').init(function(){
        nav.address();
    }).change(function(e){
        var content=$('#content'),
            timer=window.setTimeout(function(){ // Implement for timeout
                content.html('Loading seems to be taking a while.');
            },3800),clearTimeout=window.clearTimeout(timer);
        $.ajax({
            type:'GET',
            url:'api'+(e.path.length!=1 ? '/'+encodeURIComponent(e.path.toLowerCase().substr(1)) : ''),
            dataType:'json',
            cache:true,
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
 -  HTML5 `pushState` and `replaceState` **(Chrome 10, Firefox 4, Safari 5, Opera 11.5)** with crawlable SEO fallback
 -  Rewrite query string, [Making AJAX Applications Crawlable](//code.google.com/web/ajaxcrawling/docs/getting-started.html)
 -  Rewrite www to no-www domain
 -  Slash and backslash issues
 -  Rewrite uppercase letter URL to lowercase
 -  Rewrite space and underscore with dash
 -  Remove .php extension
 -  Remove dot
 -  Remove comma
 -  404 error page


### Speed Performance

 -  [jsperf](http://jsperf.com/jquery-ajax-jsonp-timeout-performormance) `jQuery $.ajax() timeout` vs `window.setTimeout()`
 -  [jsperf](http://jsperf.com/ajax-jsonp-vs-ajax-json) `Ajax JSONP` vs `Ajax JSON`
 -  [jsperf](http://jsperf.com/getjson-vs-ajax-json) `$.ajax() json` vs `$.getJSON()`
 -  [jsperf](http://jsperf.com/rename-title) `document.title=data.title` vs `$('title').html(data.title)`
 -  [jsperf](http://jsperf.com/encodeuri-vs-encodeuricomponent) `encodeURIComponent()` vs `encodeURI()`
 -  [jsperf](http://jsperf.com/decodeuri-vs-decodeuricomponent) `decodeURI()` vs `decodeURIComponent()`


### Known bugs

 -  jQuery Address - browsers that does not support `pushState` like IE must rewrite `/#/url` to `/#!/url`
 -  jQuery Address - FF6 : `/#/url`, `/#!/url` redirected to `/url` and has two XHR requests `/api` and `/api/url`
 -  jQuery Address - IE : `/url` redirected to `/#!/url` and has two XHR requests `/api` and `/api/url`
 -  jQuery Address - avoid `$.ajax()` for the first open url
 -  jQuery Address - avoid `$.ajax()` when content is in cache and is not modified
 -  W3C - CSS3 standards does not accept [any vendor prefix (-webkit-, -moz-, -o-, -khtml-, -ms-)](//www.w3.org/Bugs/Public/show_bug.cgi?id=11989)
 -  Apache and IE - domain.com//контакты rewrited to urlencode domain.com/%D0%BA%D0%BE%D0%BD%D1%82%D0%B0%D0%BA%D1%82%D1%8B
 -  Apache - domain.com/ajax-seo returns 403 because of `DirectorySlash Off`
 -  Apache - domain.com/ajax-seo/path/index.php has redirect 301 to domain.com/path/


### How to use

 -  Apache settings in content/httpd.conf
 -  PHP settings in content/php.ini or php_flag, php_value's in .htaccess
 -  MySQL settings in content/connect.php
 -  **For MySQL UPDATE use SET `pubdate=NOW()` to affected cache!**
 -  Add humans.txt and robots.txt in website root


> jQuery Address Plugin based on [github.com/asual/jquery-address](//github.com/asual/jquery-address)