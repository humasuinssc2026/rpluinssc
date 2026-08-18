<?php
require 'api/db.php';

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN dokumen_formal TEXT");
    echo "Added dokumen_formal\n";
} catch(Exception $e) {
    echo "Failed formal: " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN dokumen_nonformal TEXT");
    echo "Added dokumen_nonformal\n";
} catch(Exception $e) {
    echo "Failed nonformal: " . $e->getMessage() . "\n";
}

$stmt = $pdo->query("PRAGMA table_info(users)");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
