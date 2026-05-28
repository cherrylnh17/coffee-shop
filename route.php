<?php
include 'env.php';

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$base_path = parse_url(APP_URL, PHP_URL_PATH);
$base_path = rtrim($base_path, '/');

$route = preg_replace('~^' . preg_quote($base_path, '~') . '~', '', $request_uri);

function loadPage($file, $fallback = 'order/404.php')
{
    if (file_exists($file)) {
        include $file;
    } else {
        include $fallback;
    }
    exit;
}

/* 
order server
*/
if (preg_match('~^/order/server/([a-zA-Z0-9_-]+)$~', $route, $matches)) {

    loadPage(
        'order/server/' . $matches[1] . '.php'
    );
}

/*
order code + table
*/
if (preg_match('~^/order/([a-zA-Z0-9]+)/([a-zA-Z0-9_-]+)/([^/]+)$~', $route, $matches)) {

    $_GET['table'] = $matches[1];
    $_GET['code']  = $matches[3];

    loadPage(
        'order/' . $matches[2] . '.php',
        'order/menu.php'
    );
}

/*
order
*/
if (preg_match('~^/order/([a-zA-Z0-9]+)/(\w+)$~', $route, $matches)) {

    $_GET['table'] = $matches[1];

    loadPage(
        'order/' . $matches[2] . '.php',
        'order/menu.php'
    );
}

/*
kasir
*/
if (preg_match('~^/kasir/([a-zA-Z0-9_-]+)$~', $route, $matches)) {

    loadPage(
        'kasir/' . $matches[1] . '.php',
        'kasir/index.php'
    );
}



/*
admin
*/
if (preg_match('~^/admin/([a-zA-Z0-9_-]+)$~', $route, $matches)) {

    loadPage(
        'admin/' . $matches[1] . '.php',
        'admin/index.php'
    );
}

/*
404
*/
http_response_code(404);

if (str_starts_with($route, '/order/')) {
    include 'order/404.php';
} else {
    include '404.php';
}
exit;