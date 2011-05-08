# [Ajax SEO maximized performance - speed, availability, user-friendly](//lab.laukstein.com/jsonp-ajax-seo/)
Ajax SEO is based on latest Web Technology (HTML5, JSON, jQuery, CSS3). Web server requirements: PHP 5.3, MySQL 5, Apache 2.
    
    
    $.address.crawlable(1).init(function(){
        $('#nav a').address();
    }).change(function(event){
        var timer=window.setTimeout(function(){
            $('#content').html('Loading seems to be taking a while.');
        },3800),clearTimeout=window.clearTimeout(timer);
        $.ajax({
            type:"GET",
            url:'api'+(event.path.length!=1 ? '/'+encodeURIComponent(event.path.toLowerCase().substr(1)) : ''),
            dataType:'json',
            cache:true,
            beforeSend:function(){
                document.title='Loading...';
                $('#content').fadeTo(200,0.33);
            },
            success:function(data,textStatus,jqXHR){
                clearTimeout;
                $('#nav a').each(function(){
                    if($(this).attr('href')==(($.address.state()+decodeURI(event.path)).replace(/\/\//,'/'))){
                        $(this).parent('li').addClass('selected').focus();
                    }else{
                        $(this).parent('li').removeAttr('class');
                    }
                });
                document.title=data.fn+'<?php echo$title?>';
                $('#content').fadeTo(20,1).html(data.content);
            },
            error:function(jqXHR,textStatus,errorThrown){
                clearTimeout;
                $('li a').each(function(){
                    $(this).parent('li').removeAttr('class');
                });
                document.title='404 Page not found';
                $('#content').fadeTo(20,1).removeAttr('style').html('<h1>404 Page not found</h1>\r<p>Sorry, this page cannot be found.</p>\r');
            }
        });
    });
    
    
### Search engine optimization

 -  HTML5, `pushState` with crawlable fallback
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

 -  jQuery Address - browsers that does not support `pushState` (IE, FF > 4, Opera): must use `/#!/url` instead of `/#/url` and remove `/#` and `/#!`
 -  jQuery Address - browsers that does not support `pushState`: if you'll try to refresh [page](//lab.laukstein.com/ajax-seo/#!/contact), you'll notice *jumping* content from 'Home' to 'Contact' in the title and content
 -  jQuery Address - browsers that supports `pushState` (Crome, Safari, FF4): `/#/url` and `/#!/url` *jumps* from `/` to `/url`
 -  jQuery Address - browsers that supports `pushState`: `/url` or `/#!/url` request (not click event) - avoid `$.ajax` request and change address url (if needed), add history
 -  jQuery Address - if `$.ajax` requested content is not modificeted - avoid `fadeTo()` and use browser cached data without repeated `$.ajax` request
 -  FF4 bug [635548](//bugzilla.mozilla.org/show_bug.cgi?id=635548): "attempt to run compile-and-go script on a cleared scope"
 -  W3C - [Not validated CSS3 vendor-specific prefixes, like -webkit-, -moz-, -o- etc.](//www.w3.org/Bugs/Public/show_bug.cgi?id=11989)
 -  W3C - [border-radius throws Parse Error [empty string]](//www.w3.org/Bugs/Public/show_bug.cgi?id=11975)
 -  Apache and IE - domain.com//контакты rewrited to urlencode domain.com/%D0%BA%D0%BE%D0%BD%D1%82%D0%B0%D0%BA%D1%82%D1%8B


### How to use

 -  Apache settings in content/httpd.conf
 -  PHP settings in content/php.ini or php_flag, php_value's in .htaccess
 -  MySQL settings in content/connect.php
 -  **For MySQL UPDATE use SET `pubdate=NOW()` to affected cache!**


> jQuery Address Plugin based on [github.com/asual/jquery-address](//github.com/asual/jquery-address)