<?php
header('Content-Type: application/json');
require_once 'db.php';

$isAdmin = isset($_GET['admin']) && $_GET['admin'] == '1';

try {
    if ($isAdmin) {
        $stmt = $pdo->query("SELECT * FROM sliders ORDER BY urutan ASC, id DESC");
    } else {
        $stmt = $pdo->query("SELECT * FROM sliders WHERE is_active = 1 ORDER BY urutan ASC, id DESC");
    }
    
    $sliders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'status' => 'success', 'data' => $sliders]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
