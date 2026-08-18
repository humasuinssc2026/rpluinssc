<?php
header('Content-Type: application/json');
require 'db.php';

try {
    $total_prodi = 0;
    try {
        $stmt2 = $pdo->query("SELECT COUNT(*) as total FROM prodi");
        $total_prodi = $stmt2->fetch(PDO::FETCH_ASSOC)['total'];
    } catch(Exception $e) {}

    // Ambil stat dari settings
    $stat_mahasiswa = 1250;
    $stat_lulusan = 450;
    try {
        $stmt_stat = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('stat_mahasiswa', 'stat_lulusan')");
        while ($row = $stmt_stat->fetch(PDO::FETCH_ASSOC)) {
            if ($row['setting_key'] === 'stat_mahasiswa' && is_numeric($row['setting_value'])) $stat_mahasiswa = $row['setting_value'];
            if ($row['setting_key'] === 'stat_lulusan' && is_numeric($row['setting_value'])) $stat_lulusan = $row['setting_value'];
        }
    } catch(Exception $e) {}

    echo json_encode([
        'success' => true,
        'data' => [
            'pendaftar' => $stat_mahasiswa, // Mahasiswa Aktif
            'prodi' => $total_prodi,
            'sks' => $stat_lulusan // Jumlah Lulusan
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal mengambil statistik.']);
}
?>
