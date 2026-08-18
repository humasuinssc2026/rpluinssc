<?php
$dbFile = __DIR__ . '/api/database.sqlite';
if (!file_exists($dbFile)) {
    // try the root folder
    $dbFile = __DIR__ . '/database.sqlite';
}

try {
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->beginTransaction();
    
    // 1. Buat tabel users_new
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT DEFAULT 'admin',
            token TEXT,
            tanggal_daftar DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // 2. Salin data admin saja
    $pdo->exec("INSERT INTO users_new (id, nama, email, password, role, token, tanggal_daftar) 
                SELECT id, nama, email, password, role, token, tanggal_daftar FROM users WHERE role = 'admin'");
    
    // 3. Drop tabel lama
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("DROP TABLE IF EXISTS hasil_rpl");
    
    // 4. Rename tabel baru
    $pdo->exec("ALTER TABLE users_new RENAME TO users");
    
    $pdo->commit();
    echo "Pembersihan database selesai.\n";
} catch (Exception $e) {
    echo "Gagal: " . $e->getMessage() . "\n";
}
