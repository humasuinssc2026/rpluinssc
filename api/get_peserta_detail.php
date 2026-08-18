<?php
header('Content-Type: application/json');
require 'db.php';

require_once 'auth.php';
$token = $_POST['user_id'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$user = authenticateUser($pdo, $token);
$userId = $user ? $user['id'] : null;

if (empty($userId)) {
    echo json_encode(['success' => false, 'message' => 'Tidak ada otorisasi']);
    exit;
}

$stmt = $pdo->prepare("SELECT nama, nik, email, prodi, status_verifikasi, tanggal_daftar, dokumen_lengkap, dokumen_status, dokumen_catatan, status_pembayaran, bukti_pembayaran, alamat_lengkap, pendidikan_terakhir, nama_ibu, asal_instansi FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
    exit;
}

echo json_encode(['success' => true, 'data' => $user]);
?>
