<?php
$file = 'c:\\Users\\info\\rpl\\js\\admin.js';
$js = file_get_contents($file);

// 1. Update showSection
$js = str_replace(
    "document.getElementById('section-konten').style.display = section === 'konten' ? 'block' : 'none';",
    "document.getElementById('section-konten').style.display = section === 'konten' ? 'block' : 'none';\n    document.getElementById('section-jadwal').style.display = section === 'jadwal' ? 'block' : 'none';",
    $js
);
$js = str_replace(
    "document.getElementById('menu-konten').classList.toggle('active', section === 'konten');",
    "document.getElementById('menu-konten').classList.toggle('active', section === 'konten');\n    document.getElementById('menu-jadwal').classList.toggle('active', section === 'jadwal');",
    $js
);
$js = str_replace(
    "if (section === 'konten') loadKontenAdmin();",
    "if (section === 'konten') loadKontenAdmin();\n    if (section === 'jadwal') loadJadwalAdmin();",
    $js
);

// 2. Add Jadwal functions
$jadwal_js = '
// ======================= JADWAL CMS =======================
async function loadJadwalAdmin() {
    try {
        const res = await fetch("api/get_jadwal.php");
        const data = await res.json();
        const tbody = document.getElementById("list-jadwal-body");
        
        if (data.success && data.data.length > 0) {
            tbody.innerHTML = data.data.map(j => `
                <tr>
                    <td style="text-align: center;">${j.urutan}</td>
                    <td style="font-weight: 600;">${j.kegiatan}</td>
                    <td>${j.tanggal}</td>
                    <td>
                        <button class="btn-action" style="background: #f59e0b; color: white;" onclick="editJadwal(${j.id}, \'${j.kegiatan.replace(/\'/g, "\\\'")}\', \'${j.tanggal.replace(/\'/g, "\\\'")}\', ${j.urutan})" title="Edit"><i class="fas fa-pen"></i></button>
                        <button class="btn-action btn-reject" onclick="deleteJadwal(${j.id})" title="Hapus"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `).join("");
        } else {
            tbody.innerHTML = "<tr><td colspan=\'4\' style=\'text-align: center; color: #64748b;\'>Belum ada jadwal.</td></tr>";
        }
    } catch (e) {
        console.error("Gagal memuat jadwal:", e);
    }
}

function editJadwal(id, kegiatan, tanggal, urutan) {
    document.getElementById("j-id").value = id;
    document.getElementById("j-kegiatan").value = kegiatan;
    document.getElementById("j-tanggal").value = tanggal;
    document.getElementById("j-urutan").value = urutan;
}

function resetFormJadwal() {
    document.getElementById("form-jadwal").reset();
    document.getElementById("j-id").value = "";
}

async function saveJadwal(e) {
    e.preventDefault();
    const id = document.getElementById("j-id").value;
    const kegiatan = document.getElementById("j-kegiatan").value;
    const tanggal = document.getElementById("j-tanggal").value;
    const urutan = document.getElementById("j-urutan").value;
    const userId = localStorage.getItem("user_id");

    try {
        const res = await fetch("api/save_jadwal.php", {
            method: "POST",
            headers: { "Content-Type": "application/json", "Authorization": userId },
            body: JSON.stringify({ id, kegiatan, tanggal, urutan })
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            resetFormJadwal();
            loadJadwalAdmin();
        }
    } catch (err) {
        alert("Terjadi kesalahan jaringan.");
    }
}

async function deleteJadwal(id) {
    if (!confirm("Anda yakin ingin menghapus jadwal ini?")) return;
    const userId = localStorage.getItem("user_id");

    try {
        const res = await fetch("api/delete_jadwal.php", {
            method: "POST",
            headers: { "Content-Type": "application/json", "Authorization": userId },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data.success) {
            loadJadwalAdmin();
        } else {
            alert(data.message);
        }
    } catch (err) {
        alert("Terjadi kesalahan jaringan.");
    }
}
';

$js = $js . "\n" . $jadwal_js;
file_put_contents($file, $js);
echo "admin.js patched\n";
