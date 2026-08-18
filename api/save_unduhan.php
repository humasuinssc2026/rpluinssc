<?php
header('Content-Type: application/json');
require 'db.php';

require_once 'auth.php';
$token = $_POST['user_id'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$admin = authenticateUser($pdo, $token);
$userId = $admin ? $admin['id'] : null;

if (!$admin || $admin['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak! Hanya admin.']);
    exit;
}

// Deteksi jika formulir sama sekali kosong (biasanya karena file terlalu besar melebihi post_max_size)
if (empty($_POST) && empty($_FILES)) {
    echo json_encode(['success' => false, 'message' => 'Gagal: Server menerima form kosong. Kemungkinan ukuran file PDF Anda (meski di bawah 2MB) tetap melebihi batas maksimal konfigurasi PHP lokal saat ini.']);
    exit;
}

file_put_contents('save_unduhan_debug.log', print_r($_POST, true) . "\n" . print_r($_FILES, true));

$nama_dokumen = $_POST['nama_dokumen'] ?? '';
if (empty($nama_dokumen) || !isset($_FILES['file_dokumen'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Nama dokumen dan file harus diisi.',
        'debug_post' => $_POST,
        'debug_files' => $_FILES,
        'debug_nama' => $nama_dokumen
    ]);
    exit;
}

$file = $_FILES['file_dokumen'];
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'];

if (!in_array(strtolower($ext), $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Format file tidak diizinkan.']);
    exit;
}

$uploadDir = __DIR__ . '/../uploads/dokumen_landing/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", $file['name']);
$targetPath = $uploadDir . $fileName;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    try {
        $stmt = $pdo->prepare("INSERT INTO unduhan (nama_dokumen, file_path) VALUES (?, ?)");
        $stmt->execute([$nama_dokumen, $fileName]);
        echo json_encode(['success' => true, 'message' => 'Dokumen berhasil diunggah.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengunggah file.']);
}
?>
