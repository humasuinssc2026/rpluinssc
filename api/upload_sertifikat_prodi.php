<?php
require_once 'db.php';
header('Content-Type: application/json');

// Validasi admin
$headers = getallheaders();
$userId = $headers['Authorization'] ?? '';

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE token = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Hanya admin yang dapat mengunggah sertifikat.']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['sertifikat']) && isset($_POST['prodi_id'])) {
        $prodiId = $_POST['prodi_id'];
        $file = $_FILES['sertifikat'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error saat upload file.");
        }
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'png', 'jpg', 'jpeg'];
        
        if (!in_array($ext, $allowed)) {
            throw new Exception("Format file tidak diizinkan. Hanya PDF, PNG, JPG yang diperbolehkan.");
        }
        
        $uploadDir = __DIR__ . '/../uploads/sertifikat/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = 'sertifikat_' . $prodiId . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $webPath = 'uploads/sertifikat/' . $fileName;
            
            // Hapus file lama jika ada
            $stmtCheck = $pdo->prepare("SELECT sertifikat_path FROM prodi WHERE id = ?");
            $stmtCheck->execute([$prodiId]);
            $oldPath = $stmtCheck->fetchColumn();
            if ($oldPath && file_exists(__DIR__ . '/../' . $oldPath)) {
                unlink(__DIR__ . '/../' . $oldPath);
            }
            
            $stmtUpdate = $pdo->prepare("UPDATE prodi SET sertifikat_path = ? WHERE id = ?");
            $stmtUpdate->execute([$webPath, $prodiId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Sertifikat berhasil diunggah.',
                'path' => $webPath
            ]);
        } else {
            throw new Exception("Gagal memindahkan file yang diupload.");
        }
    } else {
        throw new Exception("File atau ID Prodi tidak ditemukan dalam request.");
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal: ' . $e->getMessage()
    ]);
}
?>
