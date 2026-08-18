<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['id'] ?? '';
$newStatus = $data['status'] ?? '';
require_once 'auth.php';
$token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$user = authenticateUser($pdo, $token);
$adminId = $user ? $user['id'] : null;

// Check admin role
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$adminId]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin || $admin['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak!']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET status_pembayaran = ? WHERE id = ?");
    $stmt->execute([$newStatus, $userId]);
    
    // Log Aktivitas
    $logStmt = $pdo->prepare("INSERT INTO audit_logs (admin_id, action, target) VALUES (?, ?, ?)");
    $logStmt->execute([$adminId, 'Update Pembayaran', 'Peserta ID: ' . $userId . ' menjadi ' . $newStatus]);

    require_once 'mock_email.php';
    $pesertaStmt = $pdo->prepare("SELECT email, nama FROM users WHERE id = ?");
    $pesertaStmt->execute([$userId]);
    $peserta = $pesertaStmt->fetch(PDO::FETCH_ASSOC);

    if ($peserta) {
        $subject = "Pemberitahuan Status Pembayaran RPL UINSSC";
        $message = "Halo " . $peserta['nama'] . ",\n\nStatus pembayaran biaya pendaftaran RPL Anda saat ini telah diperbarui menjadi: " . $newStatus . ".\n\nSalam,\nAdmin RPL UINSSC";
        send_mock_email($peserta['email'], $subject, $message);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal update database']);
}
?>
