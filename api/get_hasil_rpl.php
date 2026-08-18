<?php
header('Content-Type: application/json');
require 'db.php';

$pesertaId = $_GET['peserta_id'] ?? null;

if (!$pesertaId) {
    echo json_encode(['success' => false, 'message' => 'Peserta ID diperlukan.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM hasil_rpl WHERE peserta_id = ? ORDER BY id ASC");
    $stmt->execute([$pesertaId]);
    $hasil = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $hasil]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal mengambil data.']);
}
?>
