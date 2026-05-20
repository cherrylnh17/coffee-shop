<?php
require_once 'path.php';
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

function loadPage($file, $fallback = '404.php')
{
    if (file_exists($file)) {
        include ROOT_PATH . $file;
    } else {
        include ROOT_PATH . $fallback;
    }

    exit;
}
/*
order server
*/
if (preg_match('~^/coffee-shop/order/server/([a-zA-Z0-9_-]+)$~', $request_uri, $matches)) {

    loadPage(
        'order/server/' . $matches[1] . '.php'
    );
}

/*
order code + table
*/
if (preg_match('~^/coffee-shop/order/(\d+)/([a-zA-Z0-9_-]+)/([^/]+)$~', $request_uri, $matches)) {

    $_GET['table'] = $matches[1];
    $_GET['code']  = $matches[3];

    loadPage(
        'order/' . $matches[2] . '.php',
        'order/index.php'
    );
}

/*
order
*/
if (preg_match('~^/coffee-shop/order/(\d+)/(\w+)$~', $request_uri, $matches)) {

    $_GET['table'] = $matches[1];

    loadPage(
        'order/' . $matches[2] . '.php',
        'order/index.php'
    );
}

/*
kasir
*/

if (preg_match('~^/coffee-shop/kasir/([a-zA-Z0-9_-]+)$~', $request_uri, $matches)) {

    loadPage(
        '/kasir/' . $matches[1] . '.php',
        '/kasir/index.php'
    );
}



/*
admin
*/
if (preg_match('~^/coffee-shop/admin/([a-zA-Z0-9_-]+)$~', $request_uri, $matches)) {

    loadPage(
        '/admin/' . $matches[1] . '.php',
        '/admin/index.php'
    );
}

/*
404
*/
http_response_code(404);
include '404.php';
exit;