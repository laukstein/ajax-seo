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

    $stmt = $mysqli->prepare('SELECT url, title, description, content FROM `' . table . '` WHERE url=? LIMIT 1');
    $stmt->bind_param('s', $url);
    $stmt->execute();
    $stmt->bind_result($url, $title, $description, $content);

    while ($stmt->fetch()) {
        $results   = true;
        // SEO page title improvement for the root page
        $pagetitle = empty($url) ? $gtitle : $title;

        function string($str) {
            if (!function_exists('_variable')) { // Avoid function redeclare
                // Usecase: Execute variable {$foo}
                // Supported since PHP 4.1.0 http://www.php.net/manual/en/language.variables.superglobals.php
                function _variable($m) {
                    return @$GLOBALS[$m[1]]; // case-sensitive variable
                }
            }
            $str = preg_replace_callback('/{\$(\w+)}/', '_variable', $str);
            return $str;
        }

        $content = string($content);
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

// Avoid undefined variables
$optional_title = isset($optional_title) ? $optional_title : null;


// Avoid XSS attacks https://w3c.github.io/webappsec/specs/content-security-policy/
header("Content-Security-Policy: script-src 'self' 'unsafe-inline' 'unsafe-eval'" . ($cdn_host ? ' ' . $cdn_host : null) . (ga ? ' www.google-analytics.com' : null));


// Max 160 character title http://blogs.msdn.com/b/ie/archive/2012/05/14/sharing-links-from-ie10-on-windows-8.aspx
$metadata  = "<title>$pagetitle</title>";

// Open Graph protocol http://ogp.me
$metadata .= "\n<meta property=og:title content=\"$pagetitle\">";
// Max 253 character description http://blogs.msdn.com/b/ie/archive/2012/05/14/sharing-links-from-ie10-on-windows-8.aspx
if (!empty($description)) $metadata .= "\n<meta property=og:description name=description content=\"$description\">";
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
". (debug ? '<link rel=stylesheet href=' . assets . "style$ver.css>" : '<style>@viewport{width:device-width;max-zoom:1}::selection{text-shadow:none;background-color:rgba(180,180,180,.5)}body,html{height:100%}html,input,select,textarea{color:#222;font-family:Cambria,Georgia,serif}html,textarea{line-height:1.5}.button,form,html{-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}html{overflow-x:hidden;overflow-y:scroll;font-size:62.45%;background-color:#f8f9fa;cursor:default}blockquote,body,dd,dl,figure{margin:0}body{font-size:1.9em;overflow-wrap:break-word;-ms-hyphenate-limit-chars:6 3 2;hyphenate-limit-chars:6 3 2;-webkit-hyphens:auto;-moz-hyphens:auto;-ms-hyphens:auto;hyphens:auto}input,main,textarea{-webkit-user-select:text;-moz-user-select:text;-ms-user-select:text;user-select:text}main{display:block}dt,h1,h2,h3{line-height:1.07;font-weight:400;font-family:"Times New Roman",serif}h1{margin-top:.1em;margin-bottom:.1em;font-size:2.65em}h2{margin-top:.2em;margin-bottom:.2em;color:#777;font-size:2em}dt,h3{margin-top:.3em;margin-bottom:.3em;font-size:1.35em}h4{margin-top:.4em;margin-bottom:.4em}small{font-size:70%}blockquote{padding-left:1.2em;font-style:italic;border-left:.3em solid #eee}blockquote cite{color:#999}blockquote cite:before{content:"\2014 \00A0";color:#999}br{word-spacing:0}code,pre{padding:.1em .4em;font-family:Consolas,"Liberation Mono",Courier,monospace;white-space:pre-wrap;background-color:#eee;-moz-tab-size:4;tab-size:4}a,input,select,textarea{outline:0;pointer-events:auto}a img,abbr,iframe{border:0}a,img{-webkit-user-drag:none;user-drag:none}a{color:#0f4cd5;text-decoration:none;touch-action:none;cursor:default}a:focus,a:hover{color:#0e439b;text-decoration:underline}img{width:auto;max-width:100%;height:auto;vertical-align:top;-ms-interpolation-mode:bicubic}abbr{border-bottom:1px dotted #ccc}hr{position:relative;margin:1em -11%;clear:both;border-width:0;border-top:1px solid #eee}legend{display:table}label{display:inline-block;padding-bottom:.2em;padding-top:.2em}.button,[type=email],[type=month],[type=number],[type=password],[type=search],[type=submit],[type=tel],[type=text],[type=time],[type=url],[type=week],[type^=date],input:not([type]),select,textarea{width:20em;padding:.6em .7em;margin:.15em 0}.button,input,select,textarea{background-color:#fff;border:1px solid #ccc;box-sizing:border-box}input,select,textarea{font-size:1em}input:focus,input:hover,select:focus,select:hover,textarea:focus,textarea:hover{border-color:#ababab;box-shadow:inset 1px 1px 3px #d6d6d6}textarea{overflow:auto;vertical-align:top;word-wrap:break-word;resize:vertical}.tab,body{margin:0 10%}.button{overflow:hidden;display:inline-block;width:auto;color:inherit;line-height:1;vertical-align:top;white-space:nowrap;text-align:center;text-overflow:ellipsis;background-image:-webkit-linear-gradient(rgba(248,249,250,0),#f8f8f8);background-image:linear-gradient(rgba(248,249,250,0),#f8f8f8)}.button:focus,.button:hover{color:inherit;text-decoration:none;background-image:none;border-color:#ababab;box-shadow:0 1px 1px rgba(0,0,0,.2)}.button.selected,.button:active{box-shadow:inset 0 -3em 6em -3em #d6d6d6,inset 1px 1px .3em #d6d6d6}#status{position:fixed;z-index:999;left:0;width:0;height:3px;background-color:#29d;will-change:transition;-webkit-transform:translateZ(0);-ms-transform:translateZ(0);transform:translateZ(0);-webkit-backface-visibility:hidden;backface-visibility:hidden;-webkit-perspective:1000;perspective:1000}#status.status-start:before{content:"";display:block;width:1em;height:100%;float:right;background-image:-webkit-linear-gradient(left,rgba(0,194,255,0),#00c2ff);background-image:linear-gradient(to right,rgba(0,194,255,0),#00c2ff);box-shadow:2px -2px 5px 1px #00c2ff}#status.status-start{width:70%;-webkit-transition-duration:5s;transition-duration:5s}#status.status-done{width:100%;opacity:0;-webkit-transition-duration:.2s;transition-duration:.2s}.tab{position:fixed;z-index:100;top:0;right:0;left:0;line-height:1.2;font-family:"Open Sans",Meiryo,"Segoe UI",sans-serif;text-align:center;white-space:nowrap}.footer a,.tab a{color:inherit;text-decoration:none}.bar{display:none}.footer,.header,.main{max-width:900px;margin:auto;box-sizing:border-box}.header,.main{background-color:#fff;border:0 solid #d9e0e2;border-width:0 1px}.header{position:relative;width:100%;padding:.1em .4em}.header:after{content:"";position:absolute;left:0;right:0;height:1.5em;background-image:-webkit-linear-gradient(#fff,rgba(255,255,255,.9)35%,rgba(255,255,255,.8)50%,rgba(255,255,255,0));background-image:linear-gradient(#fff,rgba(255,255,255,.9)35%,rgba(255,255,255,.8)50%,rgba(255,255,255,0));pointer-events:none}.nav{display:inline-block;display:-webkit-box;display:flex;margin:.1em auto;line-height:2.1;background-color:#f3f3f3;background-image:-webkit-linear-gradient(#f8f8f8,rgba(248,249,250,0));background-image:linear-gradient(#f8f8f8,rgba(248,249,250,0));border:1px solid #d9e0e2;border-bottom-color:#ccc;border-radius:.2em;box-shadow:0 1px 1px rgba(0,0,0,.1),inset 0 0 0 1px rgba(255,255,255,.5);box-sizing:border-box}.nav a{overflow:hidden;display:block;position:relative;-webkit-box-flex:1;-ms-flex:1;flex:1;flex-basis:auto;min-width:0;max-height:2.2em;padding:0 .4em;text-overflow:ellipsis;border:0 solid #d9e0e2;border-left-width:1px}.nav a:first-child{padding-left:.5em;border-left:none;border-radius:.2em 0 0 .2em}.nav a:last-child{border-radius:0 .2em .2em 0}.nav a:focus,.nav a:hover{background-color:#fff}.nav a.focus{background-color:#ebebeb}.nav a.error{background-color:#f1e2e2}.nav a.active{position:relative;z-index:1;margin:-1px auto;color:#fff;line-height:2.2;background-color:#006cff;border-color:transparent}.nav .active+a{border-color:transparent}.footer,.main{position:relative;padding:0 5%}.main{overflow:hidden;min-height:100%;padding-top:3.8em;padding-bottom:4.1em;will-change:contents}.main :target{background-color:#ff0}.main a:target{text-decoration:none}.main a:target:hover{text-decoration:underline}.main [id]:target:before{content:"";display:block;padding-top:3.5em;margin-top:-3.5em;pointer-events:none;background-color:#fff}.main h1[id]:target:before{padding-top:1.3em;margin-top:-1.3em}.footer{overflow:hidden;height:2.7em;margin-top:-2.7em;line-height:2.7;text-overflow:ellipsis;white-space:nowrap;border-top:1px solid #eee}.footer a{display:inline-block;color:#777}.footer a+a{margin-left:.6em}.footer a:focus,.footer a:hover{color:#222;text-decoration:underline}[hidden],template{display:none}@media \0screen\,screen\9{input,select,textarea{vertical-align:middle}.tab:before{background-color:#f7f7f7}.header{overflow:hidden}.nav{display:table;width:100%;border-collapse:collapse;table-layout:fixed}.nav a{display:table-cell}}@media (min-width:0\0){a{background-color:inherit}.tab:before{background-color:#f7f7f7}.header{overflow:hidden}.nav{display:table;width:100%;table-layout:fixed}.nav a{display:table-cell}}@media (-ms-high-contrast:active),(-ms-high-contrast:none){:-ms-input-placeholder{color:#ccc}.tab:before{background-color:rgba(247,247,247,0)}.header{overflow:visible}.nav{display:-ms-flexbox;display:flex}.nav a{display:block}@media (max-width:540px){.footer,.header,.tab{text-align:left}}}@media (-webkit-min-device-pixel-ratio:0){html,textarea{-webkit-overflow-scrolling:touch}html{-webkit-font-smoothing:antialiased;-webkit-text-size-adjust:100%;-webkit-perspective:1000}a,input,select,textarea{-webkit-tap-highlight-color:rgba(0,0,0,0)}a{-webkit-touch-callout:none}textarea::-webkit-scrollbar{width:8px;background-color:rgba(250,250,250,0)}textarea::-webkit-scrollbar-thumb{background-color:#8e8f91}textarea::-webkit-scrollbar-thumb:hover{background-color:#777}::-webkit-input-placeholder{color:#ccc}}@-moz-document url-prefix(){::-moz-selection{text-shadow:none;background-color:rgba(180,180,180,.5)}label:active{background-color:transparent}input,select,textarea{background-image:none;border-radius:0}::-moz-placeholder{color:#ccc}}@media (max-width:1280px){.tab,body{margin:0 3%;-webkit-transition:margin .21s;transition:margin .21s}#status:not(.expand)~.tab{-webkit-transition-duration:0;transition-duration:0}}@media (max-width:540px){html{background-color:#fff}.main [id]:before{padding-top:1.8em;margin-top:-1.8em}#status:not(.expand)~.footer,#status:not(.expand)~.tab,#status:not(.expand)~.tab .header{-webkit-transition-duration:0;transition-duration:0}.tab{margin:0;text-align:left;text-align:start;background-image:-webkit-linear-gradient(#fff,rgba(255,255,255,.9)35%,rgba(255,255,255,.8)50%,rgba(255,255,255,0));background-image:linear-gradient(#fff,rgba(255,255,255,.9)35%,rgba(255,255,255,.8)50%,rgba(255,255,255,0))}.bar{display:inline-block;position:relative;z-index:1;pointer-events:auto;padding:0;margin:0;font-size:1em;background:0 0;border:0;outline:0}.bar span{position:relative;margin:1.07em .65em 1.07em .45em;will-change:transition,transform}.bar span,.bar span:after,.bar span:before{display:block;width:1.3em;height:.21em;background-color:#222;border-radius:.1em;-webkit-transition:.2s;transition:.2s}.bar span:after,.bar span:before{content:"";position:absolute}.bar span:before{-webkit-transform:translateY(-.42em);-ms-transform:translateY(-.42em);transform:translateY(-.42em)}.bar span:after{-webkit-transform:translateY(.42em);-ms-transform:translateY(.42em);transform:translateY(.42em)}.bar:hover{background-color:rgba(0,0,0,.06)}.expand~.tab .bar span{background-color:transparent}.expand~.tab .bar span:before{-webkit-transform:rotate(45deg);-webkit-transform:rotate3d(0,0,1,45deg);-ms-transform:rotate(45deg);transform:rotate3d(0,0,1,45deg)}.expand~.tab .bar span:after{-webkit-transform:rotate(-45deg);-webkit-transform:rotate3d(0,0,1,-45deg);-ms-transform:rotate(-45deg);transform:rotate3d(0,0,1,-45deg)}[type=email],[type=month],[type=number],[type=password],[type=submit],[type=tel],[type=text],[type=time],[type=url],[type=week],[type^=date],input.button,input:not([type]),label,select,textarea{width:100%}.main,.tab{background-color:transparent;-webkit-transition:0;transition:0}.footer,.header,.main{min-width:13.6em}.footer,.header{position:fixed;left:0;bottom:0;width:75%;padding:0;-webkit-transform:translateX(-100%);-ms-transform:translateX(-100%);transform:translateX(-100%);-webkit-transition:-webkit-transform .16s cubic-bezier(.5,.31,.85,.55);transition:transform .16s cubic-bezier(.5,.31,.85,.55)}.header{top:0;padding-bottom:6.3em;background-color:#f8f9fa;border:0}.header:after{display:none}.footer,.nav{overflow:auto;border:0;-webkit-overflow-scrolling:touch}.footer{overflow:visible;z-index:100;height:auto;line-height:2.1;background-color:#f8f9fa}.footer:before{content:"";position:absolute;top:-1.5em;left:0;right:0;height:1.5em;background-image:-webkit-linear-gradient(rgba(248,249,250,0),rgba(248,249,250,.9)50%,rgba(248,249,250,.8)60%,#f8f9fa);background-image:linear-gradient(rgba(248,249,250,0),rgba(248,249,250,.9)50%,rgba(248,249,250,.8)60%,#f8f9fa);pointer-events:none}.nav,.nav a:first-child,.nav a:last-child{border-radius:0}.nav{display:block;height:100%;padding-top:2.35em;margin:0;background:0 0;box-shadow:none}.footer a,.nav a{display:block;padding:0 1.3em;float:none;border-color:#eee}.nav a{-webkit-box-flex:0;-webkit-flex:0;-ms-flex:0;flex:0;border-width:0;border-top-width:1px}.nav a:first-child{padding-left:1.3em}.footer a:first-child,.nav a:first-child{border-top-width:0}.footer a+a{margin-left:0}.main{overflow:visible;padding-top:2em;padding-bottom:1em;padding-right:1em;padding-left:1em;border-width:0;-webkit-transition:-webkit-transform .16s;transition:transform .16s}.footer a{overflow:hidden;color:inherit;text-overflow:ellipsis;border-top-width:1px;border-top-style:solid}.footer a:focus,.footer a:hover{color:#222;text-decoration:none;background-color:#fff}.expand~.footer,.expand~.tab .header{visibility:visible;-webkit-transform:translateX(0);-ms-transform:translateX(0);transform:translateX(0)}.expand~.tab .header{box-shadow:0 0 4em rgba(0,0,0,.3)}.expand~.main,.tab{pointer-events:none;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}}@media (max-width:320px){html{font-size:58%;-webkit-transition:font-size .2s;transition:font-size .2s}h1{font-size:2em}h2{font-size:1.6em}dt,h3{font-size:1.3em}}@media print{@page{margin:.5cm}*,h2,h3{color:#222;text-shadow:none;background:0 0}h2,h3,p{orphans:3;widows:3}h2,h3{page-break-after:avoid}a{color:#777;text-decoration:underline}img{page-break-inside:avoid}.main,html{background:inherit}.footer,.tab{display:none}.main{padding-top:1em;padding-bottom:1em;border-width:0}.button{text-decoration:none;background-image:none;border-color:#777}}</style>') . "
<!--[if lt IE 9]><script src=//cdn.jsdelivr.net/html5shiv/3.7.2/html5shiv.min.js></script><![endif]-->
<body itemscope itemtype=http://schema.org/WebPage>$note
<div id=status role=progressbar></div>
<div class=tab>
    <button id=\"expand\" class=bar><span></span></button>
    <header class=header>";

if ($conn) {
    if ($stmt = $mysqli->prepare('SELECT url, title FROM `' . table . '` WHERE permit=1 ORDER BY `order` ASC')) {
        $stmt->execute();
        $stmt->bind_result($data_url, $data_metatitle);
        echo "\n        <nav class=nav role=navigation>";

        while ($stmt->fetch()) echo "\n            <a class="
                . ($url === $data_url ? '"x active"' : 'x')
                . " href=\"$path$data_url\" dir=auto>$data_metatitle</a>";

        echo "\n        </nav>";
        $stmt->free_result();
        $stmt->close();
    }
    $mysqli->close();
}

echo "\n    </header>
</div>
<main id=output class=main role=main itemprop=about itemscope itemtype=http://schema.org/Article>";

if ($conn) echo "\n<h1 dir=auto>$title</h1>";

echo "\n$content
</main>
<footer class=footer itemprop=breadcrumb>
    <a href=https://github.com/laukstein/ajax-seo>GitHub project</a>
    <a href=https://github.com/laukstein/ajax-seo/archive/master.zip>Download</a>
    <a href=https://github.com/laukstein/ajax-seo/issues>Issues</a>
</footer>";

$js  = debug ? '<script src=' . assets . "script$ver.js?$path></script>" : '<script>!function(e,t,r,s,a){"use strict";function o(){var e,r,s=t.getElementById("status"),a="expand"
s.classList?s.classList.toggle(a):(e=s.className.split(" "),r=e.indexOf(a),r>=0?e.splice(r,1):e.push(a),s.className=e.join(" "))}function n(){return t.URL}function i(){g&&clearTimeout(g),O.classList.contains("status-start")&&O.classList.add("status-done")}function c(e){return e.split("#")[0]}function l(e,r,a){for(ga("send","pageview",{page:decodeURI(s.pathname)}),a?i():b.abort(),t.title=e.title,w.innerHTML=e.content,H.querySelector(".x.focus")&&H.querySelector(".x.focus").classList.remove("focus"),H.querySelector(".x.active")&&H.querySelector(".x.active").classList.remove("active"),H.querySelector(".x.error")&&H.querySelector(".x.error").classList.remove("error"),S=c(n()),t.activeElement&&"body"!==t.activeElement.nodeName.toLowerCase()&&t.activeElement.blur(),y=0;y<X.length;y+=1)if(E=X[y],c(E.href)===S){if(E.focus(),e.statusName&&(r="error"),E.classList.add(r),"error"===r){E.classList.remove("active"),E.classList.add("x-error");break}E.classList.remove("x-error"),E.classList.remove("error");break}}function u(e){if(null!==e)try{e.click()}catch(r){var s=t.createEvent("MouseEvents")
s.initEvent("click",!0,!0),e.dispatchEvent(s)}}function f(e){if(!(void 0!==S&&S.indexOf("#")>-1)){M=!1,N=!1,i()
var t=e.state;if(!t)for(N=!0,S=c(n()),y=0;y<X.length;y+=1)if(c(X[y].href)===S)return u(X[y])
x=t.status,void 0===x&&(M=!0),x=void 0!==x?x:"active",M&&"active"===x&&(M=!1),l(t,x,!1)}}function d(e,t){for(var r=e.matches||e.webkitMatchesSelector||e.mozMatchesSelector||e.msMatchesSelector;e&&1===e.nodeType;){if(r.call(e,t))return e
e=e.parentNode}return null}function h(e){if(d(e.target,".bar"))return o()
if(!d(e.target,"nav "+A)&&O.classList.contains("expand"))return O.classList.remove("expand")
if(T=d(e.target,A),null!==T&&k===T.host&&(S=T.getAttribute("href"),(0!==S.indexOf("//")&&0!==S.indexOf("http")||(S=S.split(k).pop(),0===S.indexOf(I)))&&!("/"===S[0]&&0!==S.indexOf(I)||S.indexOf("#")>-1))){e.preventDefault()
var s=c(T.href),a=c(n()),i=T.innerText||T.textContent;N||(M=T.classList.contains("x-error")),!M&&!N&&s===a||T.classList.contains("focus")||(O.hasAttribute("class")&&O.removeAttribute("class"),0===S.indexOf(I)&&(S=S.slice(I.length)),q=I+S,M&&a===n()?r.replaceState(null,i,q):r.pushState(null,i,q),H.querySelector(".x.focus")&&H.querySelector(".x.focus").classList.remove("focus"),i=T.innerText||T.textContent,T.classList.remove("error"),T.classList.add("focus"),r.replaceState(null,i,q),L&&clearTimeout(L),L=setTimeout(function(){t.title=i},3),b.abort(),b.open("GET",I+"api"+(S.length>0?"/":"")+S),M&&b.setRequestHeader("If-Modified-Since","Sat, 1 Jan 2000 00:00:00 GMT"),b.send())}}function p(){g=setTimeout(function(){O.classList.add("status-start")},100)}function v(e,t){M="active"===t?!1:!0,r.replaceState(e,e.title,q),l(e,t,!0)}function m(){if(i(),x=this.status,L&&clearTimeout(L),x)if(200!==x)v({statusName:"error",title:"'.$pagetitle_error.'",content:"<h1>'.$pagetitle_error.'</h1><p>Sorry, this page hasn'."'".'t been found. Try to <a class=\"x x-error\" href="+S+">reload</a> the page or head to <a class=x href="+I+">home</a>."},"error")
else try{v(JSON.parse(this.response),"active")}catch(e){v({statusName:"error",title:"Server error",content:"<h1>'.$title_error.'</h1><p>Sorry, experienced server error. Try to <a class=\"x x-error\" href="+S+">reload</a> the page or head to <a class=x href="+I+">home</a>."},"error")}}if(e[a]=function(){e[a].q=e[a].q||[],e[a].q.push(arguments)},ga("create","'.(ga?ga:'UA-XXXX-Y').'","'.(strlen(ga_domain)>0?ga_domain:'auto').'"),ga("send","pageview"),!r.pushState)throw t.getElementById("expand").onclick=o,Error("History API not supported.")
var x,L,g,y,S,q,E,T,b=new XMLHttpRequest,O=t.getElementById("status"),w=t.getElementById("output"),M=!1,N=!1,k=s.host,I="'.$path.'",A=".x",H=t.querySelector("nav"),X=H?H.querySelectorAll(A):null;r.replaceState({title:t.title,content:w.innerHTML},t.title,n()),setTimeout(function(){e.onpopstate=f},100),t.addEventListener(e.hasOwnProperty("ontouchstart")?"touchstart":"click",h,!0),O.addEventListener("transitionend",function(e){e.target.classList.contains("status-done")&&(e.target.classList.remove("status-start"),e.target.classList.remove("status-done"))},!0),b.onloadstart=p,b.onload=m,b.onabort=i}(window,document,history,location,"ga")</script>';
$js .= ga ? "\n<script src=//www.google-analytics.com/analytics.js async></script>" : null;
echo "\n$js";