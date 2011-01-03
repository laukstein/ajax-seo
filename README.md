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

 - Ajax bug for browsers that does not support `pushState` (IE, Firefox 3.6.13, Opera 11) and does redirecting to hash address `#/` or `#!/`. 
   If you try to refresh [page](http://lab.laukstein.com/address-json/#!/contact), DOM refresh-<i>jumps</i> content from [/](http://lab.laukstein.com/address-json/) to [/#!/contact](http://lab.laukstein.com/address-json/#!/contact). 
   You can see it better in browser title bar.
 - IE7 browser refresh address changes from [#!/контакты](http://lab.laukstein.com/address-json/#!/контакты) to [#!/ÐºÐ¾Ð½ÑÐ°ÐºÑÑ](http://alab.laukstein.com/address-json/#!/ÐºÐ¾Ð½ÑÐ°ÐºÑÑ) and [#!/ÃÂºÃÂ¾ÃÂ½ÃÂÃÂ°ÃÂºÃÂÃÂ](http://alab.laukstein.com/address-json/#!/ÃÂºÃÂ¾ÃÂ½ÃÂÃÂ°ÃÂºÃÂÃÂ)


### Speed Performance:

 - `$.ajax() json` vs <strike>[$.getJSON()](http://jsperf.com/getjson-vs-ajax-json)</strike>
 - `document.title=data.title` vs <strike>[$('title').html(data.title);](http://jsperf.com/rename-title)</strike>
 - `encodeURIComponent()` vs <strike>[encodeURI()](http://jsperf.com/encodeuri-vs-encodeuricomponent)</strike>
 - `decodeURI()` vs <strike>[decodeURIComponent()](http://jsperf.com/decodeuri-vs-decodeuricomponent)</strike>


> jQuery Address Plugin based on [https://github.com/asual/jquery-address](https://github.com/asual/jquery-address)