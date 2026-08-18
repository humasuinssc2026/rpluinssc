<?php
header('Content-Type: application/json');
require 'db.php';

try {
    // Total pendaftar (selain admin)
    $stmt1 = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role != 'admin' OR role IS NULL");
    $total_pendaftar = $stmt1->fetch(PDO::FETCH_ASSOC)['total'];

    // Total program studi
    $stmt2 = $pdo->query("SELECT COUNT(*) as total FROM prodi");
    $total_prodi = $stmt2->fetch(PDO::FETCH_ASSOC)['total'];

    // Total SKS yang dikonversi
    $stmt3 = $pdo->query("SELECT SUM(sks) as total FROM hasil_rpl");
    $total_sks = $stmt3->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    echo json_encode([
        'success' => true,
        'data' => [
            'pendaftar' => $total_pendaftar,
            'prodi' => $total_prodi,
            'sks' => $total_sks
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal mengambil statistik.']);
}
?>
