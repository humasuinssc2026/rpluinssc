<?php
require 'db.php';
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS pedoman_files (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        prodi_id INTEGER,
        nama_dokumen TEXT NOT NULL,
        file_path TEXT NOT NULL,
        tanggal DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Tabel pedoman_files berhasil dibuat.\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
