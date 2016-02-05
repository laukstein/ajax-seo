<?php
//
// JSON API
//

$arr = array();

/*// Simulate slow response and errors
function simulator() {
    global $result, $arr, $title, $content, $pagetitle, $title_error, $content_error, $pagetitle_error;

    $a = [0, 2, 4, 6];
    $i = $a[rand(0, count($a) - 1)];

    if ($i > 0) {
        if ($i === 6) {
            http_response_code(403);
            header('Content-Type: text/plain');
            exit('403 Forbidden');
        }

        sleep($i);

        if ($result && $i === 4) {
            http_response_code(404);

            $arr['error'] = true;
            $title        = $title_error;
            $content      = "<h1 dir=auto>$title</h1>\n" . $content_error;
            $pagetitle    = $pagetitle_error;
        }
    }
}

if ($debug) simulator();*/


// Genarate
if (!$result) $arr['error'] = true;

$arr['title']   = $pagetitle;
$arr['content'] = $content;
$arr = array_filter($arr);
$arr = $debug ? json_encode($arr, JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : json_encode($arr, JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

if (version_compare(PHP_VERSION, '5.4', '<')) {
    $arr = preg_replace('/\\\\u([a-f0-9]{4})/e', "iconv('UCS-4LE', 'utf-8', pack('V',  hexdec('U$1')))", json_encode($arr));
    $arr = str_replace('\\/', '/', $arr);
}


// Respond
if (!$result) http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: nosnippet');
echo $arr;
