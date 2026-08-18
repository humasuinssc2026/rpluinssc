<?php
$pdo = new PDO('sqlite:c:/Users/info/rpl/database.sqlite');
$stmt = $pdo->query('SELECT * FROM settings');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
