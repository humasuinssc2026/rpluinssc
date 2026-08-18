<?php
// Tentukan path ke file SQLite
$dbFile = __DIR__ . '/../database.sqlite';

try {
    // Buat koneksi (dan otomatis buat file database jika belum ada)
    $pdo = new PDO("sqlite:" . $dbFile);
    // Atur agar PDO memunculkan exception jika ada error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buat tabel users (calon mahasiswa dan admin) jika belum ada
    $query = "
        CREATE TABLE IF NOT EXISTS users (
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
        );
    ";
    $pdo->exec($query);

    // Buat tabel pengumuman
    $pdo->exec("CREATE TABLE IF NOT EXISTS pengumuman (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        judul TEXT NOT NULL,
        isi TEXT NOT NULL,
        tanggal DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Buat tabel unduhan
    $pdo->exec("CREATE TABLE IF NOT EXISTS unduhan (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nama_dokumen TEXT NOT NULL,
        file_path TEXT NOT NULL,
        tanggal DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Buat tabel prodi
    $pdo->exec("CREATE TABLE IF NOT EXISTS prodi (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nama_prodi TEXT NOT NULL,
        nomor_penyelenggara TEXT NOT NULL,
        sertifikat_path TEXT,
        pedoman_path TEXT
    )");

    // Buat tabel pedoman_files
    $pdo->exec("CREATE TABLE IF NOT EXISTS pedoman_files (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        prodi_id INTEGER,
        nama_dokumen TEXT NOT NULL,
        file_path TEXT NOT NULL,
        tanggal DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Insert default prodi jika kosong
    $stmt = $pdo->query("SELECT COUNT(*) FROM prodi");
    if ($stmt->fetchColumn() == 0) {
        $defaultProdi = [
            ['S1 Hukum Ekonomi Syariah', '201031742342026132710'],
            ['S1 Tadris Ilmu Pengetahuan Sosial', '201031872202026132708'],
            ['S1 Pendidikan Islam Anak Usia Dini', '201031862332026132709'],
            ['S1 Manajemen Pendidikan Islam', '201031862312026132713'],
            ['S1 Sejarah Peradaban Islam', '201031802302026132711'],
            ['S1 Bimbingan dan Konseling Islam', '201031702322026132712'],
            ['S1 Akuntansi Syariah', '20103162202026132931']
        ];
        
        $insertStmt = $pdo->prepare("INSERT INTO prodi (nama_prodi, nomor_penyelenggara) VALUES (?, ?)");
        foreach ($defaultProdi as $p) {
            $insertStmt->execute($p);
        }
    }

    // Buat tabel settings
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key TEXT PRIMARY KEY,
        setting_value TEXT
    )");

    // Insert default settings jika kosong
    $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('jadwal_mulai', '2026-06-25T23:00')");
        $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('jadwal_selesai', '2026-07-30T23:59')");
        $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('is_open', 'true')");
    }

    $pdo->exec("INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES ('rektor_nama', 'Prof. Dr. H. Aan Jaelani, M.Ag')");
    $pdo->exec("INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES ('rektor_jabatan', 'Rektor UIN Siber Syekh Nurjati Cirebon')");
    $pdo->exec("INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES ('rektor_teks', 'Selamat datang di Portal Rekognisi Pembelajaran Lampau (RPL) Universitas Islam Negeri Siber Syekh Nurjati Cirebon. Program RPL ini kami dedikasikan untuk memberikan akses pendidikan tinggi yang inklusif dan berkualitas bagi seluruh lapisan masyarakat. Kami menghargai setiap pengalaman kerja dan kompetensi yang telah Anda bangun, serta memberikan peluang untuk mengonversinya menjadi SKS agar Anda dapat meraih gelar sarjana dengan lebih cepat. UIN Siber Syekh Nurjati Cirebon berkomitmen untuk terus menjadi pionir pendidikan siber Islam terdepan yang adaptif terhadap perkembangan zaman.')");
    $pdo->exec("INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES ('rektor_foto', 'uploads/foto_rektor.png')");

    // Pengaturan Web Default
    $pdo->exec("INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES ('kontak_wa', '6281234567890')");
    $pdo->exec("INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES ('kontak_email', 'admisi.uinssc')");
    $pdo->exec("INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES ('kontak_telepon', '082231820660')");
    $pdo->exec("INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES ('teks_berjalan', 'Pendaftaran RPL Program Sarjana (S-1) Gelombang 1 Dibuka — Batas akhir pembayaran: 30 Juli 2026 23:59:00 WIB')");
    $pdo->exec("INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES ('stat_mahasiswa', '1250')");
    $pdo->exec("INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES ('stat_lulusan', '450')");

    // Otomatis buat 1 akun admin default jika belum ada
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    if ($stmt->fetchColumn() == 0) {
        $passwordAdmin = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (nama, nik, email, password, role, status_verifikasi) 
                    VALUES ('Administrator', '0000000000000000', 'admin@uinssc.ac.id', '$passwordAdmin', 'admin', 'Terverifikasi')");
    }

    // --- MIGRATION PHASE 3 ---
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN dokumen_status TEXT");
    } catch (PDOException $e) {} // Abaikan jika sudah ada

    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN token TEXT");
    } catch (PDOException $e) {} // Abaikan jika sudah ada

    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN dokumen_catatan TEXT");
    } catch (PDOException $e) {} // Abaikan jika sudah ada

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

    // Tabel untuk Hasil Asesmen Konversi SKS
    $pdo->exec("CREATE TABLE IF NOT EXISTS hasil_rpl (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        peserta_id INTEGER,
        kode_mk TEXT,
        nama_mk TEXT,
        sks INTEGER,
        nilai TEXT
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
