# jQuery [Address JSON](http://lab.laukstein.com/address-json/)
SEO HTML5 pushState and fallback hash with PHP/MySQL/RewriteRule.

    $.getJSON(event.path.substr(1)+'.json',function(data){
        document.title=data.title; // Faster then $('title').html(data.title); http://jsperf.com/rename-title
        $('#content').html(data.content);
    });

Known bugs:

* Cyrillic and Semitic `$.getJSON` requests somehow does not work on IE

* Besides of displaying `#!/контакты` IE in address bar has `#!/%D7%A6%D7%95%D7%A8-%D7%A7%D7%A9%D7%A8` 


jQuery Address Plugin based on [https://github.com/asual/jquery-address](https://github.com/asual/jquery-address)