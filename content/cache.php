<?php
class datemod
{
    public $gmtime;
    
    function date($files, $dbtable, $url)
    {
        $result = mysql_query("SELECT DATE_FORMAT(pubdate, '%a, %d %b %Y %T') AS pubdate FROM " . MYSQL_TABLE . " WHERE url = '$url'");
        if (mysql_num_rows($result)) {
            foreach ($files as $val) {
                $mod     = date('D, d M Y H:i:s T', filemtime($val));
                $array[] = $mod;
            }
            
            $fmod = max($array);
            
            while ($row = @mysql_fetch_array($result, MYSQL_ASSOC)) {
                $row[]   = array(
                    'row' => array_map('htmlspecialchars', $row)
                );
                $pubdate = $row['pubdate'] . date(' T');
            }
            $this->gmtime = date('Y-m-d H:i:s', strtotime($fmod)) >= date('Y-m-d H:i:s', strtotime($pubdate)) ? $fmod : $pubdate;
        }
    }
    
    function cache($gmtime)
    {
        if (isset($gmtime)) {
            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
                if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmtime) {
                    header('Status: 304 Not Modified', true, 304);
                    exit;
                }
            }
            header("Last-Modified: $gmtime");
        }
    }
}
?>