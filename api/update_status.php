<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['user_id'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';

$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin || $admin['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak!']);
    exit;
}

if (!$data || empty($data['id']) || empty($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET status_verifikasi = ? WHERE id = ?");
    $stmt->execute([$data['status'], $data['id']]);
    
    // Log Aktivitas
    $logStmt = $pdo->prepare("INSERT INTO audit_logs (admin_id, action, target) VALUES (?, ?, ?)");
    $logStmt->execute([$userId, 'Update Status Verifikasi Berkas', 'Peserta ID: ' . $data['id'] . ' menjadi ' . $data['status']]);

    require_once 'mock_email.php';
    $pesertaStmt = $pdo->prepare("SELECT email, nama FROM users WHERE id = ?");
    $pesertaStmt->execute([$data['id']]);
    $peserta = $pesertaStmt->fetch(PDO::FETCH_ASSOC);

    if ($peserta) {
        $subject = "Pemberitahuan Status Pendaftaran RPL UINSSC";
        $message = "Halo " . $peserta['nama'] . ",\n\nStatus pendaftaran RPL Anda saat ini telah diubah menjadi: " . $data['status'] . ".\n\nSilakan cek dashboard secara berkala.\n\nSalam,\nAdmin RPL UINSSC";
        send_mock_email($peserta['email'], $subject, $message);
    }

    echo json_encode(['success' => true, 'message' => 'Status berhasil diubah.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal mengubah status.']);
}
?>
