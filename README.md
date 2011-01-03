# jQuery [Address JSON](http://lab.laukstein.com/address-json/)
SEO HTML5 pushState and fallback hash with PHP/MySQL/RewriteRule.
    
    $.ajax({
        type:"GET",
        url:encodeURIComponent(event.path.substr(1))+'.json',
        dataType:'json',
        //cache:false,
        async:false,
        success:function(data){
            document.title=data.title;
            $('#content').html(data.content);
        },
        error:function(request,status,error){
            $('#content').html('The request failed. Try to refresh page.');
        }
    });
    

### Known bugs:

 -  For browsers that does not support `pushState` (IE, >Firefox 4, Opera) if you'll try to refresh [page](http://lab.laukstein.com/address-json/#!/contact), you'll notice *jumping* content from 'Home' to 'Contact' in browser title and in page content. 
 -  IE7 browser refresh address changes from [#!/контакты](http://lab.laukstein.com/address-json/#!/контакты) to `#!/ÐºÐ¾Ð½ÑÐ°ÐºÑÑ` and `#!/ÃÂºÃÂ¾ÃÂ½ÃÂÃÂ°ÃÂºÃÂÃÂ`


### Speed Performance:

 -  `$.ajax() json` vs `$.getJSON()` <http://jsperf.com/getjson-vs-ajax-json>
 -  `document.title=data.title` vs `$('title').html(data.title)` <http://jsperf.com/rename-title>
 -  `encodeURIComponent()` vs `encodeURI()` <http://jsperf.com/encodeuri-vs-encodeuricomponent>
 -  `decodeURI()` vs `decodeURIComponent()` <http://jsperf.com/decodeuri-vs-decodeuricomponent>


> jQuery Address Plugin based on <https://github.com/asual/jquery-address>