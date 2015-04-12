<?php

include 'content/config.php';
include 'content/connect.php';
include 'content/cache.php'; cache::url();

$gtitle      = !empty($gtitle) ? $gtitle : title;
$description = null;

if ($conn) {
    $title     = $title_error     = 'Oops...';
    $pagetitle = $pagetitle_error = 'Page not found';
    $content   = $content_error   = '<p>Sorry, this page hasn\'t been found. Try to <a class=x-error href=' . $_SERVER['REQUEST_URI'] . '>reload</a> the page or head to <a class=x-error href=' . $path . '>home</a>.</p>'; // Old Webkit breaks the layout without </p>
    $results   = false;

    $stmt = $mysqli->prepare('SELECT title, description, content, GREATEST(updated, created) AS date FROM `' . table . '` WHERE url=? LIMIT 1');
    $stmt->bind_param('s', $urldb);
    $stmt->execute();
    $stmt->bind_result($title, $description, $content, $date);

    while ($stmt->fetch()) {
        $results   = true;
        // SEO page title improvement for the root page
        $pagetitle = isset($url) ? $title : $gtitle;
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
// Twitter Cards https://dev.twitter.com/cards/overview, https://cards-dev.twitter.com/validator
$metadata .= "\n<meta property=twitter:card content=summary>";

// Perform speed and security by removing referrer header http://w3c.github.io/webappsec/specs/referrer-policy/
$metadata .= "\n<meta name=referrer content=never>";

// Optimize smart device viewport (initial-scale=1 to enable zoom-in, maximum-scale=1 to disable zoom)
$metadata .= "\n<meta name=viewport content=\"width=device-width, initial-scale=1\">";

// Set toolbar color http://updates.html5rocks.com/2014/11/Support-for-theme-color-in-Chrome-39-for-Android
$metadata .= "\n<meta name=theme-color content=#eef0f0>";

// Authorship in Google https://support.google.com/webmasters/answer/1408986
// $metadata .= "\n<link rel=author href=https://plus.google.com/000000000000000000000>";

// Prefetch CDN by saving DNS resolution time https://github.com/h5bp/html5-boilerplate/blob/master/doc/extend.md#dns-prefetching
if ($cdn_host) $metadata .= "\n<link rel=dns-prefetch href=$cdn_scheme$cdn_host>";

// Resource hints http://w3c.github.io/resource-hints/
// Fetch and cache API in background when everything is downloaded https://html.spec.whatwg.org/#link-type-prefetch
if ($conn && $results) $metadata .= "\n<link rel=\"prefetch prerender\" href=$path/api" . (strlen($urldb) ? "/$urldb" : null) . '>';

// Favicon 16x16, 32x32 4-bit 16 color /favicon.ico on website root or base64 inline dataURI when project not in root http://zoompf.com/2012/04/instagram-and-optimizing-favicons
// if ($path !== '/') $metadata .= "\n<link rel=\"shortcut icon\" href=data:image/x-icon;base64,...>";
// Chrome for Android icon http://updates.html5rocks.com/2014/11/Support-for-theme-color-in-Chrome-39-for-Android
// <link rel=icon sizes=192x192 href=icon.png>

// Website copyright license
$metadata .= "\n<link rel=license href=//creativecommons.org/licenses/by/4.0/>";

// Cache manifest (Chrome external domain hosting issue http://crbug.com/167918)
// <html lang=en manifest=manifest.appcache>
// JavaScript CDNs performance stats http://www.cdnperf.com


echo "<!doctype html>
<html lang=en>
<head prefix=\"og: http://ogp.me/ns#\">
<meta charset=utf-8>
$metadata
". ($debug ? '<link rel=stylesheet href=' . assets . "style.css>" : '<style>@viewport{width:device-width;zoom:1}body,html{height:100%}button,html,input,select,textarea{color:#222;font-family:Cambria,Georgia,serif}.button,::-webkit-input-placeholder,button,html,input,label{-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}html{font-size:62.45%;line-height:1.5;text-rendering:optimizeSpeed;background-color:#f8f9fa;-ms-touch-action:manipulation;touch-action:manipulation;cursor:default}blockquote,body,dd,dl,figure{margin:0}body{font-size:1.9em;overflow-wrap:break-word;-ms-hyphenate-limit-chars:6 3 2;hyphenate-limit-chars:6 3 2;-webkit-hyphens:auto;-moz-hyphens:auto;-ms-hyphens:auto;hyphens:auto}main{display:block;-webkit-user-select:text;-moz-user-select:text;-ms-user-select:text;user-select:text}dt,h1,h2,h3{line-height:1.07;font-weight:400;font-family:Times New Roman,serif}h1,h2{margin-top:.2em;margin-bottom:.2em}h1:first-line,h2:first-line{font-size:150%}h1{font-size:1.9em}h2{color:#777;font-size:1.5em}dt,h3{margin-top:.3em;margin-bottom:.3em;font-size:1.35em}h4{margin-top:.4em;margin-bottom:.4em}small{font-size:70%}blockquote{padding-left:1.2em;font-style:italic;border-left:.3em solid #eef0f0}blockquote cite{color:#777}blockquote cite:before{content:"\2014 \00A0";color:#777}cite{display:block}br{word-spacing:0}hr{position:relative;margin:1em -3em;margin:1em -4.9vw;clear:both;border-width:0;border-top:1px solid #eef0f0}code,pre{padding:.05em .3em;font-style:italic;font-family:Consolas,Liberation Mono,Courier,monospace;white-space:pre-wrap;background-color:rgba(206,209,210,.4);-moz-tab-size:4;tab-size:4}a,button,input,select,textarea{outline:0;pointer-events:auto}a img,abbr,iframe{border:0}a,img{-webkit-user-drag:none;user-drag:none}a[href]{color:#0f4cd5;text-decoration:none;-webkit-box-decoration-break:clone;box-decoration-break:clone;cursor:default}a[href]:focus,a[href]:hover{color:#0e439b;text-decoration:underline}img{width:auto;max-width:100%;height:auto;vertical-align:top;-ms-interpolation-mode:nearest-neighbor;image-rendering:-webkit-optimize-contrast;image-rendering:-moz-crisp-edges;image-rendering:crisp-edges}abbr{border-bottom:1px dotted #ccc}legend{display:table}label{display:inline-block;padding-bottom:.2em;padding-top:.2em}::selection{text-shadow:none;background-color:rgba(206,209,210,.4)}:placeholder-shown{opacity:.54;color:inherit}.button,button,input,select,textarea{width:20em;max-width:100%;padding:.6em .7em;margin:.15em 0;font-size:1em;line-height:1.25;background-color:#fff;border:1px solid #b1b2b2;box-sizing:border-box}textarea{overflow-x:hidden;overflow-y:scroll;min-height:4.95em;max-height:13em;word-wrap:break-word;resize:none}input:focus,input:hover,textarea:focus,textarea:hover{border-color:#9f9f9f;box-shadow:inset 0 1px 3px rgba(0,0,0,.15)}[type=checkbox],[type=color],[type=file],[type=image],[type=radio]{width:auto;padding:0;border:0;box-sizing:padding-box}.button,button,select{overflow:hidden;display:inline-block;white-space:nowrap;text-align:center;text-overflow:ellipsis;background-color:#f4f4f4;border:1px solid #c0c0c1;outline:0;box-shadow:inset 0 0 0 1px #f5f5f5,1px 1px 2px rgba(0,0,0,.1)}.button{width:auto}a[href].button{color:inherit;text-decoration:none}.button:focus,.button:hover,button:focus,button:hover,select:focus,select:hover{background-color:#f8f9fa;border-color:#9f9f9f}.button:active,button:active,select:active{background-color:rgba(206,209,210,.4);box-shadow:none}option[value=""]{display:none}[tabindex]{outline:0}html{width:100vw;max-width:100%}div.noscroll{overflow:hidden}.wrapper{width:100vw;min-height:100vh}#status{opacity:0;position:fixed;z-index:999;left:0;width:0;height:3px;background-color:#006cff;box-shadow:0 0 0 1px rgba(255,255,255,.8);-webkit-transform:translateZ(0);-ms-transform:translateZ(0);transform:translateZ(0);-webkit-backface-visibility:hidden;backface-visibility:hidden;-webkit-perspective:1000;perspective:1000;will-change:transition}#status.status-start:before{content:"";display:block;width:1em;height:100%;float:right;background-image:-webkit-linear-gradient(left,rgba(0,194,255,0),#00c2ff);background-image:linear-gradient(to right,rgba(0,194,255,0),#00c2ff);box-shadow:3px -2px 5px 1px #00c2ff}#status.status-start{opacity:1;width:70%;-webkit-transition:opacity .2s,width 5s cubic-bezier(.2,1,.4,1);transition:opacity .2s,width 5s cubic-bezier(.2,1,.4,1)}#status.status-done{width:100%;opacity:0;-webkit-transition-duration:.2s;transition-duration:.2s}.tab{position:fixed;z-index:100;top:0;right:0;left:0;width:100vw;line-height:2.2;font-family:Segoe UI,Open Sans,sans-serif;text-align:center;white-space:nowrap}.bar,.focusout{display:none;box-suppress:discard}.footer,.header,.main{width:95%;max-width:900px;margin:auto;box-sizing:border-box}.header,.main{background-color:#fff;border:0 solid #d9e0e2;border-width:0 1px}.header{position:relative;padding:.1em .4em}.header:after{content:"";position:absolute;left:0;right:0;height:1.5em;background-image:-webkit-linear-gradient(#fff,rgba(255,255,255,.9)35%,rgba(255,255,255,.8)50%,rgba(255,255,255,0));background-image:linear-gradient(#fff,rgba(255,255,255,.9)35%,rgba(255,255,255,.8)50%,rgba(255,255,255,0));pointer-events:none}.nav{overflow:hidden;display:inline-block;display:-webkit-box;display:-ms-flexbox;display:flex;width:100%;margin:.1em auto;font-size:1.05em;background-color:#f4f4f4;background-image:-webkit-linear-gradient(#f8f8f8,rgba(248,249,250,0));background-image:linear-gradient(#f8f8f8,rgba(248,249,250,0));border:1px solid #d9e0e2;border-bottom-color:#ccc;border-radius:.2em;box-shadow:0 1px 1px rgba(0,0,0,.1),inset 0 0 0 1px rgba(255,255,255,.5)}.footer a,.nav.nav a{color:inherit;text-decoration:none}.nav a{display:block;position:relative;z-index:1;-webkit-box-flex:1;-ms-flex:1;flex:1;flex-basis:auto;min-width:1px;max-height:2.3em;padding:0 .43em;border:0 solid #d9e0e2;border-left-width:1px}.nav a:first-child{border-left:0}.nav a:first-of-type{border-radius:.2em 0 0 .2em}.nav a:last-of-type{border-radius:0 .2em .2em 0}.nav a:focus,.nav a:hover{background-color:#fff}.nav a.focus{background-color:rgba(206,209,210,.4)}.nav a.error{background-color:#f1e2e2}.nav a.active{margin:-1px auto;color:#fff;line-height:2.25;background-color:#006cff;border-color:transparent}.nav .active+a{border-color:transparent}.nav span{overflow:hidden;display:block;position:relative;top:-.06em;text-overflow:clip;-webkit-mask:linear-gradient(to left,rgba(0,0,0,0),#000 1.5em,#000);mask:linear-gradient(to left,rgba(0,0,0,0),#000 1.5em,#000)}.footer,.main{position:relative;padding:0 3em;padding:0 4.9vw}.main{overflow:hidden;min-height:100vh;padding-top:3.8em;padding-bottom:6em;will-change:contents}.main :target{background-color:#ff0}.main a:target{text-decoration:none}.main a:target:hover{text-decoration:underline}.main [id]:target:before{content:"";display:block;padding-top:3.5em;margin-top:-3.5em;pointer-events:none;background-color:#fff}.main h1[id]:target:before{padding-top:1.3em;margin-top:-1.3em}.footer{overflow:hidden;height:2.7em;margin-top:-2.7em;line-height:2.7;text-overflow:ellipsis;white-space:nowrap;border-top:1px solid #eee}.footer a{display:inline-block;color:#777}.footer a+a{margin-left:.6em}.footer a:focus,.footer a:hover{color:#222;text-decoration:underline}template{display:none;box-suppress:discard}@media \0screen\,screen\9{.main,.noscroll,.wrapper{height:100%}.main,.noscroll.noscroll{overflow:visible}.nav{display:table;table-layout:fixed}.nav a{display:table-cell}.footer,.handler,[hidden]{display:none}}@media (min-width:0\0) and (min-resolution:.001dpcm){.nav,.nav a:first-of-type,.nav a:last-of-type{border-radius:0}.nav{display:table;table-layout:fixed}.nav a{display:table-cell}.handler,[hidden]{display:none}}@media (-ms-high-contrast:active),(-ms-high-contrast:none){.nav,html,textarea{-ms-overflow-style:-ms-autohiding-scrollbar}a{background-color:initial}select{-ms-user-select:none}:-ms-input-placeholder{opacity:.54}::-ms-clear{display:none}select:focus::-ms-value{color:initial;background-color:initial}[hidden]{display:none;box-suppress:discard}}@media (-webkit-min-device-pixel-ratio:0){.nav,html,textarea{-webkit-overflow-scrolling:touch}html{-webkit-font-smoothing:antialiased;-webkit-text-size-adjust:100%}[tabindex],a,button,input,select,textarea{-webkit-tap-highlight-color:rgba(0,0,0,0)}a{-webkit-touch-callout:none}::-webkit-input-placeholder{opacity:.54;color:inherit;-webkit-user-select:none}}@-moz-document url-prefix(){::-moz-selection{text-shadow:none;background-color:rgba(206,209,210,.4)}label:active{background-color:transparent}button,input,select,textarea{background-image:none;border-radius:0}button::-moz-focus-inner,input::-moz-focus-inner{border:0;padding:0}}@media (max-width:540px){html{background-color:#fff}html.noscroll{overflow:hidden}hr{margin-right:-1.7em;margin-left:-1.7em}button,input,label,textarea{width:100%}[type=checkbox],[type=color],[type=file],[type=image],[type=radio]{width:auto}#status:not(.expand)~.tab .header{-webkit-transition:none;transition:none}.tab{z-index:100;width:100%;padding-bottom:.2em;text-align:left;text-align:start;background-image:-webkit-linear-gradient(#fff,rgba(255,255,255,.9)35%,rgba(255,255,255,.8)50%,rgba(255,255,255,0));background-image:linear-gradient(#fff,rgba(255,255,255,.9)35%,rgba(255,255,255,.8)50%,rgba(255,255,255,0));pointer-events:none}.bar{width:auto;height:2.35em;padding:inherit;margin:inherit;background-color:transparent;border:inherit;box-shadow:inherit;all:unset;display:inline-block;position:relative;vertical-align:top;z-index:3}.bar,.nav{pointer-events:auto}.bar:focus,.bar:hover{background-color:rgba(206,209,210,.4)}.bar span,.bar span:after,.bar span:before{display:block;width:1.3em;height:.21em;background-color:#222;border-radius:.1em;-webkit-transition:width .1s,background-color 1ms,-webkit-transform .35s cubic-bezier(.2,1,.4,1);transition:width .1s,background-color 1ms,transform .35s cubic-bezier(.2,1,.4,1)}.bar span{position:relative;margin:1.07em .65em 1.07em .55em;transform:translateY(0);-webkit-transition-duration:.15s;transition-duration:.15s;will-change:transition,transform;pointer-events:none}.bar span:before{content:"";position:absolute;-webkit-transform:translateY(-.42em);-ms-transform:translateY(-.42em);transform:translateY(-.42em)}.bar span:after{content:"";position:absolute;width:.9em;-webkit-transform:translateY(.42em);-ms-transform:translateY(.42em);transform:translateY(.42em)}.expand~.tab .bar span{background-color:transparent}.expand~.tab .bar span:before{-webkit-transform:rotate(45deg);-webkit-transform:rotate3d(0,0,1,45deg);-ms-transform:rotate(45deg);transform:rotate3d(0,0,1,45deg)}.expand~.tab .bar span:after{width:1.3em;-webkit-transform:rotate(-45deg);-webkit-transform:rotate3d(0,0,1,-45deg);-ms-transform:rotate(-45deg);transform:rotate3d(0,0,1,-45deg)}.expand~.tab .header{-webkit-transform:none;-ms-transform:none;transform:none}.focusout{display:inline}.header{position:fixed;top:0;left:0;bottom:0;width:75%;padding:0;background-color:#f8f9fa;border:0;-webkit-transform:translateX(-100%);-ms-transform:translateX(-100%);transform:translateX(-100%);-webkit-transition:-webkit-transform .35s cubic-bezier(.2,1,.4,1);transition:transform .35s cubic-bezier(.2,1,.4,1)}.header:after,.header:before{opacity:0;content:"";position:fixed;left:0;z-index:2;width:75%;height:2.35em;pointer-events:none;transition:opacity .35s cubic-bezier(.2,1,.4,1)}.header:before{top:0;background-image:-webkit-linear-gradient(#f8f9fa,rgba(248,249,250,.9)50%,rgba(248,249,250,.8)60%,rgba(248,249,250,0));background-image:linear-gradient(#f8f9fa,rgba(248,249,250,.9)50%,rgba(248,249,250,.8)60%,rgba(248,249,250,0))}.header:after{bottom:0;right:auto;background-image:-webkit-linear-gradient(rgba(248,249,250,0),rgba(248,249,250,.8)50%,rgba(248,249,250,.9)60%,#f8f9fa);background-image:linear-gradient(rgba(248,249,250,0),rgba(248,249,250,.8)50%,rgba(248,249,250,.9)60%,#f8f9fa)}.expand~.tab .header:after,.expand~.tab .header:before{opacity:1}.expand~.handler,.expand~.tab .handler{position:inherit;top:0;right:0;left:0;bottom:0;margin:0}.expand~.handler{position:fixed;z-index:99}.expand~.tab .header{box-shadow:0 0 4em rgba(0,0,0,.3)}.nav,.nav a:first-of-type,.nav a:last-of-type{border-radius:0}.nav{overflow:auto;display:block;height:100%;padding:2.3em 0;margin:0;background:0 0;border:0;box-shadow:none;box-sizing:border-box}.nav.nav a{display:block;-webkit-box-flex:0;-webkit-flex:0;-ms-flex:0;flex:0;padding:0 1.3em;float:none;border-color:#eef0f0;border-width:0;border-top-width:1px}.nav a:first-child{border-top-width:0}.main{width:auto;min-width:13.6em;padding:2em 1.9em 3.2em;border:0}.main [id]:before{padding-top:1.8em;margin-top:-1.8em}.footer{display:none}}@media (max-width:320px){html{font-size:58%}h1{font-size:1.5em}h2{font-size:1.2em}dt,h3{font-size:1.3em}}@media print{@page{margin:.5cm}*,h2,h3{color:#222;text-shadow:none;background:0 0}h2,h3,p{orphans:3;widows:3}h2,h3{page-break-after:avoid}a{color:#777;text-decoration:underline}img{page-break-inside:avoid}.main,html{background:0 0}.footer,.tab{display:none}.main{padding-top:1em;padding-bottom:1em;border-width:0}.button,button,select{box-shadow:none}}</style>') . "
<!--[if lt IE 9]><script src=//cdn.jsdelivr.net/html5shiv/3.7.2/html5shiv.min.js></script><![endif]-->
<body itemscope itemtype=http://schema.org/WebPage>
<div class=noscroll>
<div class=wrapper>$note";

if ($conn) {
    if ($stmt = $mysqli->prepare('SELECT url, title FROM `' . table . '` WHERE permit=1 ORDER BY `order` ASC')) {
        $stmt->execute();
        $stmt->bind_result($data_url, $data_metatitle);

        echo "\n<div id=status role=progressbar></div>
<div class=tab>
    <button class=bar id=bar tabindex=0><span></span></button>
    <span id=focusin class=focusin tabindex=0></span>
    <header class=header>
        <nav class=nav id=nav role=navigation>";

        while ($stmt->fetch()) echo "\n            <a" . ($urldb === $data_url ? ' class=active' : null) . " href=\"" . (strlen($data_url) ? "$path/$data_url" : (strlen($path) ? $path : '/')) ."\" dir=auto><span>$data_metatitle</span></a>";

        echo "\n            <div class=handler id=focusout tabindex=0></div>
        </nav>
    </header>
</div>
<div class=handler id=reset></div>";

        $stmt->free_result();
        $stmt->close();
    }
    $mysqli->close();
}

echo "\n<main id=output class=main role=main itemprop=about itemscope itemtype=http://schema.org/Article>";

if ($conn) echo "\n<h1 dir=auto>$title</h1>";

echo "\n$content
</main>
<footer class=footer itemprop=breadcrumb>
    <a href=https://github.com/laukstein/ajax-seo>GitHub project</a>
    <a href=https://github.com/laukstein/ajax-seo/archive/master.zip>Download</a>
    <a href=https://github.com/laukstein/ajax-seo/issues>Issues</a>
</footer>
</div>
</div>";

$js  = $debug ? '<script src="' . assets . "script.js#" . (strlen($path) ? $path : '/') . '" async></script>' : '<script>void function(e,t){"use strict";"object"==typeof module&&"object"==typeof module.exports?module.exports=t():"function"==typeof define&&define.amd?define(["as"],t):e.as=t()}(this,function(){"use strict";var e,t,r,s,a=window,o=document,n=history,i=location,l={classList:"classList"in document.createElement("_"),eventListener:o.addEventListener?!0:!1},c={bar:o.getElementById("bar"),focusin:o.getElementById("focusin"),focusout:o.getElementById("focusout"),reset:o.getElementById("reset"),nav:o.getElementById("nav"),status:o.getElementById("status"),output:o.getElementById("output")},u={toggleClassName:function(e,t){if(e)if(l.classList)e.classList.toggle(t);else{var r=e.className.split(" "),s=r.indexOf(t);s>=0?r.splice(s,1):r.push(t),e.className=r.join(" ")}},toFocus:function(){o.activeElement!==c.focusin&&u.toToggle(!0)},toToggle:function(e){u.toggleClassName(o.body.parentElement,"noscroll"),u.toggleClassName(c.status,"expand"),e&&c.focusin&&setTimeout(function(){c.focusin.focus()},0)}},f={version:4,analytics:"' . ga . '",path:function(){var e=o.currentScript||function(){var e=o.getElementsByTagName("script");return e[e.length-1]}(),t=e.src.split("#")[1]||"/ajax-seo",r=new RegExp("("+t+")(.*)$");return o.URL.replace(r,"$1")}(),url:o.URL,title:o.title,activeElement:function(){var e,t=o.querySelectorAll?o.querySelectorAll("[href]"):[];for(e=0;e<t.length;e+=1)if(t[e].href===o.URL)return t[e];return null}(),error:!1};if(f.analytics&&(a.ga=function(){ga.q=ga.q||[],ga.q.push(arguments)},ga("create",f.analytics,"auto"),ga("send","pageview")),c.bar&&l.eventListener&&(c.bar.addEventListener("focus",function(){u.toToggle(!0)},!0),c.bar.addEventListener("click",u.toFocus,!0),c.nav&&c.nav.addEventListener("click",u.toToggle,!0),c.focusout&&c.focusout.addEventListener("focus",u.toToggle,!0),c.reset&&c.reset.addEventListener("click",u.toToggle,!0)),!n.pushState||!l.classList||!l.eventListener)throw new Error("Browser legacy: History API not supported");if(!c.output)throw new Error("Layout issue: missing elements");return s={filter:function(e){return e?e.replace(/#.*$/,"").toLowerCase():void 0},reset:function(){t&&clearTimeout(t),c.status&&c.status.classList.contains("status-start")&&c.status.classList.add("status-done")},click:function(e){if(e)try{e.click()}catch(t){var r=o.createEvent("MouseEvents");r.initEvent("click",!0,!0),e.dispatchEvent(r)}},nav:{nodeList:c.nav?Array.from?Array.from("a"):[].slice.call(c.nav.querySelectorAll("a")):null,activeElement:function(){var e;if(s.nav.nodeList)for(e=0;e<s.nav.nodeList.length;e+=1)if(s.filter(s.nav.nodeList[e].href)===f.url)return s.nav.nodeList[e];return null}},update:function(e,t,a,l){f.analytics&&ga("send","pageview",{page:decodeURI(i.pathname)}),a?s.reset():r.abort(),s.nav.nodeList&&(c.focus=c.nav.querySelector(".focus"),c.active=c.nav.querySelector(".active"),c.error=c.nav.querySelector(".error"),c.focus&&c.focus.classList.remove("focus"),c.active&&c.active.classList.remove("active"),c.error&&c.error.classList.remove("error")),o.activeElement&&"BODY"===o.activeElement.tagName&&o.activeElement.blur(),f.url=s.filter(o.URL),l=l||s.nav.activeElement(),l&&(l.focus(),l.classList.add(t),"error"===t&&l.classList.add("x-error")),o.title=f.title=e.title,o.body.scrollTop=0,c.output.innerHTML=e.content,i.hash&&(n.replaceState({title:f.title,content:c.output.innerHTML},f.title,f.url),i.replace(f.url+i.hash))},retry:!1,popstate:function(e){if(!(i.hash&&s.filter(f.url)===s.filter(o.URL)||f.url&&f.url.indexOf("#")>-1)){f.error=!1,s.retry=!1,s.reset();var t,r=e.state,a=r&&r.status?r.status:"active";r||(s.retry=!0,f.url=s.filter(o.URL),t=s.nav.activeElement(),s.click(t)),"error"===a&&(f.error=!0),f.error&&"active"===a&&(f.error=!1),s.update(r,a,!1,t)}},loadstart:function(){c.status&&(t=setTimeout(function(){c.status.classList.add("status-start")},100))},callback:function(e,t){f.error="active"===t?!1:!0,n.replaceState(e,e.title,null),s.update(e,t,!0)},load:function(){s.reset();var t=this.status;if(e&&clearTimeout(e),t)if(200!==t)s.callback({status:"error",title:"' . $pagetitle_error . '",content:"<h1>' . $title_error . '</h1><p>Sorry, this page hasn\'t been found. Try to <a class=x-error href="+f.url+">reload</a> the page or head to <a href="+f.path+">home</a>."},"error");else try{s.callback(JSON.parse(this.response),"active")}catch(r){s.callback({status:"error",title:"Server error",content:"<h1>' . $title_error . '</h1><p>Sorry, experienced server error. Try to <a class=x-error href="+f.url+">reload</a> the page or head to <a href="+f.path+">home</a>."},"error")}},closest:function(e,t){if(!e||!t)return null;if(e.closest)return e.closest(t);for(var r=e.matches||e.webkitMatchesSelector||e.msMatchesSelector;e&&1===e.nodeType;){if(r.call(e,t))return e;e=e.parentNode}return null},listener:function(t){if(t){var a=t.target,i=new RegExp("^"+f.path+"($|#|/.{1,}).*","i"),l={};a&&("A"!==a.tagName&&(a=s.closest(a,"a[href]")),a&&"A"===a.tagName&&a.hasAttribute("href")&&i.test(a.href)&&(f.url=a.href.toLowerCase().replace(/(\/)+(?=\1)/g,"").replace(/(^https?:(\/))/,"$1/").replace(/\/$/,""),l.attr=s.filter(f.url),l.address=s.filter(o.URL),l.attr===l.address&&f.url.indexOf("#")>-1||(t.preventDefault(),a.blur(),f.activeElement=a,s.retry||(f.error=f.activeElement.classList.contains("x-error")),f.title=f.activeElement.innerText||f.activeElement.textContent,f.error&&l.address===o.URL?n.replaceState(null,f.title,f.url):f.error||f.url===o.URL||n.pushState(null,f.title,f.url),!f.error&&!s.retry&&l.attr===l.address||f.activeElement.classList.contains("focus")||(e&&clearTimeout(e),e=setTimeout(function(){o.title=f.title},3),c.status&&(c.status.classList.remove("status-start"),c.status.classList.remove("status-done")),s.nav.nodeList&&(f.error&&(f.activeElement.classList.remove("x-error"),f.activeElement.classList.remove("error")),c.focus=c.nav.querySelector(".focus"),c.focus&&c.focus.classList.remove("focus")),f.activeElement.classList.add("focus"),r.abort(),r.open("GET",f.path+"/api"+f.url.replace(new RegExp("^"+f.path,"i"),"")),f.error&&r.setRequestHeader("If-Modified-Since","Sat, 1 Jan 2000 00:00:00 GMT"),r.send()))))}},resetStatus:function(e){var t=e.target;t.contains("status-done")&&(t.classList.remove("status-start"),t.clacachessList.remove("status-done"))},init:function(){c.status&&c.status.addEventListener("transitionend",s.resetStatus,!0),setTimeout(function(){a.onpopstate=s.popstate},100),n.replaceState({title:f.title,content:c.output.innerHTML},f.title,f.url),r=new XMLHttpRequest,r.onloadstart=s.loadstart,r.onload=s.load,r.onabort=s.reset,o.addEventListener(a.hasOwnProperty("ontouchstart")?"touchstart":"click",s.listener,!0)}},s.init(),f});</script>';
$js .= ga ? "\n<script src=//www.google-analytics.com/analytics.js async></script>" : null;
echo "\n$js";
