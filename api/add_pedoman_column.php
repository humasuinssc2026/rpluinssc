<?php
require 'db.php';
try {
    $pdo->exec("ALTER TABLE prodi ADD COLUMN pedoman_path TEXT");
    echo "Kolom pedoman_path berhasil ditambahkan.\n";
} catch(Exception $e) {
    echo "Info/Error: " . $e->getMessage() . "\n";
}
?>
