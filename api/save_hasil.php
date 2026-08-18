<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
require_once 'auth.php';
$token = $_POST['user_id'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$admin = authenticateUser($pdo, $token);
$userId = $admin ? $admin['id'] : null;

if (!$admin || $admin['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak!']);
    exit;
}

$id = $data['id'] ?? '';
$peserta_id = $data['peserta_id'] ?? '';
$kode_mk = $data['kode_mk'] ?? '';
$nama_mk = $data['nama_mk'] ?? '';
$sks = $data['sks'] ?? '';
$nilai = $data['nilai'] ?? '';

if (empty($peserta_id) || empty($kode_mk) || empty($nama_mk) || empty($sks) || empty($nilai)) {
    echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
    exit;
}

try {
    if (empty($id)) {
        $stmt = $pdo->prepare("INSERT INTO hasil_rpl (peserta_id, kode_mk, nama_mk, sks, nilai) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$peserta_id, $kode_mk, $nama_mk, $sks, $nilai]);
        
        $logStmt = $pdo->prepare("INSERT INTO audit_logs (admin_id, action, target) VALUES (?, ?, ?)");
        $logStmt->execute([$userId, 'Input Nilai RPL', 'Peserta ID: ' . $peserta_id . ' | MK: ' . $kode_mk]);
        
        echo json_encode(['success' => true, 'message' => 'Nilai berhasil ditambahkan']);
    } else {
        $stmt = $pdo->prepare("UPDATE hasil_rpl SET kode_mk = ?, nama_mk = ?, sks = ?, nilai = ? WHERE id = ?");
        $stmt->execute([$kode_mk, $nama_mk, $sks, $nilai, $id]);
        
        $logStmt = $pdo->prepare("INSERT INTO audit_logs (admin_id, action, target) VALUES (?, ?, ?)");
        $logStmt->execute([$userId, 'Edit Nilai RPL', 'Peserta ID: ' . $peserta_id . ' | MK: ' . $kode_mk]);

        echo json_encode(['success' => true, 'message' => 'Nilai berhasil diperbarui']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data']);
}
?>
