<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Tidak ada file gambar yang diunggah.']);
    exit;
}

$file = $_FILES['image'];
$uploadDir = __DIR__ . '/../uploads/galeri/';

// Buat direktori jika belum ada
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Validasi tipe file (hanya izinkan gambar)
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
$fileType = mime_content_type($file['tmp_name']);

if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Tipe file tidak valid. Hanya JPG, PNG, atau WEBP yang diperbolehkan.']);
    exit;
}

// Generate nama file unik
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('galeri_') . '.' . strtolower($extension);
$destination = $uploadDir . $filename;
$imagePath = 'uploads/galeri/' . $filename;

if (move_uploaded_file($file['tmp_name'], $destination)) {
    try {
        $stmt = $pdo->prepare("INSERT INTO galeri (image_path) VALUES (?)");
        $stmt->execute([$imagePath]);

        echo json_encode([
            'success' => true,
            'message' => 'Gambar galeri berhasil diunggah.'
        ]);
    } catch (Exception $e) {
        // Hapus file yang terlanjur diunggah jika gagal simpan ke DB
        if (file_exists($destination)) {
            unlink($destination);
        }
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengunggah gambar.']);
}
?>
