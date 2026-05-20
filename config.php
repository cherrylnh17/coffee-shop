<?php
require_once 'env.php';

$host = DB_HOST;
$db_name = DB_NAME;
$username = DB_USER;
$password = DB_PASS;
try {
    // bikin koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);

    // atur mode error PDO ke Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Koneksi Gagal: " . $e->getMessage();
    die();
}

// set waktu wib
date_default_timezone_set(APP_LOCATION);
