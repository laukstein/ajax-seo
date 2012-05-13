<?php
// API
// --------------------------------------------------

// Simulate API slow respond
//sleep(3);

header('Content-Type: application/json; charset=utf-8');

// Check if url exist
if (mysql_num_rows($result)) {
    // HTTP header caching
    include('content/cache.php');
    $datemod = new datemod();
    $datemod->date(array(
        '.htaccess',
        'index.php',
        'content/.htaccess',
        'content/api.php',
        'content/cache.php',
        'content/connect.php'
    ), MYSQL_TABLE, $url);
    $datemod->cache($datemod->gmtime);
    
    while ($row = @mysql_fetch_array($result, MYSQL_ASSOC)) {
        $row[] = array(
            'row' => array_map('htmlspecialchars', $row)
        );
        $urlid = strip_tags($row['url']);
        $meta_title  = strip_tags($row['meta-title']);
        $title = strip_tags($row['title']);
        
        if (strlen($title) > 0) {
            $fn = ($meta_title !== $title) ? $title : $meta_title;
        } else {
            $fn = $meta_title;
        }
        
        $pagetitle = $meta_title . ' - ';
        if (strlen($url) == 0) {
            $pagetitle = '';
        }
        
        $array = array(
            'url' => $urlid,
            'pagetitle' => $pagetitle,
            'title' => $meta_title,
            'content' => "<h1>$fn</h1>\n<p>{$row['content']}</p>\n"
        );
        
        // Use for latest PHP standards for php.net/json-encode
        if (version_compare(PHP_VERSION, '5.4', '>=')) {
            // Add option "JSON_PRETTY_PRINT" in case you care more readability than to save some bits
            $json = json_encode($array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            $json = str_replace('\\/', '/', json_encode($array));
        }
        
        echo isset($_GET['callback']) ? $_GET['callback'] . '(' . $json . ')' : $json;
    }
    mysql_close($con);
} else {
    // Return 404 error, if url does not exist
    $validate = new validate($url);
    $validate->status();
    exit('404 Not Found');
}
?>