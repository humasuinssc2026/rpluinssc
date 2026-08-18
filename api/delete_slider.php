<?php
header('Content-Type: application/json');
require_once 'db.php';

// Ambil input JSON
$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT image_path FROM sliders WHERE id = ?");
    $stmt->execute([$id]);
    $slider = $stmt->fetch();
    
    if ($slider) {
        // Hapus file fisik
        $filePath = __DIR__ . '/../' . $slider['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        $stmt = $pdo->prepare("DELETE FROM sliders WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true, 'status' => 'success', 'message' => 'Slider berhasil dihapus']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Slider tidak ditemukan']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
