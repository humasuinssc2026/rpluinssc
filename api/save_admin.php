<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
require_once 'auth.php';
$token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$user = authenticateUser($pdo, $token);
$adminId = $user ? $user['id'] : null;

// Check admin role
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$adminId]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin || $admin['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak!']);
    exit;
}

$id = $data['id'] ?? '';
$nama = $data['nama'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
// Set NIK dummy agar tidak null, atau generate jika diperlukan
$nik = 'ADMIN' . time(); 

if (empty($nama) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Nama dan email wajib diisi']);
    exit;
}

try {
    if (empty($id)) {
        // Create new admin
        if (empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Password wajib diisi untuk admin baru']);
            exit;
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (nama, nik, email, password, role, status_verifikasi) VALUES (?, ?, ?, ?, 'admin', 'Terverifikasi')");
        $stmt->execute([$nama, $nik, $email, $hashedPassword]);
        
        echo json_encode(['success' => true, 'message' => 'Admin berhasil ditambahkan']);
    } else {
        // Update existing admin
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET nama = ?, email = ?, password = ? WHERE id = ? AND role = 'admin'");
            $stmt->execute([$nama, $email, $hashedPassword, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET nama = ?, email = ? WHERE id = ? AND role = 'admin'");
            $stmt->execute([$nama, $email, $id]);
        }
        echo json_encode(['success' => true, 'message' => 'Data admin berhasil diupdate']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data: ' . $e->getMessage()]);
}
?>
