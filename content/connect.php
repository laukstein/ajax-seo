<?php
// MySQL settings
define('MYSQL_DB', 'test');
define('MYSQL_USER', 'root');
define('MYSQL_PASS', '');
define('MYSQL_HOST', 'localhost');
define('MYSQL_TABLE', 'ajax-seo');
define('MYSQL_CON', true);
define('MYSQL_ERROR', false);

$con = @mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS);
$f   = 'content/connect.php';

//date_default_timezone_set('Etc/GMT');

// Return dir path
if (str_replace('\\', '/', pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME)) != '/') {
    $path = str_replace('\\', '/', pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME)) . '/';
} else {
    $path = str_replace('\\', '/', pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME));
}

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
    mysql_query("SET NAMES 'utf8'");
    
    $url   = isset($_GET['url']) ? $_GET['url'] : null;
    $urlid = isset($urlid) ? $urlid : null;
    
    // Return 404 error, if url does not exist
    class validate
    {
        public $title;
        public $content;
        function status()
        {
            http_response_code(404);
            $this->title   = '404 Not Found';
            $this->content = 'Sorry, this page cannot be found.';
        }
    }
    
    $sql = 'SELECT * FROM `' . MYSQL_TABLE .'`';
    if (!mysql_query($sql)) {
        // Set the global server time zone, needs for SUPER privileges
        mysql_query("SET GLOBAL time_zone = '" . date('T') . "'");
        
        mysql_query("SET NAMES 'utf8'");
        
        // Create table
        mysql_query('CREATE TABLE IF NOT EXISTS `' . MYSQL_TABLE . '` (
					  id mediumint(8) NOT NULL AUTO_INCREMENT,
					  array mediumint(8) NOT NULL,
					  url varchar(70) NOT NULL,
					  `meta-title` varchar(70) NOT NULL,
					  `meta-description` varchar(154) NOT NULL,
					  `meta-keywords` varchar(250) NOT NULL,
					  title varchar(70) NOT NULL,
					  content text NOT NULL,
					  pubdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					  PRIMARY KEY (id)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
        
        // Insert data
        $now = date('Y-m-d H:i:s');
        mysql_query("INSERT INTO `" . MYSQL_TABLE . "` (array, url, `meta-title`, `meta-description`, `meta-keywords`, title, content) VALUES
					(1, '', '', 'AJAX SEO is crawlable framework for AJAX applications.', 'ajax, seo, crawlable, applications, performance, speed, accessibility, usability', 'Home', 'AJAX SEO is crawlable framework for AJAX applications that applies the latest SEO standards, Page Speed and YSlow rules, Google HTML/CSS Style Guide, etc to improve maximal performance, speed, accessibility and usability.<br>\nThe source code is build on latest Web technology, like HTML5, Microdata, PHP 5, etc.'),
					(2, 'about', 'About', '', '', '', 'About content'),
					(3, 'portfolio', 'Portfolio', '', '', 'Portfolio', 'Portfolio content'),
					(4, 'contact', 'Contact us', '', '', 'Contact', 'Contact content'),
					(5, 'контакты', 'Контакты', '', '', '', 'Содержание контактом'),
					(6, 'צור-קשר', 'צור קשר', '', '', '', 'תוכן לצור קשר');
        
        if (is_writable($f)) {
            chmod($f, 0600);
        }
        
        // Valid indexing & serving directives https://developers.google.com/webmasters/control-crawl-index/docs/robots_meta_tag
        header('X-Robots-Tag: none', true);
        header('Content-Type: text/html');
        $note = 'Congratulations, installation has completed successfully.';
    }
} else {
    // Installer on not reachable database
    include('content/install.php');
}
?>