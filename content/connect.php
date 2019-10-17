<?php
//
// Connect to database
//

$mysqli = @new mysqli(hostname, username, password, database, port);
$conn   = !$mysqli->connect_errno && !!$mysqli->query('SELECT 1 FROM `' . table . '` LIMIT 0');
$note   = null;

// Path for assets
$cdn_host   = parse_url(assets, PHP_URL_HOST);
$cdn_host   = $cdn_host === $host ? null : $cdn_host;
$cdn_scheme = parse_url(assets, PHP_URL_SCHEME);
$cdn_scheme = $cdn_scheme ? $cdn_scheme . '://' : '//';

if ($conn) {
    if (!connection) {
        // Yahoo since 2007 supports content exclude from search engine's index with class=robots-nocontent http://www.ysearchblog.com/2007/05/02/introducing-robots-nocontent-for-page-sections/
        // Yandex supports content exclude on using non-standard element <noindex></noindex> and conditional comment <!--noindex--><!--/noindex--> https://yandex.com/support/webmaster/controlling-robot/html.html#noindex

        header('Cache-Control: no-cache, no-store, must-revalidate'); // Chrome issue https://code.google.com/p/chromium/issues/detail?id=2763, requires "no-store" to avoid cache

        $note = "\n<style nonce=\"MN+nJYptMzWJvlkA0FFLXQ==\" scoped>@-webkit-keyframes slide-down{0%{-webkit-transform:translateY(-110%);transform:translateY(-110%)}10%,90%{-webkit-transform:translateY(0);transform:translateY(0)}100%{-webkit-transform:translateY(-110%);transform:translateY(-110%)}}@keyframes slide-down{0%,100%{transform:translateY(-110%)}10%,90%{transform:translateY(0)}}.note{overflow:hidden;position:fixed;z-index:10000;top:0;left:0;right:0;padding:.8em 3%;text-align:center;text-overflow:ellipsis;text-shadow:1px 1px 0 rgba(255,255,255,.7);white-space:nowrap;background-color:#fd0;border:0 solid transparent;border-width:0 1em;box-sizing:border-box;-webkit-transition-duration:.3s;transition-duration:.3s;-webkit-animation:slide-down 4s forwards;animation:slide-down 4s forwards}.note:hover{-webkit-transform:translateY(0);transform:translateY(0)}#note:checked~.note{-webkit-transform:translateY(-110%);transform:translateY(-110%);-webkit-animation:inherit;animation:inherit}</style>
<!--noindex-->
<input id=note type=checkbox hidden>
<label for=note class=note>Congratulations for successful installation</label>
<!--/noindex-->";
        $string = preg_replace("/define\('(connection)', false\);/", "define('$1', true);", file_get_contents($file));
        $fopen  = fopen($file, 'w');

        fwrite($fopen, $string);
        fclose($fopen);
    }
} else {
    // Not reachable database
    // SEO friendly blackout status https://googlewebmastercentral.blogspot.com/2011/01/how-to-deal-with-planned-site-downtime.html
    // Website outages and blackouts the right way https://plus.google.com/115984868678744352358/posts/Gas8vjZ5fmB
    // CloudFlare doesn't cache 5XX error pages
    http_response_code(503);
    // Try to reach server after 1 minute
    header('Retry-After: 60');
    // Valid indexing & serving directives https://developers.google.com/webmasters/control-crawl-index/docs/robots_meta_tag
    header('X-Robots-Tag: none');

    function refresh() {
        global $safepath;
        ob_end_clean();
        header('Location: ' . $safepath);
        exit;
    }

    if (connection) {
        $string = preg_replace("/define\('(connection)', true\);/", "define('$1', false);", file_get_contents($file));
        $fopen  = fopen($file, 'w');

        fwrite($fopen, $string);
        fclose($fopen);
        refresh();
    } else if (!$ishome) {
        refresh();
    }

    include 'content/install.php';
}
