<?php

// --------------------------------------------------
// Global configuration
// --------------------------------------------------



//date_default_timezone_set('Etc/GMT');



// Debug mode
// --------------------------------------------------
$debug = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1']);
if ($debug) {
    error_reporting(E_ALL);
} else {
    error_reporting(0);
}



// Add latest PHP functions
// --------------------------------------------------
if (version_compare(PHP_VERSION, '5.4', '<')) {
    include 'function.http-response-code.php';
}



// Gzip
// --------------------------------------------------
if (!ob_start('ob_gzhandler')) {
    ob_start();
}



// Prevent XSS and SQL Injection
// --------------------------------------------------
if (strpos($_SERVER['HTTP_HOST'], $_SERVER['SERVER_NAME']) === false) {
    http_response_code(400);
    
    // Robots meta tag and X-Robots-Tag HTTP header specifications https://developers.google.com/webmasters/control-crawl-index/docs/robots_meta_tag
    header('X-Robots-Tag: none');
    
    header('Content-Type: text/plain');
    exit('400 Bad Request');
}



// Return dir path
if (str_replace('\\', '/', pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME)) != '/') {
    $path = str_replace('\\', '/', pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME)) . '/';
} else {
    $path = str_replace('\\', '/', pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME));
}

?>