<?php
require_once 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID Galeri tidak diberikan.']);
    exit;
}

$id = $_POST['id'];

try {
    // Ambil path file terlebih dahulu
    $stmt = $pdo->prepare("SELECT image_path FROM galeri WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        $filePath = __DIR__ . '/../' . $file['image_path'];
        
        // Hapus file fisik jika ada
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Hapus dari database
        $deleteStmt = $pdo->prepare("DELETE FROM galeri WHERE id = ?");
        $deleteStmt->execute([$id]);

        echo json_encode([
            'success' => true,
            'message' => 'Gambar galeri berhasil dihapus.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Data tidak ditemukan.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menghapus data: ' . $e->getMessage()
    ]);
}
?>
