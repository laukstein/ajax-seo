<?php

// Configuration
// --------------------------------------------------
include 'content/config.php';



// Connect to MySQL
// --------------------------------------------------
include 'content/connect.php';

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
    $content   = $content_error   = 'Sorry, this page cannot be found.';

    // Check if url exist
    if (mysql_num_rows($result)) {
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
$meta_tags  = "<title>$pagetitle</title>";

// Open Graph protocol http://ogp.me
$meta_tags .= "\n<meta property=og:title content=\"$pagetitle\">";
// 253 character description http://blogs.msdn.com/b/ie/archive/2012/05/14/sharing-links-from-ie10-on-windows-8.aspx
$meta_tags .= "\n<meta name=description content=\"$meta_description\">";
$meta_tags .= "\n<meta property=og:description content=\"$meta_description\">";
// Twitter Cards https://dev.twitter.com/docs/cards
// $meta_tags .= "\n<meta property=twitter:card content=summary>"; // Twitterbot will crawl as default 'summary' when twitter:card is not set
$https      = empty($_SERVER['HTTPS']) ? null : ($_SERVER['HTTPS'] == 'on') ? 's' : null;
$fullurl    = 'http' . $https . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$meta_tags .= "\n<meta property=og:url content=\"$fullurl\">";

// Declare the family friendly content http://schema.org/WebPage
$meta_tags .= "\n<meta itemprop=isFamilyFriendly content=true>";

// Opt-out of pinning by Pinterest, save copyrights and avoid SEO impact http://pinterest.com/about/help/#linking_faqs
// Return the meta tag just for Pinterest, since W3C validator will return it as a unregistered specification.
if (stripos($_SERVER['HTTP_USER_AGENT'], 'Pinterest') !== false) {
    $meta_tags .= "\n<meta name=pinterest content=nopin>";
}

// Perform speed and security on removing referrer-header-value http://wiki.whatwg.org/wiki/Meta_referrer
$meta_tags .= "\n<meta name=referrer content=never>";

// Optimize mobile device viewport and return the same pixel density like on desktop
// 2012-06-13 Dropped target-densityDpi and its translated CSS property resolution http://lists.w3.org/Archives/Public/www-style/2012Jun/0283.html, http://trac.webkit.org/changeset/119527
$meta_tags .= "\n<meta name=viewport content=\"width=device-width, user-scalable=0\">";

// Authorship in Google Search http://support.google.com/webmasters/bin/answer.py?hl=en&answer=1408986
//$meta_tags .= "\n<link rel=author href=https://plus.google.com/000000000000000000000/posts>";

// Save page loading time with DNS prefetching https://github.com/h5bp/html5-boilerplate/blob/master/doc/extend.md#dns-prefetching
// Prefetch own CDN
if ($issetcdn) {
    $meta_tags .= "\n<link rel=dns-prefetch href=$assets>";
}
// Prefetch EdgeCast's CDN
$meta_tags .= "\n<link rel=dns-prefetch href=http://code.jquery.com>";
// Prefetch Google CDN
// $meta_tags .= "\n<link rel=dns-prefetch href=//ajax.googleapis.com>";
// Prefetch Google Analytics
$meta_tags .= "\n<link rel=dns-prefetch href=//www.google-analytics.com>";


// Assets development and production minified versions
function path($filename) {
    global $debug, $assets;

    if ($debug) {
        return $assets.'/images/'.$filename;
    } else {
        preg_match('/^(.+)\.([^\.]+)$/', $filename, $matches);
        return $assets.'/'.$matches[1].'.min.'.$matches[2];
    }
}
$assets_style        = path('style.css');
$assets_address      = path('jquery.address.js');
$assets_touchtoclick = path('jquery.touchtoclick.js');


// Working on Cache Manifest
// <html itemscope itemtype=http://schema.org/WebPage manifest=manifest.appcache>

echo "<!DOCTYPE html>
<html itemscope itemtype=http://schema.org/WebPage>
<head>
<meta charset=UTF-8>
$meta_tags
<link rel=stylesheet href=$assets_style>
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
    <div class=wrapper-logo><a class=logo href=https://github.com/laukstein/ajax-seo rel=home>AJAX SEO{$title_installation} <small>Bring your App crawable</small></a></div>\n";


if (MYSQL_CON) {
    $result = mysql_query('SELECT url, `meta-title`, title FROM `' . MYSQL_TABLE . '` ORDER BY array ASC');

    if (mysql_num_rows($result)) {
        echo "    <div class=wrapper-nav>\n        <nav class=\"clearfix list-nav\">\n            ";

        while ($row = @mysql_fetch_array($result, MYSQL_ASSOC)) {
            $row[] = array(
                'row' => array_map('htmlspecialchars', $row)
            );
            echo '<a class="transition item js-as';

            if ($url == $row['url']) {
                echo ' selected';
            }

            echo "\" href=\"$path{$row['url']}\"";

            if ((strlen($row['title']) > 0) && ($row['meta-title'] !== $row['title'])) {
                echo " title=\"{$row['title']}\"";
            }
            $nav_fn  = (strlen($row['url']) > 0) ? $row['meta-title'] :  "<span class=home>{$row['title']}</span>";
            echo ">$nav_fn</a>";
        }
        echo "\n        </nav>\n    </div>\n";
    }
}


echo "    </header>\n<article class=article>\n    <div class=\"content js-content\">\n";


// Check if Apache mod_rewrite is enabled
if (in_array('rewrite_module', apache_get_modules())) {
    if (MYSQL_CON) {
        $meta_title = isset($meta_title) ? $meta_title : null;
        if ((strlen($title) > 0) && ($meta_title !== $title)) {
            $meta_title = $title;
        }
        echo "      <h1>$meta_title</h1>\n        <p>$content</p>\n";
        mysql_close($con);
    } else {
        echo $installation;
    }
} else {
    echo 'To use the framework you need to enable at least Apache mod_rewrite. Fallow the configuration in config/httpd.conf';
}


echo '    </div>
</article>
</div>
<footer class="ui-center footer">
    <nav class=breadcrumb itemprop=breadcrumb>
        <a href=https://github.com/laukstein/ajax-seo>Contribute on github</a> >
        <a href=https://github.com/laukstein/ajax-seo/zipball/master>Download</a> >
        <a href=https://github.com/laukstein/ajax-seo/issues>Submit issue</a>
    </nav>
</footer>';


if(MYSQL_CON){

// code.jquery.com EdgeCast's CDN has the best performance http://royal.pingdom.com/2012/07/24/best-cdn-for-jquery-in-2012/
// In case you use HTTPS replace it with Google CDN //ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js

echo "\n<script src=http://code.jquery.com/jquery-1.8.2.min.js></script>
<script>window.jQuery || document.write('<script src=$assets/images/jquery-1.8.2.min.js><\/script>')</script>
<script src=$assets_address></script>
<script src=$assets_touchtoclick></script>
<script>
(function() {
    'use strict';

    // Common variables
    var nav         = $('.js-as'),
        content     = $('.js-content'),
        init        = true,
        state       = window.history.pushState !== undefined,
        // Response
        handler     = function(data) {
            document.title = data.pagetitle;
            content.fadeTo(20, 1).html(data.content);

            // Google Analytics tracking
            return _gaq && _gaq.push(['_trackPageview']);
        };


    // Auto-hide mobile device address bar
    if (/mobile/i.test(navigator.userAgent) && window.location.hash.indexOf('#') === -1) {
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


    $.address.tracker(function() {}).crawlable(1).state('$rootpath').init(function() {
        // Initialize jQuery Address
        nav.address();
    }).change(function(e) {
        // Select nav link
        nav.each(function() {
            var link = $(this);

            if (link.attr('href') === (($.address.state() + decodeURI(e.path)).replace(/\/\//, '/'))) {
                link.addClass('selected').focus();
            } else {
                link.removeClass('selected');
            }
        });

        if (state && init) {
            init = false;
        } else {
            var fadeTimer;

            // Load API content
            $.ajax({
                url: 'api' + (e.path.length !== 1 ? '/' + e.path.substr(1) : ''),
                dataType: 'jsonp',
                crossDomain: true,
                cache: true,
                jsonpCallback: 'foo',
                timeout: 3800,
                beforeSend: function() {
                    fadeTimer = setTimeout(function() {
                        content.fadeTo(200, 0.33);
                    }, 300);
                },
                success: function(data) {
                    if (fadeTimer) {
                        clearTimeout(fadeTimer);
                    }

                    handler(data);
                },
                error: function(jqXHR, textStatus) {
                    nav.removeClass('selected');

                    if (fadeTimer) {
                        clearTimeout(fadeTimer);
                    }

                    if (textStatus === 'timeout') {
                        content.html('Loading seems to be taking a while...');
                    }

                    document.title = '$pagetitle_error';
                    content.fadeTo(20, 1).html('<h1>$title_error</h1><p>$content_error</p>');
                }
            });
        }
    });
})();


// Optimized Google Analytics snippet, http://mathiasbynens.be/notes/async-analytics-snippet
var _gaq = [
    ['_setAccount', 'UA-XXXXX-X'],
    ['_trackPageview']
];
(function(d, t) {
    'use strict';
    var g = d.createElement(t),
        s = d.getElementsByTagName(t)[0];
    g.src = '//www.google-analytics.com/ga.js';
    s.parentNode.insertBefore(g, s);
}(document, 'script'));
</script>\n";

}

echo "</body>\n</html>";