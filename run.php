<?php
require 'api/db.php';
try {
    $pdo->exec('ALTER TABLE users ADD COLUMN dokumen_lengkap TEXT');
} catch(Exception $e) {}
echo "Migration OK\n";
?>
