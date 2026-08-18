<?php
header('Content-Type: application/json');
require 'db.php';

require_once 'auth.php';
$token = $_POST['user_id'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$admin = authenticateUser($pdo, $token);
$userId = $admin ? $admin['id'] : null;

if (!$admin || $admin['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak! Hanya admin.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$judul = $data['judul'] ?? '';
$isi = $data['isi'] ?? '';

if (empty($judul) || empty($isi)) {
    echo json_encode(['success' => false, 'message' => 'Judul dan isi tidak boleh kosong.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO pengumuman (judul, isi) VALUES (?, ?)");
    $stmt->execute([$judul, $isi]);
    echo json_encode(['success' => true, 'message' => 'Pengumuman berhasil disimpan.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pengumuman.']);
}
?>
