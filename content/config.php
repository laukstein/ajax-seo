<?php
//
// Global configuration
//

// Debug mode
define('debug', true);

if (debug && preg_match('/^(127.0.0.1|10.0.0.\d{1,3})$/', $_SERVER['REMOTE_ADDR'])) {
    // Development
    error_reporting(E_ALL);
    $debug = true;
} else {
    // Production
    error_reporting(0);
    $debug = false;
}

$host = $_SERVER['SERVER_NAME'];

if ($host !== $_SERVER['HTTP_HOST']) {
    // Prevent XSS and SQL injection
    http_response_code(400);

    // Robots meta tag and X-Robots-Tag HTTP header specifications
    //     https://developers.google.com/webmasters/control-crawl-index/docs/robots_meta_tag
    header('X-Robots-Tag: none');

    header('Content-Type: text/plain');
    exit('400 Bad Request');
}

// Compatibility
$comp = true;
$fix  = null;

if (function_exists('apache_get_modules') && !in_array('mod_rewrite', apache_get_modules())) {
    // Check if Apache mod_rewrite enabled
    $comp = false;
    $fix .= "\n* enable Apache mod_rewrite";
}

function minify_output($buffer) {
    $search = ['/ {2,}/', '/<!--(?!\[if).*?-->|\t|<\/(option|li|dt|dd|tr|th|td)>|(?:\r?\n[ \t]*)+/s'];
    $blocks = preg_split('/(<\/?pre[^>]*>)/', $buffer, null, PREG_SPLIT_DELIM_CAPTURE);
    $replace = [' ', ''];
    $buffer = '';

    foreach ($blocks as $i => $block) $buffer .= $i % 4 === 2 ? $block : preg_replace($search, $replace, $block);

    return $buffer;
}

$toMinify = !$debug && isset($toMinify) && $toMinify;
$phpv = PHP_VERSION;

// Add http_response_code function
if (version_compare($phpv, '5.4', '<')) include 'content/function.http-response-code.php';
if (version_compare($phpv, '5.2', '>=')) {
    // PHP 5.2 backward compatibility
    // date.timezone settings required since PHP 5.3
    if (version_compare($phpv, '5.3', '>=') && !ini_get('date.timezone')) date_default_timezone_set('UTC');
    if (version_compare($phpv, '5.4', '>') && extension_loaded('zlib')) {
        // Compress output with Gzip, PHP 5.4.4 bug https://bugs.php.net/bug.php?id=55544
        if (ob_get_length()) {
            ob_end_clean();
        }

        ob_start($toMinify ? 'minify_output' : 'ob_gzhandler');
    } else if ($toMinify) {
        ob_start('minify_output');
    }
} else {
    $comp = false;
    $fix .= "\n* upgrade to PHP 5.2 or later";
}
if (!$comp) {
    http_response_code(503);

    // Retry-After 1 hour
    header('Retry-After: 3600');

    header('X-Robots-Tag: none');
    header('Content-Type: text/plain');
    exit('Your server is outdated' . $fix);
}

function string($str) {
    // Template prototype
    if (!function_exists('_variable')) {
        // Avoid function redeclare
        // Usecase: Execute variable {$foo}
        // Supported since PHP 4.1.0 http://www.php.net/manual/en/language.variables.superglobals.php
        function _variable($m) {
            // case-sensitive variable
            return @$GLOBALS[$m[1]];
        }
    }

    $str = preg_replace_callback('/{\$(\w+)}/', '_variable', $str);

    return $str;
}

// Common variables
$file   = __FILE__;
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ? 'https' : 'http';
$path   = str_replace('\\', '/', str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', getcwd()));
$url    = strtolower(rawurldecode($_SERVER['REQUEST_URI']));
$url    = $url === '/' ? '/' : preg_replace('/^' . addcslashes($path, '/') . '\/api/', '', $url);
$url    = preg_replace('/^' . addcslashes($path, '/') . '/', '', $url);
$safepath = strlen($path) ? $path : '/';
$ishome = $safepath === $path . $url;

$urldb  = preg_replace('/^\//', '', $url);
$uri    = $scheme . '://' . $host . $url;
$urlend = basename($url);

// 1-2s TTFB improvement by avoiding IPV6 lookup for the hostname
// hostname "localhost" will cause extra time http://thisinterestsme.com/slow-mysqli-connection/
define('hostname', '127.0.0.1');
define('port', 3306);
define('username', 'root');
define('password', '');
define('database', 'test');
define('table', 'ajax-seo');
define('connection', false);

// Assets URL default: string('{$path}/assets/')
define('assets', string('{$path}/assets/'));
define('title', 'Ajax SEO');

// Google Analytics configuration
define('ga', null);
define('ga_domain', null);
