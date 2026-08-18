let globalPeserta = [];
let statsChartInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    loadPeserta();
    
    document.getElementById('search-input').addEventListener('input', filterPeserta);
    document.getElementById('filter-status').addEventListener('change', filterPeserta);
    document.getElementById('filter-prodi').addEventListener('change', filterPeserta);
    document.getElementById('filter-bayar').addEventListener('change', filterPeserta);
    document.getElementById('filter-bayar').addEventListener('change', filterPeserta);
    document.getElementById('filter-tahun').addEventListener('change', filterPeserta);
    
    loadKonten();
    loadProdiAdmin();
    initAdminCharts();
    checkNotifications();
    setInterval(checkNotifications, 30000); // Check every 30 seconds
});

function showSection(section) {
    document.getElementById('section-peserta').style.display = section === 'peserta' ? 'block' : 'none';
    document.getElementById('section-pembayaran').style.display = section === 'pembayaran' ? 'block' : 'none';
    document.getElementById('section-konten').style.display = section === 'konten' ? 'block' : 'none';
    document.getElementById('section-prodi').style.display = section === 'prodi' ? 'block' : 'none';
    document.getElementById('section-slider').style.display = section === 'slider' ? 'block' : 'none';
    document.getElementById('section-admin').style.display = section === 'admin' ? 'block' : 'none';
    document.getElementById('section-logs').style.display = section === 'logs' ? 'block' : 'none';
    document.getElementById('section-galeri').style.display = section === 'galeri' ? 'block' : 'none';
    document.getElementById('section-settings').style.display = section === 'settings' ? 'block' : 'none';
    
    document.getElementById('menu-peserta').classList.toggle('active', section === 'peserta');
    document.getElementById('menu-pembayaran').classList.toggle('active', section === 'pembayaran');
    document.getElementById('menu-konten').classList.toggle('active', section === 'konten');
    document.getElementById('menu-prodi').classList.toggle('active', section === 'prodi');
    document.getElementById('menu-slider').classList.toggle('active', section === 'slider');
    document.getElementById('menu-admin').classList.toggle('active', section === 'admin');
    document.getElementById('menu-galeri').classList.toggle('active', section === 'galeri');
    document.getElementById('menu-logs').classList.toggle('active', section === 'logs');
    document.getElementById('menu-settings').classList.toggle('active', section === 'settings');

    if (section === 'settings') loadSettings();
    if (section === 'admin') loadAdmins();
    if (section === 'pembayaran') loadPembayaran();
    if (section === 'logs') loadLogs();
    if (section === 'slider') loadSlidersAdmin();
    if (section === 'galeri') loadGaleriAdmin();
    if (section === 'konten') loadKontenAdmin();
}

async function loadPeserta() {
    const loading = document.getElementById('loading');
    const tableContainer = document.getElementById('table-container');
    const tbody = document.getElementById('peserta-body');
    
    loading.style.display = 'block';
    tableContainer.style.display = 'none';
    
    const userId = localStorage.getItem('user_id');
    if (!userId || localStorage.getItem('role') !== 'admin') {
        alert('Akses ditolak! Hanya admin.');
        window.location.href = 'login.html';
        return;
    }

    try {
        const res = await fetch('api/get_peserta.php', {
            headers: { 'Authorization': userId }
        });
        const data = await res.json();
        
        if (data.success) {
            globalPeserta = data.data; // Simpan di global variabel
            calculateStats();
            renderTable(globalPeserta);
            
            loading.style.display = 'none';
            tableContainer.style.display = 'block';
        } else {
            alert(data.message);
            window.location.href = 'login.html';
        }
    } catch (error) {
        console.error('Error fetching data:', error);
        loading.innerHTML = 'Terjadi kesalahan saat memuat data.';
    }
}

const docLabels = {
    'f1': 'Ijazah PT Asal',
    'f2': 'Transkrip Nilai',
    'f3': 'Sertifikat Akreditasi',
    'nf1': 'Curriculum Vitae',
    'nf2': 'Surat Pengalaman Kerja',
    'nf3': 'Sertifikat Profesi / Keahlian',
    'nf4': 'Sertifikat Kursus PT',
    'nf5': 'Sertifikat Kursus Industri',
    'nf6': 'Surat Rekomendasi/Penugasan',
    'nf7': 'Penghargaan/Prestasi',
    'nf8': 'Keanggotaan Asosiasi',
    'nf9': 'Karya Ilmiah/Publikasi',
    'nf10': 'Laporan Tertulis Atasan',
    'nf11': 'Log book / Portofolio',
    'nf12': 'Contoh Karya',
    'nf13': 'Dokumen Lainnya'
};

function viewDokumen(index) {
    const p = globalPeserta[index];
    document.getElementById('modal-nama').textContent = p.nama;
    
    document.getElementById('bio-hp').textContent = p.nomor_hp || '-';
    document.getElementById('bio-lahir').textContent = p.tanggal_lahir || '-';
    document.getElementById('bio-alamat').textContent = p.alamat_lengkap || '-';
    document.getElementById('bio-pendidikan').textContent = p.pendidikan_terakhir || '-';
    document.getElementById('bio-ibu').textContent = p.nama_ibu || '-';
    document.getElementById('bio-instansi').textContent = p.asal_instansi || '-';
    
    let docs = {};
    if (p.dokumen_lengkap) {
        try { docs = JSON.parse(p.dokumen_lengkap); } catch(e){}
    }
    
    const fList = document.getElementById('modal-formal-list');
    const nfList = document.getElementById('modal-nonformal-list');
    fList.innerHTML = '';
    nfList.innerHTML = '';
    
    Object.entries(docs).forEach(([key, val]) => {
        const li = document.createElement('li');
        
        const title = document.createElement('span');
        let docStatus = {};
        if (p.dokumen_status) {
            try { docStatus = JSON.parse(p.dokumen_status); } catch(e) {}
        }
        const statusStr = docStatus[key] === 'approved' ? ' (Diterima)' : (docStatus[key] === 'rejected' ? ' (Ditolak)' : '');
        
        const docName = docLabels[key] || key;
        title.textContent = docName + statusStr;
        if (docStatus[key] === 'approved') title.style.color = '#166534';
        if (docStatus[key] === 'rejected') title.style.color = '#991b1b';
        
        const link = document.createElement('button');
        link.className = 'btn-view';
        link.innerHTML = '<i class="fas fa-eye"></i> Lihat';
        link.onclick = () => showPdfInline('uploads/dokumen/' + val, docName, key, p.id);
        
        li.appendChild(title);
        li.appendChild(link);
        
        if (key.startsWith('nf')) nfList.appendChild(li);
        else fList.appendChild(li);
    });
    
    document.getElementById('dokumen-modal').style.display = 'flex';
    document.getElementById('pdf-viewer-container').innerHTML = '<p style="color: #64748b;">Klik tombol "Lihat" pada dokumen di samping untuk menampilkan pratinjau di sini.</p>';
    document.getElementById('doc-action-container').style.display = 'none';
}

function showPdfInline(url, docName, docKey, pesertaId) {
    document.getElementById('pdf-viewer-container').innerHTML = `<iframe src="${url}" width="100%" height="100%" style="border: none;"></iframe>`;
    document.getElementById('doc-action-container').style.display = 'block';
    document.getElementById('active-doc-title').textContent = docName;
    document.getElementById('active-doc-key').value = docKey;
    document.getElementById('active-peserta-id').value = pesertaId;
}

async function setDocStatus(status) {
    const docKey = document.getElementById('active-doc-key').value;
    const pesertaId = document.getElementById('active-peserta-id').value;
    const userId = localStorage.getItem('user_id');

    let catatan = '';
    if (status === 'rejected') {
        catatan = prompt("Masukkan catatan atau alasan mengapa dokumen ini ditolak:");
        if (catatan === null) return; // Dibatalkan oleh admin
    }

    try {
        const res = await fetch('api/update_dokumen_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': userId },
            body: JSON.stringify({ peserta_id: pesertaId, doc_key: docKey, status: status, catatan: catatan })
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            closeDokumenModal();
            loadPeserta();
        }
    } catch (e) {
        alert('Gagal terhubung ke server.');
    }
}

function closeDokumenModal() {
    document.getElementById('dokumen-modal').style.display = 'none';
}

async function updateStatus(id, newStatus) {
    if (!confirm(`Apakah Anda yakin ingin mengubah status menjadi ${newStatus}?`)) return;
    
    const userId = localStorage.getItem('user_id');
    try {
        const res = await fetch('api/update_status.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Authorization': userId 
            },
            body: JSON.stringify({ id: id, status: newStatus, user_id: userId })
        });
        
        const data = await res.json();
        if (data.success) {
            alert('Status berhasil diubah!');
            loadPeserta(); // Reload data
        } else {
            alert(data.message);
        }
    } catch (err) {
        alert('Terjadi kesalahan koneksi.');
    }
}

async function updatePayment(id, newStatus) {
    let msg = newStatus === 'Lunas' ? 'memverifikasi pembayaran ini' : 'menolak bukti pembayaran ini';
    if (!confirm(`Apakah Anda yakin ingin ${msg}?`)) return;
    
    const userId = localStorage.getItem('user_id');
    try {
        const res = await fetch('api/update_payment.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Authorization': userId 
            },
            body: JSON.stringify({ id: id, status: newStatus })
        });
        
        const data = await res.json();
        if (data.success) {
            alert('Status pembayaran berhasil diperbarui!');
            loadPeserta(); // Reload data
        } else {
            alert(data.message);
        }
    } catch (err) {
        alert('Terjadi kesalahan koneksi.');
    }
}

function exportExcel() {
    const userId = localStorage.getItem('user_id');
    if (!userId) {
        alert("Sesi tidak valid");
        return;
    }
    window.location.href = `api/export_excel.php?token=${userId}`;
}

// Fitur logout sederhana (hapus sesi dengan memanggil script logout.php atau arahkan ke login.html)
// Untuk keamanan, seharusnya ada logout.php. Tapi untuk simulasi, kita lemparkan kembali ke beranda.
function logout() {
    if(confirm('Anda yakin ingin keluar?')) {
        localStorage.removeItem('user_id');
        localStorage.removeItem('role');
        window.location.href = 'index.html';
    }
}

function renderTable(dataArray) {
    const tbody = document.getElementById('peserta-body');
    tbody.innerHTML = '';
    
    if (dataArray.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">Belum ada pendaftar atau tidak ditemukan.</td></tr>';
        return;
    }
    
    dataArray.forEach((p, index) => {
        let statusClass = 'status-waiting';
        if (p.status_verifikasi === 'Terverifikasi') statusClass = 'status-verified';
        if (p.status_verifikasi === 'Ditolak') statusClass = 'status-rejected';
        
        // cari index aslinya di globalPeserta untuk viewDokumen
        const realIndex = globalPeserta.findIndex(g => g.id === p.id);
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${index + 1}</td>
            <td style="font-weight: 600;">${p.nama}</td>
            <td>${p.nik}</td>
            <td>${p.email}</td>
            <td>${p.prodi}</td>
            <td style="font-size: 0.8rem;">${p.tanggal_daftar}</td>
            <td><span class="status-badge ${statusClass}">${p.status_verifikasi}</span></td>
            <td>
                <span class="status-badge" style="background: ${p.status_pembayaran === 'Lunas' ? '#dcfce7' : (p.status_pembayaran === 'Menunggu Verifikasi Pembayaran' ? '#fef3c7' : '#f1f5f9')}; color: ${p.status_pembayaran === 'Lunas' ? '#166534' : (p.status_pembayaran === 'Menunggu Verifikasi Pembayaran' ? '#92400e' : '#475569')}">${p.status_pembayaran}</span>
                ${p.bukti_pembayaran ? `<br><a href="uploads/pembayaran/${p.bukti_pembayaran}" target="_blank" style="font-size:0.75rem; color:#3b82f6; display:inline-block; margin-top:5px;"><i class="fas fa-search"></i> Lihat Bukti</a>` : ''}
            </td>
            <td>
                <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: nowrap;">
                    <button class="btn-view" onclick="viewDokumen(${realIndex})" title="Lihat Berkas"><i class="fas fa-file-pdf"></i></button>
                    ${p.status_verifikasi === 'Menunggu Verifikasi' && p.status_pembayaran === 'Lunas' ? `
                        <button class="btn-action btn-verify" onclick="updateStatus(${p.id}, 'Terverifikasi')" title="Terima"><i class="fas fa-check"></i></button>
                        <button class="btn-action btn-reject" onclick="updateStatus(${p.id}, 'Ditolak')" title="Tolak" style="margin: 0;"><i class="fas fa-times"></i></button>
                    ` : ''}
                    ${p.status_verifikasi === 'Terverifikasi' ? `
                        <button class="btn-action" style="background: #0ea5e9; color: white;" onclick="openHasilModal(${p.id}, '${p.nama.replace(/'/g, "\\'")}', '${p.prodi || ''}', '${p.jadwal_asesmen_tgl || ''}', '${p.jadwal_asesmen_lokasi || ''}')" title="Input Nilai RPL"><i class="fas fa-calculator"></i></button>
                    ` : ''}
                    ${p.status_pembayaran === 'Menunggu Verifikasi Pembayaran' ? `
                        <button class="btn-action btn-verify" onclick="updatePayment(${p.id}, 'Lunas')" title="Verifikasi Bayar"><i class="fas fa-money-bill-wave"></i></button>
                        <button class="btn-action btn-reject" onclick="updatePayment(${p.id}, 'Belum Bayar')" title="Tolak Bayar" style="margin: 0;"><i class="fas fa-times"></i></button>
                    ` : ''}
                    <button class="btn-action" style="background: #f59e0b; color: white;" onclick="openEditModal(${realIndex})" title="Edit"><i class="fas fa-pen"></i></button>
                    <a href="https://wa.me/${p.nomor_hp}?text=Halo%20${encodeURIComponent(p.nama)},%20ini%20dari%20Admin%20RPL%20UINSSC.%20Terkait%20pendaftaran%20Anda..." target="_blank" class="btn-action" style="background: #25d366; color: white; text-decoration: none; padding: 6px 10px; display: inline-flex; align-items: center; justify-content: center;" title="Hubungi via WA"><i class="fab fa-whatsapp"></i></a>
                    <button class="btn-action btn-reject" onclick="deletePeserta(${p.id})" title="Hapus" style="margin: 0;"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function calculateStats() {
    let total = globalPeserta.length;
    let waitingPay = 0;
    let menungguBayar = 0;
    let menungguBerkas = 0;
    let terverifikasi = 0;

    globalPeserta.forEach(p => {
        if (p.status_pembayaran === 'Menunggu Verifikasi Pembayaran') menungguBayar++;
        if (p.status_verifikasi === 'Menunggu Verifikasi') menungguBerkas++;
        if (p.status_verifikasi === 'Terverifikasi') terverifikasi++;
    });
    
    document.getElementById('stat-total').textContent = globalPeserta.length;
    document.getElementById('stat-menunggu-bayar').textContent = menungguBayar;
    document.getElementById('stat-menunggu-berkas').textContent = menungguBerkas;
    document.getElementById('stat-terverifikasi').textContent = terverifikasi;

    renderChart(menungguBayar, menungguBerkas, terverifikasi);
}

function checkNotifications() {
    if (globalPeserta.length > 0) {
        let count = 0;
        globalPeserta.forEach(p => {
            if (p.status_pembayaran === 'Menunggu Verifikasi Pembayaran') count++;
            if (p.status_verifikasi === 'Menunggu Verifikasi') count++;
        });
        
        const badge = document.getElementById('notif-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }
}

function openEditModal(index) {
    const p = globalPeserta[index];
    document.getElementById('edit-id').value = p.id;
    document.getElementById('edit-nama').value = p.nama;
    document.getElementById('edit-nik').value = p.nik;
    document.getElementById('edit-email').value = p.email;
    document.getElementById('edit-prodi').value = p.prodi;
    document.getElementById('edit-modal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('edit-modal').style.display = 'none';
}

async function saveEditPeserta(e) {
    e.preventDefault();
    const id = document.getElementById('edit-id').value;
    const nama = document.getElementById('edit-nama').value;
    const nik = document.getElementById('edit-nik').value;
    const email = document.getElementById('edit-email').value;
    const prodi = document.getElementById('edit-prodi').value;
    const userId = localStorage.getItem('user_id');

    try {
        const res = await fetch('api/edit_peserta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': userId },
            body: JSON.stringify({ id, nama, nik, email, prodi })
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            closeEditModal();
            loadPeserta();
        }
    } catch (err) {
        alert('Terjadi kesalahan koneksi.');
    }
}

async function deletePeserta(id) {
    if (!confirm('Peringatan: Aksi ini akan menghapus data pendaftar secara permanen. Apakah Anda yakin?')) return;
    
    const userId = localStorage.getItem('user_id');
    try {
        const res = await fetch('api/delete_peserta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': userId },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            loadPeserta();
        }
    } catch (err) {
        alert('Terjadi kesalahan koneksi.');
    }
}

function renderChart(waitingPay, waitingDoc, verified) {
    const ctx = document.getElementById('statsChart');
    if (!ctx) return;
    
    if (statsChartInstance) {
        statsChartInstance.destroy();
    }
    
    statsChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Menunggu Pembayaran', 'Menunggu Verifikasi', 'Terverifikasi'],
            datasets: [{
                data: [waitingPay, waitingDoc, verified],
                backgroundColor: ['#f59e0b', '#8b5cf6', '#22c55e'],
                borderWidth: 2,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            cutout: '60%'
        }
    });
}

function filterPeserta() {
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    const filterStatus = document.getElementById('filter-status').value;
    const filterProdi = document.getElementById('filter-prodi').value;
    const filterBayar = document.getElementById('filter-bayar').value;
    const filterTahun = document.getElementById('filter-tahun').value;
    
    const filtered = globalPeserta.filter(p => {
        const matchSearch = p.nama.toLowerCase().includes(searchTerm) || p.nik.includes(searchTerm);
        const matchStatus = filterStatus === '' || p.status_verifikasi === filterStatus;
        const matchProdi = filterProdi === '' || (p.prodi && p.prodi.includes(filterProdi));
        const matchTahun = filterTahun === '' || (p.tanggal_daftar && p.tanggal_daftar.startsWith(filterTahun));
        
        let matchBayar = true;
        if (filterBayar === 'Lunas') {
            matchBayar = p.status_pembayaran === 'Lunas';
        } else if (filterBayar === 'Belum') {
            matchBayar = p.status_pembayaran !== 'Lunas';
        }
        
        return matchSearch && matchStatus && matchProdi && matchBayar && matchTahun;
    });
    
    renderTable(filtered);
}

// ======================= CMS FUNCTIONS =======================
async function loadKonten() {
    try {
        const res = await fetch('api/get_konten.php');
        const data = await res.json();
        if (data.success) {
            renderPengumuman(data.data.pengumuman);
            renderUnduhan(data.data.unduhan);
            
            if (data.data.rektor) {
                document.getElementById('r-nama').value = data.data.rektor.rektor_nama || '';
                document.getElementById('r-jabatan').value = data.data.rektor.rektor_jabatan || '';
                document.getElementById('r-teks').value = data.data.rektor.rektor_teks || '';
            }
        }
    } catch (e) {
        console.error("Gagal memuat konten", e);
    }
}

async function saveRektor(e) {
    e.preventDefault();
    const nama = document.getElementById('r-nama').value;
    const jabatan = document.getElementById('r-jabatan').value;
    const teks = document.getElementById('r-teks').value;
    const file = document.getElementById('r-foto').files[0];
    const userId = localStorage.getItem('user_id');

    const formData = new FormData();
    formData.append('rektor_nama', nama);
    formData.append('rektor_jabatan', jabatan);
    formData.append('rektor_teks', teks);
    if (file) formData.append('rektor_foto', file);

    try {
        const res = await fetch('api/save_rektor.php', {
            method: 'POST',
            headers: { 'Authorization': userId },
            body: formData
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            document.getElementById('r-foto').value = ''; // Reset file input
            loadKonten();
        }
    } catch (err) {
        alert("Terjadi kesalahan jaringan.");
    }
}

function renderPengumuman(list) {
    const ul = document.getElementById('list-pengumuman');
    ul.innerHTML = '';
    list.forEach(p => {
        const li = document.createElement('li');
        li.style.padding = "0.8rem 0";
        li.style.borderBottom = "1px solid #e2e8f0";
        li.style.display = "flex";
        li.style.justifyContent = "space-between";
        li.style.alignItems = "center";
        
        li.innerHTML = `
            <div>
                <strong>${p.judul}</strong><br>
                <small style="color:#64748b;">${p.tanggal}</small>
            </div>
            <button class="btn-action btn-reject" onclick="deleteKonten(${p.id}, 'pengumuman')" title="Hapus"><i class="fas fa-trash"></i></button>
        `;
        ul.appendChild(li);
    });
}

function renderUnduhan(list) {
    const ul = document.getElementById('list-unduhan');
    ul.innerHTML = '';
    list.forEach(u => {
        const li = document.createElement('li');
        li.style.padding = "0.8rem 0";
        li.style.borderBottom = "1px solid #e2e8f0";
        li.style.display = "flex";
        li.style.justifyContent = "space-between";
        li.style.alignItems = "center";
        
        li.innerHTML = `
            <div>
                <strong>${u.nama_dokumen}</strong><br>
                <small style="color:#64748b;">File: ${u.file_path}</small>
            </div>
            <button class="btn-action btn-reject" onclick="deleteKonten(${u.id}, 'unduhan')" title="Hapus"><i class="fas fa-trash"></i></button>
        `;
        ul.appendChild(li);
    });
}

async function savePengumuman(e) {
    e.preventDefault();
    const judul = document.getElementById('p-judul').value;
    const isi = document.getElementById('p-isi').value;
    const userId = localStorage.getItem('user_id');
    
    try {
        const res = await fetch('api/save_pengumuman.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': userId },
            body: JSON.stringify({ judul, isi })
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            document.getElementById('form-pengumuman').reset();
            loadKonten();
        }
    } catch(err) {
        alert("Terjadi kesalahan jaringan.");
    }
}

async function saveUnduhan(e) {
    e.preventDefault();
    const nama = document.getElementById('u-nama').value;
    const file = document.getElementById('u-file').files[0];
    const userId = localStorage.getItem('user_id');
    
    if (!file) return alert("Pilih file terlebih dahulu.");
    
    const formData = new FormData();
    formData.append('nama_dokumen', nama);
    formData.append('file_dokumen', file);
    
    try {
        const res = await fetch('api/save_unduhan.php', {
            method: 'POST',
            headers: { 'Authorization': userId },
            body: formData
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            document.getElementById('form-unduhan').reset();
            loadKonten();
        }
    } catch(err) {
        alert("Terjadi kesalahan jaringan.");
    }
}

async function deleteKonten(id, type) {
    if (!confirm('Anda yakin ingin menghapus konten ini?')) return;
    const userId = localStorage.getItem('user_id');
    
    try {
        const res = await fetch('api/delete_konten.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': userId },
            body: JSON.stringify({ id, type })
        });
        const data = await res.json();
        if (data.success) {
            loadKonten();
        } else {
            alert(data.message);
        }
    } catch(err) {
        alert("Terjadi kesalahan jaringan.");
    }
}

// ======================= GALERI CMS =======================
async function loadGaleriAdmin() {
    try {
        const res = await fetch('api/get_galeri.php');
        const data = await res.json();
        const tbody = document.getElementById('galeri-admin-body');
        
        if (data.success && data.data.length > 0) {
            tbody.innerHTML = data.data.map((g, index) => `
                <tr>
                    <td style="text-align: center;">${index + 1}</td>
                    <td style="text-align: center;">
                        <img src="${g.image_path}" style="max-height: 80px; max-width: 150px; border-radius: 4px; object-fit: cover;">
                    </td>
                    <td>${g.tanggal}</td>
                    <td style="text-align: center;">
                        <button class="btn-action btn-reject" onclick="deleteGaleri(${g.id})" title="Hapus"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: #64748b;">Belum ada foto galeri.</td></tr>';
        }
    } catch (e) {
        console.error("Gagal memuat galeri:", e);
    }
}

async function saveGaleri(e) {
    e.preventDefault();
    const file = document.getElementById('g-file').files[0];
    if (!file) return alert("Pilih foto terlebih dahulu.");

    const formData = new FormData();
    formData.append('image', file);

    const userId = localStorage.getItem('user_id');
    
    try {
        const res = await fetch('api/save_galeri.php', {
            method: 'POST',
            headers: { 'Authorization': userId },
            body: formData
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            document.getElementById('form-galeri').reset();
            loadGaleriAdmin();
        }
    } catch (err) {
        alert("Terjadi kesalahan jaringan.");
    }
}

async function deleteGaleri(id) {
    if (!confirm('Anda yakin ingin menghapus foto galeri ini?')) return;
    const userId = localStorage.getItem('user_id');

    const formData = new FormData();
    formData.append('id', id);

    try {
        const res = await fetch('api/delete_galeri.php', {
            method: 'POST',
            headers: { 'Authorization': userId },
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            loadGaleriAdmin();
        } else {
            alert(data.message);
        }
    } catch (err) {
        alert("Terjadi kesalahan jaringan.");
    }
}

// Manajemen Prodi
async function loadProdiAdmin() {
    try {
        const res = await fetch('api/get_prodi.php');
        const data = await res.json();
        const tbody = document.getElementById('prodi-admin-body');
        if (!tbody) return;
        
        if (data.success) {
            tbody.innerHTML = data.data.map((p, index) => {
                let certHtml = p.sertifikat_path 
                    ? `<a href="${p.sertifikat_path}" target="_blank" style="color: #10b981; font-weight: bold; text-decoration: none;"><i class="fas fa-check-circle"></i> Tersedia (Lihat)</a>` 
                    : `<span style="color: #ef4444;"><i class="fas fa-times-circle"></i> Belum ada</span>`;
                
                let pedomanHtml = `
                    <div style="background: #f8fafc; padding: 0.8rem; border-radius: 8px; border: 1px solid #cbd5e1; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        <div style="font-size: 0.9rem; font-weight: 600; color: #475569; margin-bottom: 0.5rem;">${p.pedoman_count} File Tersimpan</div>
                        <button class="btn-sso" style="background: #10b981; color: white; border: none; padding: 0.4rem 0.8rem; font-size: 0.75rem; border-radius: 4px; cursor: pointer;" onclick="openPedomanModal(${p.id}, '${p.nama_prodi}')"><i class="fas fa-folder-open"></i> Kelola File</button>
                    </div>
                `;
                
                return `
                    <tr>
                        <td style="text-align:center; vertical-align: top; padding-top: 1.5rem;">${index + 1}</td>
                        <td style="font-weight: 600; vertical-align: top; padding-top: 1.5rem;">${p.nama_prodi}</td>
                        <td style="text-align:center; vertical-align: top; padding-top: 1.5rem;">
                            <span style="background: #f1f5f9; padding: 0.4rem 0.8rem; border-radius: 6px; border: 1px solid #e2e8f0; font-family: monospace;">${p.nomor_penyelenggara}</span>
                        </td>
                        <td style="text-align:center; vertical-align: top;">
                            <div style="margin-bottom: 0.8rem; font-size: 0.9rem;">${certHtml}</div>
                            <div style="background: #f8fafc; padding: 0.8rem; border-radius: 8px; border: 1px dashed #cbd5e1;">
                                <form onsubmit="uploadSertifikatProdi(event, ${p.id})" style="display: flex; flex-direction: column; gap: 0.5rem; align-items: stretch;">
                                    <input type="file" id="cert-file-${p.id}" required accept=".pdf,.png,.jpg,.jpeg" style="width: 100%; padding: 0.3rem; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.75rem; background: white; box-sizing: border-box;">
                                    <button type="submit" class="btn-sso" style="background: #3b82f6; color: white; border: none; padding: 0.4rem; font-size: 0.75rem; border-radius: 4px; cursor: pointer; transition: 0.2s;"><i class="fas fa-upload"></i> Update Sertifikat</button>
                                </form>
                            </div>
                        </td>
                        <td style="text-align:center; vertical-align: top;">
                            ${pedomanHtml}
                        </td>
                        <td style="vertical-align: top; padding-top: 1.5rem;">
                            <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                <button class="btn-action" style="background: #f59e0b; color: white;" onclick="openProdiModal(${p.id}, '${p.nama_prodi}', '${p.nomor_penyelenggara}')" title="Edit Data Prodi"><i class="fas fa-pen"></i></button>
                                <button class="btn-action btn-reject" onclick="deleteProdi(${p.id})" title="Hapus Prodi"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }
    } catch (e) {
        console.error("Gagal load prodi:", e);
    }
}

function openProdiModal(id = '', nama = '', nomor = '') {
    document.getElementById('prodi-id').value = id;
    document.getElementById('prodi-nama').value = nama;
    document.getElementById('prodi-nomor').value = nomor;
    document.getElementById('prodi-modal-title').textContent = id ? 'Edit Program Studi' : 'Tambah Program Studi';
    document.getElementById('prodi-modal').style.display = 'flex';
}

function closeProdiModal() {
    document.getElementById('prodi-modal').style.display = 'none';
}

async function saveProdi(e) {
    e.preventDefault();
    const id = document.getElementById('prodi-id').value;
    const nama_prodi = document.getElementById('prodi-nama').value;
    const nomor_penyelenggara = document.getElementById('prodi-nomor').value;

    try {
        const res = await fetch('api/save_prodi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': localStorage.getItem('user_id') },
            body: JSON.stringify({ id, nama_prodi, nomor_penyelenggara })
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            closeProdiModal();
            loadProdiAdmin();
        }
    } catch (err) {
        alert("Terjadi kesalahan jaringan");
    }
}

async function deleteProdi(id) {
    if (!confirm('Anda yakin ingin menghapus program studi ini? Data peserta terkait mungkin akan terpengaruh jika namanya diubah/dihapus.')) return;
    try {
        const res = await fetch('api/delete_prodi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': localStorage.getItem('user_id') },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            loadProdiAdmin();
        }
    } catch (err) {
        alert("Terjadi kesalahan jaringan");
    }
}

async function uploadSertifikatProdi(e, prodiId) {
    e.preventDefault();
    const fileInput = document.getElementById(`cert-file-${prodiId}`);
    if (!fileInput.files.length) return;

    const formData = new FormData();
    formData.append('prodi_id', prodiId);
    formData.append('sertifikat', fileInput.files[0]);

    try {
        const res = await fetch('api/upload_sertifikat_prodi.php', {
            method: 'POST',
            headers: { 'Authorization': localStorage.getItem('user_id') },
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            alert(data.message);
            loadProdiAdmin(); // Refresh tabel
        } else {
            alert('Gagal: ' + data.message);
        }
    } catch (error) {
        console.error('Error upload sertifikat:', error);
        alert('Terjadi kesalahan server.');
    }
}

// ======================= PEDOMAN FILES FUNCTIONS =======================
function openPedomanModal(prodiId, prodiName) {
    document.getElementById('pedoman-prodi-id').value = prodiId;
    document.getElementById('pedoman-prodi-nama').textContent = prodiName;
    document.getElementById('pedoman-modal').style.display = 'flex';
    loadPedomanFiles(prodiId);
}

function closePedomanModal() {
    document.getElementById('pedoman-modal').style.display = 'none';
    document.getElementById('pedoman-nama-dokumen').value = '';
    document.getElementById('pedoman-file-input').value = '';
}

async function loadPedomanFiles(prodiId) {
    const tbody = document.getElementById('pedoman-files-body');
    tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Memuat file...</td></tr>';
    
    try {
        const res = await fetch(`api/get_pedoman_files.php?prodi_id=${prodiId}`);
        const data = await res.json();
        
        if (data.success && data.data.length > 0) {
            tbody.innerHTML = data.data.map((f, i) => {
                const isPdf = f.file_path.toLowerCase().endsWith('.pdf');
                const lihatBtn = isPdf 
                    ? `<a href="${f.file_path}" target="_blank" class="btn-action" style="background:#3b82f6; color:white; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; padding:0.3rem 0.5rem;" title="Lihat"><i class="fas fa-eye"></i></a>`
                    : `<span class="btn-action" style="background:#e2e8f0; color:#64748b; cursor:not-allowed; display:inline-flex; align-items:center; justify-content:center; padding:0.3rem 0.5rem;" title="Bukan PDF, tidak bisa preview"><i class="fas fa-eye-slash"></i></span>`;
                    
                return `
                <tr>
                    <td style="text-align:center;">${i + 1}</td>
                    <td style="font-weight: 500;">${f.nama_dokumen}</td>
                    <td style="color:#64748b; font-size:0.85rem;">${f.tanggal}</td>
                    <td style="text-align:center;">
                        ${lihatBtn}
                        <button class="btn-action btn-reject" onclick="deletePedomanFile(${f.id}, ${prodiId})" title="Hapus"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: #64748b;">Belum ada file pedoman.</td></tr>';
        }
    } catch (e) {
        console.error(e);
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: #ef4444;">Gagal memuat file.</td></tr>';
    }
}

async function uploadPedomanFile(e) {
    e.preventDefault();
    const prodiId = document.getElementById('pedoman-prodi-id').value;
    const namaDokumen = document.getElementById('pedoman-nama-dokumen').value;
    const fileInput = document.getElementById('pedoman-file-input');
    
    if (!fileInput.files.length) return;

    const formData = new FormData();
    formData.append('prodi_id', prodiId);
    formData.append('nama_dokumen', namaDokumen);
    formData.append('pedoman', fileInput.files[0]);

    try {
        const res = await fetch('api/upload_pedoman_prodi.php', {
            method: 'POST',
            headers: { 'Authorization': localStorage.getItem('user_id') },
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            document.getElementById('pedoman-nama-dokumen').value = '';
            fileInput.value = '';
            loadPedomanFiles(prodiId);
            loadProdiAdmin(); // Refresh badge count in main table
        } else {
            alert('Gagal: ' + data.message);
        }
    } catch (error) {
        console.error('Error upload pedoman:', error);
        alert('Terjadi kesalahan server.');
    }
}

async function deletePedomanFile(fileId, prodiId) {
    if (!confirm('Apakah Anda yakin ingin menghapus file ini?')) return;
    
    try {
        const res = await fetch('api/delete_pedoman_file.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': localStorage.getItem('user_id')
            },
            body: JSON.stringify({ id: fileId })
        });
        const data = await res.json();
        
        if (data.success) {
            loadPedomanFiles(prodiId);
            loadProdiAdmin(); // Refresh badge count in main table
        } else {
            alert('Gagal: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan server.');
    }
}

// ======================= SETTINGS FUNCTIONS =======================
async function loadSettings() {
    try {
        const res = await fetch('api/settings.php');
        const data = await res.json();
        if (data.success && data.data) {
            document.getElementById('set-mulai').value = data.data.jadwal_mulai;
            document.getElementById('set-selesai').value = data.data.jadwal_selesai;
            document.getElementById('set-isopen').value = data.data.is_open;
        }
    } catch (e) {
        console.error("Gagal load settings", e);
    }
}

async function saveSettings(e) {
    e.preventDefault();
    const jadwal_mulai = document.getElementById('set-mulai').value;
    const jadwal_selesai = document.getElementById('set-selesai').value;
    const is_open = document.getElementById('set-isopen').value;
    
    try {
        const res = await fetch('api/settings.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': localStorage.getItem('user_id') },
            body: JSON.stringify({ jadwal_mulai, jadwal_selesai, is_open })
        });
        const data = await res.json();
        alert(data.message);
    } catch (e) {
        alert("Terjadi kesalahan jaringan");
    }
}

// ======================= ADMIN MANAGEMENT FUNCTIONS =======================
async function loadAdmins() {
    try {
        const res = await fetch('api/get_admins.php', {
            headers: { 'Authorization': localStorage.getItem('user_id') }
        });
        const data = await res.json();
        if (data.success) {
            const tbody = document.getElementById('admin-body');
            if (data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Belum ada admin lain.</td></tr>';
                return;
            }
            tbody.innerHTML = data.data.map((a, i) => `
                <tr>
                    <td style="text-align:center;">${i + 1}</td>
                    <td style="font-weight:600;">${a.nama}</td>
                    <td>${a.email}</td>
                    <td>
                        <button class="btn-action" style="background: #f59e0b; color: white;" onclick="openAdminModal(${a.id}, '${a.nama}', '${a.email}')" title="Edit"><i class="fas fa-pen"></i></button>
                        <button class="btn-action btn-reject" onclick="deleteAdmin(${a.id})" title="Hapus"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (e) {
        console.error("Gagal load admins", e);
    }
}

function openAdminModal(id = '', nama = '', email = '') {
    document.getElementById('admin-id').value = id;
    document.getElementById('admin-nama').value = nama;
    document.getElementById('admin-email').value = email;
    document.getElementById('admin-password').value = '';
    
    const title = document.getElementById('admin-modal-title');
    const note = document.getElementById('admin-password-note');
    if (id) {
        title.textContent = 'Edit Admin';
        note.textContent = '(Kosongkan jika tidak ingin mengubah kata sandi)';
        document.getElementById('admin-password').required = false;
    } else {
        title.textContent = 'Tambah Admin Baru';
        note.textContent = '';
        document.getElementById('admin-password').required = true;
    }
    document.getElementById('admin-modal').style.display = 'flex';
}

function closeAdminModal() {
    document.getElementById('admin-modal').style.display = 'none';
}

async function saveAdmin(e) {
    e.preventDefault();
    const id = document.getElementById('admin-id').value;
    const nama = document.getElementById('admin-nama').value;
    const email = document.getElementById('admin-email').value;
    const password = document.getElementById('admin-password').value;

    try {
        const res = await fetch('api/save_admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': localStorage.getItem('user_id') },
            body: JSON.stringify({ id, nama, email, password })
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            closeAdminModal();
            loadAdmins();
        }
    } catch (err) {
        alert("Terjadi kesalahan jaringan");
    }
}

async function deleteAdmin(id) {
    if (!confirm('Anda yakin ingin menghapus akun admin ini?')) return;
    try {
        const res = await fetch('api/delete_admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': localStorage.getItem('user_id') },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            loadAdmins();
        }
    } catch (err) {
        alert("Terjadi kesalahan jaringan");
    }
}

// ======================= PEMBAYARAN VALIDATION FUNCTIONS =======================
async function loadPembayaran() {
    const loading = document.getElementById('loading-pembayaran');
    const tableContainer = document.getElementById('table-pembayaran-container');
    const tbody = document.getElementById('pembayaran-body');
    
    loading.style.display = 'block';
    tableContainer.style.display = 'none';
    
    const userId = localStorage.getItem('user_id');

    try {
        const res = await fetch('api/get_peserta.php', {
            headers: { 'Authorization': userId }
        });
        const data = await res.json();
        
        if (data.success) {
            globalPeserta = data.data; // Update global
            const filtered = data.data.filter(p => p.status_pembayaran === 'Menunggu Verifikasi Pembayaran');
            
            tbody.innerHTML = '';
            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Tidak ada peserta yang menunggu verifikasi pembayaran saat ini.</td></tr>';
            } else {
                tbody.innerHTML = filtered.map((p, i) => `
                    <tr>
                        <td style="text-align:center;">${i + 1}</td>
                        <td style="font-weight:600;">${p.nama}</td>
                        <td>${p.nik}</td>
                        <td>${p.tanggal_daftar}</td>
                        <td style="text-align:center;">
                            ${p.bukti_pembayaran 
                                ? `<button class="btn-view" onclick="viewBuktiPembayaran('${p.bukti_pembayaran}', '${p.nama}', ${p.id})"><i class="fas fa-search"></i> Lihat Bukti</button>` 
                                : '<span style="color:#ef4444;">Belum Ada</span>'}
                        </td>
                        <td>
                            <button class="btn-action btn-verify" onclick="updatePayment(${p.id}, 'Lunas')" title="Setujui"><i class="fas fa-check"></i></button>
                            <button class="btn-action btn-reject" onclick="updatePayment(${p.id}, 'Belum Bayar')" title="Tolak" style="margin:0;"><i class="fas fa-times"></i></button>
                        </td>
                    </tr>
                `).join('');
            }
            loading.style.display = 'none';
            tableContainer.style.display = 'block';
        }
    } catch (error) {
        loading.innerHTML = 'Terjadi kesalahan saat memuat data.';
    }
}

function viewBuktiPembayaran(fileName, namaUser, userId) {
    document.getElementById('bukti-nama').textContent = namaUser;
    document.getElementById('bukti-user-id').value = userId;
    
    const url = 'uploads/pembayaran/' + fileName;
    const isPdf = fileName.toLowerCase().endsWith('.pdf');
    const container = document.getElementById('bukti-viewer-container');
    
    if (isPdf) {
        container.innerHTML = `<iframe src="${url}" width="100%" height="100%" style="border: none;"></iframe>`;
    } else {
        container.innerHTML = `<img src="${url}" style="max-width: 100%; max-height: 500px; object-fit: contain;">`;
    }
    
    document.getElementById('bukti-modal').style.display = 'flex';
}

function closeBuktiModal() {
    document.getElementById('bukti-modal').style.display = 'none';
}

function processPembayaran(status) {
    const userId = document.getElementById('bukti-user-id').value;
    updatePayment(userId, status);
    closeBuktiModal();
    // loadPembayaran will be called if they are on the pembayaran section, because updatePayment calls loadPeserta.
    // Let's make sure it updates current section.
    setTimeout(() => {
        if (document.getElementById('section-pembayaran').style.display === 'block') {
            loadPembayaran();
        }
    }, 500);
}

// ======================= AUDIT LOGS FUNCTIONS =======================
async function loadLogs() {
    try {
        const res = await fetch('api/get_logs.php', {
            headers: { 'Authorization': localStorage.getItem('user_id') }
        });
        const data = await res.json();
        const tbody = document.getElementById('logs-body');
        if (data.success) {
            if (data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Belum ada log aktivitas.</td></tr>';
                return;
            }
            tbody.innerHTML = data.data.map((l, i) => `
                <tr>
                    <td style="text-align:center;">${i + 1}</td>
                    <td>${l.tanggal}</td>
                    <td>Admin #${l.admin_id}</td>
                    <td><span class="status-badge" style="background:#e0f2fe; color:#0369a1;">${l.action}</span></td>
                    <td>${l.target}</td>
                </tr>
            `).join('');
        }
    } catch (e) {
        console.error("Gagal load logs", e);
    }
}

// ======================= HASIL RPL FUNCTIONS =======================
function openHasilModal(pesertaId, nama, prodi, tgl, lokasi) {
    document.getElementById('hasil-peserta-id').value = pesertaId;
    document.getElementById('hasil-nama').textContent = nama;
    document.getElementById('hasil-prodi').textContent = prodi || '-';
    document.getElementById('input-jadwal-tgl').value = tgl || '';
    document.getElementById('input-jadwal-lokasi').value = lokasi || '';
    document.getElementById('hasil-modal').style.display = 'flex';
    loadHasilRPL(pesertaId);
}

function closeHasilModal() {
    document.getElementById('hasil-modal').style.display = 'none';
}

async function loadHasilRPL(pesertaId) {
    const tbody = document.getElementById('hasil-rpl-body');
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Memuat data...</td></tr>';
    try {
        const res = await fetch(`api/get_hasil.php?peserta_id=${pesertaId}`, {
            headers: { 'Authorization': localStorage.getItem('user_id') }
        });
        const data = await res.json();
        if (data.success) {
            if (data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Belum ada nilai yang diinput.</td></tr>';
                return;
            }
            tbody.innerHTML = data.data.map(h => `
                <tr>
                    <td>${h.kode_mk}</td>
                    <td>${h.nama_mk}</td>
                    <td style="text-align:center;">${h.sks}</td>
                    <td style="text-align:center; font-weight:bold;">${h.nilai}</td>
                    <td style="text-align:center;">
                        <button class="btn-action btn-reject" onclick="deleteHasilRPL(${h.id}, ${pesertaId})" title="Hapus" style="margin:0;"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (e) {
        console.error("Gagal load hasil RPL", e);
    }
}

async function addHasilRPL() {
    const peserta_id = document.getElementById('hasil-peserta-id').value;
    const kode_mk = document.getElementById('input-kode-mk').value;
    const nama_mk = document.getElementById('input-nama-mk').value;
    const sks = document.getElementById('input-sks').value;
    const nilai = document.getElementById('input-nilai').value;

    if (!kode_mk || !nama_mk || !sks || !nilai) {
        alert('Harap isi semua field mata kuliah!');
        return;
    }

    try {
        const res = await fetch('api/save_hasil.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': localStorage.getItem('user_id') },
            body: JSON.stringify({ peserta_id, kode_mk, nama_mk, sks, nilai })
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('input-kode-mk').value = '';
            document.getElementById('input-nama-mk').value = '';
            document.getElementById('input-sks').value = '';
            document.getElementById('input-nilai').value = '';
            loadHasilRPL(peserta_id);
        } else {
            alert(data.message);
        }
    } catch (e) {
        alert("Terjadi kesalahan jaringan");
    }
}

async function deleteHasilRPL(id, pesertaId) {
    if (!confirm('Hapus nilai ini?')) return;
    try {
        const res = await fetch('api/delete_hasil.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': localStorage.getItem('user_id') },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data.success) {
            loadHasilRPL(pesertaId);
        }
    } catch (e) {
        alert("Terjadi kesalahan jaringan");
    }
}

async function saveJadwalAsesmen() {
    const peserta_id = document.getElementById('hasil-peserta-id').value;
    const tgl = document.getElementById('input-jadwal-tgl').value;
    const lokasi = document.getElementById('input-jadwal-lokasi').value;

    try {
        const res = await fetch('api/save_jadwal.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': localStorage.getItem('user_id') },
            body: JSON.stringify({ peserta_id, tgl, lokasi })
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            loadPeserta(); // Update the table data behind
        }
    } catch (e) {
        alert("Terjadi kesalahan jaringan");
    }
}

// ======================= CHART.JS =======================
let chartVerifikasiInstance = null;
let chartPembayaranInstance = null;

async function initAdminCharts() {
    try {
        const res = await fetch("api/get_admin_stats.php", {
            headers: { "Authorization": localStorage.getItem("user_id") }
        });
        const data = await res.json();
        
        if (data.success) {
            const ctxV = document.getElementById("chartVerifikasi");
            const ctxP = document.getElementById("chartPembayaran");
            
            if (!ctxV || !ctxP) return;

            // Verifikasi Chart
            if (chartVerifikasiInstance) chartVerifikasiInstance.destroy();
            chartVerifikasiInstance = new Chart(ctxV, {
                type: "pie",
                data: {
                    labels: Object.keys(data.data.verifikasi).map(k => k || "Belum Upload"),
                    datasets: [{
                        data: Object.values(data.data.verifikasi),
                        backgroundColor: ["#8b5cf6", "#22c55e", "#ef4444", "#cbd5e1"]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { title: { display: true, text: "Status Verifikasi Dokumen" } }
                }
            });

            // Pembayaran Chart
            if (chartPembayaranInstance) chartPembayaranInstance.destroy();
            chartPembayaranInstance = new Chart(ctxP, {
                type: "pie",
                data: {
                    labels: Object.keys(data.data.pembayaran).map(k => k || "Belum Bayar"),
                    datasets: [{
                        data: Object.values(data.data.pembayaran),
                        backgroundColor: ["#f59e0b", "#22c55e", "#cbd5e1", "#ef4444"]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { title: { display: true, text: "Status Pembayaran Pendaftaran" } }
                }
            });
        }
    } catch (e) {
        console.error("Gagal load admin stats", e);
    }
}

// ======================= CSV EXPORT =======================
function exportCSV() {
    if (globalPeserta.length === 0) {
        alert("Tidak ada data untuk diekspor");
        return;
    }
    
    // Header CSV
    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += "ID,Nama Lengkap,Email,NIK,Telepon,Program Studi,Status Verifikasi,Status Pembayaran,Tanggal Mendaftar\n";
    
    globalPeserta.forEach(p => {
        let row = [
            p.id,
            `"${p.nama || ""}"`,
            `"${p.email || ""}"`,
            `"${p.nik || ""}"`,
            `"${p.telepon || ""}"`,
            `"${p.prodi || ""}"`,
            `"${p.status_verifikasi || ""}"`,
            `"${p.status_pembayaran || ""}"`,
            `"${p.tanggal || ""}"`
        ];
        csvContent += row.join(",") + "\n";
    });
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `Data_Peserta_RPL_${new Date().toISOString().split("T")[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// ======================= SLIDER MANAGEMENT =======================
async function loadSlidersAdmin() {
    try {
        const res = await fetch('api/get_sliders.php?admin=1');
        const data = await res.json();
        
        const tbody = document.getElementById('slider-admin-body');
        if (!tbody) return;
        
        if (data.success) {
            if (data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Belum ada slider.</td></tr>';
                return;
            }
            
            tbody.innerHTML = data.data.map((s, index) => `
                <tr>
                    <td style="text-align:center;">${index + 1}</td>
                    <td>
                        <img src="${s.image_path}" alt="Slider ${s.id}" style="width: 150px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0;">
                    </td>
                    <td style="font-weight: 500;">
                        ${s.title || '<i style="color:#94a3b8">Tanpa Judul</i>'}<br>
                        <small style="color: #64748b;">${s.link_text ? 'Tombol: ' + s.link_text : 'Tanpa Tombol'}</small>
                    </td>
                    <td style="text-align:center;">
                        <span class="status-badge" style="background: ${s.is_active == 1 ? '#dcfce7' : '#fee2e2'}; color: ${s.is_active == 1 ? '#166534' : '#991b1b'}">
                            ${s.is_active == 1 ? 'Aktif' : 'Tidak Aktif'}
                        </span>
                    </td>
                    <td style="text-align:center;">${s.urutan}</td>
                    <td>
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <button class="btn-action" style="background: #f59e0b; color: white;" onclick="openSliderModal(${s.id}, '${s.title?.replace(/'/g, "\\'") || ''}', '${s.subtitle?.replace(/'/g, "\\'") || ''}', '${s.link_url || ''}', '${s.link_text || ''}', ${s.is_active}, ${s.urutan})" title="Edit"><i class="fas fa-pen"></i></button>
                            <button class="btn-action btn-reject" onclick="deleteSlider(${s.id})" title="Hapus" style="margin: 0;"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
    } catch (e) {
        console.error("Gagal load sliders", e);
    }
}

function openSliderModal(id = '', title = '', subtitle = '', link_url = '', link_text = '', is_active = 1, urutan = 0) {
    document.getElementById('slider-id').value = id;
    document.getElementById('slider-title').value = title;
    document.getElementById('slider-subtitle').value = subtitle;
    document.getElementById('slider-btn-link').value = link_url;
    document.getElementById('slider-btn-text').value = link_text;
    document.getElementById('slider-status').value = is_active;
    document.getElementById('slider-urutan').value = urutan;
    document.getElementById('slider-image').value = '';
    
    document.getElementById('slider-modal-title').textContent = id ? 'Edit Slider' : 'Tambah Slider Baru';
    document.getElementById('slider-image-note').style.display = id ? 'block' : 'none';
    if (!id) document.getElementById('slider-image').required = true;
    else document.getElementById('slider-image').required = false;
    
    document.getElementById('slider-modal').style.display = 'flex';
}

function closeSliderModal() {
    document.getElementById('slider-modal').style.display = 'none';
}

async function saveSlider(e) {
    e.preventDefault();
    const id = document.getElementById('slider-id').value;
    const title = document.getElementById('slider-title').value;
    const subtitle = document.getElementById('slider-subtitle').value;
    const link_url = document.getElementById('slider-btn-link').value;
    const link_text = document.getElementById('slider-btn-text').value;
    const is_active = document.getElementById('slider-status').value;
    const urutan = document.getElementById('slider-urutan').value;
    const imageFile = document.getElementById('slider-image').files[0];
    
    const formData = new FormData();
    formData.append('id', id);
    formData.append('title', title);
    formData.append('subtitle', subtitle);
    formData.append('link_url', link_url);
    formData.append('link_text', link_text);
    formData.append('is_active', is_active);
    formData.append('urutan', urutan);
    if (imageFile) formData.append('image', imageFile);
    
    try {
        const res = await fetch('api/save_slider.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            closeSliderModal();
            loadSlidersAdmin();
        }
    } catch (err) {
        alert('Terjadi kesalahan koneksi.');
        console.error(err);
    }
}

async function deleteSlider(id) {
    if (!confirm('Anda yakin ingin menghapus slider ini beserta gambarnya?')) return;
    
    try {
        const res = await fetch('api/delete_slider.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            loadSlidersAdmin();
        }
    } catch (err) {
        alert('Terjadi kesalahan koneksi.');
    }
}

// ==========================
// MANAJEMEN KONTEN WEB
// ==========================

async function savePengumuman(e) {
    e.preventDefault();
    const judul = document.getElementById('p-judul').value;
    const isi = document.getElementById('p-isi').value;
    const userId = localStorage.getItem('user_id');

    try {
        const res = await fetch('api/save_pengumuman.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': userId },
            body: JSON.stringify({ judul, isi })
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            document.getElementById('form-pengumuman').reset();
            loadKontenAdmin();
        }
    } catch (err) {
        alert('Terjadi kesalahan koneksi.');
    }
}

async function saveUnduhan(e) {
    e.preventDefault();
    const nama = document.getElementById('u-nama').value;
    const file = document.getElementById('u-file').files[0];
    const userId = localStorage.getItem('user_id');

    const formData = new FormData();
    formData.append('nama_dokumen', nama);
    formData.append('file_dokumen', file);

    try {
        const res = await fetch('api/save_unduhan.php', {
            method: 'POST',
            headers: { 'Authorization': userId },
            body: formData
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            document.getElementById('form-unduhan').reset();
            loadKontenAdmin();
        }
    } catch (err) {
        alert('Terjadi kesalahan koneksi.');
    }
}

async function loadKontenAdmin() {
    try {
        const res = await fetch('api/get_konten.php');
        const data = await res.json();
        
        if (data.success) {
            const listP = document.getElementById('list-pengumuman');
            listP.innerHTML = '';
            data.data.pengumuman.forEach(p => {
                listP.innerHTML += `
                    <li style="padding: 1rem; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 0.8rem; display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <strong style="display:block; color:#1e293b;">${p.judul}</strong>
                            <small style="color:#64748b;">${p.tanggal}</small>
                        </div>
                        <button onclick="deleteKonten('pengumuman', ${p.id})" class="btn-action btn-reject" style="margin:0;"><i class="fas fa-trash"></i></button>
                    </li>
                `;
            });

            const listU = document.getElementById('list-unduhan');
            listU.innerHTML = '';
            data.data.unduhan.forEach(u => {
                listU.innerHTML += `
                    <li style="padding: 1rem; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 0.8rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="color:#1e293b;">${u.nama_dokumen}</strong>
                            <div style="font-size:0.8rem; color:#64748b; margin-top:3px;"><a href="uploads/dokumen_landing/${u.file_path}" target="_blank">Lihat File</a></div>
                        </div>
                        <div style="display:flex; gap:0.5rem;">
                            <button onclick="openEditUnduhanModal(${u.id}, '${u.nama_dokumen.replace(/'/g, "\\'")}')" class="btn-action" style="background:#3b82f6; color:white; margin:0;"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteKonten('unduhan', ${u.id})" class="btn-action btn-reject" style="margin:0;"><i class="fas fa-trash"></i></button>
                        </div>
                    </li>
                `;
            });
        }
    } catch (err) {
        console.error('Gagal load konten:', err);
    }
}

async function deleteKonten(type, id) {
    if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) return;
    const userId = localStorage.getItem('user_id');

    try {
        const res = await fetch('api/delete_konten.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': userId },
            body: JSON.stringify({ type, id })
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            loadKontenAdmin();
        }
    } catch (err) {
        alert('Terjadi kesalahan koneksi.');
    }
}

function exportCSV() {
    const userId = localStorage.getItem('user_id');
    if (!userId) {
        alert('Sesi telah berakhir, silakan login kembali.');
        return;
    }
    window.location.href = `api/export_excel.php?token=${userId}`;
}

async function loadLogs() {
    const userId = localStorage.getItem('user_id');
    try {
        const res = await fetch('api/get_logs.php', {
            headers: { 'Authorization': userId }
        });
        const data = await res.json();
        
        const tbody = document.getElementById('logs-body');
        if (!tbody) return;

        if (data.success) {
            if (data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Belum ada log aktivitas.</td></tr>';
                return;
            }
            tbody.innerHTML = '';
            data.data.forEach((l, index) => {
                let badgeClass = 'status-verified';
                if (l.action.includes('HAPUS') || l.action.includes('TOLAK')) badgeClass = 'status-rejected';
                else if (l.action.includes('UBAH') || l.action.includes('EDIT')) badgeClass = 'status-waiting';

                tbody.innerHTML += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${l.timestamp}</td>
                        <td>Admin #${l.admin_id}</td>
                        <td><span class="status-badge ${badgeClass}">${l.action}</span></td>
                        <td>${l.target}</td>
                    </tr>
                `;
            });
        }
    } catch (err) {
        console.error('Gagal memuat log:', err);
    }
}

// ======================= HASIL KONVERSI SKS =======================
function openHasilModal(id, nama, prodi, tgl, lokasi) {
    document.getElementById('hasil-peserta-id').value = id;
    document.getElementById('hasil-nama').textContent = nama;
    document.getElementById('hasil-prodi').textContent = prodi || '-';
    
    // Reset table body
    document.getElementById('hasil-mk-body').innerHTML = '';
    
    // Load existing data
    loadHasilRpl(id);
    
    document.getElementById('hasil-modal').style.display = 'flex';
}

function closeHasilModal() {
    document.getElementById('hasil-modal').style.display = 'none';
}

function addMkRow(kode = '', nama = '', sks = '', nilai = '') {
    const tbody = document.getElementById('hasil-mk-body');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="mk_kode[]" value="${kode}" class="filter-input" style="width:100%; box-sizing:border-box;" required></td>
        <td><input type="text" name="mk_nama[]" value="${nama}" class="filter-input" style="width:100%; box-sizing:border-box;" required></td>
        <td><input type="number" name="mk_sks[]" value="${sks}" class="filter-input" style="width:100%; box-sizing:border-box;" required min="1" max="6"></td>
        <td><input type="text" name="mk_nilai[]" value="${nilai}" class="filter-input" style="width:100%; box-sizing:border-box;" required maxlength="2" placeholder="A/B/C"></td>
        <td><button type="button" class="btn-action btn-reject" style="margin:0; padding:0.3rem 0.6rem;" onclick="this.closest('tr').remove()"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(tr);
}

async function loadHasilRpl(pesertaId) {
    try {
        const res = await fetch(`api/get_hasil_rpl.php?peserta_id=${pesertaId}`);
        const data = await res.json();
        
        if (data.success && data.data.length > 0) {
            data.data.forEach(mk => {
                addMkRow(mk.kode_mk, mk.nama_mk, mk.sks, mk.nilai);
            });
        } else {
            // Default 1 empty row
            addMkRow();
        }
    } catch (e) {
        console.error('Gagal load hasil RPL:', e);
        addMkRow();
    }
}

async function saveHasilRpl(e) {
    e.preventDefault();
    
    const pesertaId = document.getElementById('hasil-peserta-id').value;
    const form = document.getElementById('form-hasil');
    
    const kodes = form.querySelectorAll('input[name="mk_kode[]"]');
    const namas = form.querySelectorAll('input[name="mk_nama[]"]');
    const skss = form.querySelectorAll('input[name="mk_sks[]"]');
    const nilais = form.querySelectorAll('input[name="mk_nilai[]"]');
    
    let mkList = [];
    for(let i = 0; i < kodes.length; i++) {
        mkList.push({
            kode: kodes[i].value,
            nama: namas[i].value,
            sks: skss[i].value,
            nilai: nilais[i].value
        });
    }
    
    const userId = localStorage.getItem('user_id');
    
    try {
        const res = await fetch('api/save_hasil_rpl.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': userId },
            body: JSON.stringify({ peserta_id: pesertaId, mk_list: mkList })
        });
        const data = await res.json();
        alert(data.message);
    } catch (err) {
        alert("Terjadi kesalahan koneksi saat menyimpan nilai.");
    }
}

function cetakSKKonversi() {
    const id = document.getElementById('hasil-peserta-id').value;
    if (!id) return;
    window.open(`cetak_sk.php?id=${id}`, '_blank');
}

// ==============================
// EDIT UNDUHAN MODAL
// ==============================
const editUnduhanModal = document.getElementById('edit-unduhan-modal');

function openEditUnduhanModal(id, nama_dokumen) {
    document.getElementById('e-unduhan-id').value = id;
    document.getElementById('e-u-nama').value = nama_dokumen;
    document.getElementById('e-u-file').value = '';
    editUnduhanModal.style.display = 'flex';
}

function closeEditUnduhanModal() {
    editUnduhanModal.style.display = 'none';
}

async function saveEditUnduhan(e) {
    e.preventDefault();
    const id = document.getElementById('e-unduhan-id').value;
    const nama = document.getElementById('e-u-nama').value;
    const file = document.getElementById('e-u-file').files[0];
    const userId = localStorage.getItem('user_id');

    const formData = new FormData();
    formData.append('id', id);
    formData.append('nama_dokumen', nama);
    if (file) {
        formData.append('file_dokumen', file);
    }

    try {
        const res = await fetch('api/edit_unduhan.php', {
            method: 'POST',
            headers: { 'Authorization': userId },
            body: formData
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            closeEditUnduhanModal();
            loadKontenAdmin();
        }
    } catch (err) {
        alert('Terjadi kesalahan koneksi.');
    }
}
