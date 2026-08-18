<?php
header('Content-Type: application/json');
require 'db.php';

require_once 'auth.php';

$token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$admin = authenticateUser($pdo, $token);

if (!$admin || $admin['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak! Hanya admin.']);
    exit;
}

try {
    $stmt = $pdo->query("SELECT id, nama, nik, email, nomor_hp, tanggal_lahir, prodi, status_verifikasi, tanggal_daftar, dokumen_lengkap, status_pembayaran, bukti_pembayaran, alamat_lengkap, pendidikan_terakhir, nama_ibu, asal_instansi FROM users WHERE role = 'peserta' ORDER BY tanggal_daftar DESC");
    $peserta = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $peserta]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal mengambil data.']);
}
?>
