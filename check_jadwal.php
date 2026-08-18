<?php
$pdo = new PDO('sqlite:c:/Users/info/rpl/database.sqlite');
$stmt = $pdo->query('SELECT * FROM jadwal');
if($stmt) {
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo "Tabel jadwal kosong atau tidak ada";
}
