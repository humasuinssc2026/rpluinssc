<?php
header('Content-Type: application/json');
require 'db.php';

try {
    $stmtPengumuman = $pdo->query("SELECT * FROM pengumuman ORDER BY id DESC");
    $pengumuman = $stmtPengumuman->fetchAll(PDO::FETCH_ASSOC);

    $stmtUnduhan = $pdo->query("SELECT * FROM unduhan ORDER BY id DESC");
    $unduhan = $stmtUnduhan->fetchAll(PDO::FETCH_ASSOC);

    $stmtSettings = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'rektor_%'");
    $settingsRaw = $stmtSettings->fetchAll(PDO::FETCH_ASSOC);
    $rektor = [];
    foreach ($settingsRaw as $s) {
        $rektor[$s['setting_key']] = $s['setting_value'];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'pengumuman' => $pengumuman,
            'unduhan' => $unduhan,
            'rektor' => $rektor
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal mengambil konten: ' . $e->getMessage()]);
}
?>
