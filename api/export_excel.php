<?php
error_reporting(0); // Sembunyikan warning PHP agar tidak merusak CSV
require 'db.php';

// Verifikasi Admin lewat Token (simulasi)
$token = $_GET['token'] ?? '';
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ? AND role = 'admin'");
$stmt->execute([$token]);
if (!$stmt->fetch()) {
    die("Akses ditolak!");
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Data_Pendaftar_RPL_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');
// Tambahkan BOM agar Excel membaca karakter UTF-8 dengan benar
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header CSV (menggunakan delimiter ; agar rapih di Excel region Indonesia)
fputcsv($output, ['ID', 'Nama Lengkap', 'NIK', 'Email', 'No HP', 'Tanggal Lahir', 'Program Studi', 'Status Pendaftaran', 'Status Pembayaran', 'Tanggal Daftar', 'Alamat', 'Pendidikan Terakhir', 'Nama Ibu', 'Asal Instansi'], ';', '"', "");

$stmt = $pdo->query("SELECT id, nama, nik, email, nomor_hp, tanggal_lahir, prodi, status_verifikasi, status_pembayaran, tanggal_daftar, alamat_lengkap, pendidikan_terakhir, nama_ibu, asal_instansi FROM users WHERE role = 'peserta' ORDER BY tanggal_daftar DESC");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['id'],
        $row['nama'],
        "'" . $row['nik'], // Tambahkan petik agar NIK tidak jadi format scientific di Excel
        $row['email'],
        "'" . $row['nomor_hp'],
        $row['tanggal_lahir'],
        $row['prodi'],
        $row['status_verifikasi'],
        $row['status_pembayaran'],
        $row['tanggal_daftar'],
        $row['alamat_lengkap'],
        $row['pendidikan_terakhir'],
        $row['nama_ibu'],
        $row['asal_instansi']
    ], ';', '"', "");
}
fclose($output);
?>
