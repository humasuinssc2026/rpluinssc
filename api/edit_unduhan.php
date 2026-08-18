<?php
header('Content-Type: application/json');
require 'db.php';

// Verifikasi auth (sederhana)
$headers = getallheaders();
$userId = $headers['Authorization'] ?? '';

if (empty($userId)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = $_POST['id'] ?? '';
$nama_dokumen = $_POST['nama_dokumen'] ?? '';

if (empty($id) || empty($nama_dokumen)) {
    echo json_encode(['success' => false, 'message' => 'ID dan nama dokumen harus diisi.']);
    exit;
}

// Cek apakah ada file yang diunggah
if (isset($_FILES['file_dokumen']) && $_FILES['file_dokumen']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/dokumen_landing/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . '_' . basename($_FILES['file_dokumen']['name']);
    $uploadPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['file_dokumen']['tmp_name'], $uploadPath)) {
        // Hapus file lama jika ada
        $stmt = $pdo->prepare("SELECT file_path FROM unduhan WHERE id = ?");
        $stmt->execute([$id]);
        $oldFile = $stmt->fetchColumn();
        if ($oldFile && file_exists($uploadDir . $oldFile)) {
            unlink($uploadDir . $oldFile);
        }

        $stmt = $pdo->prepare("UPDATE unduhan SET nama_dokumen = ?, file_path = ? WHERE id = ?");
        if ($stmt->execute([$nama_dokumen, $fileName, $id])) {
            echo json_encode(['success' => true, 'message' => 'Dokumen berhasil diperbarui.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui database.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengunggah file baru.']);
    }
} else {
    // Hanya update nama
    $stmt = $pdo->prepare("UPDATE unduhan SET nama_dokumen = ? WHERE id = ?");
    if ($stmt->execute([$nama_dokumen, $id])) {
        echo json_encode(['success' => true, 'message' => 'Nama dokumen berhasil diperbarui.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui database.']);
    }
}
?>
