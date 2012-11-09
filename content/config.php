<?php

// --------------------------------------------------
// Global configuration
// --------------------------------------------------



//date_default_timezone_set('Etc/GMT');



// Debug mode
// --------------------------------------------------
$ip = array('127.0.0.1', 'localhost');
if (in_array($_SERVER['REMOTE_ADDR'], $ip)) {
    error_reporting(E_ALL);
    $debug = true;
} else {
    error_reporting(0);
    $debug = false;
}



// Add latest PHP functions
// --------------------------------------------------
if (version_compare(PHP_VERSION, '5.4', '<')) {
    include 'content/function.http-response-code.php';
}



// Gzip
// --------------------------------------------------
// PHP 5.4.4 bug https://bugs.php.net/bug.php?id=55544
if (version_compare(PHP_VERSION, '5.4.5', '>') && !ob_start('ob_gzhandler')) {
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



// Return root path
$rootpath = substr($path, 0, -1);