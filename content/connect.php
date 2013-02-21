<?php

// MySQL settings
// --------------------------------------------------
define('MYSQL_DB', 'test');
define('MYSQL_USER', 'root');
define('MYSQL_PASS', '');
define('MYSQL_HOST', 'localhost');
define('MYSQL_TABLE', 'ajax-seo');
define('MYSQL_CON', true);
define('MYSQL_ERROR', false);



// Path for static assets
// --------------------------------------------------
define('CDN_PATH', null);
$issetcdn = CDN_PATH ? true : false;

// PHP 5.4.7 has fixed host recognition when scheme is ommitted
$protocol = null;
if (version_compare(PHP_VERSION, '5.4.7', '<')) {
    $protocol = 'http:';
}

$assets   = !$debug && $issetcdn ? $protocol . '//' . CDN_PATH : $rootpath . '/assets/';



// Connect to db
// --------------------------------------------------
$con = @mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS);



$f   = 'content/connect.php';

if (@mysql_select_db(MYSQL_DB, $con)) {
    // Define MySQL connection status
    if (!MYSQL_CON) {
        $change = file_get_contents($f);
        $change = preg_replace("/define\('(MYSQL_CON)', false\);/", "define('$1', true);", $change);
        $change = preg_replace("/define\('(MYSQL_ERROR)', true\);/", "define('$1', false);", $change);

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

    if (!mysql_query('SELECT * FROM `' . MYSQL_TABLE .'`')) {
        // Set the global server timezone to GMT, needs for SUPER privileges
        mysql_query("SET GLOBAL time_zone = '+00:00'");

        // Create table
        mysql_query('CREATE TABLE IF NOT EXISTS `' . MYSQL_TABLE . '` (
              id int AUTO_INCREMENT PRIMARY KEY,
              array int NOT NULL,
              url char(70) NOT NULL,
              `meta-title` char(70) NOT NULL,
              `meta-description` char(154) NOT NULL,
              title char(70) NOT NULL,
              content text NOT NULL,
              updated datetime NOT NULL,
              created timestamp DEFAULT current_timestamp
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Create trigger
        mysql_query('CREATE TRIGGER updated BEFORE
            UPDATE ON `' . MYSQL_TABLE . '`
              FOR EACH ROW SET new.updated=NOW()');

        // Insert data
        mysql_query("INSERT INTO `" . MYSQL_TABLE . "` (array, url, `meta-title`, `meta-description`, title, content) VALUES
            (1, '', '', 'AJAX SEO is crawlable framework for AJAX applications.', 'Home', 'AJAX SEO is crawlable framework for AJAX applications that applies the latest SEO standards, Page Speed and YSlow rules, Google HTML/CSS Style Guide, etc. to improve maximal performance, speed, accessibility and usability.<br>\nThe source code is build on latest Web technology, HTML Living Standard - HTML5, CSS3, Microdata, etc.'),
            (2, 'about', 'About', '', '', 'About content'),
            (3, 'test/url/nested-url', 'Nested URL', '', 'Nested URL', 'Nested URL example'),
            (4, 'contact', 'Contact us', '', 'Contact', 'Contact content'),
            (5, 'контакты', 'Контакты', '', '', 'Содержание контактом'),
            (6, 'צור-קשר', 'צור קשר', '', '', 'תוכן לצור קשר')");

        if (is_writable($f)) {
            chmod($f, 0600);
        }

        $note = 'Congratulations, installation has completed successfully.';
    }
} else {
    // Installer on not reachable database
    include 'content/install.php';
}