<?php
//
// Install setup
//

$error = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dhost   = trim($_POST['host']);
    $user    = trim($_POST['user']);
    $pass    = trim($_POST['pass']);
    $db      = trim($_POST['db']);
    $table   = trim($_POST['table']);
    $gtitle  = trim($_POST['title']);
    $cdn     = trim($_POST['cdn']);
    $aid     = trim($_POST['analytics_id']);
    $adomain = trim($_POST['analytics_domain']);

    $array = array(
        'hostname'=>$dhost,
        'username'=>$user,
        'password'=>$pass,
        'database'=>$db,
        'table'=>$table,
        'title'=>(!empty($gtitle) ? $gtitle  : 'Ajax SEO'),
        'cdn'=>(!empty($cdn) ? $cdn : ''),
        'analytics_id'=>(!empty($aid) ? $aid : ''),
        'analytics_domain'=>(!empty($adomain) ? $adomain : '')
    );

    $string = file_get_contents($f);

    // Define MySQL settings
    // Optional function runkit_constant_redefine()
    foreach ($array as $key => &$value) {
        $string = preg_replace("/define\('($key)', '(.*)'\);/", "define('$1', '$value');", $string);
    }

    if (!@is_writable($f)) @chmod($f, 0755);

    // Save settings
    $fopen = fopen($f, 'w');
    if (fwrite($fopen, $string)) {
        fclose($fopen);
        @chmod($f, 0600);

        $mysqli = @new mysqli($dhost, $user, $pass, $db);
        $conn   = $mysqli->connect_errno ? false : true;

        // Add data in database
        if ($conn) {
            $mysqli->query('DROP TABLE IF EXISTS `' . $table . '`');

            $char = 'CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
            if (version_compare(preg_replace('#[^0-9\.]#', '', $mysqli->server_info), '5.5.3', '<')) {
                $char = 'CHARSET=utf8 COLLATE=utf8_unicode_ci';
                $mysqli->query('SET NAMES utf8');
            }

            $mysqli->query('CREATE TABLE IF NOT EXISTS `' . $table . '` (
                  id int AUTO_INCREMENT PRIMARY KEY,
                  array int NOT NULL,
                  url char(70) NOT NULL UNIQUE KEY,
                  `meta-title` char(70) NOT NULL,
                  `meta-description` char(154) NOT NULL,
                  title char(70) NOT NULL,
                  content text NOT NULL,
                  updated datetime NOT NULL,
                  created timestamp DEFAULT current_timestamp
                ) ENGINE=MyISAM DEFAULT ' . $char);
            $mysqli->query('CREATE TRIGGER updated BEFORE UPDATE ON `' . $table . '` FOR EACH ROW SET new.updated=NOW()'); // Requires TRIGGER global privilege

            /* Lorem text http://www.loremipsum.de/downloads/original.txt */
            if ($mysqli->query("INSERT INTO `" . $table . "` (array, url, `meta-title`, `meta-description`, title, content) VALUES
                (1, '', 'Home', 'Extend user experience with Ajax SEO framework', 'Ajax SEO <small>Version 2.0</small>', '<h2>Extend user experience</h2>\n<p>Bring stable, aesthetic, fast and secure application experience with Ajax SEO framework. Ajax SEO based on bleeding edge W3C standards and SEO guides to maximize crawlability, accessibility, usability, performance and security.<br>\n<a class=button href=https://github.com/laukstein/ajax-seo/zipball/master>Download Ajax SEO</a><br>\nBased on W3C, Google, Yahoo, etc. rules to deliver pure user experience.</p>\n<h3>Test case</h3>\n<p>Check <a class=js-as href=history>history</a> feature and after <a href=javascript:history.forward()>history.forward()</a>.</p>'),
                (2, 'lorem-ipsum', 'Lorem ipsum', '', '', '<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>\n<p>Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.</p>\n<p>Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.</p>\n<p>Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.</p>\n<p>Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis.</p>\n<p>At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, At accusam aliquyam diam diam dolore dolores duo eirmod eos erat, et nonumy sed tempor et et invidunt justo labore Stet clita ea et gubergren, kasd magna no rebum. sanctus sea sed takimata ut vero voluptua. est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat.</p>\n<p>Consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>'),
                (3, 'history', 'History', '', 'Manage history', '<p>Try <a href=javascript:history.back()>history.back()</a> and check <a class=js-as href=bind>bind event</a>.</p>'),
                (4, 'bind', 'Bind', '', 'Bind event', '<p>Bind on Ajax loaded <a class=js-as href=test/nested.html>content</a> with class=js-as.</p>'),
                (5, 'test/nested.html', 'Nested', '', 'Nested URL', '<p>This is nested URL example with .html ending.<br>\nTry <a class=js-as href=кириллица.html>Cyrillic URL</a>.</p>'),
                (6, 'кириллица.html', 'Cyrillic', '', 'Кириллический URL', '<p>This is Cyrillic URL example.<br>\nTry <a class=js-as dir=rtl href=כתיבת-html5-bidi dir=auto>כתיבת HTML5 bidi</a>.</p>'),
                (7, 'כתיבת-html5-bidi', 'כתיבת HTML5 bidi', '', '', '<p>HTML5 bidirectional writing mode is mix of left-to-right and right-to-left writing, like טקסט בעברית and Latin character text.<br>\nTry <a class=js-as href=no-page>not existing page</a>.</p>')") === false) {
                $conn  = false;
                $error = 'Error on adding data to database \'' . $table . '\'';
            } else {
                refresh();
            }
        } else {
            $error = 'Could not connect to server: ' . $mysqli->connect_error;
        }
    } else {
        fclose($fopen);

        $error = $f .' is write protected, unable to save settings';
    }

    $error = '<p class=error>' . $error . '</p>';
}

// Installer setup
$dhost   = !empty($dhost) ? $dhost : hostname;
$user    = !empty($user) ? $user : username;
$pass    = !empty($pass) ? $pass : password;
$db      = !empty($db) ? $db : database;
$table   = !empty($table) ? $table : table;
$gtitle  = !empty($gtitle) ? $gtitle : title;
$cdn     = !empty($cdn) ? $cdn : cdn;
$aid     = !empty($aid) ? $aid : analytics_id;
$adomain = !empty($adomain) ? $adomain : analytics_domain;

$meta_title     = 'Installation';
$pagetitle      = $meta_title . ' - ' . $gtitle;
$optional_title = ' ' . $meta_title;
$content        = '<style>
.main { padding-top: 1.2em; }
.install { display: inline-block; }
.install li, input.button, .install .i1 input { width: 100%; }
.install li { float: left; }
label { width: 30%; }
.i1 label, .i2 label {
    width: auto;
    padding: 0;
    margin: 0;
}
input:not([type]), [type=password], [type=url] { width: 70%; }
.i2 input { width: 50%; }
.error { color: #ff2121; }
@media (max-width: 540px) { label, input:not([type]), [type=password], [type=url], .i2 input { width: 100%; } }
</style>
<form method=post>
    <h1>MySQL connection details</h1>
    <ol class=install>
        <li><label for=host>Database host</label><input id=host name=host placeholder=localhost value="' . $dhost . '">
        <li><label for=user>User name</label><input id=user name=user placeholder=root value="' . $user . '">
        <li><label for=pass>Password</label><input id=pass name=pass placeholder=Password type=password>
        <li><label for=db>Database name</label><input id=db name=db placeholder=db value="' . $db . '">
        <li><label for=table>Table</label><input id=table name=table placeholder=table value="' . $table . '">' . $error . '
        <li><hr><label for=title>Page title</label><input id=title name=title placeholder=Title value="' . $gtitle . '">
        <li class=i1>
            <p><label for=cdn>CDN URL protocol-less or with HTTP/HTTPS</label></p>
            <input id=cdn name=cdn placeholder=//cdn.com/assets/ value="' . $cdn . '">
        </li>
        <li class=i2>
            <p>Google <a rel=nofollow href=https://developers.google.com/analytics/devguides/collection/analyticsjs/ target=_blank>Universal Analytics</a> <label for=aid>tracking ID</label> and <label for=adomain>domain</label></p>
            <input id=aid name=analytics_id placeholder=UA-XXXX-Y value="' . $aid . '"><input id=adomain name=analytics_domain placeholder=domain.com value="' . $adomain . '">
        </li>
        <li><input class=button name=install value=Install type=submit>
    </ol>
</form>
<p>The configuration will be saved in connect.php, after you can open and edit it through a text editor.</p>';

// Chrome CSS3 transition explode bug when form has three or more input elements
// #167083 status in http://crbug.com/167083
// test case http://lab.laukstein.com/bug/input