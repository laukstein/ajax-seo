<?php

// Add latest PHP functions
if (version_compare(PHP_VERSION, '5.4', '<')) {
    include('content/function.http-response-code.php');
}

// Prevent XSS and SQL Injection
if (strpos($_SERVER['HTTP_HOST'], $_SERVER['SERVER_NAME']) === false) {
    http_response_code(400);
    header('X-Robots-Tag: none');
    header('Content-Type: text/plain');
    exit('400 Bad Request');
}

// Gzip
if (!ob_start('ob_gzhandler')) {
    ob_start();
}

// Database settings
include('content/connect.php');

if (MYSQL_CON) {
    $result = mysql_query("SELECT url, name, title, content FROM " . MYSQL_TABLE . " WHERE url = '$url'");
    
    // JSON/JSONP respond
    if (isset($_GET['api'])) {
        include('content/api.php');
        exit;
    }
    
    // Check if url exist
    if (mysql_num_rows($result)) {
        // HTTP header caching
        include('content/cache.php');
        $datemod = new datemod();
        $datemod->date(array(
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
            $urlid     = $row['url'];
            $name      = $row['name'];
            $title     = $row['title'];
            $content   = $row['content'];
        }
        $pretitle  = 'AJAX SEO';
        $pagetitle = $name . ' - ' . $pretitle;
        
        // SEO page title improvement for the root page
        if (strlen($url) == 0) {
            $pagetitle = 'AJAX SEO';
        }
    } else {
        // Return 404 error, if url does not exist
        $validate = new validate($url);
        $validate->status();
        $title     = $validate->title;
        $pagetitle = $title;
        $content   = $validate->content;
    }
}

// Avoid undefined variables
$note               = isset($note) ? $note : null;
$title_installation = isset($title_installation) ? $title_installation : null;
$installation       = isset($installation) ? $installation : null;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset=utf-8>
<title><?php echo $pagetitle; ?></title>
<meta name=description content="AJAX SEO is crawlable framework for AJAX applications">
<meta name=keywords content="ajax, seo, crawlable, applications, performance, speed, accessibility, usability">
<!-- Secure less byte request without HTTP Referrer, http://wiki.whatwg.org/wiki/Meta_referrer -->
<meta name=referrer content=never>
<!-- Mobile UI - avoid user zoom, match UI width with device screen width -->
<meta name=viewport content="initial-scale=0.666, maximum-scale=0.666, width=device-width, target-densityDpi=high-dpi">
<link rel=stylesheet href=<?php echo $path; ?>images/style.css>
<!--[if lt IE 9]><script src=//html5shiv.googlecode.com/svn/trunk/html5.js></script><![endif]-->
</head>
<body itemscope itemtype=http://schema.org/WebPage>
<?php
if ($note == null) {
} else {
    echo "<div id=note>$note</div>";
}
?>
<div id=container>
<header>
<a id=logo href=//github.com/laukstein/ajax-seo rel=home>AJAX SEO<?php echo $title_installation; ?></a>
<nav class=header-nav>
<?php
if (MYSQL_CON) {
    $result = mysql_query('SELECT url, name, title FROM ' . MYSQL_TABLE . ' ORDER BY `order` ASC');
    while ($row = @mysql_fetch_array($result, MYSQL_ASSOC)) {
        $row[] = array(
            'row' => array_map('htmlspecialchars', $row)
        );
        echo '<a';
        
        if ($url == $row['url']) {
            echo ' class=selected';
        }
        
        echo " href=\"$path{$row['url']}\"";
        
        if ((strlen($row['title']) > 0) && ($row['name'] !== $row['title'])) {
            echo " title=\"{$row['title']}\"";
        }
        
        echo ">{$row['name']}</a>\r\n";
    }
}
?>
</nav>
<article>
<span id=content>
<?php
if (MYSQL_CON) {
    $name = isset($name) ? $name : null;
    if ((strlen($title) > 0) && ($name !== $title)) {
        $name = $title;
    }
    echo "<h1>$name</h1>\r\n<p>$content</p>\r\n";
    mysql_close($con);
} else {
    echo $installation;
}
?>
</span>
</article>
</header>
<footer>
<nav itemprop=breadcrumb>
    <a href=//github.com/laukstein/ajax-seo title="AJAX SEO Git Repository">Contribute on github</a> >
    <a href=//github.com/laukstein/ajax-seo/zipball/master title="Download AJAX SEO">Download</a> >
    <a href=//github.com/laukstein/ajax-seo/issues>Submit issue</a>
</nav>
</footer>
</div>
<?php if(MYSQL_CON){ ?>
<!-- code.jquery.com Edgecast's CDN has better performance http://royal.pingdom.com/2010/05/11/cdn-performance-downloading-jquery-from-google-microsoft-and-edgecast-cdns/
     If you use HTTPS, replace jQuery CDN source with Google CDN //ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js -->
<script src=http://code.jquery.com/jquery-1.7.2.min.js></script>
<script>window.jQuery || document.write('<script src=<?php echo $path; ?>images/jquery-1.7.2.min.js><\/script>')</script>
<script src=<?php echo $path; ?>images/jquery.address.js></script>
<script>
(function () {
    'use strict';

    var nav = $('.header-nav a'),
        content = $('#content'),
        init = true,
        state = window.history.pushState !== undefined,
        handler = function (data) {
            // Response
            document.title = data.pagetitle + '<?php echo $pretitle; ?>';
            content.fadeTo(20, 1).removeAttr('style').html(data.content);
            if ($.browser.msie) {
                content.removeAttr('filter');
            }
        };

    $.address.crawlable(1).state('<?php
if (strlen(utf8_decode($path)) > 1) {
    echo substr($path, 0, -1);
} else {
    echo $path;
}
?>').init(function () {
        // Initialize jQuery Address
        nav.address();
    }).change(function (e) {
        // Select nav link
        nav.each(function () {
            var link = $(this);
            if (link.attr('href') === (($.address.state() + decodeURI(e.path)).replace(/\/\//, '/'))) {
                link.addClass('selected').focus();
            } else {
                link.removeAttr('class');
            }
        });

        if (state && init) {
            init = false;
        } else {
            // Implement timeout
            var timer = window.setTimeout(function () {
                content.html('Loading seems to be taking a while...');
            }, 3800);

            // Load API content
            $.ajax({
                type: 'GET',
                url: // '//lab.alaukstein.com/ajax-seo/'+
                'api' + (e.path.length !== 1 ? '/' + encodeURIComponent(e.path.toLowerCase().substr(1)) : ''),
                // You maight switch it to 'jsonp'
                dataType: 'json',
                // Uncomment the next line in case you use 'jsonp'
                //jsonpCallback: 'i',
                cache: true,
                beforeSend: function () {
                    document.title = 'Loading...';
                    content.fadeTo(200, 0.33);
                },
                success: function (data, textStatus, jqXHR) {
                    window.clearTimeout(timer);
                    handler(data);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    window.clearTimeout(timer);
                    nav.removeAttr('class');
                    document.title = '404 Not Found - ' + '<?php echo $pretitle; ?>';
                    content.fadeTo(20, 1).removeAttr('style').html('<h1>404 Not Found</h1>\r<p>Sorry, this page cannot be found.</p>\r');
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
</script>
<?php } ?>
</body>
</html>