<?php

include 'content/config.php';
include 'content/connect.php';
include 'content/cache.php'; cache::url();

$gtitle      = !empty($gtitle) ? $gtitle : title;
$description = null;

if ($conn) {
    $title     = $title_error     = 'Oops...';
    $pagetitle = $pagetitle_error = 'Page not found';
    $content   = $content_error   = '<p>Sorry, this page hasn\'t been found. Try to <a class="x x-error" href=' . $url . '>reload</a> the page or head to <a class="x x-error" href=' . $path . '>home</a>.</p>'; // Old Webkit breaks the layout without </p>
    $results   = false;

    $stmt = $mysqli->prepare('SELECT url, title, description, content FROM `' . table . '` WHERE url=? LIMIT 1');
    $stmt->bind_param('s', $url);
    $stmt->execute();
    $stmt->bind_result($url, $title, $description, $content);

    while ($stmt->fetch()) {
        $results   = true;
        // SEO page title improvement for the root page
        $pagetitle = empty($url) ? $gtitle : $title;
        $content   = string($content);
    }
    $stmt->free_result();
    $stmt->close();

    // URL does not exist
    if (!$results) {
        http_response_code(404);

        $title     = $title_error;
        $content   = $content_error;
        $pagetitle = $pagetitle_error;
    }

    // JSON/JSONP respond
    if (isset($_GET['api'])) {
        include 'content/api.php';
        exit;
    }
}


// Avoid XSS attacks https://w3c.github.io/webappsec/specs/content-security-policy/
$equal_origin = false;
if ($cdn_host) {
    preg_match('/([^.]*\.[^.]{2}|[^.]*)\.([^.]*)$/i', $host, $matches_host);
    preg_match('/([^.]*\.[^.]{2}|[^.]*)\.([^.]*)$/i', $cdn_host, $matches_cdn_host);
    $equal_origin = $matches_host[0] === $matches_cdn_host[0];
}
header("Content-Security-Policy: script-src 'self' 'unsafe-inline'" . ($equal_origin ? null : " $cdn_host") . (ga ? ' www.google-analytics.com' : null));

// Max 160 character title http://blogs.msdn.com/b/ie/archive/2012/05/14/sharing-links-from-ie10-on-windows-8.aspx
$metadata  = "<title>$pagetitle</title>";

// Open Graph protocol http://ogp.me
$metadata .= "\n<meta property=og:title content=\"$pagetitle\">";
// Max 253 character description http://blogs.msdn.com/b/ie/archive/2012/05/14/sharing-links-from-ie10-on-windows-8.aspx
if (!!($description)) $metadata .= "\n<meta property=og:description name=description content=\"$description\">";
// Twitter Cards https://dev.twitter.com/docs/cards
$metadata .= "\n<meta property=twitter:card content=summary>"; // Twitterbot will crawl as default 'summary' when twitter:card is not set (Twitterbot has some issue with it, need to be set)

// Perform speed and security on removing referrer-header-value http://wiki.whatwg.org/wiki/Meta_referrer
$metadata .= "\n<meta name=referrer content=never>";

// Optimize smart device viewport
$metadata .= "\n<meta name=viewport content=\"width=device-width, maximum-scale=1\">";

// Authorship in Google https://support.google.com/webmasters/answer/1408986
// $metadata .= "\n<link rel=author href=https://plus.google.com/000000000000000000000>";

// Prefetch CDN by saving DNS resolution time https://github.com/h5bp/html5-boilerplate/blob/master/doc/extend.md#dns-prefetching
if ($cdn_host) $metadata .= "\n<link rel=dns-prefetch href=$cdn_scheme$cdn_host>";

// Fetch and cache API in background when everything is downloaded http://www.whatwg.org/specs/web-apps/current-work/#link-type-prefetch
if ($conn) $metadata .= "\n<link rel=\"prefetch prerender\" href=$path" . 'api' . (!empty($url) ? '/' . $url : null) . '>';

// Favicon 16x16, 32x32 4-bit 16 color /favicon.ico on website root or base64 inline dataURI when project not in root http://zoompf.com/2012/04/instagram-and-optimizing-favicons
// if ($path !== '/') $metadata .= "\n<link rel=\"shortcut icon\" href=\"data:image/x-icon;base64,...\">";

// Website copyright license
$metadata .= "\n<link rel=license href=//creativecommons.org/licenses/by/4.0/>";

// Cache manifest (Chrome external domain hosting issue http://crbug.com/167918)
// <html lang=en manifest=manifest.appcache>
// JavaScript CDNs performance stats http://www.cdnperf.com


echo "<!DOCTYPE html>
<head prefix=\"og: http://ogp.me/ns#\">
<meta charset=UTF-8>
$metadata
". ($debug ? '<link rel=stylesheet href=' . assets . "style$ver.css>" : '<style>@viewport{width:device-width;max-zoom:1}::selection{text-shadow:none;background-color:rgba(180,180,180,.5)}body,html{height:100%}button,html,input,select,textarea{color:#222;font-family:Cambria,Georgia,serif}.button,::-webkit-input-placeholder,button,form,html,input{-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}html{overflow-y:scroll;font-size:62.45%;line-height:1.5;text-rendering:optimizeSpeed;background-color:#f8f9fa;cursor:default}blockquote,body,dd,dl,figure{margin:0}body{font-size:1.9em;overflow-wrap:break-word;-ms-hyphenate-limit-chars:6 3 2;hyphenate-limit-chars:6 3 2;-webkit-hyphens:auto;-moz-hyphens:auto;-ms-hyphens:auto;hyphens:auto}label,main{-webkit-user-select:text;-moz-user-select:text;-ms-user-select:text;user-select:text}main{display:block}dt,h1,h2,h3{line-height:1.07;font-weight:400;font-family:Times New Roman,serif}h1,h2{margin-top:.2em;margin-bottom:.2em}h1{font-size:2.65em}h2{color:#777;font-size:2em}dt,h3{margin-top:.3em;margin-bottom:.3em;font-size:1.35em}h4{margin-top:.4em;margin-bottom:.4em}small{font-size:70%}blockquote{padding-left:1.2em;font-style:italic;border-left:.3em solid #eef0f0}blockquote cite{color:#777}blockquote cite:before{content:"\2014 \00A0";color:#777}br{word-spacing:0}hr{position:relative;margin:1em -3em;margin:1em -4.9vw;clear:both;border-width:0;border-top:1px solid #eef0f0}code,pre{padding:.1em .4em;font-style:italic;font-family:Consolas,Liberation Mono,Courier,monospace;white-space:pre-wrap;background-color:#eef0f0;-moz-tab-size:4;tab-size:4}a,button,input,select,textarea{outline:0;pointer-events:auto}a img,abbr,iframe{border:0}a,img{-webkit-user-drag:none;user-drag:none}a{color:#0f4cd5;text-decoration:none;cursor:default}a:focus,a:hover{color:#0e439b;text-decoration:underline}img{width:auto;max-width:100%;height:auto;vertical-align:top;-ms-interpolation-mode:nearest-neighbor;image-rendering:-webkit-optimize-contrast;image-rendering:-moz-crisp-edges;image-rendering:crisp-edges}abbr{border-bottom:1px dotted #ccc}legend{display:table}label{display:inline-block;padding-bottom:.2em;padding-top:.2em}.button,button,input,select,textarea{width:20em;padding:.6em .7em;margin:.15em 0;font-size:1em;line-height:1.25;vertical-align:middle;background-color:#fff;border:1px solid #b1b2b2;box-sizing:border-box}textarea{overflow-x:hidden;overflow-y:scroll;min-height:4.95em;max-height:13em;word-wrap:break-word;resize:none}input:focus,input:hover,textarea:focus,textarea:hover{border-color:#9f9f9f;box-shadow:inset 0 1px 3px rgba(0,0,0,.15)}:placeholder-shown{color:#c3c3c3}[type=checkbox],[type=color],[type=file],[type=image],[type=radio]{width:auto;padding:0;box-sizing:padding-box}.button,button,select{overflow:hidden;display:inline-block;white-space:nowrap;text-align:center;text-overflow:ellipsis;background-color:#f4f4f4;border:1px solid #c0c0c1;outline:0;box-shadow:inset 0 0 0 1px #f5f5f5,1px 1px 2px rgba(0,0,0,.1)}.button{width:auto}a.button{color:inherit;text-decoration:none}.button:focus,.button:hover,button:focus,button:hover,select:focus,select:hover{border-color:#9f9f9f;background-color:rgba(228,228,228,.3)}.button:active,button:active,select:active{background-color:#e2e4e5;box-shadow:none}[tabindex]{outline:0}#status{position:fixed;z-index:999;left:0;width:0;height:3px;background-color:#29d;-webkit-transform:translateZ(0);-ms-transform:translateZ(0);transform:translateZ(0);-webkit-backface-visibility:hidden;backface-visibility:hidden;-webkit-perspective:1000;perspective:1000;will-change:transition}#status.status-start:before{content:"";display:block;width:1em;height:100%;float:right;background-image:-webkit-linear-gradient(left,rgba(0,194,255,0),#00c2ff);background-image:linear-gradient(to right,rgba(0,194,255,0),#00c2ff);box-shadow:2px -2px 5px 1px #00c2ff}#status.status-start{width:70%;-webkit-transition-duration:5s;transition-duration:5s}#status.status-done{width:100%;opacity:0;-webkit-transition-duration:.2s;transition-duration:.2s}.tab{position:fixed;z-index:100;top:0;right:0;left:0;line-height:2.2;font-family:Open Sans,Meiryo,Segoe UI,sans-serif;text-align:center;white-space:nowrap}.bar,.focusout{display:none}.footer,.header,.main{width:95%;max-width:900px;margin:auto;box-sizing:border-box}.header,.main{background-color:#fff;border:0 solid #d9e0e2;border-width:0 1px}.header{position:relative;padding:.1em .4em}.header:after{content:"";position:absolute;left:0;right:0;height:1.5em;background-image:-webkit-linear-gradient(#fff,rgba(255,255,255,.9)35%,rgba(255,255,255,.8)50%,rgba(255,255,255,0));background-image:linear-gradient(#fff,rgba(255,255,255,.9)35%,rgba(255,255,255,.8)50%,rgba(255,255,255,0));pointer-events:none}.nav{display:inline-block;display:-webkit-box;display:flex;margin:.1em auto;line-height:2.1;background-color:#f3f3f3;background-image:-webkit-linear-gradient(#f8f8f8,rgba(248,249,250,0));background-image:linear-gradient(#f8f8f8,rgba(248,249,250,0));border:1px solid #d9e0e2;border-bottom-color:#ccc;border-radius:.2em;box-shadow:0 1px 1px rgba(0,0,0,.1),inset 0 0 0 1px rgba(255,255,255,.5)}.footer a,.nav a{color:inherit;text-decoration:none}.nav a{overflow:hidden;display:block;position:relative;z-index:1;-webkit-box-flex:1;-ms-flex:1;flex:1;flex-basis:auto;min-width:0;max-height:2.15em;padding:0 .4em;text-overflow:ellipsis;border:0 solid #d9e0e2;border-left-width:1px}.nav a:first-child{border-left:0}.nav a:first-of-type{border-radius:.2em 0 0 .2em}.nav a:last-of-type{border-radius:0 .2em .2em 0}.nav a:focus,.nav a:hover{background-color:#fff}.nav a.focus{background-color:#ebebeb}.nav a.error{background-color:#f1e2e2}.nav a.active{position:relative;z-index:1;margin:-1px auto;color:#fff;line-height:2.2;background-color:#006cff;border-color:transparent}.nav .active+a{border-color:transparent}.footer,.main{position:relative;padding:0 3em;padding:0 4.9vw}.main{overflow:hidden;min-height:100%;padding-top:3.8em;padding-bottom:4.1em;will-change:contents}.main :target{background-color:#ff0}.main a:target{text-decoration:none}.main a:target:hover{text-decoration:underline}.main [id]:target:before{content:"";display:block;padding-top:3.5em;margin-top:-3.5em;pointer-events:none;background-color:#fff}.main h1[id]:target:before{padding-top:1.3em;margin-top:-1.3em}.footer{overflow:hidden;height:2.7em;margin-top:-2.7em;line-height:2.7;text-overflow:ellipsis;white-space:nowrap;border-top:1px solid #eee}.footer a{display:inline-block;color:#777}.footer a+a{margin-left:.6em}.footer a:focus,.footer a:hover{color:#222;text-decoration:underline}[hidden],template{display:none}@media \0screen\,screen\9{.header{max-width:100%}.nav{display:table;width:100%;table-layout:fixed;border-collapse:collapse}.nav a{display:table-cell}.handler{display:none}}@media (min-width:0\0){a{background-color:inherit}.nav,.nav a:first-of-type,.nav a:last-of-type{border-radius:0}.nav{display:table;width:100%;table-layout:fixed}.nav a{display:table-cell}.handler{display:none}}@media (-ms-high-contrast:active),(-ms-high-contrast:none){:-ms-input-placeholder{color:#c3c3c3}.nav{display:-ms-flexbox;display:flex}.nav a{display:block}@media (max-width:540px){.footer,.header,.tab{text-align:left}}}@media (-webkit-min-device-pixel-ratio:0){.nav,html,textarea{-webkit-overflow-scrolling:touch}html{-webkit-font-smoothing:antialiased;-webkit-text-size-adjust:100%}[tabindex],a,button,input,select,textarea{-webkit-tap-highlight-color:rgba(0,0,0,0)}a{-webkit-touch-callout:none}::-webkit-input-placeholder{color:#c3c3c3}}@-moz-document url-prefix(){::-moz-selection{text-shadow:none;background-color:rgba(180,180,180,.5)}label:active{background-color:transparent}button,input,select,textarea{background-image:none;border-radius:0}::-moz-placeholder{color:#c3c3c3}}@media (max-width:540px){html{background-color:#fff}hr{margin-right:-1.7em;margin-left:-1.7em}button,input,label,textarea{width:100%}[type=checkbox],[type=color],[type=file],[type=image],[type=radio]{width:auto}#status:not(.expand)~.footer,#status:not(.expand)~.tab .header{-webkit-transition:none;transition:none}.tab{text-align:left;text-align:start;padding-bottom:.2em;background-image:-webkit-linear-gradient(#fff,rgba(255,255,255,.9)35%,rgba(255,255,255,.8)50%,rgba(255,255,255,0));background-image:linear-gradient(#fff,rgba(255,255,255,.9)35%,rgba(255,255,255,.8)50%,rgba(255,255,255,0))}.bar{display:inline-block;position:relative;z-index:3}.bar:focus,.bar:hover{background-color:rgba(0,0,0,.06)}.bar span,.bar span:after,.bar span:before{display:block;width:1.3em;height:.21em;background-color:#222;border-radius:.1em;-webkit-transition:.35s cubic-bezier(.2,1,.4,1);transition:.35s cubic-bezier(.2,1,.4,1)}.bar span{position:relative;margin:1.07em .65em 1.07em .45em;transform:translateY(0);-webkit-transition-duration:.15s;transition-duration:.15s;will-change:transition,transform;pointer-events:none}.bar span:before{content:"";position:absolute;-webkit-transform:translateY(-.42em);-ms-transform:translateY(-.42em);transform:translateY(-.42em)}.bar span:after{content:"";position:absolute;-webkit-transform:translateY(.42em);-ms-transform:translateY(.42em);transform:translateY(.42em)}.expand~.tab .bar span{background-color:transparent}.expand~.tab .bar span:before{-webkit-transform:rotate(45deg);-webkit-transform:rotate3d(0,0,1,45deg);-ms-transform:rotate(45deg);transform:rotate3d(0,0,1,45deg)}.expand~.tab .bar span:after{-webkit-transform:rotate(-45deg);-webkit-transform:rotate3d(0,0,1,-45deg);-ms-transform:rotate(-45deg);transform:rotate3d(0,0,1,-45deg)}.expand~.footer,.expand~.tab .header{-webkit-transform:none;-ms-transform:none;transform:none}.focusout{display:inline}.footer,.header,.main{min-width:13.6em}.footer,.header{position:fixed;left:0;bottom:0;width:75%;padding:0;-webkit-transform:translateX(-100%);-ms-transform:translateX(-100%);transform:translateX(-100%);-webkit-transition:-webkit-transform .35s cubic-bezier(.2,1,.4,1);transition:transform .35s cubic-bezier(.2,1,.4,1)}.header{top:0;padding-bottom:6.3em;background-color:#f8f9fa;border:0}.header:after,.header:before{opacity:0;content:"";position:fixed;left:0;z-index:2;width:75%;height:2.35em;pointer-events:none;transition:opacity .35s cubic-bezier(.2,1,.4,1)}.header:before{top:0;background-image:-webkit-linear-gradient(#f8f9fa,rgba(248,249,250,.9)50%,rgba(248,249,250,.8)60%,rgba(248,249,250,0));background-image:linear-gradient(#f8f9fa,rgba(248,249,250,.9)50%,rgba(248,249,250,.8)60%,rgba(248,249,250,0))}.header:after{bottom:0;right:auto;background-image:-webkit-linear-gradient(rgba(248,249,250,0),rgba(248,249,250,.8)50%,rgba(248,249,250,.9)60%,#f8f9fa);background-image:linear-gradient(rgba(248,249,250,0),rgba(248,249,250,.8)50%,rgba(248,249,250,.9)60%,#f8f9fa)}.expand~.tab .header:after,.expand~.tab .header:before{opacity:1}.expand~.handler,.expand~.tab .handler{position:inherit;top:0;right:0;left:0;bottom:0;margin:0}.expand~.handler{position:fixed;z-index:99}.expand~.tab .header{box-shadow:0 0 4em rgba(0,0,0,.3)}.footer,.nav{border:0;-webkit-overflow-scrolling:touch}.footer{overflow:visible;z-index:100;height:auto;line-height:2.1;background-color:#f8f9fa}.footer:before{content:"";position:absolute;top:-1.5em;left:0;right:0;height:1.5em;background-image:-webkit-linear-gradient(rgba(248,249,250,0),rgba(248,249,250,.8)50%,rgba(248,249,250,.9)60%,#f8f9fa);background-image:linear-gradient(rgba(248,249,250,0),rgba(248,249,250,.8)50%,rgba(248,249,250,.9)60%,#f8f9fa);pointer-events:none}.nav,.nav a:first-of-type,.nav a:last-of-type{border-radius:0}.nav{overflow:auto;display:block;height:100%;padding:2.35em 0;margin:0;background:0 0;box-shadow:none;box-sizing:border-box}.footer a,.nav.nav a{display:block;padding:0 1.3em;float:none;border-color:#eef0f0}.nav a{-webkit-box-flex:0;-webkit-flex:0;-ms-flex:0;flex:0;border-width:0;border-top-width:1px}.footer a:first-child,.nav a:first-child{border-top-width:0}.main{overflow:visible;width:100%;padding:2em 1.7em;border:0}.main [id]:before{padding-top:1.8em;margin-top:-1.8em}.footer a+a{margin-left:0}.footer a{overflow:hidden;color:inherit;text-overflow:ellipsis;border-top-width:1px;border-top-style:solid}.footer a:focus,.footer a:hover{color:#222;text-decoration:none;background-color:#fff}}@media (max-width:320px){html{font-size:58%}h1{font-size:2em}h2{font-size:1.6em}dt,h3{font-size:1.3em}}@media print{@page{margin:.5cm}*,h2,h3{color:#222;text-shadow:none;background:0 0}h2,h3,p{orphans:3;widows:3}h2,h3{page-break-after:avoid}a{color:#777;text-decoration:underline}img{page-break-inside:avoid}.main,html{background:0 0}.footer,.tab{display:none}.main{padding-top:1em;padding-bottom:1em;border-width:0}.button,button,select{box-shadow:none}}</style>') . "
<!--[if lt IE 9]><script src=//cdn.jsdelivr.net/html5shiv/3.7.2/html5shiv.min.js></script><![endif]-->
<body itemscope itemtype=http://schema.org/WebPage>$note
<div id=status role=progressbar></div>
<div class=tab>
    <div class=bar role=button onfocus=toggle(true) tabindex=0><span onfocus=through(event)></span></div>
    <span id=focusout class=focusout tabindex=0></span>
    <header class=header>";

if ($conn) {
    if ($stmt = $mysqli->prepare('SELECT url, title FROM `' . table . '` WHERE permit=1 ORDER BY `order` ASC')) {
        $stmt->execute();
        $stmt->bind_result($data_url, $data_metatitle);
        echo "\n        <nav class=nav role=navigation>";

        while ($stmt->fetch()) echo "\n            <a class="
                . ($url === $data_url ? '"x active"' : 'x')
                . " href=\"$path$data_url\" dir=auto>$data_metatitle</a>";

        echo "\n            <div class=handler onfocus=toggle() tabindex=0></div>\n        </nav>";
        $stmt->free_result();
        $stmt->close();
    }
    $mysqli->close();
}

echo "\n    </header>
</div>
<div class=handler onclick=toggle()></div>
<main id=output class=main role=main itemprop=about itemscope itemtype=http://schema.org/Article>";

if ($conn) echo "\n<h1 dir=auto>$title</h1>";

echo "\n$content
</main>
<footer class=footer itemprop=breadcrumb>
    <a href=https://github.com/laukstein/ajax-seo>GitHub project</a>
    <a href=https://github.com/laukstein/ajax-seo/archive/master.zip>Download</a>
    <a href=https://github.com/laukstein/ajax-seo/issues>Issues</a>
</footer>";

$js  = $debug ? '<script src=' . assets . "script$ver.js></script>" : '<script>!function(e,t,r,s,a){"use strict";function o(){return t.URL}function n(){g&&clearTimeout(g),b.classList.contains("status-start")&&b.classList.add("status-done")}function i(e){return e.split("#")[0]}function c(e,r,a){for(' . (ga ? 'ga("send","pageview",{page:decodeURI(s.pathname)}),' : null) . 'a?n():T.abort(),t.title=e.title,O.innerHTML=e.content,A.querySelector(".x.focus")&&A.querySelector(".x.focus").classList.remove("focus"),A.querySelector(".x.active")&&A.querySelector(".x.active").classList.remove("active"),A.querySelector(".x.error")&&A.querySelector(".x.error").classList.remove("error"),y=i(o()),t.activeElement&&"body"!==t.activeElement.nodeName.toLowerCase()&&t.activeElement.blur(),L=0;L<H.length;L+=1)if(q=H[L],i(q.href)===y){if(q.focus(),e.statusName&&(r="error"),q.classList.add(r),"error"===r){q.classList.remove("active"),q.classList.add("x-error");break}q.classList.remove("x-error"),q.classList.remove("error");break}}function l(e){if(null!==e)try{e.click()}catch(r){var s=t.createEvent("MouseEvents");s.initEvent("click",!0,!0),e.dispatchEvent(s)}}function u(e){if(!(void 0!==y&&y.indexOf("#")>-1)){w=!1,M=!1,n();var t=e.state;if(!t)for(M=!0,y=i(o()),L=0;L<H.length;L+=1)if(i(H[L].href)===y)return l(H[L]);m=t.status,void 0===m&&(w=!0),m=void 0!==m?m:"active",w&&"active"===m&&(w=!1),c(t,m,!1)}}function f(e,t){for(var r=e.matches||e.webkitMatchesSelector||e.mozMatchesSelector||e.msMatchesSelector;e&&1===e.nodeType;){if(r.call(e,t))return e;e=e.parentNode}return null}function d(e){if(!f(e.target,".bar")&&!f(e.target,".handler")){if(!f(e.target,"nav "+k)&&b.classList.contains("expand"))return b.classList.remove("expand");if(E=f(e.target,k),null!==E&&N===E.host&&(y=E.getAttribute("href"),(0!==y.indexOf("//")&&0!==y.indexOf("http")||(y=y.split(N).pop(),0===y.indexOf(I)))&&!("/"===y[0]&&0!==y.indexOf(I)||y.indexOf("#")>-1))){e.preventDefault();var s=i(E.href),a=i(o()),n=E.innerText||E.textContent;M||(w=E.classList.contains("x-error")),!w&&!M&&s===a||E.classList.contains("focus")||(b.hasAttribute("class")&&b.removeAttribute("class"),0===y.indexOf(I)&&(y=y.slice(I.length)),S=I+y,w&&a===o()?r.replaceState(null,n,S):r.pushState(null,n,S),A.querySelector(".x.focus")&&A.querySelector(".x.focus").classList.remove("focus"),n=E.innerText||E.textContent,E.classList.remove("error"),E.classList.add("focus"),r.replaceState(null,n,S),x&&clearTimeout(x),x=setTimeout(function(){t.title=n},3),T.abort(),T.open("GET",I+"api"+(y.length>0?"/":"")+y),w&&T.setRequestHeader("If-Modified-Since","Sat, 1 Jan 2000 00:00:00 GMT"),T.send())}}}function h(){g=setTimeout(function(){b.classList.add("status-start")},100)}function p(e,t){w="active"===t?!1:!0,r.replaceState(e,e.title,S),c(e,t,!0)}function v(){if(n(),m=this.status,x&&clearTimeout(x),m)if(200!==m)p({statusName:"error",title:"'.$pagetitle_error.'",content:"<h1>'.$pagetitle_error.'</h1><p>Sorry, this page hasn'."'".'t been found. Try to <a class=\"x x-error\" href="+y+">reload</a> the page or head to <a class=x href="+I+">home</a>."},"error");else try{p(JSON.parse(this.response),"active")}catch(e){p({statusName:"error",title:"Server error",content:"<h1>'.$title_error.'</h1><p>Sorry, experienced server error. Try to <a class=\"x x-error\" href="+y+">reload</a> the page or head to <a class=x href="+I+">home</a>."},"error")}}if(e.toggle=function(e){var r,s,a="expand",o=t.getElementById("status");o.classList?o.classList.toggle(a):(r=o.className.split(" "),s=r.indexOf(a),s>=0?r.splice(s,1):r.push(a),o.className=r.join(" ")),e&&t.getElementById("focusout").focus()},e.through=function(e){t.elementFromPoint(e.clientX,e.clientY).focus()},e[a]=function(){e[a].q=e[a].q||[],e[a].q.push(arguments)}' . (ga ? ',ga("create","' . ga . '","' . (strlen(ga_domain) > 0 ? ga_domain : 'auto') . '"),ga("send","pageview")' : null) . ',!r.pushState)throw new Error("History API not supported");var m,x,g,L,y,S,q,E,T=new XMLHttpRequest,b=t.getElementById("status"),O=t.getElementById("output"),w=!1,M=!1,N=s.host,I="'.$path.'",k=".x",A=t.querySelector("nav"),H=A?A.querySelectorAll(k):null;r.replaceState({title:t.title,content:O.innerHTML},t.title,o()),setTimeout(function(){e.onpopstate=u},100),t.addEventListener(e.hasOwnProperty("ontouchstart")?"touchstart":"click",d,!0),b.addEventListener("transitionend",function(e){e.target.classList.contains("status-done")&&(e.target.classList.remove("status-start"),e.target.classList.remove("status-done"))},!0),T.onloadstart=h,T.onload=v,T.onabort=n}(window,document,history,location' . (ga ? ',"ga"' : null) . ')</script>';
$js .= ga ? "\n<script src=//www.google-analytics.com/analytics.js async></script>" : null;
echo "\n$js";