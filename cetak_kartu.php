<?php
require 'api/db.php';

$id = $_GET['id'] ?? null;
if (!$id) die("ID Peserta tidak valid.");

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$peserta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$peserta) die("Peserta tidak ditemukan.");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Tanda Peserta - <?= htmlspecialchars($peserta['nama']) ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #e2e8f0; margin: 0; padding: 20px; display: flex; justify-content: center; }
        .card { background: #fff; width: 600px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); overflow: hidden; position: relative; }
        .card-header { background: #1e293b; color: white; padding: 20px; text-align: center; border-bottom: 5px solid #10b981; }
        .card-header h2 { margin: 0; font-size: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .card-header p { margin: 5px 0 0 0; font-size: 14px; opacity: 0.9; }
        .card-body { padding: 30px; }
        .info-row { display: flex; margin-bottom: 12px; border-bottom: 1px dashed #cbd5e1; padding-bottom: 8px; }
        .info-label { width: 180px; font-weight: 600; color: #475569; }
        .info-value { flex: 1; color: #0f172a; font-weight: 700; }
        .qr-placeholder { border: 2px solid #e2e8f0; width: 120px; height: 120px; display: flex; align-items: center; justify-content: center; float: right; margin-left: 20px; background: #f8fafc; border-radius: 8px; font-size: 10px; color: #94a3b8; text-align: center; }
        .card-footer { background: #f8fafc; padding: 15px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
        @media print {
            body { background: #fff; padding: 0; display: block; }
            .card { box-shadow: none; width: 100%; border: 1px solid #000; border-radius: 0; }
            button.print-btn { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <button class="print-btn" onclick="window.print()" style="position: fixed; top: 20px; right: 20px; padding: 10px 20px; font-size: 16px; cursor: pointer; background: #3b82f6; color: white; border: none; border-radius: 6px;">Cetak PDF</button>
    
    <div class="card">
        <div class="card-header">
            <h2>KARTU TANDA PESERTA</h2>
            <p>Rekognisi Pembelajaran Lampau (RPL) - UIN Sultan Syarif Kasim Riau</p>
        </div>
        <div class="card-body">
            <div class="qr-placeholder">
                [QR CODE]<br>RPL-<?= $peserta['id'] . '-' . date('Y') ?>
            </div>
            
            <div class="info-row">
                <div class="info-label">No. Registrasi</div>
                <div class="info-value">RPL-<?= date('Y') ?>-<?= str_pad($peserta['id'], 5, '0', STR_PAD_LEFT) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Nama Lengkap</div>
                <div class="info-value"><?= htmlspecialchars($peserta['nama']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">NIK</div>
                <div class="info-value"><?= htmlspecialchars($peserta['nik']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Program Studi</div>
                <div class="info-value"><?= htmlspecialchars($peserta['prodi']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Status Verifikasi</div>
                <div class="info-value" style="color: #10b981;"><?= htmlspecialchars($peserta['status_verifikasi']) ?></div>
            </div>
            
            <?php if (!empty($peserta['jadwal_asesmen_tgl'])): ?>
            <div style="margin-top: 30px; background: #fef3c7; padding: 15px; border-radius: 8px; border-left: 4px solid #f59e0b;">
                <h4 style="margin: 0 0 10px 0; color: #92400e;">Jadwal Asesmen/Wawancara:</h4>
                <p style="margin: 0; color: #92400e;"><strong>Tanggal:</strong> <?= htmlspecialchars($peserta['jadwal_asesmen_tgl']) ?></p>
                <p style="margin: 5px 0 0 0; color: #92400e;"><strong>Lokasi:</strong> <?= htmlspecialchars($peserta['jadwal_asesmen_lokasi']) ?></p>
            </div>
            <?php endif; ?>
            
            <div style="clear: both;"></div>
        </div>
        <div class="card-footer">
            Kartu ini adalah bukti resmi pendaftaran RPL. Harap dibawa saat jadwal asesmen atau wawancara.
        </div>
    </div>
</body>
</html>
