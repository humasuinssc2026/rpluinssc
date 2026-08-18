<?php
header('Content-Type: application/json');
require 'db.php';
require_once 'auth.php';

$token = $_POST['user_id'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$admin = authenticateUser($pdo, $token);
if (!$admin || $admin['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak! Hanya admin.']);
    exit;
}

$nama = $_POST['rektor_nama'] ?? '';
$jabatan = $_POST['rektor_jabatan'] ?? '';
$teks = $_POST['rektor_teks'] ?? '';

if (!$nama || !$jabatan || !$teks) {
    echo json_encode(['success' => false, 'message' => 'Semua kolom teks harus diisi.']);
    exit;
}

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    $stmt->execute([$nama, 'rektor_nama']);
    $stmt->execute([$jabatan, 'rektor_jabatan']);
    $stmt->execute([$teks, 'rektor_teks']);

    // Handle File Upload
    if (isset($_FILES['rektor_foto']) && $_FILES['rektor_foto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['rektor_foto'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($ext, $allowed)) {
            if (!is_dir('../uploads')) mkdir('../uploads', 0775, true);
            
            $filename = 'rektor_' . time() . '.' . $ext;
            $filepath = '../uploads/' . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $dbpath = 'uploads/' . $filename;
                $stmt->execute([$dbpath, 'rektor_foto']);
            }
        } else {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Format foto tidak valid. Gunakan JPG atau PNG.']);
            exit;
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Pengaturan Sambutan Pimpinan berhasil disimpan.']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem.']);
}
?>
