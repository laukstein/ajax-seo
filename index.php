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
$metadata .= "\n<meta name=viewport content=\"width=device-width,initial-scale=1\">";

// Early handshake DNS https://w3c.github.io/resource-hints/#dns-prefetch
if ($cdn_host) $metadata .= "\n<link rel=dns-prefetch href=$cdn_scheme$cdn_host/>";
// // Early handshake DNS, TCP and TLS https://w3c.github.io/resource-hints/#preconnect
// if ($cdn_host) $metadata .= "\n<link rel=preconnect href=$cdn_scheme$cdn_host/>";

// Resource hints http://w3c.github.io/resource-hints/
// Fetch and cache API in background when everything is downloaded https://html.spec.whatwg.org/#link-type-prefetch
if ($conn && $result) $metadata .= "\n<link rel=\"prefetch prerender\" href=$path/api" . ($url === '/' ? '' : $url) . '>';

// Manifest for a web application https://w3c.github.io/manifest/
$metadata .= "\n<link rel=manifest href=$path/manifest.json>";

// SVG favicon https://github.com/whatwg/html/issues/110
$metadata .= "\n<link rel=mask-icon href=$path/icon.svg color=#0b62bb>";
// Favicon 16x16 4-bit 16 color favicon.ico in website root http://zoompf.com/2012/04/instagram-and-optimizing-favicons
// 16px used on all browsers https://github.com/audreyr/favicon-cheat-sheet, http://realfavicongenerator.net/faq#.Vpasouh96Hs
if (!empty($path)) $metadata .= "\n<link rel=\"shortcut icon\" href=$path/favicon.png>";

// Copyright license
$metadata .= "\n<link rel=license href=$path/LICENSE>";

echo "<!doctype html>
<html lang=en>
<head prefix=\"og: http://ogp.me/ns#\">
<meta charset=utf-8>
$metadata
" . ($debug ? '<link rel=stylesheet href=' . assets . "style.css>" : '<style>' . (file_get_contents('assets/style.min.css')) . '</style>') . "
<!--[if lt IE 9]><script src=//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js></script><![endif]-->
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
    <button class=bar id=bar tabindex=0 hidden><span></span></button>
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
    echo "\n" . ($debug ? '<script src="' . assets . "script.js#" . $safepath . '"></script>' : '<script>!function(e){"use strict";"function"==typeof define&&define.amd?define(["as"],e):"object"==typeof module&&module.exports?module.exports=e():window.as=e()}(function(){"use strict";var e,t,s,r=window,n=document,a=navigator,o=history,i=location,l={html:n.documentElement,wrapper:n.getElementById("wrapper"),bar:n.getElementById("bar"),collapse:n.getElementById("collapse"),focusin:n.getElementById("focusin"),focusout:n.getElementById("focusout"),reset:n.getElementById("reset"),nav:n.getElementById("nav"),status:n.getElementById("status"),output:n.getElementById("output")},c={classList:"classList"in l.html,click:"click"in l.html,dnt:"1"===a.doNotTrack||"1"===r.doNotTrack||"1"===a.msDoNotTrack,error:{e:null},eventListener:!!n.addEventListener,pointer:a.pointerEnabled?"pointerdown":a.maxTouchPoints>0||r.matchMedia&&r.matchMedia("(pointer: coarse)").matches||"ontouchstart"in r?"touchstart":"mousedown",valid:function(e){try{return e()}catch(t){return this.error.e=t,this.error}}},u={version:"5.1.1",viewportWidth:720,analytics:"' . ga . '",dnt:c.dnt,domain:"' . ga_domain . '",origin:function(){var e=n.currentScript||function(){var e=n.getElementsByTagName("script");return e[e.length-1]}(),t=e.src.split("#")[1]||"/ajax-seo";return"/"===t?i.origin:decodeURIComponent(n.URL).replace(new RegExp("("+t+")(.*)$"),"$1")}(),url:decodeURIComponent(n.URL),title:n.title,activeElement:function(){var e,t=n.querySelectorAll?n.querySelectorAll("[href]:not([target=_blank])"):[],s=decodeURIComponent(n.URL).toUpperCase();for(e=0;e<t.length;e+=1)if(t[e].href.toUpperCase()===s)return t[e];return null}(),error:void 0},d=r.console||{error:function(){}},v={};return c.eventListener?(!u.dnt&&u.analytics&&(v.analytics={listener:function(e){e=e===!0?"addEventListener":"removeEventListener",l.analytics[e]("load",v.analytics.load),l.analytics[e]("error",v.analytics.listener),l.analytics[e]("readystatechange",v.analytics.readystatechange),e||l.analytics.removeAttribute("id")},load:function(){"function"==typeof r.ga&&(ga("create",u.analytics,u.domain,{storage:"none",clientId:localStorage.gaClientId}),localStorage.gaClientId||ga(function(e){localStorage.gaClientId=e.get("clientId")}),ga("send","pageview"),v.analytics.listener())},readystatechange:function(){"complete"!==l.analytics.readyState&&"loaded"!==l.analytics.readyState||("function"==typeof r.ga?v.analytics.load():v.analytics.listener())},timestamp:+new Date+""},l.analytics=n.createElement("script"),l.analytics.src="//www.google-analytics.com/analytics.js",l.analytics.id=v.analytics.timestamp,n.body.appendChild(l.analytics),l.analytics=n.getElementById(v.analytics.timestamp),l.analytics&&v.analytics.listener(!0)),!c.classList&&Element.prototype&&Object.defineProperty(Element.prototype,"classList",{get:function(){function e(){return s.className.split(/\s+/)}function t(t){return function(r){var n=e(),a=n.indexOf(r);t(n,a,r),s.className=n.join(" ")}}var s=this;return{add:t(function(e,t,s){~t||e.push(s)}),remove:t(function(e,t){~t&&e.splice(t,1)}),item:function(t){return e()[t]||null},toggle:t(function(e,t,s){~t?e.splice(t,1):e.push(s)}),contains:function(t){return!!~e().indexOf(t)}}}}),l.wrapper&&l.bar&&l.collapse&&l.focusin&&l.focusout&&l.reset&&l.nav&&l.output?(l.nodeList=l.nav&&Array.from&&Array.from(l.nav.querySelectorAll("a"))||[].slice.call(l.nav.querySelectorAll("a")),c.touch="touchstart"===c.pointer,l.closest=function(e,t){if(!e||!t)return null;if(e.closest)return e.closest(t);for(var s=e.matches||e.webkitMatchesSelector||e.msMatchesSelector;e&&1===e.nodeType;){if(s.call(e,t))return e;e=e.parentNode}return null},l.anchor=function(e){return e?("A"!==e.tagName&&(e=l.closest(e,"a[href]")),e&&"A"===e.tagName&&e.href&&"_blank"!==e.target?e:null):null},v.nav={expand:function(){l.html.classList.add("noscroll"),l.status.classList.add("expand")},toggleReal:function(e){l.status.classList.contains("expand")?(e.preventDefault(),v.nav.preventPassFocus=!0,l.html.classList.remove("noscroll"),l.collapse.setAttribute("tabindex",0),setTimeout(function(){l.focusout.setAttribute("tabindex",0),l.collapse.focus(),l.status.classList.remove("expand")},10)):"touchstart"===e.type?(e.preventDefault(),v.nav.expand()):setTimeout(function(){n.activeElement!==e.target&&e.target.focus()},0)},focus:function(e){l.status.classList.contains("expand")||(e.target.blur(),l.nav.scrollTop=0,v.nav.expand(),l.focusin.setAttribute("tabindex",0),l.focusout.removeAttribute("tabindex"),setTimeout(function(){l.focusin.focus()},10))},disable:function(e){e.target.removeAttribute("tabindex")},collapse:function(e){var t,s=e&&("pointerdown"===e.type||"mousedown"===e.type);s&&e.target===l.nav&&l.nav.clientWidth<=e.clientX?e.preventDefault():s&&1!==e.which||(s&&(t=l.anchor(e.target),t&&t.click()),l.html.classList.remove("noscroll"),l.status.classList.remove("expand"),setTimeout(function(){v.nav.preventPassFocus=!0,l.focusout.setAttribute("tabindex",0)},10))},collapseTab:function(e){e.shiftKey||"Tab"!==e.key&&9!==e.keyCode||(v.nav.collapse(e),setTimeout(function(){l.collapse.setAttribute("tabindex",0),setTimeout(function(){l.collapse.focus()},10)},0))},keydown:function(e){e.target!==l.bar||"Enter"!==e.key&&13!==e.keyCode||v.nav.toggleReal(e),(e.target===l.focusout?e.shiftKey:!e.shiftKey)||"Tab"!==e.key&&9!==e.keyCode||(v.nav.collapse(e),e.target!==l.focusout||e.shiftKey||(l.collapse.setAttribute("tabindex",0),setTimeout(function(){l.collapse.focus()},10)))},passFocus:function(e){v.nav.preventPassFocus?delete v.nav.preventPassFocus:l.status.classList.contains("expand")||(l.focusout.focus(),v.nav.disable(e))},init:function(){var e=v.nav;(l.wrapper.offsetWidth<=u.viewportWidth?!e.events:e.events)&&(e.events=!e.events,e.listener=e.events?"addEventListener":"removeEventListener",l.bar[e.listener](c.pointer,e.toggleReal,!0),e.events?l.focusout.setAttribute("tabindex",0):l.focusout.removeAttribute("tabindex"),c.touch?l.nav[e.listener]("click",e.collapse,!0):(l.bar[e.listener]("focus",e.focus,!0),l.bar[e.listener]("keydown",e.keydown,!0),l.focusin[e.listener]("blur",e.disable,!0),l.nodeList&&l.nodeList[l.nodeList.length-1][e.listener]("keydown",e.collapseTab,!0),l.focusout[e.listener]("focus",e.expand,!0),l.focusout[e.listener]("blur",e.disable,!0),l.focusout[e.listener]("keydown",e.keydown,!0),l.collapse[e.listener]("focus",e.passFocus,!0),l.collapse[e.listener]("blur",e.disable,!0),l.reset[e.listener]("blur",e.disable,!0),l.nav[e.listener](c.pointer,e.collapse,!0)),l.reset[e.listener](c.pointer,e.collapse,!0))}},v.nav.init(),r.addEventListener("resize",function(){v.nav.timeoutScale&&clearTimeout(v.nav.timeoutScale),v.nav.timeoutScale=setTimeout(v.nav.init,100)},!0),o.pushState?(s={filter:function(e,t){return e?(e=decodeURIComponent(e).replace(/#.*$/,""),t?e:e.toLowerCase()):void 0},reset:function(){e&&clearTimeout(e),l.status&&l.status.classList.contains("status-start")&&l.status.classList.add("status-done")},click:function(e){if(e)if(c.click)e.click();else{var t=n.createEvent("MouseEvents");t.initEvent("click",!0,!0),e.dispatchEvent(t)}},nav:{nodeList:l.nodeList,activeElement:function(){if(s.nav.nodeList){var e;for(e=0;e<s.nav.nodeList.length;e+=1)if(s.filter(s.nav.nodeList[e].href)===u.url)return s.nav.nodeList[e]}return null}},update:function(e,r,a){if(e){!u.dnt&&u.analytics&&"function"==typeof ga&&ga("send","pageview",{page:u.url}),r?s.reset():t.abort(),s.nav.nodeList&&(l.focus=l.nav.querySelector(".focus"),l.active=l.nav.querySelector(".active"),l.error=l.nav.querySelector(".error"),l.focus&&l.focus.classList.remove("focus"),l.active&&l.active.classList.remove("active"),l.error&&l.error.classList.remove("error")),u.url=s.filter(n.URL),u.activeElement=a||s.nav.activeElement(),u.activeElement&&(u.activeElement.focus(),u.activeElement.classList.add(u.error?"error":"active"),u.error&&u.activeElement.classList.add("x-error")),u.error?(l.status.classList.add("error"),l.status.classList.add("status-error")):(l.status.classList.remove("error"),l.status.classList.remove("status-error")),n.title=u.title=e.title;var o=n.scrollingElement||l.html.scrollTop||n.body;o.scrollTop=0,l.output.innerHTML=e.content,i.hash&&i.replace(u.url+i.hash),delete s.inprogress}},retry:!1,popstate:function(e){var t,r=e.state;s.reset(),s.retry=!r,u.error=r&&r.error||!1,r||e.srcElement.location.pathname===e.target.location.pathname||(u.url=s.filter(n.URL),t=s.nav.activeElement(),s.click(t)),s.update(r,!1,t)},loadstart:function(){l.status&&(l.status.classList.remove("status-done"),l.status.classList.remove("status-start"),e&&clearTimeout(e),e=setTimeout(function(){l.status.classList.add("status-start")},0))},callback:function(e){u.error=e.error||!1,u.activeElement=s.nav.activeElement()||u.activeElement,o.replaceState(e,e.title,null),s.update(e,!0,u.activeElement)},load:function(){var e=this.response;e=c.valid(function(){return JSON.parse(e)}),s.callback(e===c.error?{error:!0,title:"Server error",content:"<h1>Whoops...</h1><p>Experienced server error. Try to <a class=x-error href="+u.url+">reload</a>"+(u.url===u.origin?"":" or head to <a href="+u.origin+">home page</a>")+"."}:e)},resetStatus:function(e){l.status&&(!l.status.classList.contains("status-error")||e&&u.error||l.status.classList.remove("status-error"),l.status.classList.contains("status-done")&&(l.status.classList.remove("status-start"),l.status.classList.remove("status-done")))},listener:function(e){if(e){var a=l.anchor(e.target),i=new RegExp("^"+u.origin+"($|#|/.{1,}).*","i"),c={};if(!a||!i.test(a.href.replace(/\/$/,"")))return;if(setTimeout(function(){a!==n.activeElement&&a.focus()},0),a.href.toLowerCase()===u.url.toLowerCase())return void e.preventDefault();if(u.url=a.href.toLowerCase().replace(/(\/)+(?=\1)/g,"").replace(/(^https?:(\/))/,"$1/").replace(/\/$/,""),c.attr=s.filter(u.url,!0),c.url=decodeURIComponent(n.URL),c.address=s.filter(c.url),c.attr===c.address&&u.url.indexOf("#")>-1)return void setTimeout(function(){o.replaceState({error:u.error,title:u.title,content:l.output.innerHTML},n.title,decodeURIComponent(u.url))},0);if(e.preventDefault(),v.nav.events&&l.status.classList.contains("expand")&&(v.nav.collapse(),l.reset.setAttribute("tabindex",0),setTimeout(function(){l.reset.focus()},10)),u.activeElement=a,u.activeNav=a.parentNode===l.nav,!s.retry&&u.activeNav&&(u.error=u.activeElement.classList.contains("x-error")),u.title=u.activeElement.textContent,u.error&&c.address===c.url?o.replaceState(null,u.title,u.url):u.url!==c.url&&o.pushState(null,u.title,u.url),!u.error&&!s.retry&&c.attr===c.address||u.activeNav&&u.activeElement.classList.contains("focus"))return;n.title=u.title,s.resetStatus(),s.nav.nodeList&&(u.error&&u.activeNav&&(u.activeElement.classList.remove("x-error"),u.activeElement.classList.remove("error")),l.focus=l.nav.querySelector(".focus"),l.focus&&l.focus.classList.remove("focus")),u.activeNav&&u.activeElement.classList.add("focus"),s.inprogress&&(t.abort(),r.stop?r.stop():n.execCommand&&n.execCommand("Stop",!1)),t.open("GET",u.origin+"/api"+c.attr.replace(new RegExp("^"+u.origin,"i"),"")),u.error&&t.setRequestHeader("If-Modified-Since","Sat, 1 Jan 2000 00:00:00 GMT"),s.inprogress=!0,t.send()}},init:function(){l.status&&l.status.addEventListener("transitionend",s.resetStatus,!0),setTimeout(function(){r.onpopstate=s.popstate},150),o.replaceState({error:u.error,title:u.title,content:l.output.innerHTML},u.title,u.url),t=new XMLHttpRequest,t.addEventListener("loadstart",s.loadstart,!0),t.addEventListener("load",s.load,!0),t.addEventListener("abort",s.reset,!0),l.html.addEventListener("click",s.listener,!0)}},u.analytics||delete u.analytics,u.domain||delete u.domain,s.init(),u.error=l.status&&l.status.classList.contains("status-error"),u):(u.error="Browser missing History API support",d.error(u.error,"http://caniuse.com/#feat=history"),u)):(u.error="Missing HTML Elements",d.error(u.error,"https://github.com/laukstein/ajax-seo"),u)):(u.error="Browser missing EventListener support",d.error(u.error,"http://caniuse.com/#feat=addeventlistener"),u)});</script>');
}
