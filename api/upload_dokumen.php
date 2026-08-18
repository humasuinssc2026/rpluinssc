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

$uploadDir = '../uploads/dokumen/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Get existing docs and status
$stmt = $pdo->prepare("SELECT dokumen_lengkap, dokumen_status, dokumen_catatan FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$existingDocs = [];
$existingStatus = [];
$existingCatatan = [];
if ($user) {
    if (!empty($user['dokumen_lengkap'])) $existingDocs = json_decode($user['dokumen_lengkap'], true) ?? [];
    if (!empty($user['dokumen_status'])) $existingStatus = json_decode($user['dokumen_status'], true) ?? [];
    if (!empty($user['dokumen_catatan'])) $existingCatatan = json_decode($user['dokumen_catatan'], true) ?? [];
}

$docsUpdated = false;

// Allowed doc keys
$allowedKeys = [
    'f1', 'f2', 'f3', 
    'nf1', 'nf2', 'nf3', 'nf4', 'nf5', 'nf6', 'nf7', 'nf8', 'nf9', 'nf10', 'nf11', 'nf12', 'nf13'
];

foreach ($allowedKeys as $key) {
    if (isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION);
        if (strtolower($ext) !== 'pdf') {
            echo json_encode(['success' => false, 'message' => "Gagal: Dokumen $key harus berformat PDF!"]);
            exit;
        }
        $fileName = $key . '_' . $userId . '_' . time() . '.pdf';
        if (move_uploaded_file($_FILES[$key]['tmp_name'], $uploadDir . $fileName)) {
            $existingDocs[$key] = $fileName;
            if (isset($existingStatus[$key])) unset($existingStatus[$key]);
            if (isset($existingCatatan[$key])) unset($existingCatatan[$key]);
            $docsUpdated = true;
        }
    }
}

if ($docsUpdated) {
    try {
        $jsonStr = json_encode($existingDocs);
        $statusStr = json_encode($existingStatus);
        $catatanStr = json_encode($existingCatatan);
        $stmt = $pdo->prepare("UPDATE users SET dokumen_lengkap = ?, dokumen_status = ?, dokumen_catatan = ? WHERE id = ?");
        $stmt->execute([$jsonStr, $statusStr, $catatanStr, $userId]);
        echo json_encode(['success' => true, 'message' => 'Dokumen berhasil diunggah dan disimpan.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Tidak ada file baru yang valid untuk diunggah.']);
}
?>
