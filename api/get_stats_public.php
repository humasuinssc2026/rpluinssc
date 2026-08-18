<?php
header('Content-Type: application/json');
require 'db.php';

try {
    $total_prodi = 0;
    try {
        $stmt2 = $pdo->query("SELECT COUNT(*) as total FROM prodi");
        $total_prodi = $stmt2->fetch(PDO::FETCH_ASSOC)['total'];
    } catch(Exception $e) {}

    // Dummy data untuk statistik karena ini murni CMS
    echo json_encode([
        'success' => true,
        'data' => [
            'pendaftar' => 1250, // Mahasiswa Aktif
            'prodi' => $total_prodi,
            'sks' => 450 // Jumlah Lulusan
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal mengambil statistik.']);
}
?>
