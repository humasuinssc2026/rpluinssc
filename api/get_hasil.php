<?php
header('Content-Type: application/json');
require 'db.php';

$peserta_id = $_GET['peserta_id'] ?? '';
require_once 'auth.php';
$token = $_POST['user_id'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$user = authenticateUser($pdo, $token);
$userId = $user ? $user['id'] : null;

if (empty($peserta_id)) {
    echo json_encode(['success' => false, 'message' => 'Peserta ID tidak valid']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM hasil_rpl WHERE peserta_id = ?");
    $stmt->execute([$peserta_id]);
    $hasil = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $hasil]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal mengambil data']);
}
?>
