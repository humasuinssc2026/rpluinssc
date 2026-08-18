<?php
header('Content-Type: application/json');
require 'db.php';

require_once 'auth.php';
$token = $_POST['user_id'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$admin = authenticateUser($pdo, $token);
$userId = $admin ? $admin['id'] : null;

if (!$admin || $admin['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak!']);
    exit;
}

try {
    // Statistik Verifikasi
    $stmt1 = $pdo->query("SELECT status_verifikasi, COUNT(*) as count FROM users WHERE role != 'admin' OR role IS NULL GROUP BY status_verifikasi");
    $verifikasi_stats = [];
    while ($row = $stmt1->fetch(PDO::FETCH_ASSOC)) {
        $verifikasi_stats[$row['status_verifikasi']] = $row['count'];
    }

    // Statistik Pembayaran
    $stmt2 = $pdo->query("SELECT status_pembayaran, COUNT(*) as count FROM users WHERE role != 'admin' OR role IS NULL GROUP BY status_pembayaran");
    $pembayaran_stats = [];
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        $pembayaran_stats[$row['status_pembayaran']] = $row['count'];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'verifikasi' => $verifikasi_stats,
            'pembayaran' => $pembayaran_stats
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal mengambil statistik admin.']);
}
?>
