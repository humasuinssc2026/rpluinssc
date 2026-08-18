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
    $stmt = $pdo->query("SELECT id, nama, email, nik FROM users WHERE role = 'admin' ORDER BY id ASC");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $admins]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal mengambil data admin.']);
}
?>
