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

// 253 character description http://blogs.msdn.com/b/ie/archive/2012/05/14/sharing-links-from-ie10-on-windows-8.aspx
$meta_tags .= "\n<meta name=description content=\"$meta_description\">";

// Declare the family friendly content http://schema.org/WebPage
$meta_tags .= "\n<meta itemprop=isFamilyFriendly content=true>";

// Opt-out of pinning by Pinterest, save copyrights and avoid SEO impact http://pinterest.com/about/help/#linking_faqs
$meta_tags .= "\n<meta name=pinterest content=nopin>";

// Perform speed and security on removing referrer-header-value http://wiki.whatwg.org/wiki/Meta_referrer
$meta_tags .= "\n<meta name=referrer content=never>";

// Return on mobile width 480px with same DPI like on desktop
$meta_tags .= "\n<meta name=viewport content=\"width=480\">";

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



// Assets development and production minified version.
if ($debug) {
    $path_css = $assets.'/images/style.css';
    $path_js  = $assets.'/images/jquery.address.js';
} else {
    $path_css = $assets.'/images/style.min.css';
    $path_js  = $assets.'/images/jquery.address.min.js';
}



echo "<!DOCTYPE html>
<html lang=en itemscope itemtype=http://schema.org/WebPage>
<head>
<meta charset=UTF-8>
$meta_tags
<link rel=stylesheet href=$path_css>
<!--[if lt IE 9]><script src=//html5shiv.googlecode.com/svn/trunk/html5.js></script><![endif]-->
</head>
<body class=clearfix>\n";


if ($note !== null) {
    // Yahoo since 2007 seems to be supporting the feature to exclude content from search engine's index with class=robots-nocontent http://www.ysearchblog.com/2007/05/02/introducing-robots-nocontent-for-page-sections/
    // Yandex supports the same feature on using HTML non standard element <noindex>to exclude content from indexing</noindex> and <!--noindex-->to do the same<!--/noindex--> http://help.yandex.ru/webmaster/?id=1111858

    echo "<!--noindex--><div class=note>$note</div><!--/noindex-->\n";
}


echo "<div class=\"container center-container\">
<header class=clearfix>
    <div><a class=logo href=https://github.com/laukstein/ajax-seo rel=home>AJAX SEO{$title_installation} <small>Bring your App crawable</small></a></div>\n";


if (MYSQL_CON) {
    $result = mysql_query('SELECT url, `meta-title`, title FROM `' . MYSQL_TABLE . '` ORDER BY array ASC');

    if (mysql_num_rows($result)) {
        echo "  <nav class=\"nav clearfix\">\n";

        while ($row = @mysql_fetch_array($result, MYSQL_ASSOC)) {
            $row[] = array(
                'row' => array_map('htmlspecialchars', $row)
            );
            echo '      <a';

            if ($url == $row['url']) {
                echo ' class="js-as selected"';
            } else {
                echo ' class=js-as';
            }

            echo " href=\"$path{$row['url']}\"";

            if ((strlen($row['title']) > 0) && ($row['meta-title'] !== $row['title'])) {
                echo " title=\"{$row['title']}\"";
            }
            $nav_fn  = (strlen($row['url']) > 0) ? $row['meta-title'] :  "<span class=home>{$row['title']}</span>";
            echo ">$nav_fn</a>\r\n";
        }
        echo "    </nav>\n";
    }
}


echo "    </header>\n<article class=article>\n    <div class=\"content js-content\">\n";


if (MYSQL_CON) {
    $meta_title = isset($meta_title) ? $meta_title : null;
    if ((strlen($title) > 0) && ($meta_title !== $title)) {
        $meta_title = $title;
    }
    echo "      <h1>$meta_title</h1>\r\n        <p>$content</p>\r\n";
    mysql_close($con);
} else {
    echo $installation;
}


echo '    </div>
</article>
</div>
<footer class="footer center-container">
    <nav itemprop=breadcrumb>
        <a href=https://github.com/laukstein/ajax-seo title="AJAX SEO Git Repository">Contribute on github</a> >
        <a href=https://github.com/laukstein/ajax-seo/zipball/master title="Download AJAX SEO">Download</a> >
        <a href=https://github.com/laukstein/ajax-seo/issues>Submit issue</a>
    </nav>
</footer>
';


if(MYSQL_CON){

// code.jquery.com EdgeCast's CDN has the best performance http://royal.pingdom.com/2012/07/24/best-cdn-for-jquery-in-2012/
// In case you use HTTPS replace it with Google CDN //ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js

echo "<script src=http://code.jquery.com/jquery-1.8.1.min.js></script>
<script>window.jQuery || document.write('<script src=$assets/images/jquery-1.8.1.min.js><\/script>')</script>
<script src=$path_js></script>
<script>
(function () {
    'use strict';


    // Common variables
    var pageYOffset = null,
        nav         = $('.js-as'),
        content     = $('.js-content'),
        init        = true,
        state       = window.history.pushState !== undefined,
        // Response
        handler     = function (data) {
            document.title = data.pagetitle;
            content.fadeTo(20, 1).html(data.content);

            // GA tracking
            _gaq && _gaq.push(['_trackPageview']);
        };


    // Hide mobile device address bar
    if  (/mobile/i.test(navigator.userAgent) && !pageYOffset && !location.hash) {
        window.scrollTo(0, 1);
    }


    $.address.tracker(function () {}).crawlable(1).state('$rootpath').init(function () {
        // Initialize jQuery Address
        nav.address();
    }).change(function (e) {
        // Select nav link
        nav.each(function () {
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
                type: 'GET',
                url: 'api' + (e.path.length !== 1 ? '/' + e.path.toLowerCase().substr(1) : ''),
                dataType: 'json',
                // jsonpCallback: 'i',
                cache: true,
                timeout: 3800,
                beforeSend: function () {
                    fadeTimer = setTimeout(function() {
                        content.fadeTo(200, 0.33);
                    }, 300);
                },
                success: function (data, textStatus, jqXHR) {
                    if (fadeTimer) {
                        clearTimeout(fadeTimer);
                    }

                    handler(data);
                },
                error: function (jqXHR, textStatus, errorThrown) {
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
(function (d, t) {
    'use strict';
    var g = d.createElement(t),
        s = d.getElementsByTagName(t)[0];
    g.src = '//www.google-analytics.com/ga.js';
    s.parentNode.insertBefore(g, s);
}(document, 'script'));
</script>\n";

}

echo "</body>\n</html>";

?>