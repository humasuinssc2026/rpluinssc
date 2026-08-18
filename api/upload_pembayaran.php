<?php
header('Content-Type: application/json');
require 'db.php';

require_once 'auth.php';
$token = $_POST['user_id'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$user = authenticateUser($pdo, $token);
$userId = $user ? $user['id'] : null;
if (empty($userId)) {
    echo json_encode(['success' => false, 'message' => 'Otorisasi gagal']);
    exit;
}

if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Gagal mengunggah file bukti pembayaran']);
    exit;
}

$uploadDir = '../uploads/pembayaran/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$ext = pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION);
$fileName = 'payment_' . $userId . '_' . time() . '.' . $ext;

if (move_uploaded_file($_FILES['bukti']['tmp_name'], $uploadDir . $fileName)) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET bukti_pembayaran = ?, status_pembayaran = 'Menunggu Verifikasi Pembayaran' WHERE id = ?");
        $stmt->execute([$fileName, $userId]);
        echo json_encode(['success' => true, 'message' => 'Bukti pembayaran berhasil diunggah.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan file ke server']);
}
?>
