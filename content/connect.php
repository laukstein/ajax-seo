<?php

// MySQL settings
// --------------------------------------------------
define('database', 'test');
define('hostname', 'localhost');
define('username', 'root');
define('password', '');
define('table', 'ajax-seo');
define('connection', true);
define('error', false);
define('cdn', null);

// Path for static assets
$issetcdn   = cdn ? true : false;
$cdn_host   = parse_url(cdn, PHP_URL_HOST);
$cdn_scheme = parse_url(cdn, PHP_URL_SCHEME);
$cdn_scheme = isset($cdn_scheme) ? $cdn_scheme . '://' : '//';
$cdn_uri    = $cdn_scheme . $cdn_host;
$debug      = isset($debug) ? $debug : null;
$path       = isset($path) ? $path : null;
$assets     = $debug ? $path : ($issetcdn ? cdn : $path);

$con = @mysql_connect(hostname, username, password); // Connect to db
$f   = 'content/connect.php';

if (@mysql_select_db(database, $con)) {
    if (!connection) { // Define MySQL connection status
        $change = file_get_contents($f);
        $change = preg_replace("/define\('(connection)', false\);/", "define('$1', true);", $change);
        $change = preg_replace("/define\('(error)', true\);/", "define('$1', false);", $change);

        // Change connect.php file permissions if needed
        if (!@is_writable($f)) {
            chmod($f, 0755);
        }

        $fopen = fopen($f, 'w');

        fwrite($fopen, $change);
        fclose($fopen);

        header("Location: {$_SERVER['REQUEST_URI']}");
        exit;
    }

    array_map('trim', $_GET);
    array_map('stripslashes', $_GET);
    array_map('mysql_real_escape_string', $_GET);

    $url   = isset($_GET['url']) ? $_GET['url'] : null;
    $urlid = isset($urlid) ? $urlid : null;

    if (!mysql_query('SELECT * FROM `' . table .'`')) {
        // Set the global server timezone to GMT, needs for SUPER privileges
        mysql_query("SET GLOBAL time_zone = '+00:00'");

        // MySQL backward compatibility
        $ver = preg_replace('#[^0-9\.]#', '', mysql_get_server_info());
        if (version_compare($ver, '5.5.3', '>=')) {
            $char = 'CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
        } else {
            mysql_query('SET NAMES utf8');
            $char = 'CHARSET=utf8 COLLATE=utf8_unicode_ci';
        }

        // Create table
        mysql_query('CREATE TABLE IF NOT EXISTS `' . table . '` (
              id int AUTO_INCREMENT PRIMARY KEY,
              array int NOT NULL,
              url char(70) NOT NULL,
              `meta-title` char(70) NOT NULL,
              `meta-description` char(154) NOT NULL,
              title char(70) NOT NULL,
              content text NOT NULL,
              updated datetime NOT NULL,
              created timestamp DEFAULT current_timestamp
            ) ENGINE=MyISAM DEFAULT ' . $char);
        // Create trigger (needs TRIGGER global privilege)
        mysql_query('CREATE TRIGGER updated BEFORE
            UPDATE ON `' . table . '`
              FOR EACH ROW SET new.updated=NOW()');
        // Insert data
        mysql_query("INSERT INTO `" . table . "` (array, url, `meta-title`, `meta-description`, title, content) VALUES
            (1, '', 'Home', 'Ajax SEO is crawlable framework for Ajax applications.', 'Make Apps crawable', '<h2>Improve your user experience</h2>\n<p>Ajax SEO is crawlable framework for Ajax applications that uses the latest SEO standards, Page Speed and YSlow rules, Google HTML/CSS Style Guide, etc. to improve and maximize performance, security, accessibility, usability and user experience.</p>\n<p>The source code is build on latest W3C standards, HTML Living Standard HTML5, CSS3, Microdata, etc.<br>\nCheck <a class=js-as href=history>history</a> feature and after <a href=javascript:history.forward()>history.forward()</a>.</p>'),
            (2, 'history', 'History', '', 'Manage history', '<p>Try <a href=javascript:history.back()>history.back()</a> and check <a class=js-as href=bind>bind event</a>.</p>'),
            (3, 'bind', 'Bind', '', 'Bind event', '<p>Bind on Ajax loaded <a class=js-as href=test/nested.html>content</a> with class=js-as.</p>'),
            (4, 'test/nested.html', 'Nested', '', 'Nested URL', '<p>This is nested URL example with .html ending. Try <a class=js-as href=кириллица.html>Cyrillic URL</a>.</p>'),
            (5, 'кириллица.html', 'Cyrillic', '', 'Кириллический URL', '<p>This is Cyrillic URL example. Try <a class=js-as dir=rtl href=עברית>RTL עברית</a>.</p>'),
            (6, 'עברית', 'RTL text', '', 'RTL text', '<p>This is RTL example <span dir=rtl>טקסט בעברית</span>. Try <a class=js-as href=no-page>not existing page</a>.</p>')");

        if (is_writable($f)) {
            chmod($f, 0600);
        }

        $note = 'Congratulations, installation has completed successfully.';
    }
} else {
    // Installer on not reachable database
    include 'content/install.php';
}