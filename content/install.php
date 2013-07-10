<?php

// Define MySQL connection status
// --------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'GET' && connection) {
    $change = file_get_contents($f);
    $change = preg_replace("/define\('(connection)', true\);/", "define('$1', false);", $change);
    $change = preg_replace("/define\('(error)', true\);/", "define('$1', false);", $change);

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
    $db     = trim($_POST['db']);
    $host   = trim($_POST['host']);
    $user   = trim($_POST['user']);
    $pass   = trim($_POST['pass']);
    $change = preg_replace("/define\('(database)', '(.*)'\);/", "define('$1', '" . $db . "');", $change);
    $change = preg_replace("/define\('(hostname)', '(.*)'\);/", "define('$1', '" . $host . "');", $change);
    $change = preg_replace("/define\('(username)', '(.*)'\);/", "define('$1', '" . $user . "');", $change);
    $change = preg_replace("/define\('(password)', '(.*)'\);/", "define('$1', '" . $pass . "');", $change);
    $change = preg_replace("/define\('(table)', '(.*)'\);/", "define('$1', '" . trim($_POST['table']) . "');", $change);
    $change = preg_replace("/define\('(error)', false\);/", "define('$1', true);", $change);
    $change = preg_replace("/define\('(cdn)', null\);/", "define('$1', " . ((strlen($_POST['cdn']) > 0) ? "'". trim($_POST['cdn']) . "'"  : 'null') . ");", $change);

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
header('Retry-After: 60'); // Try to reach server after 1 minute
// Valid indexing & serving directives, https://developers.google.com/webmasters/control-crawl-index/docs/robots_meta_tag
header('X-Robots-Tag: none');


// Installer setup
// --------------------------------------------------
$meta_title     = 'Installation';
$pagetitle      = 'Ajax SEO' . $meta_title;
$optional_title = ' ' . $meta_title;
// Chrome CSS3 transition explode bug when form has three or more input elements http://lab.laukstein.com/bug/input, status http://crbug.com/167083
$content   = '<style>
.installation {
    display: inline-block;
}
.installation li {
    clear: both;
}
label {
    padding-top: .6em;
    padding-bottom: .6em;
    margin-top: .4em;
    margin-bottom: .4em;
}
input, .error {
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
        <li><input class=transition id=db name=db value="' . database . '"><label for=db>Database name</label>
        <li><input class=transition id=user placeholder=root name=user value="' . username . '"><label for=user>User name</label>
        <li><input class=transition id=pass type=password name=pass><label for=pass>Password</label>
        <li><input class=transition id=host placeholder=localhost name=host value="' . hostname . '"><label for=host>Database host</label>
        <li><input class=transition id=table name=table value="' . table . '"><label for=table>Table</label>
        <li>
            <hr>' . $error . '<p>CDN assets URL like protocol-less //cdn.' . $host . '/ or with HTTP/HTTPS</p>
            <input class=transition id=cdn placeholder=Optional name=cdn value="' . cdn . '"><label for=cdn>CDN URL</label>
        </li>
        <li><input class="transition button" type=submit name=install value=Install>
    </ol>
</form>
<p>The configuration will be saved in connect.php, after you can open and edit it trough text editor.</p>';