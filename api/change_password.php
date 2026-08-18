<?php
header('Content-Type: application/json');
require 'db.php';

require_once 'auth.php';
$token = $_POST['user_id'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$user = authenticateUser($pdo, $token);
$userId = $user ? $user['id'] : null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Sesi tidak valid.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Kata sandi tidak boleh kosong.']);
    exit;
}

$newPassword = password_hash($data['password'], PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$newPassword, $userId]);
    echo json_encode(['success' => true, 'message' => 'Kata sandi berhasil diperbarui.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui kata sandi.']);
}
?>
