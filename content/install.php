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
    $change = preg_replace("/define\('(CDN_DOMAIN)', null\);/", "define('$1', " . ((strlen($_POST['cdndomain']) > 0) ? "'". trim($_POST['cdndomain']) . "'"  : 'null') . ");", $change);

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
$error              = (MYSQL_ERROR) ? "\n    <p class=error>Could not connect to server</p>" : null;
$installation       = '<style>
.installation label {
    width: 105px;
}
input[type="submit"], .error {
    margin-left: 107px;
}
.error {
    color: #ff2121;
}
</style>
<form method=post>
    <ul class=installation>
        <li><p><b>MySQL connection details</b></p>
        <li><label for=db>Database name</label><input id=db type=text name=db value="' . MYSQL_DB . '" placeholder=ajax_seo>
        <li><label for=user>User name</label><input id=user type=text name=user value="' . MYSQL_USER . '">
        <li><label for=pass>Password</label><input id=pass type=password name=pass>
        <li><label for=host>Database host</label><input id=host name=host value="' . MYSQL_HOST . '" placeholder=localhost>
        <li><label for=table>Table</label><input id=table type=text name=table value="' . MYSQL_TABLE . '">
        <li><hr><label for=cdndomain>CDN domain</label><input id=cdndomain type=text name=cdndomain value="' . CDN_DOMAIN . '">
        <li><input type=submit name=install value=Install><input type=hidden name=submitted value=true>
    </ul>' . $error . '
</form>
<p>The configuration will be saved in connect.php, after you can open and edit it trough text editor.</p>';

?>