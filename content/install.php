<?php

// Define MySQL connection status
// --------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'GET' && MYSQL_CON) {
    $change = file_get_contents($f);
    $change = preg_replace("/define\('(MYSQL_CON)', true\);/", "define('$1', false);", $change);
    $change = preg_replace("/define\('(MYSQL_ERROR)', true\);/", "define('$1', false);", $change);

    // Change connect.php file permissions if needed
    if (!@is_writable($f)) {
        @chmod($f, 0755);
    }
    $fopen = fopen($f, 'w');
    fwrite($fopen, $change);
    fclose($fopen);

    header("Location: {$_SERVER['REQUEST_URI']}");
    exit;
}

$error = null;

// Add MySQL settings
// --------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $change = file_get_contents($f);

    $db   = trim($_POST['db']);
    $host = trim($_POST['host']);
    $user = trim($_POST['user']);
    $pass = trim($_POST['pass']);

    $change = preg_replace("/define\('(MYSQL_DB)', '(.*)'\);/", "define('$1', '" . $db . "');", $change);
    $change = preg_replace("/define\('(MYSQL_HOST)', '(.*)'\);/", "define('$1', '" . $host . "');", $change);
    $change = preg_replace("/define\('(MYSQL_USER)', '(.*)'\);/", "define('$1', '" . $user . "');", $change);
    $change = preg_replace("/define\('(MYSQL_PASS)', '(.*)'\);/", "define('$1', '" . $pass . "');", $change);
    $change = preg_replace("/define\('(MYSQL_TABLE)', '(.*)'\);/", "define('$1', '" . trim($_POST['table']) . "');", $change);
    $change = preg_replace("/define\('(MYSQL_ERROR)', false\);/", "define('$1', true);", $change);
    $change = preg_replace("/define\('(CDN_PATH)', null\);/", "define('$1', " . ((strlen($_POST['cdnpath']) > 0) ? "'". trim($_POST['cdnpath']) . "'"  : 'null') . ");", $change);

    // Change connect.php file permissions if needed
    if (!@is_writable($f)) {
        @chmod($f, 0755);
    }
    $fopen = fopen($f, 'w');
    fwrite($fopen, $change);
    fclose($fopen);

    // Redirect if db connection is valid
    if (@mysql_select_db($db, @mysql_connect($host, $user, $pass))) {
        header("Location: {$_SERVER['REQUEST_URI']}");
        exit;
    }

    // Still db connection issue
    $error = '<p class=error>Could not connect to server</p><hr>';
}



// SEO friendly blackout status, http://googlewebmastercentral.blogspot.com/2011/01/how-to-deal-with-planned-site-downtime.html
// --------------------------------------------------
// Website outages and blackouts the right way - https://plus.google.com/115984868678744352358/posts/Gas8vjZ5fmB
http_response_code(503);
// Try to reach server after 1 minute
header('Retry-After: 60');
// Valid indexing & serving directives, https://developers.google.com/webmasters/control-crawl-index/docs/robots_meta_tag
header('X-Robots-Tag: none');



// Installer setup
// --------------------------------------------------
$meta_title         = 'Installation';
$pagetitle          = 'AJAX SEO ' . $meta_title;
$title_installation = ' ' . $meta_title;
// Chrome CSS3 transition explode bug when form has three or more input elements http://lab.laukstein.com/bug/input, status http://crbug.com/167083
$installation       = '<style>
.installation {
    display: inline-block;
}
.installation li {
    display: block;
    padding-top: 1em;
    padding-bottom: 1em;
}
input,
.error {
    float: right;
}
.error {
    width: 20em;
    color: #ff2121;
    clear: both;
}
</style>
<form method=post>
    <ol class=installation>
        <li><h1>MySQL connection details</h1>
        <li><input class="transition ie-input" id=db name=db value="' . MYSQL_DB . '"><label for=db>Database name</label>
        <li><input class="transition ie-input" id=user placeholder=root name=user value="' . MYSQL_USER . '"><label for=user>User name</label>
        <li><input class="transition ie-input" id=pass type=password name=pass><label for=pass>Password</label>
        <li><input class="transition ie-input" id=host placeholder=localhost name=host value="' . MYSQL_HOST . '"><label for=host>Database host</label>
        <li><input class="transition ie-input" id=table name=table value="' . MYSQL_TABLE . '"><label for=table>Table</label>
        <li>
            <hr>' . $error . '<p>CDN assets URL like protocol-less //cdn.' . $_SERVER['SERVER_NAME'] . '/ or with HTTP/HTTPS</p>
            <input class="transition ie-input" id=cdnpath placeholder=Optional name=cdnpath value="' . CDN_PATH . '"><label for=cdnpath>CDN URL</label>
        </li>
        <li><input class="transition ie-input button" type=submit name=install value=Install>
    </ol>
</form>
<p>The configuration will be saved in connect.php, after you can open and edit it trough text editor.</p>';