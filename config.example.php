<?php
$host = "Host SQL";
$db_name = "Nama Database;
$username = "Username SQL";
$password = "Password SQL";
try {
    // bikin koneksi PDO
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    
    // atur mode error PDO ke Exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Koneksi Gagal: " . $e->getMessage();
    die();
}
?>
