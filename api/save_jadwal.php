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
$kegiatan = $data['kegiatan'] ?? '';
$tanggal = $data['tanggal'] ?? '';
$urutan = $data['urutan'] ?? 0;

if (!$kegiatan || !$tanggal) {
    echo json_encode(['success' => false, 'message' => 'Kegiatan dan tanggal harus diisi']);
    exit;
}

try {
    if ($id) {
        $stmt = $pdo->prepare("UPDATE jadwal SET kegiatan=?, tanggal=?, urutan=? WHERE id=?");
        $stmt->execute([$kegiatan, $tanggal, $urutan, $id]);
        echo json_encode(['success' => true, 'message' => 'Jadwal berhasil diperbarui']);
    } else {
        $stmt = $pdo->prepare("INSERT INTO jadwal (kegiatan, tanggal, urutan) VALUES (?, ?, ?)");
        $stmt->execute([$kegiatan, $tanggal, $urutan]);
        echo json_encode(['success' => true, 'message' => 'Jadwal berhasil ditambahkan']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan jadwal']);
}
?>
