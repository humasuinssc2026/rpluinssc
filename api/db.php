<?php
// Tentukan path ke file SQLite
$dbFile = __DIR__ . '/../database.sqlite';



    

    

    $pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        admin_id INTEGER,
        action TEXT,
        target TEXT,
        tanggal DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        pesan TEXT,
        is_read INTEGER DEFAULT 0,
        tanggal DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    

    // Tabel untuk Slider Header Dinamis
    $pdo->exec("CREATE TABLE IF NOT EXISTS sliders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        image_path TEXT NOT NULL,
        title TEXT,
        subtitle TEXT,
        link_url TEXT,
        link_text TEXT,
        is_active INTEGER DEFAULT 1,
        urutan INTEGER DEFAULT 0,
        tanggal DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Tabel untuk Galeri & Dokumentasi
    $pdo->exec("CREATE TABLE IF NOT EXISTS galeri (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        image_path TEXT NOT NULL,
        tanggal DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Tambahkan kolom jadwal asesmen ke tabel users jika belum ada
    try { $pdo->exec("ALTER TABLE users ADD COLUMN jadwal_asesmen_tgl TEXT;"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE users ADD COLUMN jadwal_asesmen_lokasi TEXT;"); } catch (PDOException $e) {}

    // Tabel untuk Manajemen Jadwal Dinamis
    $pdo->exec("CREATE TABLE IF NOT EXISTS jadwal (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        kegiatan TEXT NOT NULL,
        tanggal TEXT NOT NULL,
        urutan INTEGER DEFAULT 0
    )");

    // Insert default jadwal jika tabel kosong
    $stmt = $pdo->query("SELECT COUNT(*) FROM jadwal");
    if ($stmt->fetchColumn() == 0) {
        $defaultJadwal = [
            ['Pendaftaran & Unggah Portofolio', '18 Mei - 14 Juli 2026', 1],
            ['Seleksi Administrasi', '17 Juli 2026', 2],
            ['Asessmen Portofolio*', '18 - 20 Juli 2026', 3],
            ['Penetapan Kelulusan', '22 Juli 2026', 4],
            ['Penetapan Mata Kuliah ditempuh (per calon mahasiswa)', '22 Juli 2026', 5],
            ['Pengumuman', '22 Juli 2026', 6],
            ['Registrasi', '22 Juli 2026', 7]
        ];
        $insertJadwal = $pdo->prepare("INSERT INTO jadwal (kegiatan, tanggal, urutan) VALUES (?, ?, ?)");
        foreach ($defaultJadwal as $j) {
            $insertJadwal->execute($j);
        }
    }

} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}
?>
