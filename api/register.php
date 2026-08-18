<?php
header('Content-Type: application/json');
require 'db.php';

// Ambil data JSON dari body request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

$nama = $data['nama'] ?? '';
$nik = $data['nik'] ?? '';
$nomor_hp = $data['nomor_hp'] ?? '';
$tanggal_lahir = $data['tanggal_lahir'] ?? '';
$email = $data['email'] ?? '';
$prodi = $data['prodi'] ?? '';
$password = $data['password'] ?? '';
$konfirmasi_password = $data['konfirmasi_password'] ?? '';

if (empty($nama) || empty($nik) || empty($email) || empty($password) || empty($prodi)) {
    echo json_encode(['success' => false, 'message' => 'Kolom bertanda * wajib diisi']);
    exit;
}

if ($password !== $konfirmasi_password) {
    echo json_encode(['success' => false, 'message' => 'Konfirmasi password tidak cocok']);
    exit;
}

// Cek apakah NIK atau Email sudah terdaftar
$stmt = $pdo->prepare("SELECT id FROM users WHERE nik = ? OR email = ?");
$stmt->execute([$nik, $email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'NIK atau Email sudah terdaftar!']);
    exit;
}

// Hash password sebelum disimpan
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Masukkan data ke database
try {
    $stmt = $pdo->prepare("INSERT INTO users (nama, nik, nomor_hp, tanggal_lahir, email, password, prodi) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nama, $nik, $nomor_hp, $tanggal_lahir, $email, $hashedPassword, $prodi]);
    
    echo json_encode(['success' => true, 'message' => 'Pendaftaran berhasil! Silakan login.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal mendaftar: ' . $e->getMessage()]);
}
?>
