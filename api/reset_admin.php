<?php
require 'db.php';
$passwordAdmin = password_hash('admin123', PASSWORD_DEFAULT);
$pdo->exec("UPDATE users SET password = '$passwordAdmin' WHERE email = 'admin@uinssc.ac.id'");
echo "Password reset to admin123";
?>
