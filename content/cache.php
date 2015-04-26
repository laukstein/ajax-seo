<?php
//
// Static vs singleton class http://moisadoru.wordpress.com/2010/03/02/static-call-versus-singleton-call-in-php/
//

$dbcon = defined('connection') ? connection : false;

class cache {
    private static $dbcon;

    // HTTP header cache
    private static function http($date) {
        $date = date('D, d M Y H:i:s T', strtotime($date));

        if ((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH'])) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $date) {
            http_response_code(304);
            ob_end_clean(); // Empty the response body
            exit;
        }

        header('Last-Modified: '. $date);
    }

    // Identified URL cache
    public static function url() {
        self::http(self::$dbcon ? max(date::url(), date::file('php')) : date::file('php'));
    }

    // The opened file
    public static function me() {
        self::http(date::me());
    }

    // The opened file vs latest database update
    public static function medb() {
        self::http(self::$dbcon ? max(date::db(), date::me()) : date::me());
    }

    // Latest update
    public static function all() {
        self::http(self::$dbcon ? max(date::db(), date::file()) : date::file());
    }
}

class date {
    //public function __construct($mysqli) {} // MySQLi in class http://www.weblimner.com/tutorial/using-mysqli-in-a-seperate-class/
    public static function url() {
        global $mysqli, $url;

        $stmt = $mysqli->prepare('SELECT GREATEST(updated, created) AS date FROM `' . table . '` WHERE url=? LIMIT 1');

        $stmt->bind_param('s', $urldb);
        $stmt->execute();
        $stmt->bind_result($date);
        $stmt->fetch();
        $stmt->free_result();
        $stmt->close();

        return $date;
    }

    // Latest database update
    public static function db() {
        global $mysqli;

        $stmt = $mysqli->prepare('SELECT MAX(GREATEST(updated, created)) AS date FROM `' . table . '`');

        $stmt->execute();
        $stmt->bind_result($date);
        $stmt->fetch();
        $stmt->free_result();
        $stmt->close();

        return $date;
    }

    // The file
    public static function me() {
        return date('Y-m-d H:i:s', filemtime($_SERVER['SCRIPT_FILENAME']));
    }

    // Recent file update filtered by type
    public static function file($type = '') {
        $directory = new RecursiveDirectoryIterator('.', FilesystemIterator::SKIP_DOTS);
        $filter    = new RecursiveCallbackFilterIterator($directory, function ($current, $key, $iterator) {
            // 3.5x TTFB improvement by excluding dot files/directories like .htaccess and .git
            return substr($current->getFilename(), 0, 1) !== '.';
        });
        $iterator = new RecursiveIteratorIterator($filter, RecursiveIteratorIterator::SELF_FIRST);
        $regex    = new RegexIterator($iterator, '/.+\.(' . $type . ')/');
        $date     = array();

        foreach($regex as $fileInfo) $date[] = $fileInfo->getMTime();

        return date('Y-m-d H:i:s', max($date));
    }

    // Latest update
    public static function all() {
        return max(self::db(), self::file());
    }
}
