<?php
$file = 'c:\\Users\\info\\rpl\\index.html';
$html = file_get_contents($file);

// Replace Topbar Teks Berjalan
$html = str_replace(
    '<i class="fas fa-circle" style="font-size: 8px; margin-right: 5px;"></i> Pendaftaran RPL Program Sarjana (S-1) Gelombang 1 Dibuka — Batas akhir pembayaran: 30 Juli 2026 23:59:00 WIB',
    '<i class="fas fa-circle" style="font-size: 8px; margin-right: 5px;"></i> <span id="public-teks-berjalan">Pendaftaran RPL Program Sarjana (S-1) Gelombang 1 Dibuka — Batas akhir pembayaran: 30 Juli 2026 23:59:00 WIB</span>',
    $html
);

// Replace Footer Email
$html = str_replace(
    '<p class="footer-text"><i class="far fa-envelope" style="margin-right: 10px;"></i> admisi.uinssc</p>',
    '<p class="footer-text"><i class="far fa-envelope" style="margin-right: 10px;"></i> <span id="public-email">admisi.uinssc</span></p>',
    $html
);

// Replace Footer Telepon
$html = str_replace(
    '<p class="footer-text"><i class="fas fa-phone-alt" style="margin-right: 10px;"></i> 082231820660</p>',
    '<p class="footer-text"><i class="fas fa-phone-alt" style="margin-right: 10px;"></i> <span id="public-telepon">082231820660</span></p>',
    $html
);

// Replace WA Button
$html = str_replace(
    '<a href="https://wa.me/6281234567890?text=Halo%20Admin%20RPL%20UINSSC,%20saya%20butuh%20bantuan." target="_blank" class="floating-wa" title="Hubungi Helpdesk">',
    '<a href="https://wa.me/6281234567890?text=Halo%20Admin%20RPL%20UINSSC,%20saya%20butuh%20bantuan." target="_blank" class="floating-wa" id="public-wa-btn" title="Hubungi Helpdesk">',
    $html
);

// Add fetch logic in index.html scripts
$script_web_settings = '
        async function loadWebSettingsPublic() {
            try {
                const res = await fetch("api/settings.php");
                const data = await res.json();
                if (data.success && data.data) {
                    if (data.data.teks_berjalan) document.getElementById("public-teks-berjalan").innerText = data.data.teks_berjalan;
                    if (data.data.kontak_email) document.getElementById("public-email").innerText = data.data.kontak_email;
                    if (data.data.kontak_telepon) document.getElementById("public-telepon").innerText = data.data.kontak_telepon;
                    if (data.data.kontak_wa) {
                        const cleanWa = data.data.kontak_wa.replace(/\D/g, "");
                        document.getElementById("public-wa-btn").href = `https://wa.me/${cleanWa}?text=Halo%20Admin%20RPL%20UINSSC,%20saya%20butuh%20bantuan.`;
                    }
                }
            } catch (e) {
                console.error("Gagal memuat pengaturan web:", e);
            }
        }
';

$html = str_replace('async function loadJadwalPublic() {', $script_web_settings . "\n        async function loadJadwalPublic() {", $html);

$html = str_replace('loadJadwalPublic();', "loadJadwalPublic();\n            loadWebSettingsPublic();", $html);

file_put_contents($file, $html);
echo "index.html web settings patched\n";
