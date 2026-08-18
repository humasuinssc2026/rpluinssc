<?php
$file = 'c:\\Users\\info\\rpl\\js\\admin.js';
$js = file_get_contents($file);

// Add loadRektorData into loadKontenAdmin()
$loadKonten = "async function loadKontenAdmin() {";
$loadKontenNew = "async function loadKontenAdmin() {\n    loadRektorData();";
$js = str_replace($loadKonten, $loadKontenNew, $js);

// Add saveRektor and loadRektorData
$rektorLogic = '
// ======================= REKTOR SETTINGS =======================
async function loadRektorData() {
    try {
        const res = await fetch("api/settings.php");
        const data = await res.json();
        if (data.success && data.data) {
            document.getElementById("r-nama").value = data.data.rektor_nama || "";
            document.getElementById("r-jabatan").value = data.data.rektor_jabatan || "";
            document.getElementById("r-teks").value = data.data.rektor_teks || "";
        }
    } catch (e) {
        console.error("Gagal memuat profil pimpinan", e);
    }
}

async function saveRektor(e) {
    e.preventDefault();
    const nama = document.getElementById("r-nama").value;
    const jabatan = document.getElementById("r-jabatan").value;
    const teks = document.getElementById("r-teks").value;
    const fotoInput = document.getElementById("r-foto");
    const userId = localStorage.getItem("user_id");

    const formData = new FormData();
    formData.append("user_id", userId);
    formData.append("rektor_nama", nama);
    formData.append("rektor_jabatan", jabatan);
    formData.append("rektor_teks", teks);
    
    if (fotoInput.files.length > 0) {
        formData.append("rektor_foto", fotoInput.files[0]);
    }

    try {
        const res = await fetch("api/settings_rektor.php", {
            method: "POST",
            body: formData
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            fotoInput.value = "";
        }
    } catch (e) {
        alert("Terjadi kesalahan jaringan");
    }
}
';

$js = $js . "\n" . $rektorLogic;
file_put_contents($file, $js);
echo "admin.js rektor patched\n";
