<?php
header('Content-Type: application/json');
require 'db.php';

require_once 'auth.php';
$token = $_POST['user_id'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$admin = authenticateUser($pdo, $token);
$userId = $admin ? $admin['id'] : null;

if (!$admin || $admin['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak!']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$pesertaId = $data['peserta_id'] ?? null;
$mkList = $data['mk_list'] ?? [];

if (!$pesertaId) {
    echo json_encode(['success' => false, 'message' => 'Peserta tidak valid.']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Hapus data lama
    $stmt = $pdo->prepare("DELETE FROM hasil_rpl WHERE peserta_id = ?");
    $stmt->execute([$pesertaId]);
    
    // Insert data baru
    if (!empty($mkList)) {
        $stmt = $pdo->prepare("INSERT INTO hasil_rpl (peserta_id, kode_mk, nama_mk, sks, nilai) VALUES (?, ?, ?, ?, ?)");
        foreach ($mkList as $mk) {
            // Abaikan jika kosong
            if (empty($mk['kode']) && empty($mk['nama'])) continue;
            
            $stmt->execute([
                $pesertaId,
                $mk['kode'],
                $mk['nama'],
                (int)$mk['sks'],
                strtoupper($mk['nilai'])
            ]);
        }
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Nilai hasil konversi RPL berhasil disimpan.']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan nilai: ' . $e->getMessage()]);
}
?>
