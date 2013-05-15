<?php

// Define MySQL connection and error status
if (MYSQL_CON) {
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

// Add MySQL settings
// --------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $change = file_get_contents($f);
    $change = preg_replace("/define\('(MYSQL_HOST)', '(.*)'\);/", "define('$1', '" . trim($_POST['host']) . "');", $change);
    $change = preg_replace("/define\('(MYSQL_USER)', '(.*)'\);/", "define('$1', '" . trim($_POST['user']) . "');", $change);
    $change = preg_replace("/define\('(MYSQL_PASS)', '(.*)'\);/", "define('$1', '" . trim($_POST['pass']) . "');", $change);
    $change = preg_replace("/define\('(MYSQL_DB)', '(.*)'\);/", "define('$1', '" . trim($_POST['db']) . "');", $change);
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
    header("Location: {$_SERVER['REQUEST_URI']}");
    exit;
}

// SEO friendly blackout status, http://googlewebmastercentral.blogspot.com/2011/01/how-to-deal-with-planned-site-downtime.html
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
$error              = MYSQL_ERROR ? "\n    <p class=error>Could not connect to server</p>" : null;
// Chrome CSS3 transition explode bug when form has three or more input elements http://lab.laukstein.com/bug/input, status http://crbug.com/167083
$installation       = '<style>
form {
    display: block;
    width: 100%;
}
.installation li {
    padding-top: 2px;
    padding-bottom: 2px;
}
.installation label {
    width: 105px;
}
.button, .error {
    margin-left: 105px;
}
.error {
    color: #ff2121;
}
</style>
<form method=post>
    <ul class=installation>
        <li><p><b>MySQL connection details</b></p>
        <li><label for=db>Database name</label><input class="transition ie-input" id=db name=db value="' . MYSQL_DB . '">
        <li><label for=user>User name</label><input class="transition ie-input" id=user placeholder=root name=user value="' . MYSQL_USER . '">
        <li><label for=pass>Password</label><input class="transition ie-input" id=pass type=password name=pass>
        <li><label for=host>Database host</label><input class="transition ie-input" id=host placeholder=localhost name=host value="' . MYSQL_HOST . '">
        <li><label for=table>Table</label><input class="transition ie-input" id=table name=table value="' . MYSQL_TABLE . '">
        <li>
            <hr><p>CDN assets URL like protocol-less <b>//cdn.' . $_SERVER['SERVER_NAME'] . '/</b> or with HTTP/HTTPS</p>
            <label for=cdnpath>CDN URL</label><input class="transition ie-input" id=cdnpath placeholder=Optional name=cdnpath value="' . CDN_PATH . '">
        </li>
        <li><input class="transition ie-input button" type=submit name=install value=Install><input type=hidden name=submitted value=true>
    </ul>' . $error . '
</form>
<p>The configuration will be saved in connect.php, after you can open and edit it trough text editor.</p>';