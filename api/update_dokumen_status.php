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

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['peserta_id']) || !isset($data['doc_key']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit;
}

$pesertaId = $data['peserta_id'];
$docKey = $data['doc_key']; // e.g., 'f1', 'nf2'
$status = $data['status']; // 'approved', 'rejected'
$catatan = $data['catatan'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT dokumen_status, dokumen_catatan FROM users WHERE id = ?");
    $stmt->execute([$pesertaId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $docStatus = $row['dokumen_status'] ? json_decode($row['dokumen_status'], true) : [];
    $docStatus[$docKey] = $status;
    $newStatusJson = json_encode($docStatus);
    
    $docCatatan = $row['dokumen_catatan'] ? json_decode($row['dokumen_catatan'], true) : [];
    if ($status === 'rejected') {
        $docCatatan[$docKey] = $catatan;
    } else {
        unset($docCatatan[$docKey]);
    }
    $newCatatanJson = json_encode($docCatatan);
    
    $updateStmt = $pdo->prepare("UPDATE users SET dokumen_status = ?, dokumen_catatan = ? WHERE id = ?");
    $updateStmt->execute([$newStatusJson, $newCatatanJson, $pesertaId]);
    
    // Log to audit_logs
    $logAction = "Update Dokumen Status ($docKey) to $status";
    $logStmt = $pdo->prepare("INSERT INTO audit_logs (admin_id, action, target) VALUES (?, ?, ?)");
    $logStmt->execute([$userId, $logAction, "Peserta ID: $pesertaId"]);
    
    // Add notification if rejected
    if ($status === 'rejected') {
        $pesan = "Dokumen Anda ($docKey) ditolak dengan catatan: '$catatan'. Silakan unggah ulang pada dashboard Anda.";
        $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, pesan) VALUES (?, ?)");
        $notifStmt->execute([$pesertaId, $pesan]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Status dokumen berhasil diperbarui.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status dokumen.']);
}
?>
