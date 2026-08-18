<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

$identifier = $data['nik'] ?? $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($identifier) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'NIK/Email dan password wajib diisi']);
    exit;
}

// Cari user berdasarkan nik atau email (untuk admin)
$stmt = $pdo->prepare("SELECT * FROM users WHERE nik = ? OR email = ?");
$stmt->execute([$identifier, $identifier]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    // Generate secure token
    $token = bin2hex(random_bytes(32));
    
    // Save token to DB
    $updateStmt = $pdo->prepare("UPDATE users SET token = ? WHERE id = ?");
    $updateStmt->execute([$token, $user['id']]);

    echo json_encode([
        'success' => true, 
        'message' => 'Login berhasil!',
        'role' => $user['role'],
        'nama' => $user['nama'],
        'user_id' => $token // Send token masquerading as user_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'NIK/Email atau Password salah!']);
}
?>
