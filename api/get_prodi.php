<?php
require_once 'db.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT p.*, (SELECT COUNT(id) FROM pedoman_files pf WHERE pf.prodi_id = p.id) as pedoman_count FROM prodi p ORDER BY p.id ASC");
    $prodiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $prodiList
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil data prodi: ' . $e->getMessage()
    ]);
}
?>
