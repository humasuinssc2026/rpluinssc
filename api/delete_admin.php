<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
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

$idToDelete = $data['id'] ?? '';

if (empty($idToDelete)) {
    echo json_encode(['success' => false, 'message' => 'ID admin tidak valid']);
    exit;
}

if ($idToDelete == $adminId) {
    echo json_encode(['success' => false, 'message' => 'Anda tidak bisa menghapus akun Anda sendiri']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'");
    $stmt->execute([$idToDelete]);
    echo json_encode(['success' => true, 'message' => 'Admin berhasil dihapus']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus admin']);
}
?>
