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

$peserta_id = $data['peserta_id'] ?? '';
$tgl = $data['tgl'] ?? '';
$lokasi = $data['lokasi'] ?? '';

if (empty($peserta_id)) {
    echo json_encode(['success' => false, 'message' => 'ID Peserta tidak valid']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET jadwal_asesmen_tgl = ?, jadwal_asesmen_lokasi = ? WHERE id = ?");
    $stmt->execute([$tgl, $lokasi, $peserta_id]);
    
    // Log Aktivitas
    $logStmt = $pdo->prepare("INSERT INTO audit_logs (admin_id, action, target) VALUES (?, ?, ?)");
    $logStmt->execute([$userId, 'Update Jadwal Asesmen', 'Peserta ID: ' . $peserta_id]);
    
    echo json_encode(['success' => true, 'message' => 'Jadwal berhasil disimpan']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan jadwal']);
}
?>
