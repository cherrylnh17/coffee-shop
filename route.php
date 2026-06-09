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
order code + table  →  /order/{table}/{page}/{code}
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
order  →  /order/{table}/{page}
*/
if (preg_match('~^/order/([a-zA-Z0-9]+)/(\w+)$~', $route, $matches)) {

    $_GET['table'] = $matches[1];

    loadPage(
        'order/' . $matches[2] . '.php',
        'order/menu.php'
    );
}

/*
kasir  →  /kasir  atau  /kasir/{page}
*/

if (preg_match('~^/kasir/?$~', $route)) {
    loadPage(
        'kasir/index.php', 
        'kasir/index.php');
}

/*
/kasir/history/list
*/
if (preg_match('~^/kasir/([a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]+)$~', $route, $matches)) {

    $file = 'kasir/' . $matches[1] . '/' . $matches[2] . '.php';

    if (file_exists($file)) {
        loadPage($file, 'kasir/index.php');
    }

    loadPage(
        'kasir/' . $matches[1] . '/index.php',
        'kasir/index.php');
}

/*
/kasir/history
*/
if (preg_match('~^/kasir/([a-zA-Z0-9_-]+)/?$~', $route, $matches)) {

    $folderIndex = 'kasir/' . $matches[1] . '/index.php';

    if (file_exists($folderIndex)) {
        loadPage($folderIndex);
    }

    loadPage(
        'kasir/index.php', 
        'kasir/index.php');
}

/*
admin /admin  atau  /admin/{page}
*/

if (preg_match('~^/admin/?$~', $route)) {
    loadPage(
        'admin/index.php', 
        'admin/index.php');
}

/*
/admin/laporan/detail
*/
if (preg_match('~^/admin/([a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]+)$~', $route, $matches)) {

    $file = 'admin/' . $matches[1] . '/' . $matches[2] . '.php';
    if (file_exists($file)) {
        loadPage($file, 'admin/index.php');
    }

    loadPage(
        'admin/' . $matches[1] . '/index.php',
        'admin/index.php');
}

/*
/admin/laporan
*/
if (preg_match('~^/admin/([a-zA-Z0-9_-]+)/?$~', $route, $matches)) {

    $folderIndex = 'admin/' . $matches[1] . '/index.php';

    if (file_exists($folderIndex)) {
        loadPage($folderIndex);
    }

    loadPage(
        'admin/index.php', 
        'admin/index.php');
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