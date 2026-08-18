<?php
require 'api/db.php';
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN alamat_lengkap TEXT");
    $pdo->exec("ALTER TABLE users ADD COLUMN pendidikan_terakhir TEXT");
    $pdo->exec("ALTER TABLE users ADD COLUMN nama_ibu TEXT");
    $pdo->exec("ALTER TABLE users ADD COLUMN asal_instansi TEXT");
    echo "Migration Success\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
