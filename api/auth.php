<?php
require_once __DIR__ . '/db.php';

function authenticateUser($pdo, $token) {
    if (empty($token)) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE token = ?");
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
