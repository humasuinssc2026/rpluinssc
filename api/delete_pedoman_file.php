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
        echo json_encode(['success' => false, 'message' => 'Hanya admin yang dapat menghapus data.']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['id'])) {
        throw new Exception("ID file tidak ditemukan.");
    }
    
    $fileId = $data['id'];

    // Dapatkan path file untuk dihapus secara fisik
    $stmt = $pdo->prepare("SELECT file_path FROM pedoman_files WHERE id = ?");
    $stmt->execute([$fileId]);
    $filePath = $stmt->fetchColumn();

    if ($filePath && file_exists(__DIR__ . '/../' . $filePath)) {
        unlink(__DIR__ . '/../' . $filePath);
    }

    $stmtDelete = $pdo->prepare("DELETE FROM pedoman_files WHERE id = ?");
    $stmtDelete->execute([$fileId]);

    echo json_encode([
        'success' => true,
        'message' => 'File berhasil dihapus.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menghapus file: ' . $e->getMessage()
    ]);
}
?>
