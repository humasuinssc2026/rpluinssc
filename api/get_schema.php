<?php
$db = new PDO('sqlite:../database.sqlite');
$stmt = $db->query("SELECT sql FROM sqlite_master WHERE type='table'");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['sql'] . "\n";
}
