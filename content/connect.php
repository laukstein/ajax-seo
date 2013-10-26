<?php
//
// MySQL settings
//

define('hostname', 'localhost');
define('username', 'root');
define('password', '');
define('database', 'test');
define('table', 'ajax-seo');
define('connection', false);
define('cdn', null);
define('title', 'Ajax SEO');

// Path for static assets
$issetcdn   = cdn ? true : false;
$cdn_host   = parse_url(cdn, PHP_URL_HOST);
$cdn_scheme = parse_url(cdn, PHP_URL_SCHEME);
$cdn_scheme = isset($cdn_scheme) ? $cdn_scheme . '://' : '//';
$cdn_uri    = $cdn_scheme . $cdn_host;
$debug      = isset($debug) ? $debug : null;
$path       = isset($path) ? $path : null;
$assets     = $debug ? $path . 'assets/' : ($issetcdn ? cdn : $path . 'assets/');

// Connect to database
$mysqli  = @new mysqli(hostname, username, password, database);
$results = false;
$f       = __FILE__;
$conn    = $mysqli->connect_errno ? false : true;
$note    = null;

if ($conn) {
    $result = $mysqli->query("SHOW TABLES LIKE '" . table . "'");
    $conn   = $result->num_rows ? true : false;
    $result->close();
}
if ($conn) {
    if (!connection) {
        // Yahoo since 2007 seems to be supporting the feature to exclude content from search engine's index with class=robots-nocontent http://www.ysearchblog.com/2007/05/02/introducing-robots-nocontent-for-page-sections/
        // Yandex supports the same feature on using HTML non standard element <noindex>to exclude content from indexing</noindex> and <!--noindex-->to do the same<!--/noindex--> http://help.yandex.ru/webmaster/?id=1111858

        $note = "\n<style>
/* @keyframes currently not supported in scoped style */
@-webkit-keyframes slide-down { /* Webkit legacy on mobile devices */
    0% {
        -webkit-transform: translateY(-110%);
    }
    10% {
        -webkit-transform: translateY(0);
    }
    90% {
        -webkit-transform: translateY(0);
    }
    100% {
        -webkit-transform: translateY(-110%);
    }
}
@keyframes slide-down {
    0%, 100% {
        transform: translateY(-110%);
    }
    10%, 90% {
        transform: translateY(0);
    }
}
.note {
    overflow: hidden;
    position: absolute;
    z-index: 10000;
    top: 0;
    left: 0;
    right: 0;
    padding: 0 3%;
    line-height: 2.5;
    text-align: center;
    text-overflow: ellipsis;
    text-shadow: 1px 1px 0 rgba(255,255,255,.6);
    white-space: nowrap;
    background-color: #ffdb18;
    border-bottom: 1px solid #ffe65c;
    box-shadow: 0 0 .3em rgba(0,0,0,.6);
    -webkit-transition-duration: .3s;
            transition-duration: .3s;
    -webkit-animation: slide-down 4s forwards;
            animation: slide-down 4s forwards;
}
#note:checked ~ .note {
    -webkit-transform: translateY(-110%);
            transform: translateY(-110%);
    -webkit-animation: inherit;
            animation: inherit;
}
</style>
<!--noindex-->
<input id=note type=checkbox hidden>
<label for=note class=note>Congratulations on successful installation</label>
<!--/noindex-->";
        $string = preg_replace("/define\('(connection)', false\);/", "define('$1', true);", file_get_contents($f));
        $fopen  = fopen($f, 'w');
        fwrite($fopen, $string);
        fclose($fopen);
    }
} else {
    // SEO friendly blackout status http://googlewebmastercentral.blogspot.com/2011/01/how-to-deal-with-planned-site-downtime.html
    // Website outages and blackouts the right way https://plus.google.com/115984868678744352358/posts/Gas8vjZ5fmB
    // Valid indexing & serving directives https://developers.google.com/webmasters/control-crawl-index/docs/robots_meta_tag

    header('Retry-After: 60'); // Try to reach server after 1 minute
    header('X-Robots-Tag: none');

    function refresh() {
        global $path;
        ob_end_clean();
        header('Location: ' . $path);
        exit;
    }

    if (connection) {
        $string = preg_replace("/define\('(connection)', true\);/", "define('$1', false);", file_get_contents($f));
        $fopen  = fopen($f, 'w');
        fwrite($fopen, $string);
        fclose($fopen);
        refresh();
    } else {
        if ($path !== $request_uri) refresh();
    }

    include 'content/install.php';
}