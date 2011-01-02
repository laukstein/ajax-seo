# jQuery [Address JSON](http://lab.laukstein.com/address-json/)
SEO HTML5 pushState and fallback hash with PHP/MySQL/RewriteRule.

    $.ajax({ // Faster then $.getJSON() http://jsperf.com/getjson-vs-ajax-json
        type:"GET",
        url:encodeURIComponent(event.path.substr(1))+'.json',
        dataType:'json',
        //cache:false,
        async:false,
        success:function(data){
            document.title=data.title; // Faster then $('title').html(data.title); http://jsperf.com/rename-title
            $('#content').html(data.content);
        },
        error:function(request,status,error){
            $('#content').html('The request failed. Try to refresh page.');
        }
    });
    
Known bugs:

* IE address bar displays `#!/%D7%A6%D7%95%D7%A8-%D7%A7%D7%A9%D7%A8` besides of  `#!/контакты`

* Firefox 3.6.13 on [page](http://lab.laukstein.com/address-json/#!/contact) refresh <i>jumps</i> page from [/](http://lab.laukstein.com) to [/#!/contact](http://lab.laukstein.com/address-json/#!/contact) page content


jQuery Address Plugin based on [https://github.com/asual/jquery-address](https://github.com/asual/jquery-address)