<?php

if (empty($_GET['api'])) $toMinify = true;

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
        $modified  = max($created, $modified);
        $content = "<h1 dir=auto>$title</h1>" . (isset($headline) ? "\n<h2 itemprop=headline dir=auto>$headline</h2>" : null) .
            "\n<meta itemprop=datePublished content=" . date('Y-m-d\TH:i\Z', $created) . '><time class=pubdate itemprop=dateModified datetime=' . date('Y-m-d\TH:i\Z', $modified) . '>' .
            ($created >= $modified ? 'Posted' : 'Updated') . date(' M j, Y', $modified) . "</time>\n" . string($content);
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
    // Secure with CSP https://w3c.github.io/webappsec-csp/
    $nonceCSS = base64_encode(openssl_random_pseudo_bytes(16));
    $nonceJS = $conn ? base64_encode(openssl_random_pseudo_bytes(16)) : null;

    header("Content-Security-Policy: base-uri 'none'" .
        "; default-src 'none'" .
        "; connect-src 'self'" .
        "; frame-ancestors 'none'" .
        "; form-action 'none'" .
        "; img-src 'self'" . ($cdn_host ? " $cdn_host" : null) .
            (ga ? ' www.google-analytics.com' : null) .
        "; manifest-src 'self'" .
        "; prefetch-src 'self'" .
        ($conn ? "; script-src" . ($cdn_host ? " $cdn_host" : " 'self'") .
            " 'strict-dynamic' 'unsafe-inline' 'nonce-$nonceJS'" .
            (ga ? " www.google-analytics.com" : null) : null) .
        "; style-src" . ($cdn_host ? " $cdn_host" :
            " 'self' 'strict-dynamic' 'unsafe-inline' 'nonce-$nonceCSS'") .
            (!$conn || !connection ? " 'nonce-MN+nJYptMzWJvlkA0FFLXQ=='" : null));
    // Omit Referrer https://w3c.github.io/webappsec-referrer-policy/
    header('Referrer-Policy: no-referrer');
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
// Render fullscreen (out of "safe-area") with "viewport-fit=cover" http://stephenradford.me/removing-the-white-bars-in-safari-on-iphone-x/
//      spec https://drafts.csswg.org/css-round-display/#viewport-fit-descriptor
$metadata .= "\n<meta name=viewport content=\"width=device-width,initial-scale=1\">";

// Early handshake DNS https://w3c.github.io/resource-hints/#dns-prefetch
if ($cdn_host) $metadata .= "\n<link rel=dns-prefetch href=$cdn_scheme$cdn_host/>";
// // Early handshake DNS, TCP and TLS https://w3c.github.io/resource-hints/#preconnect
// if ($cdn_host) $metadata .= "\n<link rel=preconnect href=$cdn_scheme$cdn_host/>";

// Resource hints http://w3c.github.io/resource-hints/
// Fetch and cache API in background when everything is downloaded https://html.spec.whatwg.org/#link-type-prefetch
if ($conn && $result) $metadata .= "\n<link rel=\"prefetch prerender\" href=$path/api" . ($url === '/' ? '' : $url) . '>';

// Webapp Manifest https://w3c.github.io/manifest/
$metadata .= "\n<link rel=manifest href=$path/manifest.webmanifest>";

// SVG favicon https://github.com/whatwg/html/issues/110
$metadata .= "\n<link rel=mask-icon href=$path/icon.svg>";
// Favicon 16x16 4-bit 16 color favicon.ico in website root http://zoompf.com/2012/04/instagram-and-optimizing-favicons
// 16px used on all browsers https://github.com/audreyr/favicon-cheat-sheet, http://realfavicongenerator.net/faq#.Vpasouh96Hs
if (!empty($path)) $metadata .= "\n<link rel=icon href=$path/favicon.png>";

// Copyright license
$metadata .= "\n<link rel=license href=$path/LICENSE>";

echo "<!doctype html>
<html lang=en>
<head prefix=\"og: http://ogp.me/ns#\">
<meta charset=utf-8>
$metadata
" . ($debug ? '<link rel=stylesheet href=' . assets . "style.css nonce=\"$nonceCSS\">" : "<style nonce=\"$nonceCSS\">" .
    (file_get_contents('assets/style.min.css')) . '</style>') . "
<body itemscope itemtype=http://schema.org/WebPage>
<div class=noscroll>
<div class=wrapper id=wrapper>$note";

if ($conn) {
    // Head nav
    if ($stmt = $mysqli->prepare('SELECT url, title FROM `' . table . '` WHERE permit=1 ORDER BY `order` ASC')) {
        $stmt->execute();
        $stmt->bind_result($data_url, $data_metatitle);

        echo "\n<div id=status class=" . ($result ? 'status' : '"status error status-error"') . ' role=progressbar></div>
<div class=tab>
    <button class=bar id=bar aria-controls=nav aria-label="Menu bar" tabindex=0 hidden><span></span></button>
    <span class=focusin id=focusin hidden></span>
    <header class=header>
        <nav class=nav id=nav>
            <div class=noscroll>';

        function is_rtl($string) {
            // Check if there RTL characters (Arabic, Persian, Hebrew) https://gist.github.com/khal3d/4648574
            // RTL languages http://www.w3.org/International/questions/qa-scripts#which
            return (bool) preg_match('/[\x{0590}-\x{05ff}\x{0600}-\x{06ff}]/u', $string);
        }

        while ($stmt->fetch()) {
            $home = !strlen($data_url);

            echo "\n            <a" . ($data_url === $urldb ? ' class=active' : null) .
                ' href="' . ($home ? $safepath : "$path/$data_url") . '"' . ($home ? ' data-version=5' : null) . '>' .
                ($home ? '<span><span class=bar><span></span></span>' : null) .
                '<span' . (is_rtl($data_metatitle) ? ' dir=auto' : '') . ($home ? ' data-placeholder' : null) .
                ">$data_metatitle</span>" . ($home ? '</span>' : null) . '</a>';
        }

        echo "\n            <div class=handler id=focusout hidden></div>
            <div class=handler id=collapse hidden></div>
            </div>
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
<meta itemprop=mainEntityOfPage content=$uri>
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
</div>";

if ($conn) {
    echo "\n" . ($debug ? '<script src="' . assets . "script.js#" . $safepath . '" nonce="' . $nonceJS . '"></script>' : '<script nonce="' . $nonceJS . '">"use strict";!function(t){var e,n,r,s=document,a=navigator,o=history,i=location,l={bar:s.getElementById("bar"),collapse:s.getElementById("collapse"),focusin:s.getElementById("focusin"),focusout:s.getElementById("focusout"),html:s.documentElement,nav:s.getElementById("nav"),output:s.getElementById("output"),reset:s.getElementById("reset"),status:s.getElementById("status"),wrapper:s.getElementById("wrapper")},c={classList:"classList"in l.html,click:"click"in l.html,dnt:"1"===a.doNotTrack||"1"===t.doNotTrack||"1"===a.msDoNotTrack,error:{e:null},eventListener:!!s.addEventListener,eventListenerOptions:function(){var t=!1;try{s.addEventListener&&addEventListener("test",null,{get passive(){t=!0}})}catch(e){}return t}(),pointer:t.PointerEvent?"pointerdown":a.maxTouchPoints>0||(t.matchMedia?t.matchMedia("(pointer: coarse)").matches:"ontouchstart"in t)?"touchstart":"mousedown",valid:function(t){try{return t()}catch(e){return this.error.e=e,this.error}}},u={activeElement:function(){var t,e=s.querySelectorAll?s.querySelectorAll("[href]:not([target=_blank])"):[],n=decodeURIComponent(s.URL).toUpperCase();for(t=0;t<e.length;t+=1)if(e[t].href.toUpperCase()===n)return e[t];return null}(),analytics:"' . ga . '",domain:"' . ga_domain . '",dnt:!1,origin:function(){var t=s.currentScript||function(){var t=s.getElementsByTagName("script");return t[t.length-1]}(),e=t.src.split("#")[1]||"/ajax-seo";return"/"===e?i.origin:decodeURIComponent(s.URL).replace(new RegExp("("+e+")(.*)$"),"$1")}(),title:s.title,url:decodeURIComponent(s.URL),version:"5.4.0",viewportWidth:720},d=t.console||{error:function(){return arguments}},p={};if(!c.eventListener)return u.error="Browser missing EventListener support",d.error(u.error,"http://caniuse.com/#feat=addeventlistener"),u;if(u.analytics&&(!c.dnt||!u.dnt)){try{localStorage.localStorage="1",delete localStorage.localStorage}catch(v){t.localStorage&&delete t.localStorage,t.localStorage={}}p.analytics={listener:function(t){t=t===!0?"addEventListener":"removeEventListener",l.analytics[t]("load",p.analytics.load),l.analytics[t]("error",p.analytics.listener),l.analytics[t]("readystatechange",p.analytics.readystatechange),t||l.analytics.removeAttribute("id")},load:function(){"function"==typeof t.ga&&(ga("create",u.analytics,u.domain,{anonymizeIp:!0,clientId:localStorage.gaClientId,storage:"none"}),localStorage.gaClientId||ga(function(t){localStorage.gaClientId=t.get("clientId")}),p.analytics.listener(),p.analytics.track())},readystatechange:function(){"complete"!==l.analytics.readyState&&"loaded"!==l.analytics.readyState||("function"==typeof t.ga?p.analytics.load():p.analytics.listener())},timestamp:+new Date+"",track:function(){"function"==typeof t.ga&&ga("send",{hitType:"pageview",title:s.title,page:location.pathname})}},l.analytics=s.createElement("script"),l.analytics.src="https://www.google-analytics.com/analytics.js",l.analytics.id=p.analytics.timestamp,s.body.appendChild(l.analytics),l.analytics=s.getElementById(p.analytics.timestamp),l.analytics&&p.analytics.listener(!0)}return!c.classList&&Element.prototype&&Object.defineProperty(Element.prototype,"classList",{get:function(){function t(){return n.className.split(/\s+/)}function e(e){return function(r){var s=t(),a=s.indexOf(r);e(s,a,r),n.className=s.join(" ")}}var n=this;return{add:e(function(t,e,n){~e||t.push(n)}),contains:function(e){return!!~t().indexOf(e)},item:function(e){return t()[e]||null},remove:e(function(t,e){~e&&t.splice(e,1)}),toggle:e(function(t,e,n){~e?t.splice(e,1):t.push(n)})}}}),l.wrapper&&l.bar&&l.collapse&&l.focusin&&l.focusout&&l.reset&&l.nav&&l.output?(l.nodeList=l.nav&&l.nav.querySelectorAll("a"),l.nodeList=l.nodeList&&(Array.from&&Array.from(l.nodeList)||[].slice.call(l.nodeList)),c.touch="touchstart"===c.pointer,l.closest=function(t,e){if(!t||!e)return null;if(t.closest)return t.closest(e);for(var n=t.matches||t.webkitMatchesSelector||t.msMatchesSelector;t&&1===t.nodeType;){if(n.call(t,e))return t;t=t.parentNode}return null},l.anchor=function(t){return t?("A"!==t.tagName&&(t=l.closest(t,"a[href]")),t&&"A"===t.tagName&&t.href&&"_blank"!==t.target?t:null):null},p.nav={expand:function(){l.html.classList.add("noscroll"),l.status.classList.add("expand")},toggleReal:function(t){l.status.classList.contains("expand")?("touchstart"===t.type&&c.eventListenerOptions||t.preventDefault(),p.nav.preventPassFocus=!0,l.html.classList.remove("noscroll"),l.collapse.setAttribute("tabindex",0),setTimeout(function(){l.focusout.setAttribute("tabindex",0),l.collapse.focus({preventScroll:!0}),l.status.classList.remove("expand")},10)):"touchstart"===t.type?(c.eventListenerOptions||t.preventDefault(),p.nav.expand()):setTimeout(function(){s.activeElement!==t.target&&t.target.focus()},0)},focus:function(t){l.status.classList.contains("expand")||(t.target.blur(),l.nav.scrollTop=0,p.nav.expand(),l.focusin.setAttribute("tabindex",0),l.focusout.removeAttribute("tabindex"),setTimeout(function(){l.focusin.focus({preventScroll:!0})},10))},disable:function(t){t.target.removeAttribute("tabindex")},collapse:function(t){var e,n=t&&("pointerdown"===t.type||"mousedown"===t.type);n&&t.target===l.nav&&l.nav.clientWidth<=t.clientX?t.preventDefault():n&&1!==t.which||(n&&(e=l.anchor(t.target),e&&e.click()),l.html.classList.remove("noscroll"),l.status.classList.remove("expand"),setTimeout(function(){p.nav.preventPassFocus=!0,l.focusout.setAttribute("tabindex",0)},10))},collapseTab:function(t){t.shiftKey||"Tab"!==t.key&&9!==t.keyCode||(p.nav.collapse(t),setTimeout(function(){l.collapse.setAttribute("tabindex",0),setTimeout(function(){l.collapse.focus({preventScroll:!0})},10)},0))},keydown:function(t){t.target!==l.bar||"Enter"!==t.key&&13!==t.keyCode||p.nav.toggleReal(t),(t.target===l.focusout?t.shiftKey:!t.shiftKey)||"Tab"!==t.key&&9!==t.keyCode||(p.nav.collapse(t),t.target!==l.focusout||t.shiftKey||(l.collapse.setAttribute("tabindex",0),setTimeout(function(){l.collapse.focus({preventScroll:!0})},10)))},passFocus:function(t){p.nav.preventPassFocus?delete p.nav.preventPassFocus:l.status.classList.contains("expand")||(l.focusout.focus({preventScroll:!0}),p.nav.disable(t))},init:function(t){var e=p.nav;(t&&t!==c.pointer||(l.wrapper.offsetWidth<=u.viewportWidth?!e.events:e.events))&&(e.events=!e.events,e.listener=e.events?"addEventListener":"removeEventListener",e.options="touchstart"===c.pointer&&c.eventListenerOptions?{passive:!0}:!0,l.bar[e.listener](c.pointer,e.toggleReal,e.options),e.events?l.focusout.setAttribute("tabindex",0):l.focusout.removeAttribute("tabindex"),c.touch?l.nav[e.listener]("click",e.collapse,!0):(l.bar[e.listener]("focus",e.focus,!0),l.bar[e.listener]("keydown",e.keydown,!0),l.focusin[e.listener]("blur",e.disable,!0),l.nodeList&&l.nodeList[l.nodeList.length-1][e.listener]("keydown",e.collapseTab,!0),l.focusout[e.listener]("focus",e.expand,!0),l.focusout[e.listener]("blur",e.disable,!0),l.focusout[e.listener]("keydown",e.keydown,!0),l.collapse[e.listener]("focus",e.passFocus,!0),l.collapse[e.listener]("blur",e.disable,!0),l.reset[e.listener]("blur",e.disable,!0),l.nav[e.listener](c.pointer,e.collapse,e.options)),l.reset[e.listener](c.pointer,e.collapse,e.options),t&&(c.pointer=t,c.touch="touchstart"===c.pointer,p.nav.init()))}},p.nav.init(),"pointerdown"!==c.pointer&&t.matchMedia&&t.matchMedia("(pointer: coarse)").addListener(function(t){p.nav.init(t.matches?"touchstart":"mousedown")}),t.addEventListener("resize",function(){p.nav.timeoutScale&&clearTimeout(p.nav.timeoutScale),p.nav.timeoutScale=setTimeout(p.nav.init,100)},!0),o.pushState?(r={callback:function(t){u.error=t.error||!1,u.activeElement=r.nav.activeElement()||u.activeElement,o.replaceState(t,t.title,null),r.update(t,!0,u.activeElement)},click:function(t){var e;t&&(c.click?t.click():(e=s.createEvent("MouseEvents"),e.initEvent("click",!0,!0),t.dispatchEvent(e)))},filter:function(t,e){return t?(t=decodeURIComponent(t).replace(/#.*$/,""),e?t:t.toLowerCase()):void 0},init:function(){l.status&&l.status.addEventListener("transitionend",r.resetStatus,!0),setTimeout(function(){t.onpopstate=r.popstate},150),o.replaceState({error:u.error,title:u.title,content:l.output.innerHTML},u.title,u.url),n=new XMLHttpRequest,n.addEventListener("loadstart",r.loadstart,!0),n.addEventListener("load",r.load,!0),n.addEventListener("abort",r.reset,!0),l.html.addEventListener("click",r.listener,!0)},listener:function(e){var a,i,c={};if(e){if(i=l.anchor(e.target),a=new RegExp("^"+u.origin+"($|#|/.{1,}).*","i"),!i||!a.test(i.href.replace(/\/$/,"")))return;if(setTimeout(function(){i!==s.activeElement&&i.focus({preventScroll:!0})},0),i.href.toLowerCase()===u.url.toLowerCase())return void e.preventDefault();if(u.url=i.href.toLowerCase().replace(/(\/)+(?=\1)/g,"").replace(/(^https?:(\/))/,"$1/").replace(/\/$/,""),c.attr=r.filter(u.url,!0),c.url=decodeURIComponent(s.URL),c.address=r.filter(c.url),c.attr===c.address&&u.url.indexOf("#")>-1)return void setTimeout(function(){o.replaceState({error:u.error,title:u.title,content:l.output.innerHTML},s.title,decodeURIComponent(u.url))},0);if(e.preventDefault(),p.nav.events&&l.status.classList.contains("expand")&&(p.nav.collapse(),l.reset.setAttribute("tabindex",0),setTimeout(function(){l.reset.focus({preventScroll:!0})},10)),u.activeElement=i,u.activeNav=i.parentNode===l.nav,!r.retry&&u.activeNav&&(u.error=u.activeElement.classList.contains("x-error")),u.title=u.activeElement.textContent,u.error&&c.address===c.url?o.replaceState(null,u.title,u.url):u.url!==c.url&&o.pushState(null,u.title,u.url),!u.error&&!r.retry&&c.attr===c.address||u.activeNav&&u.activeElement.classList.contains("focus"))return;s.title=u.title,r.resetStatus(),r.nav.nodeList&&(u.error&&u.activeNav&&(u.activeElement.classList.remove("x-error"),u.activeElement.classList.remove("error")),l.focus=l.nav.querySelector(".focus"),l.focus&&l.focus.classList.remove("focus")),u.activeNav&&u.activeElement.classList.add("focus"),r.inprogress&&(n.abort(),t.stop?t.stop():s.execCommand&&s.execCommand("Stop",!1)),n.open("GET",u.origin+"/api"+c.attr.replace(new RegExp("^"+u.origin,"i"),"")),u.error&&n.setRequestHeader("If-Modified-Since","Sat, 1 Jan 2000 00:00:00 GMT"),r.inprogress=!0,n.send()}},load:function(){var t=this.response;t=c.valid(function(){return JSON.parse(t)}),r.callback(t===c.error?{error:!0,title:"Server error",content:"<h1>Whoops...</h1><p>Experienced server error. Try to <a class=x-error href="+u.url+">reload</a>"+(u.url===u.origin?"":" or head to <a href="+u.origin+">home page</a>")+"."}:t)},loadstart:function(){l.status&&(l.status.classList.remove("status-done"),l.status.classList.remove("status-start"),e&&clearTimeout(e),e=setTimeout(function(){l.status.classList.add("status-start")},0))},nav:{activeElement:function(){var t;if(r.nav.nodeList)for(t=0;t<r.nav.nodeList.length;t+=1)if(r.filter(r.nav.nodeList[t].href)===u.url)return r.nav.nodeList[t];return null},nodeList:l.nodeList},popstate:function(t){var e,n=t.state;r.reset(),r.retry=!n,u.error=n&&n.error||!1,n||t.srcElement.location.pathname===t.target.location.pathname||(u.url=r.filter(s.URL),e=r.nav.activeElement(),r.click(e)),r.update(n,!1,e)},reset:function(){e&&clearTimeout(e),l.status&&l.status.classList.contains("status-start")&&l.status.classList.add("status-done")},resetStatus:function(t){l.status&&(!l.status.classList.contains("status-error")||t&&u.error||l.status.classList.remove("status-error"),l.status.classList.contains("status-done")&&(l.status.classList.remove("status-start"),l.status.classList.remove("status-done")))},retry:!1,update:function(t,e,a){if(t){e?r.reset():n.abort(),r.nav.nodeList&&(l.focus=l.nav.querySelector(".focus"),l.active=l.nav.querySelector(".active"),l.error=l.nav.querySelector(".error"),l.focus&&l.focus.classList.remove("focus"),l.active&&l.active.classList.remove("active"),l.error&&l.error.classList.remove("error")),u.url=r.filter(s.URL),u.activeElement=a||r.nav.activeElement(),u.activeElement&&(u.activeElement.focus({preventScroll:!0}),u.activeElement.classList.add(u.error?"error":"active"),u.error&&u.activeElement.classList.add("x-error")),u.error?(l.status.classList.add("error"),l.status.classList.add("status-error")):(l.status.classList.remove("error"),l.status.classList.remove("status-error")),s.title=u.title=t.title;var o=s.scrollingElement||l.html.scrollTop||s.body;o.scrollTop=0,l.output.innerHTML=t.content,i.hash&&i.replace(u.url+i.hash),p.analytics&&p.analytics.track(),delete r.inprogress}}},u.analytics||delete u.analytics,u.domain||delete u.domain,r.init(),u.error=l.status&&l.status.classList.contains("status-error"),void(t.as=u)):(u.error="Browser missing History API support",d.error(u.error,"http://caniuse.com/#feat=history"),u)):(u.error="Missing HTML Elements",d.error(u.error,"https://github.com/laukstein/ajax-seo"),u)}(this);</script>');
}
