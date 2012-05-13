<?php

// Add latest PHP functions
if (version_compare(PHP_VERSION, '5.4', '<')) {
    include('content/function.http-response-code.php');
}

// Gzip
if (!ob_start('ob_gzhandler')) {
    ob_start();
}

// Prevent XSS and SQL Injection
if (strpos($_SERVER['HTTP_HOST'], $_SERVER['SERVER_NAME']) === false) {
    http_response_code(400);
    header('X-Robots-Tag: none');
    header('Content-Type: text/plain');
    exit('400 Bad Request');
}

// Database settings
include('content/connect.php');

if (MYSQL_CON) {
    $result = mysql_query("SELECT url, `meta-title`, `meta-description`, `meta-keywords`, title, content FROM `" . MYSQL_TABLE . "` WHERE url = '$url'");
    
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
            $urlid			  = $row['url'];
            $title			  = isset($row['title']) ? $row['title'] : null;
            $meta_title		  = isset($row['meta-title']) ? $row['meta-title'] : $title;
            $meta_description = isset($row['meta-description']) ? $row['meta-description'] : null;
            $meta_keywords	  = isset($row['meta-keywords']) ? $row['meta-keywords'] : null;
            $content		  = isset($row['content']) ? $row['content'] : null;
        }
        $pretitle  = 'AJAX SEO';
        $pagetitle = $meta_title . ' - ' . $pretitle;
        
        // SEO page title improvement for the root page
        if (strlen($url) == 0) {
            $pagetitle = 'AJAX SEO';
        }
    } else {
        // Return 404 error, if url does not exist
        $validate		  = new validate($url);
        $validate -> status();
        $title			  = $validate->title;
        $pagetitle		  = $title;
        $meta_description = null;
        $meta_keywords	  = null;
        $content		  = $validate -> content;
    }
}

// Avoid undefined variables
$note               = isset($note) ? $note : null;
$title_installation = isset($title_installation) ? $title_installation : null;
$installation       = isset($installation) ? $installation : null;


echo "<!DOCTYPE html>
<html lang=en>
<head>
<meta charset=utf-8>
<title>$pagetitle</title>
<meta name=description content=\"$meta_description\">
<meta name=keywords content=\"$meta_keywords\">
<meta name=referrer content=never>
<meta name=viewport content=\"initial-scale=0.666, maximum-scale=0.666, width=device-width, target-densityDpi=high-dpi\">
<link rel=stylesheet href={$path}images/style.css>
<!--[if lt IE 9]><script src=//html5shiv.googlecode.com/svn/trunk/html5.js></script><![endif]-->
</head>
<body itemscope itemtype=http://schema.org/WebPage>\n";


if ($note !== null) {
    echo "<div class=note>$note</div>\n";
}


echo "<div class=\"container center-container\">
<header class=clearfix>
    <div><a class=logo href=//github.com/laukstein/ajax-seo rel=home>AJAX SEO{$title_installation} <small>Bring your App crawable</small></a></div>\n";


if (MYSQL_CON) {
    $result = mysql_query('SELECT url, `meta-title`, title FROM `' . MYSQL_TABLE . '` ORDER BY array ASC');
	
	if (mysql_num_rows($result)) {
		echo "	<nav class=\"nav clearfix\">\n";
		
		while ($row = @mysql_fetch_array($result, MYSQL_ASSOC)) {
			$row[] = array(
				'row' => array_map('htmlspecialchars', $row)
			);
			echo '      <a';
			
			if ($url == $row['url']) {
				echo ' class="selected transition"';
			} else {
				echo ' class=transition';
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
// If you use HTTPS, replace jQuery CDN source with Google CDN //ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js

// Return root path
$rootpath = (strlen(utf8_decode($path)) > 1) ? substr($path, 0, -1) : $path;

echo "<script src=http://code.jquery.com/jquery-1.7.2.min.js></script>
<script>window.jQuery || document.write('<script src={$path}images/jquery-1.7.2.min.js><\/script>')</script>
<script src={$path}images/jquery.address.js></script>
<script>
(function () {
    'use strict';

    var nav = $('.nav a'),
        content = $('.content'),
        init = true,
        state = window.history.pushState !== undefined,
        handler = function (data) {
            // Response
            document.title = data.pagetitle + '$pagetitle';
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
            
            var fadeTimer;
            
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
                    nav.removeAttr('class');
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