<?php
$pdo = new PDO('sqlite:database.sqlite');
$stmt = $pdo->query('SELECT * FROM settings');
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
