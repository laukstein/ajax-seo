<?php

// Configuration
// --------------------------------------------------
include 'content/config.php';



// Connect to MySQL
// --------------------------------------------------
include 'content/connect.php';

// Avoid XSS attacks with Content Security Policy (CSP) https://dvcs.w3.org/hg/content-security-policy/raw-file/tip/csp-specification.dev.html
header("Content-Security-Policy: script-src 'self' 'unsafe-inline' 'unsafe-eval' " . ($issetcdn ? $cdn_host . ' ' : null) . "cdnjs.cloudflare.com www.google-analytics.com");



$meta_description = null;

if (MYSQL_CON) {
    $result = mysql_query("SELECT url, `meta-title`, `meta-description`, title, content FROM `" . MYSQL_TABLE . "` WHERE url = '$url'");

    // JSON/JSONP respond
    if (isset($_GET['api'])) {
        include 'content/api.php';
        exit;
    }

    $pagetitle = $pagetitle_error = 'Page not found';
    $title     = $title_error     = '404 Not Found';
    $content   = $content_error   = '<p>Sorry, this page cannot be found.</p>';

    // Check if url exist
    if (@mysql_num_rows($result)) {
        // HTTP header caching
        include 'content/cache.php';
        $datemod = new datemod();
        $datemod -> date(array(
            '.htaccess',
            'index.php',
            'content/.htaccess',
            'content/connect.php',
            'content/api.php',
            'content/cache.php'
        ), MYSQL_TABLE, $url);
        $datemod->cache($datemod->gmtime);

        while ($row = @mysql_fetch_array($result, MYSQL_ASSOC)) {
            $row[]     = array(
                'row' => array_map('htmlspecialchars', $row)
            );

            $urlid            = $row['url'];
            $title            = isset($row['title']) ? $row['title'] : null;
            $meta_title       = isset($row['meta-title']) ? $row['meta-title'] : $title;
            $meta_description = isset($row['meta-description']) ? $row['meta-description'] : null;
            $content          = isset($row['content']) ? $row['content'] : null;
        }

        $pagetitle = $meta_title . ' - AJAX SEO';

        // SEO page title improvement for the root page
        if (strlen($url) == 0) {
            $pagetitle = 'AJAX SEO';
        }
    } else {
        // If URL does not exist, return 404 error
        http_response_code(404);
    }
}

// Avoid undefined variables
$note               = isset($note) ? $note : null;
$title_installation = isset($title_installation) ? $title_installation : null;
$installation       = isset($installation) ? $installation : null;



// 160 character title http://blogs.msdn.com/b/ie/archive/2012/05/14/sharing-links-from-ie10-on-windows-8.aspx
$metadata  = "<title>$pagetitle</title>";

// Open Graph protocol http://ogp.me
$metadata .= "\n<meta property=og:title content=\"$pagetitle\">";
// 253 character description http://blogs.msdn.com/b/ie/archive/2012/05/14/sharing-links-from-ie10-on-windows-8.aspx
$metadata .= "\n<meta name=description content=\"$meta_description\">";
$metadata .= "\n<meta property=og:description content=\"$meta_description\">";
// Twitter Cards https://dev.twitter.com/docs/cards
// $metadata .= "\n<meta property=twitter:card content=summary>"; // Twitterbot will crawl as default 'summary' when twitter:card is not set
$metadata .= "\n<meta property=og:url content=\"$uri\">";

// Declare the family friendly content http://schema.org/WebPage
$metadata .= "\n<meta itemprop=isFamilyFriendly content=true>";

// Opt-out of pinning by Pinterest, save copyrights and avoid SEO impact http://pinterest.com/about/help/#linking_faqs
// Return the meta tag just for Pinterest, since W3C validator will return it as a unregistered specification.
if (stripos($_SERVER['HTTP_USER_AGENT'], 'Pinterest') !== false) {
    $metadata .= "\n<meta name=pinterest content=nopin>";
}

// Perform speed and security on removing referrer-header-value http://wiki.whatwg.org/wiki/Meta_referrer
$metadata .= "\n<meta name=referrer content=never>";

// Optimize mobile device viewport and return the same pixel density like on desktop
// 2012-06-13 Dropped target-densityDpi and its translated CSS property resolution http://lists.w3.org/Archives/Public/www-style/2012Jun/0283.html, http://trac.webkit.org/changeset/119527
$metadata .= "\n<meta name=viewport content=\"width=device-width, user-scalable=0\">";

// Authorship in Google Search http://support.google.com/webmasters/bin/answer.py?hl=en&answer=1408986
//$metadata .= "\n<link rel=author href=https://plus.google.com/000000000000000000000>";

// Save DNS resolution time with DNS prefetching https://github.com/h5bp/html5-boilerplate/blob/master/doc/extend.md#dns-prefetching
// Prefetch own CDN
if ($issetcdn) {
    $metadata .= "\n<link rel=dns-prefetch href=" . $cdn_scheme . $cdn_host . '>';
}
// Prefetch EdgeCast's CDN
$metadata .= "\n<link rel=dns-prefetch href=//cdnjs.cloudflare.com>";
// Prefetch Google Analytics
$metadata .= "\n<link rel=dns-prefetch href=//www.google-analytics.com>";

// Website copyright license
$metadata .= "\n<link rel=license href=//creativecommons.org/licenses/by/3.0/>";


// Assets development and production minified versions
function path($filename) {
    global $debug, $assets;

    preg_match('/^(.+)\.([^\.]+)$/', $filename, $matches);
    $extension = $matches[2];
    $source    = $debug ? $assets . $filename : $assets . $matches[1] . '.min.' . $extension;

    if ($extension == 'css') {
        return "<link rel=stylesheet href=$source>";
    }
    if ($extension == 'js') {
        return "<script src=$source></script>";
    }
}
$assets_style   = path('style.css');
$assets_address = path('jquery.address.js');

// Working on Cache Manifest
// Chrome Application Cache manifest .appcache issue http://crbug.com/167918
// <html itemscope itemtype=http://schema.org/WebPage manifest=manifest.appcache>

echo "<!DOCTYPE html>
<html itemscope itemtype=http://schema.org/WebPage>
<head>
<meta charset=UTF-8>
$metadata
$assets_style
<!--[if lt IE 9]><script src=//html5shiv.googlecode.com/svn/trunk/html5.js></script><![endif]-->
</head>
<body class=clearfix>\n";


if ($note !== null) {
    // Yahoo since 2007 seems to be supporting the feature to exclude content from search engine's index with class=robots-nocontent http://www.ysearchblog.com/2007/05/02/introducing-robots-nocontent-for-page-sections/
    // Yandex supports the same feature on using HTML non standard element <noindex>to exclude content from indexing</noindex> and <!--noindex-->to do the same<!--/noindex--> http://help.yandex.ru/webmaster/?id=1111858

    echo "<!--noindex--><div class=note>$note</div><!--/noindex-->\n";
}


echo "<div class=\"ui-center container\">
<header class=clearfix>
    <a class=logo href=https://github.com/laukstein/ajax-seo rel=home>AJAX SEO{$title_installation}</a>\n";


if (MYSQL_CON) {
    $result = mysql_query('SELECT url, `meta-title`, title FROM `' . MYSQL_TABLE . '` ORDER BY array ASC');

    if (@mysql_num_rows($result)) {
        echo '    <nav class="clearfix list">';

        while ($row = @mysql_fetch_array($result, MYSQL_ASSOC)) {
            $row[] = array(
                'row' => array_map('htmlspecialchars', $row)
            );
            echo "\n        <a class=\"transition item js-as";

            $data_url       = $row['url'];
            $data_title     = $row['title'];
            $data_metatitle = $row['meta-title'];

            if ($url == $data_url) {
                echo ' selected';
            }

            echo "\" href=\"$path$data_url\"";

            if (strlen($data_title) > 0 && $data_metatitle !== $data_title) {
                echo " title=\"{$data_title}\"";
            }

            $nav_fn = strlen($data_url) > 0 ? $data_metatitle :  "<span class=home>$data_metatitle</span>";
            echo ">$nav_fn</a>";
        }
        echo "\n    </nav>\n";
    }
}


echo "</header>
<div class=\"main js-content\"><main role=main>\n";


if (MYSQL_CON) {
    $meta_title = isset($meta_title) ? $meta_title : null;
    if ((strlen($title) > 0) && ($meta_title !== $title)) {
        $meta_title = $title;
    }
    echo "      <h1>$meta_title</h1>\n      $content\n";
    mysql_close($con);
} else {
    echo $installation;
}


echo '</main></div>
</div>
<footer class="ui-center footer">
    <nav class=breadcrumb itemprop=breadcrumb>
        <a href=https://github.com/laukstein/ajax-seo>Contribute on github</a> >
        <a href=https://github.com/laukstein/ajax-seo/zipball/master>Download</a> >
        <a href=https://github.com/laukstein/ajax-seo/issues>Submit issue</a>
    </nav>
</footer>';


if(MYSQL_CON){

// Comparing CDNs
// Cloudflare's cdnJS is better than Google CDN http://www.baldnerd.com/make-your-site-faster-cloudflares-cdnjs-vs-google-hosted-libraries-shocking-results/
// jQuery EdgeCast's CDN better than Google, Microsoft and Media Temple CDN http://royal.pingdom.com/2012/07/24/best-cdn-for-jquery-in-2012/

echo "\n<!--[if lt IE 9]><script src=//cdnjs.cloudflare.com/ajax/libs/jquery/1.10.1/jquery.min.js></script><![endif]-->
<!--[if gte IE 9]><!--><script src=//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.2/jquery.min.js></script><!--<![endif]-->
$assets_address
<script>
(function() {
    'use strict';

    // Common variables
    // --------------------------------------------------
    var isDevice = /mobile|android/i.test(navigator.userAgent.toLowerCase()),

        // Remove 300ms click delay on mobile devices by using touchstart event
        // Usage: $(selector).on(pointer, (function() { });
        pointer  = isDevice ? 'touchstart' : 'click',

        \$nav     = $('.js-as'),
        \$content = $('.js-content'),
        init     = true,
        state    = window.history.pushState !== undefined,
        // Google Universal Analytics tracking
        tracker  = function() {
            if (typeof ga === 'function') {
                return ga && ga('send', 'pageview', {
                    // window.location.pathname + window.location.search + window.location.hash
                    page: decodeURI(window.location.pathname)
                });
            }
        },
        \$this, request, fadeTimer,
        // Response
        handler = function(data) {
            if (fadeTimer) {
                clearTimeout(fadeTimer);
            }

            document.title = data.pagetitle;
            \$content.fadeTo(20, 1).html(data.content);
            tracker();
        };



    // Avoid console on devices and not supported browsers
    if (!window.console || isDevice) {
        console = {
            log: function() {}
        };
    }



    // Mobile optimization
    // --------------------------------------------------
    if (isDevice) {
        // Auto-hide mobile device address bar
        if (window.location.hash.indexOf('#') === -1) {
            var hideAddressbar = function() {
                var deviceHeight = screen.height,
                    bodyHeight   = document.body.clientHeight;

                // Viewport height at fullscreen
                // Android 2.3 orientationchange issue - needs for more 50px
                if (deviceHeight >= bodyHeight) {
                    document.body.style.minHeight = deviceHeight + 'px';
                }

                // Perform autoscroll
                setTimeout(window.scrollTo(0, 1), 100);
            };

            // Auto-hide address bar
            hideAddressbar();

            // Hide address bar on device orientationchange
            window.addEventListener('orientationchange', function() {
                // Hide address bar if not already scrolled
                if (window.pageYOffset === 0) {
                    hideAddressbar();
                }
            });
        }
    }

    $.address.state('$rootpath').init(function() {
        // Initialize jQuery Address
        \$nav.address();
    }).change(function(e) {
        if (state && init) {
            init = false;
        } else {
            // Halt previously created request
            if (request && request.readyState !== 4) {
                request.abort();
            }

            // Select link
            \$nav.each(function() {
                \$this = $(this);

                if (\$this.attr('href') === (($.address.state() + decodeURI(e.path)).replace(/\/\//, '/'))) {
                    \$this.addClass('selected').focus();
                } else {
                    \$this.removeClass('selected');
                }
            });

            // Load API content
            request = $.ajax({
                url: '$rootpath/api' + (e.path.length !== 1 ? '/' + e.path.substr(1) : ''),
                //dataType: 'jsonp',
                crossDomain: true,
                cache: true,
                jsonpCallback: 'foo',
                beforeSend: function() {
                    fadeTimer = setTimeout(function() {
                        \$content.fadeTo(200, 0.33);
                    }, 300);
                },
                success: handler,
                error: function(jqXHR, textStatus) {
                    if (fadeTimer) {
                        clearTimeout(fadeTimer);
                    }
                    if (textStatus !== 'abort') {
                        console.log(textStatus);

                        if (textStatus === 'timeout') {
                            content.html('Loading seems to be taking a while...');
                        }

                        \$nav.removeClass('selected');
                        document.title = '$pagetitle_error';
                        \$content.fadeTo(20, 1).html('<h1>$title_error</h1>$content_error');
                        tracker();
                    }
                }
            });
        }
    });

    // Bind whatever event to Ajax loaded content
    //$(document).on('click', '.js-as', function(e) {
    //    console.log(e.target);
    //});
})();\n\n\n";

} else {
    echo "\n<script async>\n";
}

// Optimized Google Analytics snippet http://mathiasbynens.be/notes/async-analytics-snippet
echo "(function(G,o,O,g,l){G.GoogleAnalyticsObject=O;G[O]||(G[O]=function(){(G[O].q=G[O].q||[]).push(arguments)});G[O].l=+new Date;g=o.createElement('script'),l=o.scripts[0];g.src='//www.google-analytics.com/analytics.js';l.parentNode.insertBefore(g,l)}(this,document,'ga'));
ga('create','UA-XXXX-Y');
ga('send','pageview');
</script>";

echo "\n</body>\n</html>";
