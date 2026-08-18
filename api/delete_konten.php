<?php
header('Content-Type: application/json');
require 'db.php';

require_once 'auth.php';
$token = $_POST['user_id'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$admin = authenticateUser($pdo, $token);
$userId = $admin ? $admin['id'] : null;

if (!$admin || $admin['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak! Hanya admin.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';
$type = $data['type'] ?? '';

if (empty($id) || empty($type)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit;
}

try {
    if ($type === 'pengumuman') {
        $stmt = $pdo->prepare("DELETE FROM pengumuman WHERE id = ?");
        $stmt->execute([$id]);
    } else if ($type === 'unduhan') {
        // Ambil file path dulu untuk dihapus
        $stmt = $pdo->prepare("SELECT file_path FROM unduhan WHERE id = ?");
        $stmt->execute([$id]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($doc) {
            $filePath = __DIR__ . '/../uploads/dokumen_landing/' . $doc['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $stmt = $pdo->prepare("DELETE FROM unduhan WHERE id = ?");
            $stmt->execute([$id]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Tipe tidak valid.']);
        exit;
    }
    
    echo json_encode(['success' => true, 'message' => 'Konten berhasil dihapus.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus konten.']);
}
?>
