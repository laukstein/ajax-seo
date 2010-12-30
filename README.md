# jQuery Address JSON
SEO HTML5 pushState and fallback hash with PHP/MySQL/RewriteRule.

    $.getJSON(event.path.substr(1)+'.json',function(data){
        document.title=data.title; // Faster then $('title').html(data.title); http://jsperf.com/rename-title
        $('#content').html(data.content);
    });

jQuery Address based on https://github.com/asual/jquery-address