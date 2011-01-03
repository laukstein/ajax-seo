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
    
Known bugs:

* Ajax bug for browsers that does not support `pushState` (IE, Firefox 3.6.13, Opera 11) and does redirecting to hash address `#/` or `#!/`. If you try to refresh [page](http://lab.laukstein.com/address-json/#!/contact), DOM refresh-<i>jumps</i> content from [/](http://lab.laukstein.com/address-json/) to [/#!/contact](http://lab.laukstein.com/address-json/#!/contact). You can see it better in browser title bar.

* IE7 browser refresh [#!/контакты](http://lab.laukstein.com/address-json/#!/контакты) auto-changes to [#!/ÐºÐ¾Ð½ÑÐ°ÐºÑÑ](http://alab.laukstein.com/address-json/#!/ÐºÐ¾Ð½ÑÐ°ÐºÑÑ) and [#!/ÃÂºÃÂ¾ÃÂ½ÃÂÃÂ°ÃÂºÃÂÃÂ](http://alab.laukstein.com/address-json/#!/ÃÂºÃÂ¾ÃÂ½ÃÂÃÂ°ÃÂºÃÂÃÂ) etc.


Speed Performance:

* `$.ajax() json` [<s>$.getJSON()</s>](http://jsperf.com/getjson-vs-ajax-json)

* `document.title=data.title` [<s>$('title').html(data.title);</s>](http://jsperf.com/rename-title)

* `encodeURIComponent()` [<s>encodeURI()</s>](http://jsperf.com/encodeuri-vs-encodeuricomponent)

* `decodeURI()` [<s>decodeURIComponent()</s>](http://jsperf.com/decodeuri-vs-decodeuricomponent)


<i>jQuery Address Plugin based on [https://github.com/asual/jquery-address](https://github.com/asual/jquery-address)</i>