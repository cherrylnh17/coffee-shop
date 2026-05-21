<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';

// Guard: hanya admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

// Guard: hanya POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "admin/printer");
    exit;
}

$action = $_POST['action'] ?? '';

//   redirect dengan SweetAlert session 
function redirectWithSwal(string $icon, string $title, string $text): void {
    $_SESSION['swal_msg'] = [
        'icon'  => $icon,
        'title' => $title,
        'text'  => $text,
    ];
    header("Location: " . BASE_URL . "admin/printer/index");
    exit;
}

//   sanitize string 
function str_or_null(?string $val): ?string {
    $v = trim($val ?? '');
    return $v === '' ? null : $v;
}
function int_or_null(?string $val): ?int {
    $v = trim($val ?? '');
    return $v === '' ? null : (int)$v;
}

//  TAMBAH
if ($action === 'tambah') {

    $name      = str_or_null($_POST['name']      ?? '');
    $type      = int_or_null($_POST['type']      ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!$name || !$type) {
        redirectWithSwal('error', 'Gagal!', 'Nama dan tipe printer wajib diisi.');
    }

    // Kolom per tipe
    $bt_mac     = null; $bt_channel = null; $rfcomm_dev = null; $timeout    = null;
    $ip_address = null; $port       = null;
    $usb_device = null;

    if ($type === 1) {
        // Bluetooth
        $bt_mac     = str_or_null($_POST['bt_mac']     ?? '');
        $bt_channel = int_or_null($_POST['bt_channel'] ?? '');
        $rfcomm_dev = str_or_null($_POST['rfcomm_dev'] ?? '');
        $timeout    = int_or_null($_POST['timeout']    ?? '');

        if (!$bt_mac) redirectWithSwal('error', 'Gagal!', 'MAC Address wajib diisi untuk printer Bluetooth.');

    } elseif ($type === 2) {
        // Network
        $ip_address = str_or_null($_POST['ip_address'] ?? '');
        $port       = int_or_null($_POST['port']       ?? '');

        if (!$ip_address) redirectWithSwal('error', 'Gagal!', 'IP Address wajib diisi untuk printer Network.');

    } elseif ($type === 3) {
        // USB
        $usb_device = str_or_null($_POST['usb_device'] ?? '');

        if (!$usb_device) redirectWithSwal('error', 'Gagal!', 'USB Device path wajib diisi untuk printer USB.');

    } else {
        redirectWithSwal('error', 'Gagal!', 'Tipe printer tidak valid.');
    }

    try {
        $sql = "INSERT INTO printer
                    (name, type, bt_mac, bt_channel, rfcomm_dev, ip_address, port, usb_device, timeout, is_active)
                VALUES
                    (:name, :type, :bt_mac, :bt_channel, :rfcomm_dev, :ip_address, :port, :usb_device, :timeout, :is_active)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name'       => $name,
            ':type'       => $type,
            ':bt_mac'     => $bt_mac,
            ':bt_channel' => $bt_channel,
            ':rfcomm_dev' => $rfcomm_dev,
            ':ip_address' => $ip_address,
            ':port'       => $port,
            ':usb_device' => $usb_device,
            ':timeout'    => $timeout,
            ':is_active'  => $is_active,
        ]);

        redirectWithSwal('success', 'Berhasil!', "Printer \"$name\" berhasil ditambahkan.");

    } catch (PDOException $e) {
        redirectWithSwal('error', 'Gagal!', 'Gagal menyimpan printer: ' . $e->getMessage());
    }
}

//  EDIT
if ($action === 'edit') {

    $id        = int_or_null($_POST['id']   ?? '');
    $name      = str_or_null($_POST['name'] ?? '');
    $type      = int_or_null($_POST['type'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!$id || !$name || !$type) {
        redirectWithSwal('error', 'Gagal!', 'Data tidak lengkap, pastikan ID, nama, dan tipe terisi.');
    }

    // Pastikan data printer ada
    try {
        $chk = $pdo->prepare("SELECT id FROM printer WHERE id = :id");
        $chk->execute([':id' => $id]);
        if (!$chk->fetch()) {
            redirectWithSwal('error', 'Gagal!', 'Printer tidak ditemukan.');
        }
    } catch (PDOException $e) {
        redirectWithSwal('error', 'Gagal!', 'Gagal memvalidasi data: ' . $e->getMessage());
    }

    // Reset semua kolom tipe, isi hanya yang relevan
    $bt_mac     = null; $bt_channel = null; $rfcomm_dev = null; $timeout    = null;
    $ip_address = null; $port       = null;
    $usb_device = null;

    if ($type === 1) {
        $bt_mac     = str_or_null($_POST['bt_mac']     ?? '');
        $bt_channel = int_or_null($_POST['bt_channel'] ?? '');
        $rfcomm_dev = str_or_null($_POST['rfcomm_dev'] ?? '');
        $timeout    = int_or_null($_POST['timeout']    ?? '');
        if (!$bt_mac) redirectWithSwal('error', 'Gagal!', 'MAC Address wajib diisi untuk printer Bluetooth.');

    } elseif ($type === 2) {
        $ip_address = str_or_null($_POST['ip_address'] ?? '');
        $port       = int_or_null($_POST['port']       ?? '');
        if (!$ip_address) redirectWithSwal('error', 'Gagal!', 'IP Address wajib diisi untuk printer Network.');

    } elseif ($type === 3) {
        $usb_device = str_or_null($_POST['usb_device'] ?? '');
        if (!$usb_device) redirectWithSwal('error', 'Gagal!', 'USB Device path wajib diisi untuk printer USB.');

    } else {
        redirectWithSwal('error', 'Gagal!', 'Tipe printer tidak valid.');
    }

    try {
        $sql = "UPDATE printer SET
                    name        = :name,
                    type        = :type,
                    bt_mac      = :bt_mac,
                    bt_channel  = :bt_channel,
                    rfcomm_dev  = :rfcomm_dev,
                    ip_address  = :ip_address,
                    port        = :port,
                    usb_device  = :usb_device,
                    timeout     = :timeout,
                    is_active   = :is_active
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name'       => $name,
            ':type'       => $type,
            ':bt_mac'     => $bt_mac,
            ':bt_channel' => $bt_channel,
            ':rfcomm_dev' => $rfcomm_dev,
            ':ip_address' => $ip_address,
            ':port'       => $port,
            ':usb_device' => $usb_device,
            ':timeout'    => $timeout,
            ':is_active'  => $is_active,
            ':id'         => $id,
        ]);

        redirectWithSwal('success', 'Berhasil!', "Printer \"$name\" berhasil diperbarui.");

    } catch (PDOException $e) {
        redirectWithSwal('error', 'Gagal!', 'Gagal memperbarui printer: ' . $e->getMessage());
    }
}

//  HAPUS
if ($action === 'hapus') {

    $id = int_or_null($_POST['id'] ?? '');

    if (!$id) {
        redirectWithSwal('error', 'Gagal!', 'ID printer tidak valid.');
    }

    try {
        // Ambil nama dulu untuk pesan sukses
        $chk = $pdo->prepare("SELECT name FROM printer WHERE id = :id");
        $chk->execute([':id' => $id]);
        $row = $chk->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            redirectWithSwal('error', 'Gagal!', 'Printer tidak ditemukan.');
        }

        $name = $row['name'];

        $del = $pdo->prepare("DELETE FROM printer WHERE id = :id");
        $del->execute([':id' => $id]);

        redirectWithSwal('success', 'Berhasil!', "Printer \"$name\" berhasil dihapus.");

    } catch (PDOException $e) {
        redirectWithSwal('error', 'Gagal!', 'Gagal menghapus printer: ' . $e->getMessage());
    }
}

// Aksi tidak dikenali
redirectWithSwal('error', 'Gagal!', 'Aksi tidak dikenali.');