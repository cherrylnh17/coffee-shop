<?php
// configurasi absolute path, agar ga perlu sering ketik ../../ , langsung path posisi path.php
require_once 'env.php';

// Untuk include file PHP (Server side)
define('ROOT_PATH', __DIR__);

// Untuk link Gambar/CSS/JS (Client side)
define('BASE_URL', APP_URL ?? 'http://localhost' );