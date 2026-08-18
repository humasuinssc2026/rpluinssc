<?php
$pdo = new PDO('sqlite:database.sqlite');
$pdo->exec("UPDATE settings SET setting_value = '1250' WHERE setting_key = 'stat_mahasiswa'");
$pdo->exec("UPDATE settings SET setting_value = '450' WHERE setting_key = 'stat_lulusan'");
echo "Updated default values";
