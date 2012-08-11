<?php

// Configuration
// --------------------------------------------------
include 'content/config.php';



// Connect to MySQL
// --------------------------------------------------
include 'content/connect.php';

if (MYSQL_CON) {
    $result = mysql_query("SELECT url, `meta-title`, `meta-description`, title, content FROM `" . MYSQL_TABLE . "` WHERE url = '$url'");

    // JSON/JSONP respond
    if (isset($_GET['api'])) {
        include 'content/api.php';
        exit;
    }

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
        $pretitle  = 'AJAX SEO';
        $pagetitle = $meta_title . ' - ' . $pretitle;

        // SEO page title improvement for the root page
        if (strlen($url) == 0) {
            $pagetitle = 'AJAX SEO';
        }
    } else {
        // Return 404 error, if url does not exist
        $validate         = new validate($url);
        $validate -> status();
        $title            = $validate -> title;
        $pagetitle        = $title;
        $pretitle         = null;
        $meta_description = null;
        $content          = $validate -> content;
    }
}

// Avoid undefined variables
$note               = isset($note) ? $note : null;
$title_installation = isset($title_installation) ? $title_installation : null;
$installation       = isset($installation) ? $installation : null;



// 160 character title       blogs.msdn.com/b/ie/archive/2012/05/14/sharing-links-from-ie10-on-windows-8.aspx
$meta_tags  = "<title>$pagetitle</title>";

// 253 character description blogs.msdn.com/b/ie/archive/2012/05/14/sharing-links-from-ie10-on-windows-8.aspx
$meta_tags .= "\n<meta name=description content=\"$meta_description\">";

// Declare the family friendly content schema.org/WebPage
$meta_tags .= "\n<meta itemprop=isFamilyFriendly content=true>";

// Save the copyright and keep out from Pinterest robots SEO affect blog.pinterest.com/post/17949261591/growing-up
$meta_tags .= "\n<meta name=pinterest content=nopin>";

// Perform speed and security on removing referrer-header-value wiki.whatwg.org/wiki/Meta_referrer
$meta_tags .= "\n<meta name=referrer content=never>";

// Return on mobile width 480px with same DPI like on desktop
$meta_tags .= "\n<meta name=viewport content=\"width=480, target-densityDpi=device-dpi\">";

// Authorship in Google Search support.google.com/webmasters/bin/answer.py?hl=en&answer=1408986
//$meta_tags .= "\n<link rel=author href=https://plus.google.com/u/0000000000000000000>";

// Save page loading time on pre-resolving the CDN domain name html5boilerplate.com/docs/DNS-Prefetching/
if ($issetcdn) {
    $meta_tags .= "\n<link rel=dns-prefetch href=$cdn>";
}



// Apply CSS developmenet or production minified version
if ($debug) {
    $path_css = $path.'images/style.css';
} else {
    $path_css = $cdn.'/images/style.min.css';
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
    <div><a class=logo href=//github.com/laukstein/ajax-seo rel=home>AJAX SEO{$title_installation} <small>Bring your App crawable</small></a></div>\n";


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


echo "    </header>\n<article class=article>\n    <span class=content>\n";


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


echo '    </span>
</article>
</div>
<footer class="footer center-container">
    <nav itemprop=breadcrumb>
        <a href=//github.com/laukstein/ajax-seo title="AJAX SEO Git Repository">Contribute on github</a> >
        <a href=//github.com/laukstein/ajax-seo/zipball/master title="Download AJAX SEO">Download</a> >
        <a href=//github.com/laukstein/ajax-seo/issues>Submit issue</a>
    </nav>
</footer>
';


if(MYSQL_CON){

// code.jquery.com Edgecast's CDN has better performance http://royal.pingdom.com/2010/05/11/cdn-performance-downloading-jquery-from-google-microsoft-and-edgecast-cdns/
// If you use HTTPS, replace jQuery CDN source with Google CDN //ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js

// Return root path
$rootpath = (strlen(utf8_decode($path)) > 1) ? substr($path, 0, -1) : $path;

echo "<script src=http://code.jquery.com/jquery-1.8.0.min.js></script>
<script>window.jQuery || document.write('<script src={$cdn}images/jquery-1.8.0.min.js><\/script>')</script>
<script src={$cdn}images/jquery.address.min.js></script>
<script>
(function () {
    'use strict';

    var nav = $('.js-as'),
        content = $('.content'),
        init = true,
        state = window.history.pushState !== undefined,
        handler = function (data) {
            // Response
            document.title = data.pagetitle + '$pretitle';
            content.fadeTo(20, 1).removeAttr('style').html(data.content);
            if ($.browser.msie) {
                content.removeAttr('filter');
            }

            // GA tracking
            //console.log('tracking');
            _gaq && _gaq.push(['_trackPageview']);
        };
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
            // Implement timeout
            var timer = window.setTimeout(function () {
                content.html('Loading seems to be taking a while...');
            }, 3800);

            var fadeTimer;

            // Load API content
            $.ajax({
                type: 'GET',
                url: 'api' + (e.path.length !== 1 ? '/' + encodeURIComponent(e.path.toLowerCase().substr(1)) : ''),
                // You maight switch it to 'jsonp'
                dataType: 'json',
                // Uncomment the next line in case you use 'jsonp'
                //jsonpCallback: 'i',
                cache: true,
                beforeSend: function () {
                    fadeTimer = setTimeout(function() {
                        //console.log('fading');
                        content.fadeTo(200, 0.33);
                    }, 300);
                },
                success: function (data, textStatus, jqXHR) {
                    if (fadeTimer) { clearTimeout(fadeTimer); }
                    window.clearTimeout(timer);
                    handler(data);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if (fadeTimer) { clearTimeout(fadeTimer); }
                    window.clearTimeout(timer);
                    nav.removeClass('selected');
                    document.title = 'Page not found';
                    content.fadeTo(20, 1).removeAttr('style').html('<h1>404 Not Found</h1><p>Sorry, this page cannot be found.</p>');
                    if ($.browser.msie) {
                        content.removeAttr('filter');
                    }
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