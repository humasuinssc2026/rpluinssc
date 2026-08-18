<?php
header('Content-Type: application/json');
require 'db.php';
require_once 'mock_email.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email harus diisi.']);
    exit;
}

// Cari user berdasarkan email
$stmt = $pdo->prepare("SELECT id, nama FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // Generate token reset sederhana
    $token = bin2hex(random_bytes(16));
    
    // Simpan token ke database (tabel pengguna, kolom reset_token bisa ditambahkan di lain waktu, 
    // untuk sekarang kita mock saja pengirimannya)
    
    $subject = "Permintaan Reset Password - RPL UINSSC";
    $message = "Halo " . $user['nama'] . ",\n\nKami menerima permintaan untuk mereset kata sandi akun pendaftaran RPL Anda.\n\nKlik tautan di bawah ini untuk mengatur ulang kata sandi Anda:\n\nhttp://localhost:8000/reset.html?token=" . $token . "\n\nJika Anda tidak meminta reset password, abaikan email ini.\n\nSalam,\nAdmin RPL UINSSC";
    
    send_mock_email($email, $subject, $message);
    
    // Log Activity (sederhana)
    $logFile = __DIR__ . '/../email_logs.txt';
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Password reset requested for $email (User ID: " . $user['id'] . ")\n", FILE_APPEND);
}

// Selalu kembalikan success meskipun email tidak ditemukan (security best practice: no account enumeration)
echo json_encode(['success' => true, 'message' => 'Jika email Anda terdaftar, tautan reset telah dikirim ke email tersebut.']);
?>
