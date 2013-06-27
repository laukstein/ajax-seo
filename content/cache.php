<?php

// HTTP header caching
// --------------------------------------------------
class datemod {
    public $gmtime;

    function date($files, $dbtable, $url) {
        $result = mysql_query("SELECT CONCAT(DATE_FORMAT(GREATEST(updated, created), '%a, %d %b %Y %T '), @@global.time_zone) AS date FROM `" . MYSQL_TABLE . "` WHERE url = '$url'");
        if ($result) {
            foreach ($files as $val) {
                $mod     = date('D, d M Y H:i:s T', filemtime($val));
                $array[] = $mod;
            }

            $fmod = max($array);

            while ($row = @mysql_fetch_array($result, MYSQL_ASSOC)) {
                $row[]   = array(
                    'row' => array_map('htmlspecialchars', $row)
                );
                $date = $row['date'];
            }
            $this->gmtime = date('Y-m-d H:i:s', strtotime($fmod)) >= date('Y-m-d H:i:s', strtotime($date)) ? $fmod : $date;
        }
    }

    function cache(&$gmtime) {
        if (isset($gmtime)) {
            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
                if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmtime) {
                    http_response_code(304);
                    ob_end_clean(); // Empty the response body
                    exit;
                }
            }
            header("Last-Modified: $gmtime");
        }
    }
}