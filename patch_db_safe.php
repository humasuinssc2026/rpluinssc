<?php
$file = 'c:\\Users\\info\\rpl\\api\\db.php';
$php = file_get_contents($file);

// Replace users table creation safely
$old_users = "        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama TEXT NOT NULL,
            nik TEXT UNIQUE,
            nomor_hp TEXT,
            tanggal_lahir TEXT,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            prodi TEXT,
            status_verifikasi TEXT DEFAULT 'Menunggu Verifikasi',
            tanggal_daftar DATETIME DEFAULT CURRENT_TIMESTAMP,
            dokumen_formal TEXT,
            dokumen_nonformal TEXT,
            dokumen_lengkap TEXT,
            status_pembayaran TEXT DEFAULT 'Belum Bayar',
            bukti_pembayaran TEXT,
            alamat_lengkap TEXT,
            pendidikan_terakhir TEXT,
            nama_ibu TEXT,
            asal_instansi TEXT
        );";

$new_users = "        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT DEFAULT 'admin',
            token TEXT,
            tanggal_daftar DATETIME DEFAULT CURRENT_TIMESTAMP
        );";

$php = str_replace($old_users, $new_users, $php);

// Safely remove the try/catch blocks one by one instead of using wildcard regex
$php = str_replace('    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN dokumen_status TEXT");
    } catch (PDOException $e) {} // Abaikan jika sudah ada', '', $php);

$php = str_replace('    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN token TEXT");
    } catch (PDOException $e) {} // Abaikan jika sudah ada', '', $php);

$php = str_replace('    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN dokumen_catatan TEXT");
    } catch (PDOException $e) {} // Abaikan jika sudah ada', '', $php);

$php = str_replace('    try { $pdo->exec("ALTER TABLE users ADD COLUMN jadwal_asesmen_tgl TEXT;"); } catch (PDOException $e) {}', '', $php);
$php = str_replace('    try { $pdo->exec("ALTER TABLE users ADD COLUMN jadwal_asesmen_lokasi TEXT;"); } catch (PDOException $e) {}', '', $php);

// Remove hasil_rpl table creation safely
$hasil_table = '    // Tabel untuk Hasil Asesmen Konversi SKS
    $pdo->exec("CREATE TABLE IF NOT EXISTS hasil_rpl (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        peserta_id INTEGER,
        kode_mk TEXT,
        nama_mk TEXT,
        sks INTEGER,
        nilai TEXT
    )");';
$php = str_replace($hasil_table, '', $php);

file_put_contents($file, $php);
echo "db.php patched safely\n";
