<?php

$dcon = defined('connection') ? (connection ? true : false) : false;

// Static vs singleton class http://moisadoru.wordpress.com/2010/03/02/static-call-versus-singleton-call-in-php/
class datemod {
    //public function __construct($mysqli) {} // MySQLi in class http://www.weblimner.com/tutorial/using-mysqli-in-a-seperate-class/
    // HTTP header cache
    public static function cache() {
        global $dcon, $mysqli, $url;

        $update = $fmod = date('Y-m-d H:i:s', filemtime($_SERVER['SCRIPT_FILENAME']));

        if ($dcon) {
            $stmt = isset($url) ? $mysqli->prepare("SELECT GREATEST(updated, created) AS date FROM `" . table . "` WHERE url = ? LIMIT 1")
                                : $mysqli->prepare("SELECT MAX(GREATEST(updated, created)) AS date FROM `" . table . "`");
            if (isset($url)) {
                $stmt->bind_param('s', $url);
            }
            $stmt->execute();
            $stmt->bind_result($date);

            while ($stmt->fetch()) {
                $update = max($fmod, $date);
            }

            $stmt->free_result();
            $stmt->close();
        }

        $update = date('D, d M Y H:i:s T', strtotime($update));

        if ((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH'])) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $update) {
            http_response_code(304);
            ob_end_clean(); // Empty the response body
            exit;
        }

        header('Last-Modified: '. $update);
    }

    // Recent file update
    public static function date() {
        global $dcon, $mysqli, $url;

        $directory = new RecursiveDirectoryIterator('.');
        $iterator  = new RecursiveIteratorIterator($directory);
        $regex     = new RegexIterator($iterator, '/.+\.(appcache|css|js|php|txt|xml)/');
        $modfile   = array();

        foreach($regex as $key) {
            $modfile[] = $key->getMTime();
        }

        $update = date('Y-m-d H:i:s',  max($modfile));

        if ($dcon) {
            // Recent database update
            if ($stmt = $mysqli->prepare("SELECT MAX(GREATEST(updated, created)) AS date FROM `" . table . "`")) {
                $stmt->execute();
                $stmt->bind_result($date);

                while ($stmt->fetch()) {
                    $update = max($update, $date);
                }

                $stmt->free_result();
                $stmt->close();
            }
        }

        //$update = date('Y/m/d', strtotime($update));
        return $update;
    }
}
datemod::cache();