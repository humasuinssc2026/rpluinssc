<?php
header('Content-Type: application/json');
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        echo json_encode(['success' => true, 'data' => $settings]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal mengambil pengaturan.']);
    }
} elseif ($method === 'POST') {
    require_once 'auth.php';
$token = $_POST['user_id'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$admin = authenticateUser($pdo, $token);
$userId = $admin ? $admin['id'] : null;

    if (!$admin || $admin['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Akses ditolak! Hanya admin.']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    try {
        $pdo->beginTransaction();
        
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
        $stmt_insert = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt_update = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        
        foreach ($data as $key => $value) {
            // Cek apakah key ada
            $stmt_check->execute([$key]);
            if ($stmt_check->fetchColumn() > 0) {
                // Update
                $stmt_update->execute([$value, $key]);
            } else {
                // Insert
                $stmt_insert->execute([$key, $value]);
            }
        }

        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Pengaturan jadwal berhasil disimpan.']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pengaturan.']);
    }
}
?>
