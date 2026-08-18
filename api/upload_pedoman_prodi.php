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
        echo json_encode(['success' => false, 'message' => 'Hanya admin yang dapat mengunggah pedoman.']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pedoman']) && isset($_POST['prodi_id']) && isset($_POST['nama_dokumen'])) {
        $prodiId = $_POST['prodi_id'];
        $namaDokumen = $_POST['nama_dokumen'];
        $file = $_FILES['pedoman'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error saat upload file.");
        }
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx'];
        
        if (!in_array($ext, $allowed)) {
            throw new Exception("Format file tidak diizinkan. Hanya PDF, DOC, DOCX yang diperbolehkan.");
        }
        
        $uploadDir = __DIR__ . '/../uploads/pedoman/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = 'pedoman_' . $prodiId . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $destPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $webPath = 'uploads/pedoman/' . $fileName;
            
            $stmtInsert = $pdo->prepare("INSERT INTO pedoman_files (prodi_id, nama_dokumen, file_path) VALUES (?, ?, ?)");
            $stmtInsert->execute([$prodiId, $namaDokumen, $webPath]);
            
            echo json_encode([
                'success' => true,
                'message' => 'File pedoman berhasil diunggah.',
                'path' => $webPath
            ]);
        } else {
            throw new Exception("Gagal memindahkan file yang diupload.");
        }
    } else {
        throw new Exception("File, Nama Dokumen, atau ID Prodi tidak ditemukan dalam request.");
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal: ' . $e->getMessage()
    ]);
}
?>
