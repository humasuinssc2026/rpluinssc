<?php
header('Content-Type: application/json');
require 'db.php';

require_once 'auth.php';
$token = $_POST['user_id'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$admin = authenticateUser($pdo, $token);
$userId = $admin ? $admin['id'] : null;

if (!$admin || $admin['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak! Hanya admin.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || empty($data['nama']) || empty($data['nik']) || empty($data['email'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET nama = ?, nik = ?, email = ?, prodi = ? WHERE id = ? AND role = 'peserta'");
    $stmt->execute([$data['nama'], $data['nik'], $data['email'], $data['prodi'], $data['id']]);
    echo json_encode(['success' => true, 'message' => 'Data peserta berhasil diperbarui.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data peserta.']);
}
?>
