<?php
header('Content-Type: application/json');
require 'db.php';

$headers = getallheaders();
$token = $headers['Authorization'] ?? '';

// Verifikasi sederhana admin
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();
if (!$user || $user['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID jadwal tidak valid']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM jadwal WHERE id=?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Jadwal berhasil dihapus']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus jadwal']);
}
?>
