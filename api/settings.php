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
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        
        if (isset($data['jadwal_mulai'])) $stmt->execute([$data['jadwal_mulai'], 'jadwal_mulai']);
        if (isset($data['jadwal_selesai'])) $stmt->execute([$data['jadwal_selesai'], 'jadwal_selesai']);
        if (isset($data['is_open'])) $stmt->execute([$data['is_open'], 'is_open']);
        
        if (isset($data['kontak_wa'])) $stmt->execute([$data['kontak_wa'], 'kontak_wa']);
        if (isset($data['kontak_email'])) $stmt->execute([$data['kontak_email'], 'kontak_email']);
        if (isset($data['kontak_telepon'])) $stmt->execute([$data['kontak_telepon'], 'kontak_telepon']);
        if (isset($data['teks_berjalan'])) $stmt->execute([$data['teks_berjalan'], 'teks_berjalan']);

        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Pengaturan jadwal berhasil disimpan.']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pengaturan.']);
    }
}
?>
