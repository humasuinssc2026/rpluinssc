<?php
require 'api/db.php';

$id = $_GET['id'] ?? null;
if (!$id) die("ID Peserta tidak valid.");

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$peserta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$peserta) die("Peserta tidak ditemukan.");

$stmtMk = $pdo->prepare("SELECT * FROM hasil_rpl WHERE peserta_id = ? ORDER BY id ASC");
$stmtMk->execute([$id]);
$mks = $stmtMk->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SK Konversi SKS - <?= htmlspecialchars($peserta['nama']) ?></title>
    <style>
        body { font-family: "Times New Roman", Times, serif; line-height: 1.5; color: #000; background: #fff; margin: 0; padding: 20px; }
        .kop { text-align: center; border-bottom: 3px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .kop img { width: 80px; position: absolute; left: 30px; top: 20px; }
        .kop h2 { margin: 0; font-size: 1.3rem; text-transform: uppercase; }
        .kop p { margin: 0; font-size: 0.9rem; }
        .content { max-width: 800px; margin: 0 auto; }
        h3.title { text-align: center; text-decoration: underline; margin-bottom: 20px; }
        table.info { width: 100%; margin-bottom: 20px; }
        table.info td { padding: 4px 0; vertical-align: top; }
        table.info td:first-child { width: 200px; font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table.data th, table.data td { border: 1px solid #000; padding: 8px; text-align: left; }
        table.data th { background: #f0f0f0; text-align: center; }
        .ttd { float: right; width: 300px; text-align: center; margin-top: 20px; }
        @media print {
            body { padding: 0; }
            button.print-btn { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <button class="print-btn" onclick="window.print()" style="position: fixed; top: 20px; right: 20px; padding: 10px 20px; font-size: 16px; cursor: pointer;">Cetak PDF</button>
    <div class="content">
        <div class="kop">
            <h2>KEMENTERIAN AGAMA REPUBLIK INDONESIA</h2>
            <h2>UNIVERSITAS ISLAM NEGERI SULTAN SYARIF KASIM RIAU</h2>
            <p>Jl. H.R. Soebrantas Km. 15 No. 155 Kelurahan Tuah Madani Kecamatan Tuah Madani Pekanbaru 28293</p>
        </div>
        
        <h3 class="title">SURAT KEPUTUSAN PENGAKUAN SKS (RPL)</h3>
        
        <table class="info">
            <tr><td>Nama Peserta</td><td>: <?= htmlspecialchars($peserta['nama']) ?></td></tr>
            <tr><td>NIK</td><td>: <?= htmlspecialchars($peserta['nik']) ?></td></tr>
            <tr><td>Program Studi Tujuan</td><td>: <?= htmlspecialchars($peserta['prodi']) ?></td></tr>
            <tr><td>Tanggal Ditetapkan</td><td>: <?= date('d F Y') ?></td></tr>
        </table>
        
        <p>Berdasarkan hasil asesmen portofolio Rekognisi Pembelajaran Lampau (RPL), dengan ini menetapkan pengakuan hasil belajar sebagai berikut:</p>
        
        <table class="data">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="20%">Kode MK</th>
                    <th width="50%">Mata Kuliah Yang Diakui</th>
                    <th width="10%">SKS</th>
                    <th width="15%">Nilai</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($mks)): ?>
                <tr><td colspan="5" style="text-align:center;">Belum ada data nilai.</td></tr>
                <?php else: ?>
                    <?php $no=1; $totalSks=0; foreach($mks as $mk): ?>
                    <tr>
                        <td style="text-align:center;"><?= $no++ ?></td>
                        <td><?= htmlspecialchars($mk['kode_mk']) ?></td>
                        <td><?= htmlspecialchars($mk['nama_mk']) ?></td>
                        <td style="text-align:center;"><?= htmlspecialchars($mk['sks']) ?></td>
                        <td style="text-align:center;"><?= htmlspecialchars($mk['nilai']) ?></td>
                    </tr>
                    <?php $totalSks += (int)$mk['sks']; endforeach; ?>
                    <tr>
                        <td colspan="3" style="text-align:right; font-weight:bold;">Total SKS Diakui</td>
                        <td style="text-align:center; font-weight:bold;"><?= $totalSks ?></td>
                        <td></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="ttd">
            <p>Pekanbaru, <?= date('d F Y') ?></p>
            <p>Ketua Tim Asesor RPL,</p>
            <br><br><br>
            <p style="font-weight:bold; text-decoration:underline;">(..............................................)</p>
            <p>NIP. ........................................</p>
        </div>
    </div>
</body>
</html>
