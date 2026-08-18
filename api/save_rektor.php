<?php
header('Content-Type: application/json');
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

// Verifikasi Admin
require_once 'auth.php';
$token = $_POST['user_id'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$admin = authenticateUser($pdo, $token);
$userId = $admin ? $admin['id'] : null;

if (!$admin || $admin['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak! Hanya admin.']);
    exit;
}

// Data Teks
$nama = $_POST['rektor_nama'] ?? '';
$jabatan = $_POST['rektor_jabatan'] ?? '';
$teks = $_POST['rektor_teks'] ?? '';

if (empty($nama) || empty($jabatan) || empty($teks)) {
    echo json_encode(['success' => false, 'message' => 'Semua kolom teks harus diisi.']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Update Teks
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    $stmt->execute([$nama, 'rektor_nama']);
    $stmt->execute([$jabatan, 'rektor_jabatan']);
    $stmt->execute([$teks, 'rektor_teks']);

    // Handle Upload Foto jika ada
    if (isset($_FILES['rektor_foto']) && $_FILES['rektor_foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = 'rektor_' . time() . '_' . basename($_FILES['rektor_foto']['name']);
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['rektor_foto']['tmp_name'], $filePath)) {
            $stmt->execute(['uploads/' . $fileName, 'rektor_foto']);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Data Prakata Rektor berhasil diperbarui.']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
?>
