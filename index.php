<?php

include 'content/config.php';
include 'content/connect.php';
include 'content/cache.php'; cache::url();

// PHP7 : $_GET['url'] ?? ''; // https://www.bram.us/2014/10/16/php-null-coalesce-operator/
$gtitle      = !empty($gtitle) ? $gtitle : title;
$description = null;
$result      = false;

if ($conn) {
    $title     = $title_error     = 'Whoops...';
    $pagetitle = $pagetitle_error = 'Page not found';
    // Old Webkit breaks the layout without </p>
    $content   = $content_error   = '<p>This page hasn\'t been found. Try to <a class=x-error href=' . $url . '>reload</a>' . ($ishome ? null : ' or head to <a href=' . $path . '>home page</a>') . '.</p>';

    $stmt = $mysqli->prepare('SELECT title, description, content, GREATEST(modified, created) AS date FROM `' . table . '` WHERE url=? LIMIT 1');
    $stmt->bind_param('s', $urldb);
    $stmt->execute();
    $stmt->bind_result($title, $description, $content, $date);

    while ($stmt->fetch()) {
        $result    = true;
        // SEO page title improvement for the root page
        $pagetitle = $ishome ? $title : $gtitle;
        $content   = string($content);
    }
    $stmt->free_result();
    $stmt->close();

    if (!$result) {
        // URL does not exist
        http_response_code(404);

        $title     = $title_error;
        $content   = $content_error;
        $pagetitle = $pagetitle_error;
    }

    if (isset($_GET['api'])) {
        // API
        include 'content/api.php';
        exit;
    }
}


if (empty($_GET['api'])) {
    $equal_origin = false;

    if ($cdn_host) {
        preg_match('/([^.]*\.[^.]{2}|[^.]*)\.([^.]*)$/i', $host, $matches_host);
        preg_match('/([^.]*\.[^.]{2}|[^.]*)\.([^.]*)$/i', $cdn_host, $matches_cdn_host);
        $equal_origin = $matches_host[0] === $matches_cdn_host[0];
    }

    // Avoid XSS attacks with CSP https://w3c.github.io/webappsec/specs/content-security-policy/
    // Firefox OS app suggestion https://developer.mozilla.org/en-US/Apps/CSP
    // Upgrade insecure requests https://w3c.github.io/webappsec/specs/upgrade/#upgrade_insecure_requests
    header('Content-Security-Policy:' . ($scheme === 'https' ? ' upgrade-insecure-requests;' : '') . "script-src 'self' 'unsafe-inline'" . ($equal_origin ? null : " $cdn_host") . (ga ? ' www.google-analytics.com' : null));
}

// Max 160 character title http://blogs.msdn.com/b/ie/archive/2012/05/14/sharing-links-from-ie10-on-windows-8.aspx
$metadata  = "<title>$pagetitle</title>";

// Open Graph protocol http://ogp.me
$metadata .= "\n<meta property=og:title content=\"$pagetitle\">";
// Max 253 character description http://blogs.msdn.com/b/ie/archive/2012/05/14/sharing-links-from-ie10-on-windows-8.aspx
if ($description) $metadata .= "\n<meta property=og:description name=description content=\"$description\">";
// Twitter Cards https://dev.twitter.com/cards/overview, https://cards-dev.twitter.com/validator
$metadata .= "\n<meta property=twitter:card content=summary>";

// Perform speed and security by removing referrer header http://w3c.github.io/webappsec/specs/referrer-policy/
$metadata .= "\n<meta name=referrer content=never>";

// Optimize smart device viewport (initial-scale=1 to enable zoom-in, maximum-scale=1 to disable zoom)
$metadata .= "\n<meta name=viewport content=\"width=device-width, initial-scale=1\">";

// Set toolbar color http://updates.html5rocks.com/2014/11/Support-for-theme-color-in-Chrome-39-for-Android
// Black SVG favicon <link rel=mask-icon sizes=any href=icon.svg> coloured in "theme-color" https://lists.w3.org/Archives/Public/public-whatwg-archive/2015Jun/0059.html
// Resource https://developer.apple.com/library/safari/releasenotes/General/WhatsNewInSafari/Articles/Safari_9.html#//apple_ref/doc/uid/TP40014305-CH9-SW20
$metadata .= "\n<meta name=theme-color content=#" . ($result ? 'eef0f0' : 'f79c87') . ">";

// Prefetch CDN by saving DNS resolution time https://github.com/h5bp/html5-boilerplate/blob/master/doc/extend.md#dns-prefetching
if ($cdn_host) $metadata .= "\n<link rel=dns-prefetch href=$cdn_scheme$cdn_host/>";

// Resource hints http://w3c.github.io/resource-hints/
// Fetch and cache API in background when everything is downloaded https://html.spec.whatwg.org/#link-type-prefetch
if ($conn && $result) $metadata .= "\n<link rel=\"prefetch prerender\" href=$path/api" . $url . '>';

// Favicon 16x16, 32x32 4-bit 16 color /favicon.ico on website root or base64 inline dataURI when project not in root http://zoompf.com/2012/04/instagram-and-optimizing-favicons
// if ($path !== '/') $metadata .= "\n<link rel=\"shortcut icon\" href=data:image/x-icon;base64,...>";
// Chrome for Android icon http://updates.html5rocks.com/2014/11/Support-for-theme-color-in-Chrome-39-for-Android
// <link rel=icon sizes=192x192 href=icon.png>

// Website copyright license
$metadata .= "\n<link rel=license href=//creativecommons.org/licenses/by/4.0/>";

// <html lang=en manifest=$path/manifest.appcache> Cache manifest (Chrome external domain hosting issue http://crbug.com/167918)
// JavaScript CDNs performance stats http://www.cdnperf.com

echo "<!doctype html>
<html lang=en>
<head prefix=\"og: http://ogp.me/ns#\">
<meta charset=utf-8>
$metadata
" . ($debug ? '<link rel=stylesheet href=' . assets . "style.css>" : '<style>@viewport{width:device-width;zoom:1}body,html{height:100%}button,html,input,select,textarea{color:#222;font-family:Cambria,Georgia,serif}.button,::-webkit-input-placeholder,button,html,input,label{-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}html{scroll-behavior:smooth;font-size:62.45%;line-height:1.5;text-rendering:optimizeSpeed;background-color:#f8f9fa;-ms-touch-action:manipulation;touch-action:manipulation;cursor:default}blockquote,body,dd,dl,figure{margin:0}body{font-size:1.9em;overflow-wrap:break-word;word-wrap:break-word;-ms-hyphenate-limit-chars:6 3 2;hyphenate-limit-chars:6 3 2;-webkit-hyphens:auto;-moz-hyphens:auto;-ms-hyphens:auto;hyphens:auto}main{display:block;-webkit-user-select:text;-moz-user-select:text;-ms-user-select:text;user-select:text}dt,h1,h2,h3{line-height:1.07;font-weight:400;font-family:Times New Roman,serif}h1,h2{margin-top:.2em;margin-bottom:.2em}h1:first-line,h2:first-line{font-size:150%}h1{font-size:1.9em}h2{color:#777;font-size:1.5em}dt,h3{margin-top:.3em;margin-bottom:.3em;font-size:1.35em}h4{margin-top:.4em;margin-bottom:.4em}p{margin-top:.8em;margin-bottom:.8em}p+p{margin-top:1.6em}small{font-size:70%}blockquote{padding-left:1.2em;font-style:italic;border-left:.3em solid #eef0f0;border-color:rgba(125,138,138,.13)}blockquote cite{color:#777}blockquote cite:before{content:"\2014 \00A0";color:#777}cite{display:block}br{word-spacing:0}hr{position:relative;margin:1em -3em;margin-right:-4.9vw;margin-left:-4.9vw;clear:both;border-width:0;border-top:1px solid #eef0f0;border-color:rgba(125,138,138,.13)}code,pre{padding:.05em .3em;font-style:italic;font-family:Consolas,Liberation Mono,Courier,monospace;white-space:pre-wrap;background-color:rgba(206,209,210,.4);-moz-tab-size:4;tab-size:4}a,button,input,select,textarea{outline:0;pointer-events:auto}button,input,select{vertical-align:middle}a img,abbr,iframe{border:0}a,img{-webkit-user-drag:none;user-drag:none}a[href]{color:#0f4cd5;text-decoration:none;-webkit-box-decoration-break:clone;box-decoration-break:clone;cursor:default}a[href]:focus,a[href]:hover{color:#0e439b;text-decoration:underline}img{width:auto;max-width:100%;height:auto;vertical-align:top;-ms-interpolation-mode:nearest-neighbor;image-rendering:-webkit-optimize-contrast;image-rendering:-moz-crisp-edges;image-rendering:crisp-edges}abbr{border-bottom:1px dotted #ccc}legend{display:table}label{display:inline-block;padding-bottom:.2em;padding-top:.2em;line-height:1.2;vertical-align:middle}::selection{text-shadow:none;background-color:rgba(206,209,210,.4)}:placeholder-shown{opacity:.54;color:inherit}.button,button,input,select,textarea{width:20em;max-width:100%;padding:.6em .7em;margin:.15em 0;font-size:1em;line-height:1.25;background-color:#fff;border:1px solid #b1b2b2;box-sizing:border-box}textarea{overflow-x:hidden;overflow-y:scroll;min-height:4.95em;max-height:13em;word-wrap:break-word;resize:none}input:focus,input:hover,textarea:focus,textarea:hover{border-color:#9f9f9f;box-shadow:inset 0 1px 3px rgba(0,0,0,.15)}[type=checkbox],[type=color],[type=file],[type=image],[type=radio]{width:auto;padding:0;border:0;box-sizing:content-box}.button,button,select{overflow:hidden;display:inline-block;white-space:nowrap;word-wrap:normal;text-align:center;text-overflow:ellipsis;background-color:#f4f4f4;border:1px solid #c0c0c1;outline:0;box-shadow:inset 0 0 0 1px #f5f5f5,1px 1px 2px rgba(0,0,0,.1)}.button{width:auto}a[href].button{color:inherit;text-decoration:none}.button:focus,.button:hover,button:focus,button:hover,select:focus,select:hover{background-color:#f8f9fa;border-color:#9f9f9f}.button:active,button:active,select:active{background-color:rgba(206,209,210,.4);box-shadow:none}option{min-width:100%;max-width:0}option[value=""]{display:none}[tabindex]{outline:0}html{width:100%;width:100vw;max-width:100%}div.noscroll{overflow:hidden}.wrapper{width:100%;width:100vw;min-height:100vh}.status{opacity:0;position:fixed;z-index:999;left:0;width:0;height:3px;background-color:#60d778;box-shadow:0 0 0 1px rgba(255,255,255,.8);-webkit-transform:translateZ(0);-ms-transform:translateZ(0);transform:translateZ(0);-webkit-backface-visibility:hidden;backface-visibility:hidden;-webkit-perspective:1000;perspective:1000;will-change:transform;pointer-events:none}.status-start:before{content:"";display:block;width:10%;height:100%;float:right;background-image:-webkit-linear-gradient(left,rgba(96,215,120,0),#62fb82);background-image:linear-gradient(to right,rgba(96,215,120,0),#62fb82);box-shadow:2px -2px 5px 1px #62fb82}.status-start{opacity:1;width:70%;width:70vw;-webkit-transition:opacity .2s,width 5s cubic-bezier(.2,1,.4,1);transition:opacity .2s,width 5s cubic-bezier(.2,1,.4,1)}.status-done{opacity:0;width:100%;width:100vw;-webkit-transition-duration:.2s;transition-duration:.2s}.status-error{opacity:1;width:100%;width:100vw;background-color:#f79c87;-webkit-transition:background .2s cubic-bezier(.2,1,.4,1);transition:background .2s cubic-bezier(.2,1,.4,1)}.status-error:before{display:none}.tab{position:fixed;z-index:100;top:0;right:0;left:0;width:100%;width:100vw;line-height:2.2;font-family:Segoe UI,Open Sans,sans-serif;text-align:center;white-space:nowrap;word-wrap:normal;backface-visibility:hidden}.bar,.focusin,.handler{display:none;box-suppress:discard}.footer,.header,.main{width:95%;max-width:900px;margin:auto;box-sizing:border-box}.header,.main{background-color:#fff;border:0 solid #d9e0e2;border-width:0 1px}.header{position:relative;padding:.08em .4em}.header:after{content:"";position:absolute;left:0;right:0;height:1.5em;background-image:-webkit-linear-gradient(#fff,rgba(255,255,255,.9) 35%,rgba(255,255,255,.8) 50%,rgba(255,255,255,0));background-image:linear-gradient(#fff,rgba(255,255,255,.9) 35%,rgba(255,255,255,.8) 50%,rgba(255,255,255,0));pointer-events:none}.nav{overflow:hidden;display:inline-block;display:-webkit-box;display:-ms-flexbox;display:flex;width:100%;margin:.1em auto;font-size:1.05em;background-color:#f4f4f4;background-image:-webkit-linear-gradient(#f8f8f8,rgba(248,249,250,0));background-image:linear-gradient(#f8f8f8,rgba(248,249,250,0));border:1px solid #d9e0e2;border-bottom-color:#ccc;border-radius:.2em;box-shadow:0 1px 1px rgba(0,0,0,.1),inset 0 0 0 1px rgba(255,255,255,.5)}.footer a,.nav.nav a{color:inherit;text-decoration:none}.nav a{display:block;-webkit-box-flex:1;-ms-flex:1;flex:1;flex-basis:auto;min-width:1px;padding:0 .43em;border:0 solid #d9e0e2;border-left-width:1px}.nav a:first-child{border-left:0}.nav a:first-of-type{border-radius:.2em 0 0 .2em}.nav a:last-of-type{border-radius:0 .2em .2em 0}.nav a:focus,.nav a:hover{background-color:#fff}.nav a.active,.nav a.error,.nav a.focus{margin:-1px auto;line-height:2.25;border-color:transparent}.nav .active+a,.nav .error+a,.nav .focus+a{border-color:transparent}.nav a.focus{background-color:rgba(206,209,210,.4)}.nav a.active{position:relative;z-index:1;color:#fff;background-color:#006cff}.nav a.error{background-color:#f7dad4}.nav span{overflow:hidden;display:block;position:relative;top:-.06em;max-height:2.3em;text-overflow:ellipsis}.footer,.main{position:relative;padding:0 3em;padding:0 4.9vw}.main{overflow:hidden;min-height:100vh;padding-top:3.8em;padding-bottom:6em;will-change:contents}.main :target{background-color:#ff0}.main a:target{text-decoration:none}.main a:target:hover{text-decoration:underline}.main [id]:target:before{content:"";display:block;padding-top:3.5em;margin-top:-3.5em;pointer-events:none;background-color:#fff}.main h1[id]:target:before{padding-top:1.3em;margin-top:-1.3em}.error~.main{display:flex;justify-content:center;align-items:center;text-align:center;padding-bottom:8.5em}.footer{overflow:hidden;height:2.7em;margin-top:-2.7em;line-height:2.7;text-overflow:ellipsis;white-space:nowrap;word-wrap:normal;border-top:1px solid #eee}.footer a{display:inline-block;color:#777}.footer a+a{margin-left:.6em}.footer a:focus,.footer a:hover{color:#222;text-decoration:underline}template{display:none;box-suppress:discard}@media \0screen\,screen\9{.main,.noscroll,.wrapper{height:100%}.main,.noscroll.noscroll{overflow:visible}.nav{display:table;table-layout:fixed}.nav a{display:table-cell}.footer,[hidden]{display:none}}@media (min-width:0\0)and (min-resolution:.001dpcm){.nav,.nav a:first-of-type,.nav a:last-of-type{border-radius:0}.nav{display:table;table-layout:fixed}.nav a{display:table-cell}.handler,[hidden]{display:none}}@media (-ms-high-contrast:active),(-ms-high-contrast:none){.nav,html,textarea{-ms-overflow-style:-ms-autohiding-scrollbar}a{background-color:initial}select{-ms-user-select:none}:-ms-input-placeholder{opacity:.54}::-ms-clear{display:none}select:focus::-ms-value{color:initial;background-color:initial}[hidden]{display:none;box-suppress:discard}.nav:after,.nav:before{transition:width .35s cubic-bezier(.2,1,.4,1)}}@media (-webkit-min-device-pixel-ratio:0){.nav,html,textarea{-webkit-overflow-scrolling:touch}html{-webkit-font-smoothing:antialiased;-webkit-text-size-adjust:100%}[tabindex],a,button,input,select,textarea{-webkit-tap-highlight-color:transparent}a{-webkit-touch-callout:none}::-webkit-input-placeholder{opacity:.54;color:inherit;-webkit-user-select:none}.nav span{text-overflow:clip;-webkit-mask:linear-gradient(to left,rgba(0,0,0,0),#000 1.5em,#000);mask:linear-gradient(to left,rgba(0,0,0,0),#000 1.5em,#000)}.nav span[dir]{-webkit-mask:linear-gradient(to right,rgba(0,0,0,0),#000 1.5em,#000);mask:linear-gradient(to right,rgba(0,0,0,0),#000 1.5em,#000)}}@-moz-document url-prefix(){::-moz-selection{text-shadow:none;background-color:rgba(206,209,210,.4)}label:active{background-color:transparent}button,input,select,textarea{background-image:none;border-radius:0}button::-moz-focus-inner,input::-moz-focus-inner{border:0;padding:0}}@media (max-width:540px){html{background-color:#fff}html.noscroll{overflow:hidden}hr{margin-right:-1.9em;margin-left:-1.9em}button,input,label,textarea{width:100%}[type=checkbox],[type=color],[type=file],[type=image],[type=radio]{width:auto}.status{height:4px}.status:not(.expand)~.tab .header{-webkit-transition:none;transition:none}.tab{width:100%;padding-bottom:.2em;text-align:left;text-align:start;background-image:-webkit-linear-gradient(#fff,rgba(255,255,255,.9) 35%,rgba(255,255,255,.8) 50%,rgba(255,255,255,0));background-image:linear-gradient(#fff,rgba(255,255,255,.9) 35%,rgba(255,255,255,.8) 50%,rgba(255,255,255,0));pointer-events:none}.bar{width:auto;height:2.35em;padding:inherit;margin:inherit;background-color:transparent;border:inherit;box-shadow:inherit;all:unset;display:inline-block;position:relative;vertical-align:top;z-index:3}.bar,.nav{pointer-events:auto}.bar:focus,.bar:hover{background-color:rgba(206,209,210,.4)}.bar span,.bar span:after,.bar span:before{display:block;width:1.3em;height:.21em;background-color:#222;border-radius:.1em;-webkit-transition:width .1s,background-color 1ms,-webkit-transform .35s cubic-bezier(.2,1,.4,1);transition:width .1s,background-color 1ms,transform .35s cubic-bezier(.2,1,.4,1);will-change:transform}.bar span{position:relative;margin:1.07em .65em 1.07em .55em;transform:translateY(0);-webkit-transition-duration:.15s;transition-duration:.15s;pointer-events:none}.bar span:before{content:"";position:absolute;-webkit-transform:translateY(-.42em);-ms-transform:translateY(-.42em);transform:translateY(-.42em)}.bar span:after{content:"";position:absolute;width:.9em;-webkit-transform:translateY(.42em);-ms-transform:translateY(.42em);transform:translateY(.42em)}.expand~.tab .bar span{background-color:transparent}.expand~.tab .bar span:before{-webkit-transform:rotate(45deg);-webkit-transform:rotate3d(0,0,1,45deg);-ms-transform:rotate(45deg);transform:rotate3d(0,0,1,45deg)}.expand~.tab .bar span:after{width:1.3em;-webkit-transform:rotate(-45deg);-webkit-transform:rotate3d(0,0,1,-45deg);-ms-transform:rotate(-45deg);transform:rotate3d(0,0,1,-45deg)}.header{position:fixed;top:0;left:0;bottom:0;width:75%;padding:0;background-color:#f8f9fa;border:0;-webkit-transform:translateX(-100%);-ms-transform:translateX(-100%);transform:translateX(-100%);-webkit-transition:-webkit-transform .35s cubic-bezier(.2,1,.4,1);transition:transform .35s cubic-bezier(.2,1,.4,1);backface-visibility:hidden}.expand~.tab .header{-webkit-transform:none;-ms-transform:none;transform:none}.expand~.tab .header:after,.footer{display:none;box-suppress:discard}.focusin,.handler{display:block}.expand~.handler,.expand~.tab .handler{position:inherit;top:0;right:0;left:0;bottom:0;margin:0}.expand~.handler{position:fixed;z-index:99}.expand~.tab .header{box-shadow:0 0 4em rgba(0,0,0,.3)}.nav,.nav a:first-of-type,.nav a:last-of-type{border-radius:0}.nav{overflow:auto;display:block;height:100%;padding:2.3em 0;margin:0;background:0 0;border:0;box-shadow:none;box-sizing:border-box;backface-visibility:hidden}.nav:after,.nav:before{content:"";position:fixed;left:0;z-index:2;width:0;height:2.35em;pointer-events:none;will-change:transform}.expand~.tab .nav:after,.expand~.tab .nav:before{width:75%;width:75vw}.nav:before{top:0;background-image:-webkit-linear-gradient(#f8f9fa,rgba(248,249,250,.9) 50%,rgba(248,249,250,.8) 60%,rgba(248,249,250,0));background-image:linear-gradient(#f8f9fa,rgba(248,249,250,.9) 50%,rgba(248,249,250,.8) 60%,rgba(248,249,250,0))}.nav:after{bottom:0;right:auto;background-image:-webkit-linear-gradient(rgba(248,249,250,0),rgba(248,249,250,.8) 50%,rgba(248,249,250,.9) 60%,#f8f9fa);background-image:linear-gradient(rgba(248,249,250,0),rgba(248,249,250,.8) 50%,rgba(248,249,250,.9) 60%,#f8f9fa)}.nav.nav a{display:block;-webkit-box-flex:0;-webkit-flex:0;-ms-flex:0;flex:0;padding:0 1.3em;float:none;border-color:#eef0f0;border-color:rgba(125,138,138,.13);border-width:0;border-top-width:1px}.nav a:first-child{border-top-width:0}.main{width:auto;min-width:13.6em;padding:2em 1.9em 3.2em;border:0}.error~.main{padding-bottom:6.7em}.main [id]:before{padding-top:1.8em;margin-top:-1.8em}}@media (max-width:320px){html{font-size:58%}h1{font-size:1.5em}h2{font-size:1.2em}dt,h3{font-size:1.3em}}@media print{@page{margin:.5cm}*,h2,h3{color:#222;text-shadow:none;background:0 0}h2,h3,p{orphans:3;widows:3}h2,h3{page-break-after:avoid}a{color:#777;text-decoration:underline}img{page-break-inside:avoid}.main,html{background:0 0}.footer,.tab{display:none}.main{padding-top:1em;padding-bottom:1em;border-width:0}.button,button,select{box-shadow:none}}</style>') . "
<!--[if lt IE 9]><script src=//cdn.jsdelivr.net/html5shiv/3.7.2/html5shiv.min.js></script><![endif]-->
<body itemscope itemtype=http://schema.org/WebPage>
<div class=noscroll>
<div class=wrapper>$note";

if ($conn) {
    if ($stmt = $mysqli->prepare('SELECT url, title FROM `' . table . '` WHERE permit=1 ORDER BY `order` ASC')) {
        $stmt->execute();
        $stmt->bind_result($data_url, $data_metatitle);

        echo "\n<div id=status class=" . ($result ? 'status' : '"status error status-error"') . ' role=progressbar></div>
<div class=tab>
    <button class=bar id=bar tabindex=0 hidden><span></span></button>
    <span class=focusin id=focusin tabindex=0 hidden></span>
    <header class=header>
        <nav class=nav id=nav role=navigation>';

        function is_rtl($string) {
            // Check if there RTL characters (Arabic, Persian, Hebrew) https://gist.github.com/khal3d/4648574
            // RTL languages http://www.w3.org/International/questions/qa-scripts#which
            return (bool) preg_match('/[\x{0590}-\x{05ff}\x{0600}-\x{06ff}]/u', $string);
        }

        while ($stmt->fetch()) echo "\n            <a" . ($data_url === $urldb ? ' class=active' : null) .
            ' href="' . (strlen($data_url) ? "$path/$data_url" : (strlen($path) ? $path : '/')) . '"><span' .
            (is_rtl($data_metatitle) ? ' dir=auto' : '') . ">$data_metatitle</span></a>";

        echo "\n            <div class=handler id=focusout tabindex=0 hidden></div>
        </nav>
    </header>
</div>
<div class=handler id=reset hidden></div>";

        $stmt->free_result();
        $stmt->close();
    }
    $mysqli->close();
}

echo "\n<main class=main role=main itemprop=about itemscope itemtype=http://schema.org/Article>
<div id=output>";

if ($conn) echo "\n<h1 dir=auto>$title</h1>";

echo "\n$content
</div>
</main>
<footer class=footer itemprop=breadcrumb>
    <a href=https://github.com/laukstein/ajax-seo>GitHub project</a>
    <a href=https://github.com/laukstein/ajax-seo/archive/master.zip>Download</a>
    <a href=https://github.com/laukstein/ajax-seo/issues>Issues</a>
</footer>
</div>
</div>";

$js  = $debug ? '<script src="' . assets . "script.js#" . (strlen($path) ? $path : '/') . '"></script>' : '<script>!function(e,t){"use strict";"object"==typeof module&&"object"==typeof module.exports?module.exports=t():"function"==typeof define&&define.amd?define(["as"],t):e.as=t()}(this,function(){"use strict";var e,t,r,s,a=window,o=document,n=history,i=location,l={classList:"classList"in o.documentElement,click:"click"in o.documentElement,eventListener:!!o.addEventListener,touch:a.hasOwnProperty("ontouchstart"),valid:function(e){try{return e()}catch(t){return l.valid.error.e=t,l.valid.error}}},c={bar:o.getElementById("bar"),focusin:o.getElementById("focusin"),focusout:o.getElementById("focusout"),reset:o.getElementById("reset"),nav:o.getElementById("nav"),status:o.getElementById("status"),output:o.getElementById("output")},u={toggleClass:function(e,t){if(e)if(l.classList)e.classList.toggle(t);else{var r=e.className.split(" "),s=r.indexOf(t);s>=0?r.splice(s,1):r.push(t),e.className=r.join(" ")}},toFocus:function(){o.activeElement!==c.focusin&&u.toToggle(!0)},toToggle:function(e){u.toggleClass(o.body.parentElement,"noscroll"),u.toggleClass(c.status,"expand"),e&&c.focusin&&setTimeout(function(){c.focusin.focus()},0)}},d={version:4.2,analytics:"' . ga . '",origin:function(){var e=o.currentScript||function(){var e=o.getElementsByTagName("script");return e[e.length-1]}(),t=e.src.split("#")[1]||"/ajax-seo",r=new RegExp("("+t+")(.*)$");return o.URL.replace(r,"$1")}(),url:o.URL,title:o.title,activeElement:function(){var e,t=o.querySelectorAll?o.querySelectorAll("[href]"):[];for(e=0;e<t.length;e+=1)if(t[e].href.toUpperCase()===o.URL.toUpperCase())return t[e];return null}(),error:c.status&&c.status.classList.contains("status-error")};if(l.valid.error={e:null},d.analytics&&(a.ga=function(){ga.q=ga.q||[],ga.q.push(arguments)},ga("create",d.analytics,"' . ga_domain . '"),ga("send","pageview")),c.bar&&l.eventListener&&(c.bar.addEventListener("focus",function(){u.toToggle(!0)},!0),c.bar.addEventListener("click",u.toFocus,!0),c.nav&&c.nav.addEventListener("click",u.toToggle,!0),c.focusout&&c.focusout.addEventListener("focus",u.toToggle,!0),c.reset&&c.reset.addEventListener("click",u.toToggle,!0)),!n.pushState||!l.classList||!l.eventListener)throw new Error("Browser legacy: History API not supported");if(!c.output)throw new Error("Layout issue: missing elements");return s={filter:function(e){return e?e.replace(/#.*$/,"").toLowerCase():void 0},reset:function(){t&&clearTimeout(t),c.status&&c.status.classList.contains("status-start")&&c.status.classList.add("status-done")},click:function(e){var t;e&&(l.click?e.click():(t=o.createEvent("MouseEvents"),t.initEvent("click",!0,!0),e.dispatchEvent(t)))},nav:{nodeList:c.nav?Array.from?Array.from("a"):[].slice.call(c.nav.querySelectorAll("a")):null,activeElement:function(){var e;if(s.nav.nodeList)for(e=0;e<s.nav.nodeList.length;e+=1)if(s.filter(s.nav.nodeList[e].href)===d.url)return s.nav.nodeList[e];return null}},update:function(e,t,a){if(e){d.analytics&&ga("send","pageview",{page:decodeURI(i.pathname)}),t?s.reset():r.abort(),s.nav.nodeList&&(c.focus=c.nav.querySelector(".focus"),c.active=c.nav.querySelector(".active"),c.error=c.nav.querySelector(".error"),c.focus&&c.focus.classList.remove("focus"),c.active&&c.active.classList.remove("active"),c.error&&c.error.classList.remove("error")),o.activeElement&&"BODY"===o.activeElement.tagName&&o.activeElement.blur(),d.url=s.filter(o.URL),a=a||s.nav.activeElement(),a&&(a.focus(),a.classList.add(d.error?"error":"active"),d.error&&a.classList.add("x-error")),d.error?(c.status.classList.add("error"),c.status.classList.add("status-error")):(c.status.classList.remove("error"),c.status.classList.remove("status-error")),o.title=d.title=e.title;var l=o.scrollingElement||o.documentElement.scrollTop||o.body;l.scrollTop=0,c.output.innerHTML=e.content,i.hash&&(n.replaceState({error:d.error,title:d.title,content:c.output.innerHTML},d.title,d.url),i.replace(d.url+i.hash))}},retry:!1,popstate:function(e){if(!(i.hash&&s.filter(d.url)===s.filter(o.URL)||d.url&&d.url.indexOf("#")>-1)){var t,r=e.state;s.reset(),s.retry=!r,d.error=r&&r.error||!1,r||(d.url=s.filter(o.URL),t=s.nav.activeElement(),s.click(t)),o.activeElement.blur(),s.update(r,!1,t)}},loadstart:function(){c.status&&(c.status.classList.remove("status-done"),c.status.classList.remove("status-start"),t&&clearTimeout(t),t=setTimeout(function(){c.status.classList.add("status-start")},0))},callback:function(e){d.error=e.error||!1,n.replaceState(e,e.title,null),s.update(e,!0)},load:function(){var t=this.response;t=l.valid(function(){return JSON.parse(t)}),e&&clearTimeout(e),s.callback(t===l.valid.error?{error:!0,title:"Server error",content:"<h1>Whoops...</h1><p>Experienced server error. Try to <a class=x-error href="+d.url+">reload</a>"+(d.url===d.origin?"":" or head to <a href="+d.origin+">home page</a>")+"."}:t)},closest:function(e,t){if(!e||!t)return null;if(e.closest)return e.closest(t);for(var r=e.matches||e.webkitMatchesSelector||e.msMatchesSelector;e&&1===e.nodeType;){if(r.call(e,t))return e;e=e.parentNode}return null},resetStatus:function(e){c.status&&(!c.status.classList.contains("status-error")||e&&d.error||c.status.classList.remove("status-error"),c.status.classList.contains("status-done")&&(c.status.classList.remove("status-start"),c.status.classList.remove("status-done")))},listener:function(t){if(t){var a=t.target,i=new RegExp("^"+d.origin+"($|#|/.{1,}).*","i"),l={};a&&("A"!==a.tagName&&(a=s.closest(a,"a[href]")),a&&"A"===a.tagName&&a.hasAttribute("href")&&i.test(a.href)&&(d.url=a.href.toLowerCase().replace(/(\/)+(?=\1)/g,"").replace(/(^https?:(\/))/,"$1/").replace(/\/$/,""),l.attr=s.filter(d.url),l.address=s.filter(o.URL),l.attr===l.address&&d.url.indexOf("#")>-1||(t.preventDefault(),a.blur(),d.activeElement=a,s.retry||(d.error=d.activeElement.classList.contains("x-error")),d.title=d.activeElement.innerText.replace(/\n/,"")||d.activeElement.textContent,d.error&&l.address===o.URL?n.replaceState(null,d.title,d.url):d.error||d.url===o.URL||n.pushState(null,d.title,d.url),!d.error&&!s.retry&&l.attr===l.address||d.activeElement.classList.contains("focus")||(e&&clearTimeout(e),e=setTimeout(function(){o.title=d.title},3),s.resetStatus(),s.nav.nodeList&&(d.error&&(d.activeElement.classList.remove("x-error"),d.activeElement.classList.remove("error")),c.focus=c.nav.querySelector(".focus"),c.focus&&c.focus.classList.remove("focus")),d.activeElement.classList.add("focus"),r.abort(),r.open("GET",d.origin+"/api"+d.url.replace(new RegExp("^"+d.origin,"i"),"")),d.error&&r.setRequestHeader("If-Modified-Since","Sat, 1 Jan 2000 00:00:00 GMT"),r.send()))))}},init:function(){c.status&&c.status.addEventListener("transitionend",s.resetStatus,!0),setTimeout(function(){a.onpopstate=s.popstate},150),n.replaceState({error:d.error,title:d.title,content:c.output.innerHTML},d.title,d.url),r=new XMLHttpRequest,r.onloadstart=s.loadstart,r.onload=s.load,r.onabort=s.reset,o.addEventListener(l.touch?"touchstart":"click",s.listener,!0)}},d.analytics||delete d.analytics,s.init(),d});</script>';
$js .= ga ? "\n<script src=//www.google-analytics.com/analytics.js async defer></script>" : null;
echo "\n$js";
