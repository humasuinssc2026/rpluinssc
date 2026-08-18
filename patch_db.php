<?php
$file = 'c:\\Users\\info\\rpl\\api\\db.php';
$php = file_get_contents($file);

// Replace users table creation
$old_users = "CREATE TABLE IF NOT EXISTS users (
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

$new_users = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT DEFAULT 'admin',
            token TEXT,
            tanggal_daftar DATETIME DEFAULT CURRENT_TIMESTAMP
        );";

$php = str_replace($old_users, $new_users, $php);

// Remove Phase 3 migrations related to users
$php = preg_replace('/try \{.*ALTER TABLE users ADD COLUMN.*\} catch \(PDOException \$e\) \{\} \/\/ Abaikan jika sudah ada/sU', '', $php);

// Remove hasil_rpl table creation
$php = preg_replace('/\/\/ Tabel untuk Hasil Asesmen Konversi SKS\s*\$pdo->exec\("CREATE TABLE IF NOT EXISTS hasil_rpl \([^)]*\)"\);/s', '', $php);

file_put_contents($file, $php);
echo "db.php patched\n";
