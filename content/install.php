<?php
//
// Install setup
//

$error = null;
$drop  = true;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dhost     = trim($_POST['host']);
    $user      = trim($_POST['user']);
    $pass      = trim($_POST['pass']);
    $db        = trim($_POST['db']);
    $table     = trim($_POST['table']);
    $drop      = !empty($_POST['drop']) ? true : false;
    $gtitle    = trim($_POST['title']);
    $assets    = trim($_POST['assets']);
    $ga        = trim($_POST['ga']);
    $ga_domain = trim($_POST['ga_domain']);
    $xdebug    = !empty($_POST['debug']) ? 'true' : 'false';

    $array = array(
        'hostname'  => "'$dhost'",
        'username'  => "'$user'",
        'password'  => "'$pass'",
        'database'  => "'$db'",
        'table'     => "'$table'",
        'title'     => "'$gtitle'",
        'assets'    => "'$assets'",
        'ga'        => "'$ga'",
        'ga_domain' => "'$ga_domain'",
        'debug'     => $xdebug
    );

    $string = file_get_contents($f);

    // Define MySQL settings
    // Optional function runkit_constant_redefine()
    foreach ($array as $key => &$value) $string = preg_replace("/define\('($key)', (.*)\);/", "define('$1', $value);", $string);

    if (!@is_writable($f)) @chmod($f, 0755);

    // Save settings
    $fopen = fopen($f, 'w');
    if (fwrite($fopen, $string)) {
        fclose($fopen);
        @chmod($f, 0600);

        $mysqli = @new mysqli($dhost, $user, $pass, $db);
        // Add data in database
        if (!$mysqli->connect_errno) {
            if ($drop) $mysqli->query('DROP TABLE IF EXISTS `' . $table . '`');
            if (!!@$mysqli->query("SHOW TABLES LIKE '" . $table . "'")->num_row) {
                $error = 'Table \'' . $table . '\' already exists. Pick another name or <label for=drop>drop the existing one</label>.';
            } else {
                $char = 'CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
                if (version_compare(preg_replace('#[^0-9\.]#', '', $mysqli->server_info), '5.5.3', '<')) {
                    $char = 'CHARSET=utf8 COLLATE=utf8_unicode_ci';
                    $mysqli->query('SET NAMES utf8');
                }

                $mysqli->query('CREATE TABLE `' . $table . '` (
                      id int AUTO_INCREMENT,
                      `order` int NOT NULL,
                      permit int NOT NULL DEFAULT \'1\',
                      url char(70) NOT NULL,
                      title char(70) NOT NULL,
                      description char(154) NOT NULL,
                      content text NOT NULL,
                      updated datetime NOT NULL,
                      created timestamp DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (id),
                      UNIQUE KEY url (url)
                    ) ENGINE=MyISAM DEFAULT ' . $char);
                $mysqli->query('CREATE TRIGGER updated BEFORE UPDATE ON `' . $table . '` FOR EACH ROW SET new.updated=NOW()'); // Requires TRIGGER global privilege
                if ($mysqli->query("INSERT INTO `" . $table . "` (`order`, permit, url, title, description, content) VALUES
                                    (1, 1, '', 'Ajax SEO v3', 'Extend user experience with crawlable webapp framework Ajax SEO', '<h2>Extend user experience</h2>\n<a class=button role=button href=https://github.com/laukstein/ajax-seo/zipball/master download>Download recent code</a>\n<p>Ajax SEO is crawlable webapp framework for outstanding UX.</p>\n<ul>\n    <li>Cross-platform\n    <li>W3C cutting-edge standards\n        <ul>\n            <li>Native JavaScript, HTML5.1 APIs\n            <li>SEO accessible, crawlable and indexable\n        </ul>\n    </li>\n    <li>Grade-A performance, security and usability\n    <li>Simple, responsive, intuitive, maintainable\n    <li><a class=x href=more-features>More features</a>\n</ul>\n<p>Use by adding <code>class=x</code> to any API compatible hyperlink.<br>Here, <code><a class=x href=history>href=history</a></code> requires API request <code>api/history</code>.</p>\n<p>Legacy browser support in <a href=https://github.com/laukstein/ajax-seo/releases target=_blank>earlier releases</a>.</p>'),
                                    (2, 1, 'history', 'History', 'When it begin', '<h2>When it begin</h2>\n<blockquote>\n    <p>I always keep learning, research, innovate and brainstorm ideas to make a better life, to merge art and technology and improve the World\'s experience.</p>\n    <cite><a href=http://laukstein.com>Binyamin Laukstein</a>, <time datetime=\"2014-05-27\">May 27, 2014</time></cite>\n</blockquote>\n<p>In 2001 I become passionately interested how Web works and how easily it changes the World knowledge. Since then I make the Web a better place and in my spare time I propose Web standards, UX concepts, advise major companies and innovate patents.</p>\n<p>Ajax SEO idea began in 2007 on using Dynamic Drive <a rel=nofollow href=http://www.dynamicdrive.com/dynamicindex17/tabcontent.htm target=_blank>Tab Content Script</a> with HTTP cookie memorized last opened tab, where I expend its features with Google Analytics compatibility.</p>\n<p>Later, in 2010 I found Asual\'s (Rostislav Hristov) jQuery <a rel=nofollow href=https://github.com/asual/jquery-address target=_blank>Address plugin</a> and wondered about missed SEO, <a rel=nofollow href=https://developers.google.com/webmasters/ajax-crawling/docs/getting-started target=_blank>Ajax crawlability</a>, Apache + MySQL + PHP compatibility, and so I began my first GitHub project <a href=https://github.com/laukstein/ajax-seo>Ajax SEO</a> with jQuery Address plugin.</p>\n<p>Through time I saw Asual and jQuery code too slow, outdated and unnecessary and in 2013, while developing cross-platform webapps on multiple devices, I decided to go forward - remove dependencies and make own code with native JavaScript and more W3C cutting-edge standards.</p>'),
                                    (3, 1, 'nested/test-cases.html', 'Test cases', '', '<p>Check API requests in Network log.<br>In order to see JavaScript Console.log reports, apply debug: true in your configuration.</p>\n<ul>\n    <li>Navigating history <mark>history</mark>\n        <ul>\n            <li><a class=x href=javascript:history.back()>history.back()</a>\n            <li><a class=x href=javascript:history.forward()>history.forward()</a>\n        </ul>\n    </li>\n    <li>Inside this URL <mark>stop</mark>\n        <ul>\n            <li><a class=x rel=nofollow href={\$basename}>{\$basename}</a>\n            <li><a class=x rel=nofollow href={\$basename} target=_blank>{\$basename} with target=_blank</a>\n            <li><a class=x rel=nofollow href={\$basename}><span>&lt;span&gt;{\$basename}&lt;/span&gt;</span></a>\n            <li><a class=x rel=nofollow href={\$path}{\$url}>{\$path}{\$url}</a>\n            <li><a class=x rel=nofollow href=//{\$host}{\$path}{\$url}>//{\$host}{\$path}{\$url}</a>\n            <li><a class=x rel=nofollow href=#terms>#terms</a> [bug - history navigating fails on hash URL]\n            <li><a class=x rel=nofollow href={\$basename}#terms>{\$basename}#terms</a> [...]\n            <li><a class=x rel=nofollow href={\$path}{\$url}#terms>{\$path}{\$url}#terms</a> [...]\n            <li><a class=x rel=nofollow href=//{\$host}{\$path}{\$url}#terms>//{\$host}{\$path}{\$url}#terms</a> [...]\n        </ul>\n    </li>\n    <li>Existing URL <mark>run</mark>\n        <ul>\n            <li><a class=x rel=nofollow href={\$path}>{\$path}</a>\n            <li><a class=x rel=nofollow href=../טיפוגרפיה-html5-bidi>../טיפוגרפיה-html5-bidi</a> [bug - failed request]\n            <li><a class=x rel=nofollow href={\$path}טיפוגרפיה-html5-bidi>{\$path}טיפוגרפיה-html5-bidi</a>\n            <li><a class=x rel=nofollow href=//{\$host}{\$path}טיפוגרפיה-html5-bidi#lorem-ipsum>//{\$host}{\$path}טיפוגרפיה-html5-bidi#lorem-ipsum</a>\n        </ul>\n    </li>\n    <li>Redirected URL <mark>run</mark>\n        <ul>\n            <li><a class=x rel=nofollow href=//{\$host}/{\$path}>//{\$host}/{\$path}</a>\n            <li><a class=x rel=nofollow href={\$path}History>{\$path}History</a> [bug - does not refresh redirected URL in address bar and Google Analytics request]\n            <li><a class=x rel=nofollow href={\$path}lorem-ipsum>{\$path}lorem-ipsum</a> [...]\n        </ul>\n    </li>\n    <li>Failed URL <mark>error</mark>\n        <ul>\n            <li><a class=x rel=nofollow href=nonexisting/url>nonexisting/url</a>\n            <li><a class=x rel=nofollow href={\$path}nested/nonexisting/url>{\$path}nested/nonexisting/url</a>\n            <li><a class=x rel=nofollow href=//{\$host}/{\$path}/broken>//{\$host}/{\$path}/broken</a>\n            <li><a class=x rel=nofollow href={\$path}/broken>{\$path}/broken</a> [bug - avoid multiple slashes in URL in client-side popstate]\n        </ul>\n    <li>Ouside the scope <mark>none</mark>\n        <ul>\n            <li><a class=x rel=nofollow href=//www.{\$host}{\$path}>//www.{\$host}{\$path}</a>\n            <li><a class=x rel=nofollow href=//{\$host}//broken/url>//{\$host}//broken/url</a>\n            <li><a class=x href=//laukstein.com/>//laukstein.com/</a>\n        </ul>\n    </li>\n</ul>\n<hr>\n<dl>\n    <dt id=\"terms\">Terms\n    <dd><mark>history</mark> - act as history navigation (popstate API)\n    <dd><mark>stop</mark> - change URL without API request (pushState API)\n    <dd><mark>run</mark> - requre API if not in cache\n    <dd><mark>error</mark> - return error page\n    <dd><mark>none</mark> - prevent Ajax request and act as usual link (if project source is not in root)\n</dl>\n<hr>\n<ul>\n    <li><a rel=nofollow href=\"https://code.google.com/p/chromium/issues/detail?id=63040\" target=_blank>#63040</a> Chrome fires initial popstate, fixed on Chrome 34\n    <li>Webkit fires initial popstate while injected script, fixed on Chrome 35\n    <li><a rel=nofollow href=\"https://code.google.com/p/chromium/issues/detail?id=371549\" target=_blank>#371549</a> Chrome repeatedly repeated same hash URL history/popstate by onclick on same URL (hangs on error page and recreates XMLHttpRequest)\n    <li><a rel=nofollow href=\"https://bugzilla.mozilla.org/show_bug.cgi?id=706806\" target=_blank>#706806</a>, <a rel=nofollow href=\"https://bugzilla.mozilla.org/show_bug.cgi?id=428916\" target=_blank>#428916</a>, <a rel=nofollow href=\"https://bugzilla.mozilla.org/show_bug.cgi?id=443098\" target=_blank>#443098</a> Firefox does not retry new XMLHttpRequest but returns cache\n    <li>innerText is not standardised and supported by Firefox\n</ul>'),
                                    (4, 1, 'טיפוגרפיה-html5-bidi', 'טיפוגרפיה Supercalifragilisticexpialidocious', '', '<p>This page represents long text usecase and HTML5 bidi example of RTL and LTR typing.</p>\n<hr>\n<h1><code>&lt;h1&gt;</code> Heading</h1>\n<h2><code>&lt;h2&gt;</code> Heading</h2>\n<h3><code>&lt;h3&gt;</code> Heading</h3>\n<h4><code>&lt;h4&gt;</code> Heading</h4>\n<p><code>&lt;p&gt;</code> Paragraph</p>\n<hr>\n<h1 id=lorem-ipsum>Lorem ipsum</h1>\n<a rel=nofollow href=http://www.loremipsum.de/downloads/original.txt target=_blank>http://www.loremipsum.de/downloads/original.txt</a>\n<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>\n<p>Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.</p>\n<p>Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.</p>\n<p>Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.</p>\n<p>Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis.</p>\n<p>At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, At accusam aliquyam diam diam dolore dolores duo eirmod eos erat, et nonumy sed tempor et et invidunt justo labore Stet clita ea et gubergren, kasd magna no rebum. sanctus sea sed takimata ut vero voluptua. est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat.</p>\n<p>Consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>'),
                                    (5, 0, 'more-features', 'More features', '', '<ul>\n    <li>Unicode compatible URL, follow .htaccess URL RewriteRule\'s\n    <li>Hide URL from menu with database <code>permit</code> \"<b>0</b>\"\n    <li>Execute PHP variables from database, syntax: <code>{<span>\$path</span>}</code> returns \"<b>{\$path}</b>\"\n</ul>')") === false) {
                    $error = 'Error on adding data to database \'' . $table . '\'';
                } else {
                    refresh();
                }
            }
        } else {
            $error = 'Could not connect to server: ' . $mysqli->connect_error;
        }
    } else {
        fclose($fopen);

        $error = $f .' is write protected, unable to save settings';
    }

    $error = '<p class="reset error">' . $error . '</p>';
}

// Installer setup
$dhost     = !empty($dhost) ? $dhost : hostname;
$user      = !empty($user) ? $user : username;
$pass      = !empty($pass) ? $pass : password;
$db        = !empty($db) ? $db : database;
$table     = !empty($table) ? $table : table;
$drop      = $drop ? ' checked' : null;
$gtitle    = !empty($gtitle) ? $gtitle : title;
$assets    = (!connection && empty(assets)) ? string('{$path}assets/') : (!empty($assets) ? $assets : assets);
$ga        = !empty($ga) ? $ga : ga;
$ga_domain = !empty($ga_domain) ? $ga_domain : ga_domain;
$adebug    = !empty($xdebug) && $xdebug === 'true' || debug ? ' checked' : null;

// Content output
$title     = 'Installation';
$pagetitle = $gtitle . ' ' . $title;
$content   = '<style scoped>.main{padding-top:1.2em}label{width:30%}input{width:70%}[type=checkbox]{width:auto}.n2{width:35%}button{width:100%}.reset{margin:1em 0}.reset label{width:auto}.error{color:#ff2121}@media (max-width:540px){label,input,.n2{width:100%}[type=checkbox]{width:auto}}</style>
<form method=post>
    <h1>' . $pagetitle . '</h1>
    <dl>
        <dt>MySQL connection
        <dd><label for=host>Database host</label><input id=host name=host placeholder=localhost value="' . $dhost . '">
        <dd><label for=user>User name</label><input id=user name=user placeholder=root value="' . $user . '">
        <dd><label for=pass>Password</label><input id=pass name=pass placeholder=Password type=password>
        <dd><label for=db>Database name</label><input id=db name=db placeholder=db value="' . $db . '">
        <dd><label for=table>Table</label><input id=table class=n2 name=table placeholder=table value="' . $table . '"><label><input id=drop name=drop type=checkbox' . $drop . '> Drop table if exists</label>' . $error . '
        <dt><hr>Page details
        <dd><label for=title>Page title</label><input id=title name=title placeholder=Title value="' . $gtitle . '">
        <dd><label for=assets>Full assets URL</label><input id=assets name=assets placeholder="' . $path . 'assets/" value="' . $assets . '">
        <dd><label for=ga>Google Analytics</label><input id=ga class=n2 name=ga placeholder=UA-XXXX-Y value="' . $ga . '"><input class=n2 name=ga_domain placeholder="' . $host . ' (optional)" value="' . $ga_domain . '">
        <dd class=reset><label><input name=debug type=checkbox' . $adebug . '> Debug in localhost (error_reporting, uncompressed assets and console.log)</label>
        <dd><button name=install>Install</button><p>Your configuration will be saved in config.php, after you can open and edit it manually.</p>
    </dl>
</form>';

// Chrome CSS3 transition explode bug when form has three or more input elements
// #167083 status in http://crbug.com/167083
// test case http://lab.laukstein.com/bug/input