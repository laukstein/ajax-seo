<?php

include 'content/config.php';
include 'content/connect.php';
include 'content/cache.php';

$gtitle           = !empty($gtitle) ? $gtitle : title;
$meta_description = null;

if ($conn) {
    $title     = $title_error     = '404 Not Found';
    $pagetitle = $pagetitle_error = 'Page not found';
    $content   = $content_error   = '<p>Sorry, this page cannot be found.</p>';

    $stmt = $mysqli->prepare('SELECT url, title, `meta-title`, `meta-description`, content FROM `' . table . '` WHERE url=? LIMIT 1');
    $stmt->bind_param('s', $url);
    $stmt->execute();
    $stmt->bind_result($url, $title, $meta_title, $meta_description, $content);

    while ($stmt->fetch()) {
        $results    = true;
        $title      = !empty($title) && ($meta_title !== $title) ? $title : $meta_title;
        $meta_title = isset($meta_title) ? $meta_title : $title;
        // SEO page title improvement for the root page
        $pagetitle  = empty($url) ? $gtitle : $meta_title; //. ' - ' . $gtitle;
    }

    // URL does not exist
    if (!$results) {
        http_response_code(404);

        $title     = $title_error;
        $content   = $content_error;
        $pagetitle = $pagetitle_error;
    }

    $stmt->free_result();
    $stmt->close();

    // JSON/JSONP respond
    if (isset($_GET['api'])) {
        include 'content/api.php';
        exit;
    }
}

// Avoid undefined variables
$optional_title = isset($optional_title) ? $optional_title : null;


// Avoid XSS attacks https://dvcs.w3.org/hg/content-security-policy/raw-file/tip/csp-specification.dev.html
header("Content-Security-Policy: script-src 'self' 'unsafe-inline' 'unsafe-eval'"
    . ($issetcdn ? ' ' . $cdn_host : null) . ' cdnjs.cloudflare.com'
    //. ' apis.google.com'
    . ' www.google-analytics.com');


// 160 character title http://blogs.msdn.com/b/ie/archive/2012/05/14/sharing-links-from-ie10-on-windows-8.aspx
$metadata  = "<title>$pagetitle</title>";

// Open Graph protocol http://ogp.me
$metadata .= "\n<meta property=og:title content=\"$pagetitle\">";
// 253 character description http://blogs.msdn.com/b/ie/archive/2012/05/14/sharing-links-from-ie10-on-windows-8.aspx
if (!empty($meta_description)) $metadata .= "\n<meta property=og:description name=description content=\"$meta_description\">";
// Twitter Cards https://dev.twitter.com/docs/cards
$metadata .= "\n<meta property=twitter:card content=summary>"; // Twitterbot will crawl as default 'summary' when twitter:card is not set (Twitterbot has some issue with it, need to be set)
//$metadata .= "\n<meta property=og:url content=\"$uri\">"; // No more required

// Opt-out of pinning by Pinterest, save copyrights and avoid SEO impact https://en.help.pinterest.com/entries/21063792-Prevent-pinning-from-your-site
// Return the meta tag just for Pinterest, since W3C validator will return it as a unregistered specification.
// if (stripos($_SERVER['HTTP_USER_AGENT'], 'Pinterest') !== false) $metadata .= "\n<meta name=pinterest content=nopin>";

// Perform speed and security on removing referrer-header-value http://wiki.whatwg.org/wiki/Meta_referrer
$metadata .= "\n<meta name=referrer content=never>";

// Optimize mobile device viewport and return the same pixel density like on desktop
// 2012-06-13 Dropped target-densityDpi and its translated CSS property resolution http://lists.w3.org/Archives/Public/www-style/2012Jun/0283.html, http://trac.webkit.org/changeset/119527
$metadata .= "\n<meta name=viewport content=\"width=device-width, maximum-scale=1\">";

// Authorship in Google Search https://support.google.com/webmasters/answer/1408986
// $metadata .= "\n<link rel=author href=https://plus.google.com/000000000000000000000>";

// Prefetch CDN by saving DNS resolution time https://github.com/h5bp/html5-boilerplate/blob/master/doc/extend.md#dns-prefetching
if ($issetcdn) $metadata .= "\n<link rel=dns-prefetch href=$cdn_uri>"; // Own DNS
// $metadata .= "\n<link rel=dns-prefetch href=//cdnjs.cloudflare.com>";     // CloudFlare
// $metadata .= "\n<link rel=dns-prefetch href=https://apis.google.com>";    // Google+ button
// $metadata .= "\n<link rel=dns-prefetch href=//www.google-analytics.com>"; // Google Analytics

if ($conn) {
    // Fetch and cache API in background when everything is downloaded http://www.whatwg.org/specs/web-apps/current-work/#link-type-prefetch
    $metadata .= "\n<link rel=\"prefetch prerender\" href=api" . (!empty($url) ? '/' . $url : null) . '>';
}

// Favicon 16x16, 32x32 4-bit 16 color /favicon.ico on website root or base64 inline dataURI when project not in root http://zoompf.com/2012/04/instagram-and-optimizing-favicons
if ($path !== '/') $metadata .= "\n<link rel=\"shortcut icon\" href=\"data:image/x-icon;base64,AAABAAIAICAQAAEABADoAgAAJgAAABAQEAABAAQAKAEAAA4DAAAoAAAAIAAAAEAAAAABAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAvGMMALxkDQC9ZRAAxnsyAMd+NwDNjE0Az5FUANGVWwDSmWEA1J1oANaibwD6+fgAAAAAAP8A/wD//wAA////ALu7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7ALu7u7u7u7u7u7u7u7u0uwC7i7u7u7u7u7u7u7u7MCUAUge7u7u7u7u7u7u7u7IQAAAru7u7u7u7u7u7u7u5ALsAa7u7u7u7u7u7u7uwABu7sAALu7u7u7u7u7u7sBALu7AAC7u7u7u7u7u7u7u4ELsQW7u7u7u7u7u7u7u7sgAAACu7u7u7u7u7u7u7u4AlAFIEu7u7u7u7u7u7u7u6uwC7S7u7u7u7u7u7u7u7u7sAu7u7u7u7u7u7u7u7u7ALALALu7u7u7u7u7u7u7uwCwCwC7u7u7u7u7u7u7u7u7sAsAu7u7u7u7u7u7u7u7u7ALALu7u7u7u7u7u7u7u7u7uwC7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAoAAAAEAAAACAAAAABAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAvGMMALxkDQC9ZRAAxnsyAMd+NwDNjE0Az5FUANGVWwDSmWEA1J1oANaibwD6+fgAAAAAAP8A/wD//wAA////ALu7u7ALu7u7u7tLsAu4u7u7swJQBSB7u7u7IQAAAru7u7uQC7AGu7u7AAG7uwAAu7sBALu7AAC7u7uBC7EFu7u7uyAAAAK7u7u4AlAFIEu7u7ursAu0u7u7u7uwC7u7u7u7ALALALu7u7sAsAsAu7u7u7uwCwC7u7u7u7ALALu7AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA==\">";

// Website copyright license
$metadata .= "\n<link rel=license href=//creativecommons.org/licenses/by/3.0/>";

// Cache manifest (Chrome external domain hosting issue http://crbug.com/167918)
// <html manifest=manifest.appcache>

echo "<!DOCTYPE html>
<html lang=en>
<head prefix=\"og: http://ogp.me/ns#\">
<meta charset=UTF-8>
$metadata
<link rel=stylesheet href=$assets$css>
<!--[if lt IE 9]><script src=//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.6.2/html5shiv.min.js></script><![endif]-->
<body class=\"status js-status\" itemscope itemtype=http://schema.org/WebPage>";

// Declare the family friendly content http://schema.org/WebPage
echo "\n<meta itemprop=isFamilyFriendly content=true>$note";

if ($conn) {
    if ($stmt = $mysqli->prepare('SELECT url, `meta-title` FROM `' . table . '` ORDER BY array ASC')) {
        $stmt->execute();
        $stmt->bind_result($data_url, $data_metatitle);

echo "\n<div class=progress></div>
<div class=tab>
    <a class=\"item item-header js-header\" href=javascript:;>≡</a>
    <a class=\"item item-footer js-footer\" href=javascript:;>i</a>
    <header class=header>
        <nav class=nav>";

        while ($stmt->fetch()) {
            echo "\n            <a class=\"js-as"
                . ($url == $data_url ? ' active' : null) . "\" href=\"$path$data_url\""
                . ' dir=auto>' . (!empty($data_url) ? $data_metatitle : "<span class=home>$data_metatitle</span>") . '</a>';
        }

        echo "\n        </nav>
    </header>
</div>";

        $stmt->free_result();
        $stmt->close();
    }

    $mysqli->close();
}

echo "\n<main class=\"main js-content\" role=main itemprop=about itemscope itemtype=http://schema.org/Article>";

if ($conn) {
    echo "\n<h1 dir=auto>$title</h1>";
}

echo "\n$content
</main>
<footer class=footer itemprop=breadcrumb>
    <a href=https://github.com/laukstein/ajax-seo>Contribute on github</a>
    <a href=https://github.com/laukstein/ajax-seo/zipball/master>Download</a>
    <a href=https://github.com/laukstein/ajax-seo/issues>Submit issue</a>
</footer>";

if($conn){

// Comparing CDNs
// CloudFlare's cdnJS is better than Google CDN http://www.baldnerd.com/make-your-site-faster-cloudflares-cdnjs-vs-google-hosted-libraries-shocking-results/
// jQuery EdgeCast's CDN better than Google, Microsoft and Media Temple CDN http://royal.pingdom.com/2012/07/24/best-cdn-for-jquery-in-2012/

echo "\n<!--[if IE]><script src=//cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js></script><![endif]-->
<!--[if !IE]>--><script src=//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js></script><!--<![endif]-->
<script src=$assets$js></script>
<script>
(function() {
    'use strict';

    var d = document,

        // Remove 300ms click delay on mobile devices by using touchstart event
        // Usage: $(selector).on(pointer, (function() { });
        pointer = (('ontouchstart' in window) || window.DocumentTouch && d instanceof window.DocumentTouch) ? 'touchstart' : 'click',

        // Check if CSS property supported http://lea.verou.me/2009/02/check-if-a-css-property-is-supported/
        isSupported = function(property) {
            return property in document.body.style;
        },

        init  = true,
        state = window.history.pushState !== undefined,

        \$nav     = $('.js-as'),
        \$content = $('.js-content'),
        \$status  = $('.js-status'),

        // Google Universal Analytics tracking
        tracker = function() {
            if (typeof ga !== 'undefined') {
                return ga && ga('send', 'pageview', {
                    page: decodeURI(window.location.pathname)
                });
            }
        },
        \$this, request, fadeTimer,
        stateLink = function(e, className) {
            \$nav.each(function() {
                \$this = $(this);

                if (\$this.attr('href') === decodeURI($.address.state() + e.path).replace(/\/\//, '/')) {
                    \$this.addClass(className).focus();
                } else {
                    \$this.removeClass(className);
                }
                if (\$status.hasClass('show')) \$status.removeClass('show show-header show-footer');
            });
        },
        handler = function(data) { // Response
            if (\$status.hasClass('start')) \$status.addClass('done');

            d.title = data.pagetitle;
            \$content.html(data.content);
            tracker();
        };

    // Avoid console.log error on legacy browsers
    if (!window.console) {
        window.console = {
            log: function() {}
        };
    }

    $(d).on('webkitTransitionEnd transitionend', '.js-status.done .progress', function() {
        \$status.removeClass('start done');
    });

    function toggle(event, remove, add) {
        event.stopPropagation();
        \$status.removeClass(remove).toggleClass(add);
        if (\$status.hasClass('show-header') || \$status.hasClass('show-footer')) {
            \$status.addClass('show');
        } else {
            \$status.removeClass('show');
        }
    }

    $('.js-header').on(pointer, function(e){
        toggle(e, 'show-footer', 'show-header');
    });
    $('.js-footer').on(pointer, function(e){
        toggle(e, 'show-header', 'show-footer');
    });

    if (window.matchMedia) {
        // https://developer.mozilla.org/en-US/docs/Web/Guide/CSS/Testing_media_queries
        var matchMedia = window.matchMedia('(max-width: 532px)'),
            cleanStatus = function() {
                if (matchMedia.matches) {
                    $(d).on(pointer, function(){
                        if (\$status.hasClass('show')) \$status.removeClass('show show-header show-footer');
                    });
                } else {
                    $(d).off(pointer);
                    \$status.removeClass('show show-header show-footer');
                }
            };

        cleanStatus();
        matchMedia.addListener(cleanStatus);
    }

    $.address.state('$path').init(function() {
        // Initialize jQuery Address
        \$nav.address();
    }).change(function(e) {
        if (state && init) {
            init = false;
        } else {
            // Halt previously created request
            if (request && request.readyState !== 4) request.abort();

            stateLink(e, 'selected');

            // Load API content
            request = $.ajax({
                url: '{$path}api' + (e.path.length !== 1 ? '/' + encodeURI(e.path.substr(1)) : ''),
                //dataType: 'jsonp',
                //jsonpCallback: 'foo',
                //cache: true,
                beforeSend: function() {
                    if (isSupported('transition')) \$status.removeClass('start done');

                    fadeTimer = setTimeout(function() {
                        if (isSupported('transition')) \$status.addClass('start');
                    }, 100); // Avoid fadeTimer() if content already in cache
                },
                success: function(data) {
                    if (fadeTimer) clearTimeout(fadeTimer);

                    \$nav.removeClass('selected');
                    stateLink(e, 'active');
                    handler(data);
                },
                error: function(jqXHR, textStatus) {
                    if (fadeTimer) {
                        clearTimeout(fadeTimer);
                        if (isSupported('transition')) \$status.removeClass('start done');
                        \$nav.removeClass('active selected').blur();
                    }
                    if (textStatus !== 'abort') {
                        console.log(textStatus);

                        if (textStatus === 'timeout') \$content.html('Loading seems to be taking a while...');
                        \$nav.removeClass('selected');
                        d.title = '$pagetitle_error';
                        \$content.html('<h1>$title_error</h1>$content_error');
                        tracker();
                    }
                }
            });
        }
    });

    // Bind whatever event to Ajax loaded content
    //$(d).on('click', '.js-as', function(e) {
    //    console.log(e.target);
    //});
})();\n";

} else {
    echo "\n<script>";
}

// Optimized Universal Analytics http://mathiasbynens.be/notes/async-analytics-snippet
echo "\n(function(G,o,O,g,l){G.GoogleAnalyticsObject=O;G[O]||(G[O]=function(){(G[O].q=G[O].q||[]).push(arguments)});G[O].l=+new Date;g=o.createElement('script'),l=o.scripts[0];g.src='//www.google-analytics.com/analytics.js';l.parentNode.insertBefore(g,l)}(this,document,'ga'));
ga('create','UA-XXXX-Y','domain.com');
ga('send','pageview');
</script>";