<?php

include 'content/config.php';
include 'content/connect.php';
include 'content/cache.php'; cache::url();

$description = null;
$result      = false;

if ($conn) {
    $title     = $title_error     = 'Whoops...';
    $pagetitle = $pagetitle_error = 'Page not found';
    // Old Webkit breaks the layout without </p>
    $content   = $content_error   = '<p>This page hasn\'t been found. Try to <a class=x-error href=' . $path . $url . '>reload</a>' . ($ishome ? null : ' or head to <a href=' . $path . '>home page</a>') . '.</p>';

    $stmt = $mysqli->prepare('SELECT title, headline, description, content, created, modified FROM `' . table . '` WHERE url=? LIMIT 1');
    $stmt->bind_param('s', $urldb);
    $stmt->execute();
    $stmt->bind_result($title, $headline, $description, $content, $created, $modified);

    while ($stmt->fetch()) {
        $result    = true;
        // SEO page title improvement for the root page
        $pagetitle = $ishome ? (!empty($title) ? $title : title) : $title;
        $created   = strtotime($created);
        $modified  = strtotime($modified);
        $content = "<h1 dir=auto>$title</h1>" . (isset($headline) ? "\n<h2 itemprop=headline dir=auto>$headline</h2>" : null) .
            "\n<meta itemprop=datePublished content=" . date('Y-m-d\TH:i\Z', $created) . '><time class=pubdate itemprop=dateModified datetime=' . date('Y-m-d\TH:i\Z', $modified) . '>' .
            ($created >= $modified ? 'Posted' : 'Updated') . date(' M j, Y', max($created, $modified)) . "</time>\n" . string($content);
    }

    $stmt->free_result();
    $stmt->close();

    if (!$result) {
        // URL does not exist
        http_response_code(404);

        $title     = $title_error;
        $content   = "<h1 dir=auto>$title</h1>\n" . $content_error;
        $pagetitle = $pagetitle_error;
    }

    if (isset($_GET['api'])) {
        // API
        include 'content/api.php';
        exit;
    }
}

if (empty($_GET['api'])) {
    // Avoid XSS attacks with CSP https://w3c.github.io/webappsec-csp/
    // Omit Referrer https://w3c.github.io/webappsec-referrer-policy/
    // Firefox OS app suggestion https://developer.mozilla.org/en-US/Apps/CSP
    header('Content-Security-Policy: script-src' . ($debug ? null : " 'unsafe-inline'") . ($cdn_host ? " $cdn_host" : " 'self'") . (ga ? ' www.google-analytics.com' : null) . '; referrer no-referrer');
}

// Max 160 character title http://blogs.msdn.com/b/ie/archive/2012/05/14/sharing-links-from-ie10-on-windows-8.aspx
$metadata  = "<title>$pagetitle</title>";

// Open Graph protocol http://ogp.me
$metadata .= "\n<meta property=og:title content=\"$pagetitle\">";
// Max 253 character description http://blogs.msdn.com/b/ie/archive/2012/05/14/sharing-links-from-ie10-on-windows-8.aspx
if ($description) $metadata .= "\n<meta property=og:description name=description content=\"$description\">";
// Twitter Cards https://dev.twitter.com/cards/overview, https://cards-dev.twitter.com/validator
$metadata .= "\n<meta property=twitter:card content=summary>";

// Optimize smart device viewport (initial-scale=1 to enable zoom-in, maximum-scale=1 to disable zoom) https://developer.chrome.com/multidevice/webview/pixelperfect#viewport http://io13-high-dpi.appspot.com/#11
// Avoid tap 350ms delay https://webkit.org/blog/5610/more-responsive-tapping-on-ios/
$metadata .= "\n<meta name=viewport content=\"width=device-width, initial-scale=1\">";

// Early handshake DNS https://w3c.github.io/resource-hints/#dns-prefetch
if ($cdn_host) $metadata .= "\n<link rel=dns-prefetch href=$cdn_scheme$cdn_host/>";
// // Early handshake DNS, TCP and TLS https://w3c.github.io/resource-hints/#preconnect
// if ($cdn_host) $metadata .= "\n<link rel=preconnect href=$cdn_scheme$cdn_host/>";

// Resource hints http://w3c.github.io/resource-hints/
// Fetch and cache API in background when everything is downloaded https://html.spec.whatwg.org/#link-type-prefetch
if ($conn && $result) $metadata .= "\n<link rel=\"prefetch prerender\" href=$path/api" . ($url === '/' ? '' : $url) . '>';

// // Manifest for a web application https://w3c.github.io/manifest/
// <link rel=manifest href=manifest.json>

// // SVG favicon https://github.com/whatwg/html/issues/110
// $metadata .= "\n<link rel=mask-icon href=$path/icon.svg>";
// Favicon 16x16 4-bit 16 color favicon.ico in website root http://zoompf.com/2012/04/instagram-and-optimizing-favicons
// 16px used on all browsers https://github.com/audreyr/favicon-cheat-sheet, http://realfavicongenerator.net/faq#.Vpasouh96Hs
if (!empty($path)) $metadata .= "\n<link rel=\"shortcut icon\" href=$path/favicon.png>";

// Website copyright license
$metadata .= "\n<link rel=license href=//creativecommons.org/licenses/by/4.0/>";

echo "<!doctype html>
<html lang=en>
<head prefix=\"og: http://ogp.me/ns#\">
<meta charset=utf-8>
$metadata
" . ($debug ? '<link rel=stylesheet href=' . assets . "style.css>" : '<style>@viewport{width:device-width;zoom:1}body,html{height:100%}button,html,input,select,textarea{color:#222;font-family:Cambria,Georgia,serif}.button,button,html,label{-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}html{scroll-behavior:smooth;font-size:62.45%;line-height:1.5;text-rendering:optimizeSpeed;background-color:#f8f9fa;touch-action:manipulation;cursor:default}blockquote,body,dd,dl,figure{margin:0}body{font-size:1.9em;overflow-wrap:break-word;word-wrap:break-word;-ms-hyphenate-limit-chars:6 3 2;hyphenate-limit-chars:6 3 2;-webkit-hyphens:auto;-ms-hyphens:auto;hyphens:auto}main{display:block;-webkit-user-select:text;-moz-user-select:text;-ms-user-select:text;user-select:text}dt,h1,h2,h3{line-height:1.07;font-weight:400;font-family:Times New Roman,serif}h1,h2{margin-top:.2em;margin-bottom:.2em}h1:first-line,h2:first-line{font-size:150%}h1{font-size:1.9em}h2{color:#777;font-size:1.5em}dt,h3{margin-top:.3em;margin-bottom:.3em;font-size:1.35em}h4{margin-top:.4em;margin-bottom:.4em}p{margin-top:.8em;margin-bottom:.8em}p+p{margin-top:1.6em}small{font-size:70%}blockquote{padding-left:1.2em;font-style:italic;border-left:.3em solid #eef0f0;border-color:rgba(125,138,138,.13)}blockquote cite{color:#777}blockquote cite:before{content:"\2014 \00A0";color:#777}cite{display:block}br{word-spacing:0}hr{position:relative;margin:1em -3em;margin-right:-4.9vw;margin-left:-4.9vw;clear:both;border:0;border-top:1px solid #eef0f0;border-color:rgba(125,138,138,.13)}code,pre{padding:.05em .3em;font-style:italic;font-family:Consolas,Liberation Mono,Courier,monospace;white-space:pre-wrap;background-color:rgba(206,209,210,.4);-moz-tab-size:4;tab-size:4}a,button,input,select,textarea{outline:0;pointer-events:auto}button,input,select{vertical-align:middle}a img,abbr,iframe{border:0}a,img{-webkit-user-drag:none;user-drag:none}a[href]{color:#0f4cd5;text-decoration:none;-webkit-box-decoration-break:clone;box-decoration-break:clone}a[href]:focus,a[href]:hover{color:#0e439b;text-decoration:underline}img{width:auto;max-width:100%;height:auto;vertical-align:top;-ms-interpolation-mode:nearest-neighbor;image-rendering:-webkit-optimize-contrast;image-rendering:-moz-crisp-edges;image-rendering:crisp-edges;image-rendering:pixelated}abbr{border-bottom:1px dotted #ccc}legend{display:table}label{display:inline-block;padding-bottom:.2em;padding-top:.2em;line-height:1.2;vertical-align:middle}::selection{text-shadow:none;background-color:rgba(206,209,210,.4)}.button,button,input,select,textarea{width:20em;max-width:100%;padding:.6em .7em;margin:.15em 0;font-size:1em;line-height:1.25;background-color:#fff;border:1px solid #b1b2b2;box-sizing:border-box}textarea{overflow-x:hidden;overflow-y:scroll;min-height:4.95em;max-height:13em;word-wrap:break-word;resize:none}input:focus,input:hover,textarea:focus,textarea:hover{border-color:#9f9f9f;box-shadow:inset 0 1px 3px rgba(0,0,0,.15)}[type=checkbox],[type=color],[type=file],[type=image],[type=radio]{width:auto;padding:0;border:0;box-sizing:content-box}.button,button,select{overflow:hidden;display:inline-block;white-space:nowrap;word-wrap:normal;text-align:center;text-overflow:ellipsis;background-color:#f4f4f4;border:1px solid #c0c0c1;outline:0;box-shadow:inset 0 0 0 1px #f5f5f5,1px 1px 2px rgba(0,0,0,.1);cursor:pointer}[disabled]{color:#bababa;text-decoration:none;text-shadow:0 1px 1px #fff;border-color:#e3e6e7;box-shadow:none;transition:none;pointer-events:none;cursor:default}button[disabled],select[disabled]{background-color:#f4f4f4;background-color:rgba(179,179,179,.08);border-color:transparent}.button{width:auto}a[href].button{color:inherit;text-decoration:none}.button:focus,.button:hover,button:focus,button:hover,select:focus,select:hover{background-color:#f8f9fa;border-color:#9f9f9f}.button:active,button:active,select:active{background-color:rgba(206,209,210,.4);box-shadow:none}option{min-width:100%;max-width:0}[tabindex]{outline:0}html{width:100%;width:100vw;max-width:100%}div.noscroll{overflow:hidden}.wrapper{width:100%;width:100vw;min-height:100vh}.status{opacity:0;position:fixed;z-index:999;left:0;width:0;height:3px;background-color:#60d778;box-shadow:0 0 0 1px rgba(255,255,255,.8);-webkit-transform:translateZ(0);-ms-transform:translateZ(0);transform:translateZ(0);-webkit-backface-visibility:hidden;backface-visibility:hidden;-webkit-perspective:1000;perspective:1000;will-change:transform;pointer-events:none}.status-start:before{content:"";display:block;width:10%;height:100%;float:right;background-image:linear-gradient(to right,rgba(96,215,120,0),#62fb82);box-shadow:2px -2px 5px 1px #62fb82}.status-start{opacity:1;width:70%;width:70vw;transition:opacity .2s,width 5s cubic-bezier(.2,1,.4,1)}.status-done{opacity:0;width:100%;width:100vw;transition-duration:.2s}.status-error{opacity:1;width:100%;width:100vw;background-color:#f79c87;transition:background .2s cubic-bezier(.2,1,.4,1)}.status-error:before{display:none}.tab{position:fixed;z-index:100;top:0;right:0;left:0;width:100%;width:100vw;line-height:2.2;font-family:Segoe UI Historic,Segoe UI Symbol,Open Sans,sans-serif;text-align:center;white-space:nowrap;word-wrap:normal;backface-visibility:hidden}.footer,.header,.main{width:95%;max-width:900px;margin:auto;box-sizing:border-box}.header,.main{background-color:#fff;border:0 solid #d9e0e2;border-width:0 1px}.header{position:relative;padding:.08em .4em}.header:after{content:"";position:absolute;left:0;right:0;height:1.5em;background-image:linear-gradient(#fff,rgba(255,255,255,.9) 35%,rgba(255,255,255,.8) 50%,rgba(255,255,255,0));pointer-events:none}.nav{overflow:hidden;display:inline-block;display:-ms-flexbox;display:flex;width:100%;margin:.1em auto;font-size:1.05em;background-color:#f4f4f4;background-image:linear-gradient(#f8f8f8,rgba(248,249,250,0));border:1px solid #d9e0e2;border-bottom-color:#ccc;border-radius:.2em;box-shadow:0 1px 1px rgba(0,0,0,.1),inset 0 0 0 1px rgba(255,255,255,.5)}.footer a,.nav.nav a{color:inherit;text-decoration:none}.nav a{display:block;position:relative;-ms-flex:1;flex:1;flex-basis:auto;min-width:1px;padding:0 .43em;border:0 solid #d9e0e2;border-left-width:1px}.nav a:first-child{max-width:3.2em;padding:0;border-left:0}.nav a:first-of-type{border-radius:.2em 0 0 .2em}.nav a:last-of-type{border-radius:0 .2em .2em 0}.nav a:focus,.nav a:hover{background-color:#fff}.nav a.active,.nav a.error,.nav a.focus{margin:-1px auto;line-height:2.25;border-color:transparent}.nav .active+a,.nav .error+a,.nav .focus+a{border-color:transparent}.nav a.focus{background-color:rgba(206,209,210,.4)}.nav a.active{z-index:1;color:#fff;background-color:#006cff}.nav a.error{background-color:#f7dad4}.nav a>span{overflow:hidden;display:block;position:relative;top:-.06em;max-height:2.3em;text-overflow:ellipsis}.bar{width:auto;height:2.35em;padding:inherit;margin:inherit;background-color:transparent;border:inherit;box-shadow:inherit;all:unset;display:none;box-suppress:discard;position:relative;z-index:3;vertical-align:top}.nav .bar{display:inline-block}[data-version]:before{content:attr(data-version);position:absolute;bottom:.2em;right:1.5em;font-weight:700;font-size:.55em;pointer-events:none}.bar span,.bar span:after,.bar span:before{display:block;width:1.3em;height:.21em;background-color:#222;border-radius:.5em}.active .bar span,.active .bar span:after,.active .bar span:before{background-color:#fff}.bar span{position:relative;margin:1.07em .65em 1.07em .55em;transform:translateY(0);pointer-events:none}.bar span:before{content:"";position:absolute;right:0;width:.9em;-webkit-transform:translateY(-.42em);-ms-transform:translateY(-.42em);transform:translateY(-.42em)}.bar span:after{content:"";position:absolute;width:.5em;-webkit-transform:translateY(.42em);-ms-transform:translateY(.42em);transform:translateY(.42em)}.footer,.main{position:relative;padding:0 3em;padding:0 4.9vw}.main{overflow:hidden;min-height:100vh;padding-top:3.8em;padding-bottom:6em;will-change:contents}.main :target{background-color:#ff0}.main a:target{text-decoration:none}.main a:target:hover{text-decoration:underline}.main [id]:target:before{content:"";display:block;padding-top:3.5em;margin-top:-3.5em;pointer-events:none;background-color:#fff}.main h1[id]:target:before{padding-top:1.3em;margin-top:-1.3em}.error~.main{display:flex;justify-content:center;align-items:center;text-align:center;padding-bottom:8.5em}.footer{overflow:hidden;height:2.7em;margin-top:-2.7em;line-height:2.7;text-overflow:ellipsis;white-space:nowrap;word-wrap:normal;border-top:1px solid #eee}.footer a{display:inline-block;color:#777}.footer a+a{margin-left:.6em}.footer a:focus,.footer a:hover{color:#222;text-decoration:underline}.nav [data-placeholder],[hidden],option[value=""]{display:none;box-suppress:discard}@media \0screen\,screen\9{.noscroll,.wrapper{height:100%}.main,.noscroll.noscroll{overflow:visible}.main{min-height:100%}.nav{display:table;table-layout:fixed}.nav a{display:table-cell}}@media (min-width:0\0)and (min-resolution:.001dpcm){.nav,.nav a:first-of-type,.nav a:last-of-type{border-radius:0}.nav{display:table;table-layout:fixed}.nav a{display:table-cell}.nav .bar,[data-version]:before{display:none}.nav [data-placeholder]{display:inline-block}}@media (-ms-high-contrast:active),(-ms-high-contrast:none){.nav,html,textarea{-ms-overflow-style:-ms-autohiding-scrollbar}a{background-color:transparent}select{-ms-user-select:none}:-ms-input-placeholder{color:#999}::-ms-clear{display:none;box-suppress:discard}select:focus::-ms-value{color:initial;background-color:initial}.nav:after,.nav:before{transition:width .35s cubic-bezier(.2,1,.4,1)}}@supports (-ms-accelerator:true){::-webkit-input-placeholder{opacity:.54}::-ms-clear{display:none;box-suppress:discard}select:focus::-ms-value{color:initial;background-color:initial}}@media (-webkit-min-device-pixel-ratio:0){@supports (not (-ms-accelerator:true)){.nav,html,textarea{-webkit-overflow-scrolling:touch}html{-webkit-font-smoothing:antialiased;-webkit-text-size-adjust:100%}[tabindex],a,button,input,select,textarea{-webkit-tap-highlight-color:transparent}a{-webkit-touch-callout:none}::-webkit-input-placeholder{text-overflow:ellipsis!important;opacity:.54;color:inherit}.nav a>span{text-overflow:clip;-webkit-mask:linear-gradient(to left,rgba(0,0,0,0),#000 1.5em,#000);mask:linear-gradient(to left,rgba(0,0,0,0),#000 1.5em,#000)}.nav a>span[dir]{-webkit-mask:linear-gradient(to right,rgba(0,0,0,0),#000 1.5em,#000);mask:linear-gradient(to right,rgba(0,0,0,0),#000 1.5em,#000)}}}@-moz-document url-prefix(){::-moz-selection{text-shadow:none;background-color:rgba(206,209,210,.4)}label:active{background-color:transparent}button,input,select,textarea{background-image:none;border-radius:0}button::-moz-focus-inner,input::-moz-focus-inner{border:0;padding:0}}@media (max-width:540px){html{background-color:#fff}html.noscroll{overflow:hidden}hr{margin-right:-1.9em;margin-left:-1.9em}button,input,textarea{width:100%}.expand~.tab .header:after,.footer,.nav .bar,[data-version]:before{display:none;box-suppress:discard}[type=checkbox],[type=color],[type=file],[type=image],[type=radio]{width:auto}.status{height:4px}.status:not(.expand)~.tab .header{transition:none}.tab{width:100%;padding-bottom:.2em;text-align:left;text-align:start;background-image:linear-gradient(#fff,rgba(255,255,255,.9) 35%,rgba(255,255,255,.8) 50%,rgba(255,255,255,0));pointer-events:none}.tab>.bar{display:inline-block}.bar,.nav{pointer-events:auto}.bar{cursor:pointer}.bar span,.bar span:after,.bar span:before{transition:width .1s,background-color 1ms,transform .35s cubic-bezier(.2,1,.4,1);will-change:transform}.bar span{transition-duration:.15s}.bar:focus,.bar:hover{background-color:rgba(206,209,210,.4)}.expand~.tab .bar span{background-color:transparent}.expand~.tab .bar span:before{width:1.3em;-webkit-transform:rotate(45deg);-ms-transform:rotate(45deg);transform:rotate3d(0,0,1,45deg)}.expand~.tab .bar span:after{width:1.3em;-webkit-transform:rotate(-45deg);-ms-transform:rotate(-45deg);transform:rotate3d(0,0,1,-45deg)}.expand~.tab .nav:after,.expand~.tab .nav:before,.header{width:75%;width:75vw;min-width:8em}.header{position:fixed;top:0;left:0;bottom:0;padding:0;background-color:#f8f9fa;border:0;-webkit-transform:translateX(-100%);-ms-transform:translateX(-100%);transform:translateX(-100%);backface-visibility:hidden}.expand~.tab .header{box-shadow:0 0 4em rgba(0,0,0,.3);box-shadow:0 0 4em rgba(0,0,0,.1),0 0 44vw rgba(0,0,0,.3);-webkit-transform:none;-ms-transform:none;transform:none;transition:transform .35s cubic-bezier(.2,1,.4,1)}.focusin,.handler,.nav [data-placeholder]{display:block}.expand~.handler,.expand~.tab .handler{position:initial;top:0;right:0;left:0;bottom:0;margin:0}.expand~.handler{position:fixed;z-index:99}.expand~.main{-webkit-filter:grayscale(100%);filter:grayscale(100%);transition:filter .35s cubic-bezier(.2,1,.4,1)}.nav,.nav a:first-of-type,.nav a:last-of-type{border-radius:0}.nav{overflow:auto;display:block;height:100%;margin:0;background:0 0;border:0;box-shadow:none;box-sizing:border-box;backface-visibility:hidden}.nav a:first-child{max-width:100%;margin-top:2.3em;border-top:0}.nav a:last-of-type{margin-bottom:2.3em}.nav:after,.nav:before{content:"";position:fixed;left:0;z-index:2;width:0;height:2.35em;pointer-events:none;will-change:transform}.nav:before{top:0;background-image:linear-gradient(#f8f9fa,rgba(248,249,250,.9) 50%,rgba(248,249,250,.8) 60%,rgba(248,249,250,0))}.nav:after{bottom:0;right:auto;background-image:linear-gradient(rgba(248,249,250,0),rgba(248,249,250,.8) 50%,rgba(248,249,250,.9) 60%,#f8f9fa)}.nav.nav a{display:block;-ms-flex:0;flex:0;padding:0 1.3em;float:none;border-color:#eef0f0;border-color:rgba(125,138,138,.13);border-width:0;border-top-width:1px}.main{width:auto;min-width:13.6em;padding:2em 1.9em 3.2em;border:0}.error~.main{padding-bottom:6.7em}.main [id]:before{padding-top:1.8em;margin-top:-1.8em}}@media (max-width:320px){html{font-size:58%}h1{font-size:1.5em}h2{font-size:1.2em}dt,h3{font-size:1.3em}}@media print{@page{margin:.5cm}*,h2,h3{color:#222;text-shadow:none;background:0 0}h2,h3,p{orphans:3;widows:3}h2,h3{page-break-after:avoid}a{color:#777;text-decoration:underline}img{page-break-inside:avoid}.main,html{background:0 0}.footer,.tab{display:none;box-suppress:discard}.main{padding-top:1em;padding-bottom:1em;border:0}.button,button,select{box-shadow:none}}</style>') . "
<!--[if lt IE 9]><script src=//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js></script><![endif]-->
<body itemscope itemtype=http://schema.org/WebPage>
<div class=noscroll>
<div class=wrapper>$note";

if ($conn) {
    // Head nav
    if ($stmt = $mysqli->prepare('SELECT url, title FROM `' . table . '` WHERE permit=1 ORDER BY `order` ASC')) {
        $stmt->execute();
        $stmt->bind_result($data_url, $data_metatitle);

        echo "\n<div id=status class=" . ($result ? 'status' : '"status error status-error"') . ' role=progressbar></div>
<div class=tab>
    <button class=bar id=bar tabindex=0 hidden><span></span></button>
    <span class=focusin id=focusin tabindex=0 hidden></span>
    <header class=header>
        <nav class=nav id=nav>';

        function is_rtl($string) {
            // Check if there RTL characters (Arabic, Persian, Hebrew) https://gist.github.com/khal3d/4648574
            // RTL languages http://www.w3.org/International/questions/qa-scripts#which
            return (bool) preg_match('/[\x{0590}-\x{05ff}\x{0600}-\x{06ff}]/u', $string);
        }

        while ($stmt->fetch()) {
            $home = !strlen($data_url);

            echo "\n            <a" . ($data_url === $urldb ? ' class=active' : null) .
                ' href="' . ($home ? (strlen($path) ? $path : '/') : "$path/$data_url") . '"' .
                ($home ? ' data-version=4' : null) . '>' . ($home ? '<span><span class=bar><span></span></span>' : null) .
                '<span' . (is_rtl($data_metatitle) ? ' dir=auto' : '') . ($home ? ' data-placeholder' : null) .
                ">$data_metatitle</span>" . ($home ? '</span>' : null) . '</a>';
        }

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

echo "\n<main class=main itemprop=about itemscope itemtype=http://schema.org/Article>
<div id=output>
$content
</div>
</main>
<footer class=footer itemprop=breadcrumb>
    <a href=https://github.com/laukstein/ajax-seo>GitHub project</a>
    <a href=https://github.com/laukstein/ajax-seo/archive/master.zip>Download</a>
    <a href=https://github.com/laukstein/ajax-seo/issues>Issues</a>
</footer>
</div>
</div>\n" . ($debug ? '<script src="' . assets . "script.js#" . (strlen($path) ? $path : '/') . '"></script>' : '<script>!function(e){"use strict";"function"==typeof define&&define.amd?define(["as"],e):"object"==typeof module&&module.exports?module.exports=e():window.as=e()}(function(){"use strict";var e,t,r,a,s=window,n=document,o=navigator,i=history,l=location,c={classList:"classList"in n.documentElement,click:"click"in n.documentElement,dnt:"1"===o.doNotTrack||"1"===s.doNotTrack||"1"===o.msDoNotTrack,eventListener:!!n.addEventListener,pointer:o.pointerEnabled?"pointerdown":o.maxTouchPoints>0||s.matchMedia&&s.matchMedia("(pointer: coarse)").matches||"ontouchstart"in s?"touchstart":"click",valid:function(e){try{return e()}catch(t){return c.valid.error.e=t,c.valid.error}}},u={bar:n.getElementById("bar"),focusin:n.getElementById("focusin"),focusout:n.getElementById("focusout"),reset:n.getElementById("reset"),nav:n.getElementById("nav"),status:n.getElementById("status"),output:n.getElementById("output")},d={toggleClass:function(e,t){if(e)if(c.classList)e.classList.toggle(t);else{var r=e.className.split(" "),a=r.indexOf(t);a>=0?r.splice(a,1):r.push(t),e.className=r.join(" ")}},toToggle:function(e,t){d.toggleClass(n.body.parentElement,"noscroll"),d.toggleClass(u.status,"expand"),!t&&e&&u.focusin&&setTimeout(function(){u.focusin.focus()},0)},toFocus:function(e){var t="touchstart"===e.type;(n.activeElement!==u.focusin||t)&&d.toToggle(!0,t)}},v={version:5,analytics:"' . ga . '",dnt:c.dnt,origin:function(){var e=n.currentScript||function(){var e=n.getElementsByTagName("script");return e[e.length-1]}(),t=e.src.split("#")[1]||"/ajax-seo";return"/"===t?location.origin:n.URL.replace(new RegExp("("+t+")(.*)$"),"$1")}(),url:n.URL,title:n.title,activeElement:function(){var e,t=n.querySelectorAll?n.querySelectorAll("[href]"):[];for(e=0;e<t.length;e+=1)if(t[e].href.toUpperCase()===n.URL.toUpperCase())return t[e];return null}(),error:u.status&&(c.classList?u.status.classList.contains("status-error"):new RegExp("(^|\\s)status-error(\\s|$)").test(u.status.className))};if(c.valid.error={e:null},c.eventListener&&(u.bar&&(c.touch="touchstart"===c.pointer,d.toToggle["true"]=function(){d.toToggle(!0)},d.toToggle["false"]=function(){d.toToggle(!1)},d.toFocus.run=function(e){d.toFocus(e)},d.run=function(){n.documentElement.offsetWidth<=540?(c.touch||(u.bar.addEventListener("focus",d.toToggle["true"],!0),u.focusout&&u.focusout.addEventListener("focus",d.toToggle["false"],!0)),u.bar.addEventListener(c.pointer,d.toFocus.run,!0),u.nav&&u.nav.addEventListener(c.pointer,d.toToggle["true"],!0),u.reset&&u.reset.addEventListener(c.pointer,d.toToggle["true"],!0)):(c.touch||(u.bar.removeEventListener("focus",d.toToggle["true"],!0),u.focusout&&u.focusout.removeEventListener("focus",d.toToggle["false"],!0)),u.bar.removeEventListener(c.pointer,d.toFocus.run,!0),u.nav&&u.nav.removeEventListener(c.pointer,d.toToggle["true"],!0),u.reset&&u.reset.removeEventListener(c.pointer,d.toToggle["true"],!0))},d.run(),s.addEventListener("resize",function(){d.timeoutScale&&clearTimeout(d.timeoutScale),d.timeoutScale=setTimeout(d.run,100)},!0)),!c.dnt&&v.analytics&&(t={reset:function(){u.analytics.removeEventListener("load",t.load),u.analytics.removeEventListener("error",t.reset),u.analytics.removeEventListener("readystatechange",t.readystatechange),u.analytics.removeAttribute("id")},load:function(){ga("create",v.analytics,"' . ga_domain . '",{storage:"none",clientId:localStorage.gaClientId}),localStorage.gaClientId||ga(function(e){localStorage.gaClientId=e.get("clientId")}),ga("send","pageview"),t.reset()},readystatechange:function(){("complete"===u.analytics.readyState||"loaded"===u.analytics.readyState)&&("function"==typeof ga?t.load():t.reset())},timestamp:+new Date+""},u.analytics=n.createElement("script"),u.analytics.src="//www.google-analytics.com/analytics.js",u.analytics.id=t.timestamp,n.body.appendChild(u.analytics),u.analytics=n.getElementById(t.timestamp),u.analytics&&(u.analytics.addEventListener("load",t.load),u.analytics.addEventListener("error",t.reset),u.analytics.addEventListener("readystatechange",t.readystatechange)))),!i.pushState||!c.classList||!c.eventListener)throw new Error("Browser legacy: History API not supported");if(!u.output)throw new Error("Layout issue: missing elements");return a={filter:function(e,t){return e?(e=e.replace(/#.*$/,""),t?e:e.toLowerCase()):void 0},reset:function(){e&&clearTimeout(e),u.status&&u.status.classList.contains("status-start")&&u.status.classList.add("status-done")},click:function(e){if(e)if(c.click)e.click();else{var t=n.createEvent("MouseEvents");t.initEvent("click",!0,!0),e.dispatchEvent(t)}},nav:{nodeList:u.nav?Array.from?Array.from(u.nav.querySelectorAll("a")):[].slice.call(u.nav.querySelectorAll("a")):null,activeElement:function(){if(a.nav.nodeList){var e;for(e=0;e<a.nav.nodeList.length;e+=1)if(a.filter(a.nav.nodeList[e].href)===v.url)return a.nav.nodeList[e]}return null}},update:function(e,t,s){if(e){!c.dnt&&v.analytics&&"function"==typeof ga&&ga("send","pageview",{page:v.url}),t?a.reset():r.abort(),a.nav.nodeList&&(u.focus=u.nav.querySelector(".focus"),u.active=u.nav.querySelector(".active"),u.error=u.nav.querySelector(".error"),u.focus&&u.focus.classList.remove("focus"),u.active&&u.active.classList.remove("active"),u.error&&u.error.classList.remove("error")),n.activeElement&&"BODY"===n.activeElement.tagName&&n.activeElement.blur(),v.url=a.filter(n.URL),v.activeElement=s||a.nav.activeElement(),v.activeElement&&(v.activeElement.focus(),v.activeElement.classList.add(v.error?"error":"active"),v.error&&v.activeElement.classList.add("x-error")),v.error?(u.status.classList.add("error"),u.status.classList.add("status-error")):(u.status.classList.remove("error"),u.status.classList.remove("status-error")),n.title=v.title=e.title;var o=n.scrollingElement||n.documentElement.scrollTop||n.body;o.scrollTop=0,u.output.innerHTML=e.content,l.hash&&l.replace(v.url+l.hash)}},retry:!1,popstate:function(e){if(!(l.hash&&a.filter(v.url)===a.filter(n.URL)||v.url&&v.url.indexOf("#")>-1)){var t,r=e.state;a.reset(),a.retry=!r,v.error=r&&r.error||!1,r||(v.url=a.filter(n.URL),t=a.nav.activeElement(),a.click(t)),a.update(r,!1,t)}},loadstart:function(){u.status&&(u.status.classList.remove("status-done"),u.status.classList.remove("status-start"),e&&clearTimeout(e),e=setTimeout(function(){u.status.classList.add("status-start")},0))},callback:function(e){v.error=e.error||!1,i.replaceState(e,e.title,null),a.update(e,!0)},load:function(){var e=this.response;e=c.valid(function(){return JSON.parse(e)}),a.callback(e===c.valid.error?{error:!0,title:"Server error",content:"<h1>Whoops...</h1><p>Experienced server error. Try to <a class=x-error href="+v.url+">reload</a>"+(v.url===v.origin?"":" or head to <a href="+v.origin+">home page</a>")+"."}:e)},closest:function(e,t){if(!e||!t)return null;if(e.closest)return e.closest(t);for(var r=e.matches||e.webkitMatchesSelector||e.msMatchesSelector;e&&1===e.nodeType;){if(r.call(e,t))return e;e=e.parentNode}return null},resetStatus:function(e){u.status&&(!u.status.classList.contains("status-error")||e&&v.error||u.status.classList.remove("status-error"),u.status.classList.contains("status-done")&&(u.status.classList.remove("status-start"),u.status.classList.remove("status-done")))},listener:function(e){if(e){var t=e.target,o=new RegExp("^"+v.origin+"($|#|/.{1,}).*","i"),l={};if(!t)return;if("A"!==t.tagName&&(t=a.closest(t,"a[href]")),!t||"A"!==t.tagName||!t.hasAttribute("href")||!o.test(t.href.replace(/\/$/,"")))return;if(v.url=t.href.toLowerCase().replace(/(\/)+(?=\1)/g,"").replace(/(^https?:(\/))/,"$1/").replace(/\/$/,""),l.attr=a.filter(v.url,!0),l.address=a.filter(n.URL),l.attr===l.address&&v.url.indexOf("#")>-1)return;if(e.preventDefault(),t!==n.activeElement&&t.focus(),t.blur(),v.activeElement=t,v.activeNav=t.parentNode===u.nav,!a.retry&&v.activeNav&&(v.error=v.activeElement.classList.contains("x-error")),v.title=v.activeElement.innerText?v.activeElement.innerText.replace(/\n/,""):v.activeElement.textContent,v.error&&l.address===n.URL?i.replaceState(null,v.title,v.url):v.error||v.url===n.URL||i.pushState(null,v.title,v.url),!v.error&&!a.retry&&l.attr===l.address||v.activeNav&&v.activeElement.classList.contains("focus"))return;n.title=v.title,a.resetStatus(),a.nav.nodeList&&(v.error&&v.activeNav&&(v.activeElement.classList.remove("x-error"),v.activeElement.classList.remove("error")),u.focus=u.nav.querySelector(".focus"),u.focus&&u.focus.classList.remove("focus")),v.activeNav&&v.activeElement.classList.add("focus"),s.stop?s.stop():n.execCommand&&n.execCommand("Stop",!1),r.abort(),r.open("GET",v.origin+"/api"+l.attr.replace(new RegExp("^"+v.origin,"i"),"")),v.error&&r.setRequestHeader("If-Modified-Since","Sat, 1 Jan 2000 00:00:00 GMT"),r.send()}},init:function(){u.status&&u.status.addEventListener("transitionend",a.resetStatus,!0),setTimeout(function(){s.onpopstate=a.popstate},150),i.replaceState({error:v.error,title:v.title,content:u.output.innerHTML},v.title,v.url),r=new XMLHttpRequest,r.addEventListener("loadstart",a.loadstart,!0),r.addEventListener("load",a.load,!0),r.addEventListener("abort",a.reset,!0),n.documentElement.addEventListener(c.pointer,a.listener,!0)}},v.analytics||delete v.analytics,a.init(),v});</script>');
