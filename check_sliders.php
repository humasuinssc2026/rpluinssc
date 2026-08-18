<?php
$pdo = new PDO('sqlite:c:/Users/info/rpl/database.sqlite');
$stmt = $pdo->query('SELECT * FROM sliders');
if($stmt) {
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo "Tabel sliders kosong atau tidak ada";
}
