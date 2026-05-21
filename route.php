<?php

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


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
if (preg_match('~^/coffee-shop/order/server/([a-zA-Z0-9_-]+)$~', $request_uri, $matches)) {

    loadPage(
        'order/server/' . $matches[1] . '.php'
    );
}

/*
order code + table
*/
if (preg_match('~^/coffee-shop/order/([a-zA-Z0-9]+)/([a-zA-Z0-9_-]+)/([^/]+)$~', $request_uri, $matches)) {

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
if (preg_match('~^/coffee-shop/order/([a-zA-Z0-9]+)/(\w+)$~', $request_uri, $matches)) {

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
        'kasir/' . $matches[1] . '.php',
        'kasir/index.php'
    );
}



/*
admin
*/
if (preg_match('~^/coffee-shop/admin/([a-zA-Z0-9_-]+)$~', $request_uri, $matches)) {

    loadPage(
        'admin/' . $matches[1] . '.php',
        'admin/index.php'
    );
}

/*
404
*/
http_response_code(404);

if (str_starts_with($request_uri, '/coffee-shop/order/')) {
    include 'order/404.php';
} else {
    include '404.php';  
}
//
exit;