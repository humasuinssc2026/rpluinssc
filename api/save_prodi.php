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
$nama_prodi = $data['nama_prodi'] ?? '';
$nomor_penyelenggara = $data['nomor_penyelenggara'] ?? '';

if (empty($nama_prodi) || empty($nomor_penyelenggara)) {
    echo json_encode(['success' => false, 'message' => 'Nama dan Nomor Penyelenggara wajib diisi']);
    exit;
}

try {
    if (empty($id)) {
        // Create
        $stmt = $pdo->prepare("INSERT INTO prodi (nama_prodi, nomor_penyelenggara) VALUES (?, ?)");
        $stmt->execute([$nama_prodi, $nomor_penyelenggara]);
        echo json_encode(['success' => true, 'message' => 'Program Studi berhasil ditambahkan']);
    } else {
        // Update
        $stmt = $pdo->prepare("UPDATE prodi SET nama_prodi = ?, nomor_penyelenggara = ? WHERE id = ?");
        $stmt->execute([$nama_prodi, $nomor_penyelenggara, $id]);
        echo json_encode(['success' => true, 'message' => 'Program Studi berhasil diperbarui']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data']);
}
?>
