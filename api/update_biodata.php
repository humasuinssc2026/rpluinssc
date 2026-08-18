<?php
header('Content-Type: application/json');
require 'db.php';

require_once 'auth.php';
$token = $_POST['user_id'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$user = authenticateUser($pdo, $token);
$userId = $user ? $user['id'] : null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
    exit;
}

$alamat_lengkap = $data['alamat_lengkap'] ?? '';
$pendidikan_terakhir = $data['pendidikan_terakhir'] ?? '';
$nama_ibu = $data['nama_ibu'] ?? '';
$asal_instansi = $data['asal_instansi'] ?? '';

if (empty($alamat_lengkap) || empty($pendidikan_terakhir) || empty($nama_ibu) || empty($asal_instansi)) {
    echo json_encode(['success' => false, 'message' => 'Semua kolom biodata wajib diisi.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET alamat_lengkap = ?, pendidikan_terakhir = ?, nama_ibu = ?, asal_instansi = ? WHERE id = ? AND role = 'peserta'");
    $stmt->execute([$alamat_lengkap, $pendidikan_terakhir, $nama_ibu, $asal_instansi, $userId]);
    
    echo json_encode(['success' => true, 'message' => 'Biodata berhasil disimpan.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan biodata.']);
}
?>
