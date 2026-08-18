<?php
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_GET['prodi_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID Prodi tidak diberikan.']);
    exit;
}

$prodiId = $_GET['prodi_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM pedoman_files WHERE prodi_id = ?");
    $stmt->execute([$prodiId]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    usort($files, function($a, $b) {
        return strnatcasecmp(trim($a['nama_dokumen']), trim($b['nama_dokumen']));
    });

    echo json_encode([
        'success' => true,
        'data' => $files
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil data: ' . $e->getMessage()
    ]);
}
?>
