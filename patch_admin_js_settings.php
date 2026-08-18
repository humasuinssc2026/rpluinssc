<?php
$file = 'c:\\Users\\info\\rpl\\js\\admin.js';
$js = file_get_contents($file);

// Update loadSettings
$loadSettingsOld = "        if (data.success && data.data) {
            document.getElementById('set-mulai').value = data.data.jadwal_mulai;
            document.getElementById('set-selesai').value = data.data.jadwal_selesai;
            document.getElementById('set-isopen').value = data.data.is_open;
        }";

$loadSettingsNew = "        if (data.success && data.data) {
            if(document.getElementById('set-mulai')) document.getElementById('set-mulai').value = data.data.jadwal_mulai;
            if(document.getElementById('set-selesai')) document.getElementById('set-selesai').value = data.data.jadwal_selesai;
            if(document.getElementById('set-isopen')) document.getElementById('set-isopen').value = data.data.is_open;
            
            if(document.getElementById('set-wa')) document.getElementById('set-wa').value = data.data.kontak_wa || '';
            if(document.getElementById('set-email')) document.getElementById('set-email').value = data.data.kontak_email || '';
            if(document.getElementById('set-telepon')) document.getElementById('set-telepon').value = data.data.kontak_telepon || '';
            if(document.getElementById('set-teks-berjalan')) document.getElementById('set-teks-berjalan').value = data.data.teks_berjalan || '';
        }";

$js = str_replace($loadSettingsOld, $loadSettingsNew, $js);

// Add saveWebSettings
$saveWebSettings = '
async function saveWebSettings(e) {
    e.preventDefault();
    const kontak_wa = document.getElementById("set-wa").value;
    const kontak_email = document.getElementById("set-email").value;
    const kontak_telepon = document.getElementById("set-telepon").value;
    const teks_berjalan = document.getElementById("set-teks-berjalan").value;
    
    try {
        const res = await fetch("api/settings.php", {
            method: "POST",
            headers: { "Content-Type": "application/json", "Authorization": localStorage.getItem("user_id") },
            body: JSON.stringify({ kontak_wa, kontak_email, kontak_telepon, teks_berjalan })
        });
        const data = await res.json();
        alert(data.message);
    } catch (e) {
        alert("Terjadi kesalahan jaringan");
    }
}
';

$js = str_replace('async function loadAdmins()', $saveWebSettings . "\nasync function loadAdmins()", $js);

file_put_contents($file, $js);
echo "admin.js settings patched\n";
