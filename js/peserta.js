document.addEventListener('DOMContentLoaded', () => {
    loadPesertaDetail();
});

async function loadPesertaDetail() {
    const userId = localStorage.getItem('user_id');
    const role = localStorage.getItem('role');

    if (!userId || role !== 'peserta') {
        alert('Sesi Anda tidak valid atau Anda bukan peserta.');
        window.location.href = 'login.html';
        return;
    }

    try {
        const res = await fetch('api/get_peserta_detail.php', {
            headers: { 'Authorization': userId }
        });
        const data = await res.json();
        
        if (data.success) {
            const p = data.data;
            
            // Update UI
            document.getElementById('user-name').textContent = p.nama;
            document.getElementById('detail-nama').textContent = p.nama;
            document.getElementById('detail-nik').textContent = p.nik;
            document.getElementById('detail-email').textContent = p.email;
            document.getElementById('detail-tanggal').textContent = p.tanggal_daftar;
            
            // Update Status Card
            const statusContainer = document.getElementById('status-container');
            const statusIcon = document.getElementById('status-icon');
            const statusText = document.getElementById('status-text');
            const statusDesc = document.getElementById('status-desc');
            
            statusContainer.className = 'status-card'; // reset classes
            
            if (p.status_verifikasi === 'Terverifikasi') {
                statusContainer.classList.add('Terverifikasi');
                statusIcon.className = 'fas fa-check-circle status-icon';
                statusText.textContent = 'Selamat! Pendaftaran Terverifikasi';
                statusDesc.textContent = 'Berkas Anda telah diverifikasi oleh tim asesor. Silakan tunggu email resmi untuk langkah selanjutnya atau pencetakan kartu.';
            } else if (p.status_verifikasi === 'Ditolak') {
                statusContainer.classList.add('Ditolak');
                statusIcon.className = 'fas fa-times-circle status-icon';
                statusText.textContent = 'Pendaftaran Ditolak';
                statusDesc.textContent = 'Mohon maaf, berkas Anda tidak memenuhi syarat Rekognisi Pembelajaran Lampau (RPL).';
            } else {
                statusContainer.classList.add('Menunggu');
                statusIcon.className = 'fas fa-hourglass-half status-icon';
                statusText.textContent = 'Menunggu Verifikasi';
                statusDesc.textContent = 'Berkas pendaftaran Anda sedang dalam antrean pemeriksaan oleh Tim Asesor. Silakan cek halaman ini secara berkala.';
            }

            // Calculate Progress Bar
            let progress = 25; // default for Belum Bayar
            document.getElementById('step-1').style.color = '#3b82f6';
            document.getElementById('step-2').style.color = '#475569';
            document.getElementById('step-3').style.color = '#475569';
            document.getElementById('step-4').style.color = '#475569';
            document.getElementById('step-5').style.color = '#475569';

            if (p.status_pembayaran === 'Lunas') {
                progress = 50;
                document.getElementById('step-2').style.color = '#3b82f6';
                if (p.alamat_lengkap) {
                    progress = 75;
                    document.getElementById('step-3').style.color = '#3b82f6';
                }
                if (p.dokumen_lengkap && p.dokumen_lengkap !== '{}' && p.dokumen_lengkap !== 'null') {
                    document.getElementById('step-4').style.color = '#3b82f6';
                }
                if (p.status_verifikasi === 'Terverifikasi') {
                    progress = 100;
                    document.getElementById('step-5').style.color = '#3b82f6';
                }
            }
            document.getElementById('progress-bar').style.width = progress + '%';

            const secBayar = document.getElementById('section-pembayaran');
            const secBiodata = document.getElementById('section-biodata');
            const secDokumen = document.getElementById('section-dokumen');
            const secHasil = document.getElementById('section-hasil');

            if (p.status_pembayaran !== 'Lunas') {
                secBayar.style.display = 'block';
                secBiodata.style.display = 'none';
                secDokumen.style.display = 'none';
                
                if (p.prodi && p.prodi.includes('S2')) {
                    document.getElementById('pay-prodi').textContent = 'Uang Pendaftaran — Program S-2';
                    document.getElementById('pay-amount').textContent = 'Rp 500.000';
                } else {
                    document.getElementById('pay-prodi').textContent = 'Uang Pendaftaran — Program S-1';
                    document.getElementById('pay-amount').textContent = 'Rp 250.000';
                }

                if (p.status_pembayaran === 'Menunggu Verifikasi Pembayaran') {
                    document.getElementById('upload-pembayaran-container').style.display = 'none';
                    document.getElementById('pembayaran-menunggu').style.display = 'block';
                }
            } else if (p.status_pembayaran === 'Lunas' && !p.alamat_lengkap) {
                secBayar.style.display = 'none';
                secBiodata.style.display = 'block';
                secDokumen.style.display = 'none';
            } else if (p.status_pembayaran === 'Lunas' && p.alamat_lengkap) {
                secBayar.style.display = 'none';
                secBiodata.style.display = 'none';
                secDokumen.style.display = 'block';
            }

            if (p.status_verifikasi === 'Terverifikasi') {
                secHasil.style.display = 'block';
                secDokumen.style.display = 'none'; // Sembunyikan form upload jika sudah terverifikasi
                loadHasilRPLPeserta(userId);
                
                if (p.jadwal_asesmen_tgl) document.getElementById('jadwal-tgl').textContent = p.jadwal_asesmen_tgl;
                if (p.jadwal_asesmen_lokasi) document.getElementById('jadwal-lokasi').textContent = p.jadwal_asesmen_lokasi;
            } else {
                secHasil.style.display = 'none';
            }

            if (p.dokumen_lengkap) {
                const docs = JSON.parse(p.dokumen_lengkap);
                let docStatus = {};
                let docCatatan = {};
                if (p.dokumen_status) {
                    try { docStatus = JSON.parse(p.dokumen_status); } catch(e){}
                }
                if (p.dokumen_catatan) {
                    try { docCatatan = JSON.parse(p.dokumen_catatan); } catch(e){}
                }

                Object.keys(docs).forEach(k => {
                    const badge = document.getElementById('badge-' + k);
                    const input = document.getElementById('doc_' + k);
                    if(badge) {
                        badge.style.display = 'inline-block';
                        
                        const existingNote = document.getElementById('note-' + k);
                        if (existingNote) existingNote.remove();
                        
                        if (docStatus[k] === 'rejected') {
                            badge.innerHTML = '<i class="fas fa-times"></i> Ditolak (Revisi)';
                            badge.style.background = '#fee2e2';
                            badge.style.color = '#991b1b';
                            if(input) input.style.display = 'block';
                            
                            if (docCatatan[k]) {
                                const noteDiv = document.createElement('div');
                                noteDiv.id = 'note-' + k;
                                noteDiv.style.color = '#991b1b';
                                noteDiv.style.fontSize = '0.85rem';
                                noteDiv.style.marginTop = '0.5rem';
                                noteDiv.style.padding = '0.5rem';
                                noteDiv.style.background = '#fef2f2';
                                noteDiv.style.borderRadius = '4px';
                                noteDiv.style.border = '1px solid #fecaca';
                                noteDiv.innerHTML = `<strong>Catatan Asesor:</strong> ${docCatatan[k]}`;
                                if(input) input.parentNode.insertBefore(noteDiv, input);
                            }
                        } else if (docStatus[k] === 'approved') {
                            badge.innerHTML = '<i class="fas fa-check-double"></i> Diterima';
                            badge.style.background = '#dcfce7';
                            badge.style.color = '#166534';
                            if(input) input.style.display = 'none';
                        } else {
                            badge.innerHTML = '<i class="fas fa-check"></i> Sudah Diunggah';
                            badge.style.background = '#dcfce7';
                            badge.style.color = '#166534';
                            if(input) input.style.display = 'none';
                        }
                    }
                });
            }
        } else {
            alert(data.message);
            window.location.href = 'login.html';
        }
    } catch (err) {
        console.error(err);
        alert('Gagal mengambil data dari server.');
    }
}

document.getElementById('upload-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('btn-upload');
    
    const formData = new FormData();
    const keys = ['f1', 'f2', 'f3', 'nf1', 'nf2', 'nf3', 'nf4', 'nf5', 'nf6', 'nf7', 'nf8', 'nf9', 'nf10', 'nf11', 'nf12', 'nf13'];
    let filesSelected = 0;

    keys.forEach(k => {
        const fileInput = document.getElementById('doc_' + k);
        if (fileInput && fileInput.files.length > 0) {
            formData.append(k, fileInput.files[0]);
            filesSelected++;
        }
    });

    if (filesSelected === 0) {
        alert('Pilih setidaknya 1 dokumen PDF untuk diunggah.');
        return;
    }

    btn.textContent = 'Mengunggah...';
    btn.disabled = true;
    formData.append('user_id', localStorage.getItem('user_id')); // Fallback payload

    try {
        const res = await fetch('api/upload_dokumen.php', {
            method: 'POST',
            headers: {
                'Authorization': localStorage.getItem('user_id')
            },
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            alert(data.message);
            loadPesertaDetail(); // reload to show badges
            document.getElementById('upload-form').reset();
        } else {
            alert(data.message);
        }
    } catch (err) {
        alert('Gagal terhubung ke server.');
    }
    
    btn.textContent = 'Simpan Dokumen';
    btn.disabled = false;
});

document.getElementById('form-pembayaran').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('btn-upload-pembayaran');
    const fileInput = document.getElementById('file-pembayaran');
    
    if (fileInput.files.length === 0) return;

    btn.textContent = 'Mengirim...';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('bukti', fileInput.files[0]);
    formData.append('user_id', localStorage.getItem('user_id'));

    try {
        const res = await fetch('api/upload_pembayaran.php', {
            method: 'POST',
            headers: {
                'Authorization': localStorage.getItem('user_id')
            },
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            alert(data.message);
            loadPesertaDetail();
        } else {
            alert(data.message);
        }
    } catch (err) {
        alert('Gagal terhubung ke server.');
    }
    
    btn.textContent = 'Kirim Bukti Pembayaran';
    btn.disabled = false;
});

document.getElementById('form-biodata').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('btn-save-biodata');
    btn.textContent = 'Menyimpan...';
    btn.disabled = true;

    const payload = {
        alamat_lengkap: document.getElementById('bio-alamat').value,
        pendidikan_terakhir: document.getElementById('bio-pendidikan').value,
        nama_ibu: document.getElementById('bio-ibu').value,
        asal_instansi: document.getElementById('bio-instansi').value
    };

    try {
        const res = await fetch('api/update_biodata.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': localStorage.getItem('user_id')
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        
        if (data.success) {
            alert(data.message);
            loadPesertaDetail(); // reload to show next section
        } else {
            alert(data.message);
        }
    } catch (err) {
        alert('Gagal terhubung ke server.');
    }
    
    btn.textContent = 'Simpan Biodata';
    btn.disabled = false;
});

function logout() {
    if(confirm('Anda yakin ingin keluar?')) {
        localStorage.removeItem('user_id');
        localStorage.removeItem('role');
        window.location.href = 'index.html';
    }
}

document.getElementById('form-password').addEventListener('submit', async (e) => {
    e.preventDefault();
    const passBaru = document.getElementById('pass-baru').value;
    const passKonfirm = document.getElementById('pass-konfirm').value;
    
    if (passBaru !== passKonfirm) {
        alert('Konfirmasi kata sandi tidak cocok!');
        return;
    }

    const btn = document.getElementById('btn-save-password');
    btn.textContent = 'Memperbarui...';
    btn.disabled = true;

    try {
        const res = await fetch('api/change_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': localStorage.getItem('user_id')
            },
            body: JSON.stringify({ password: passBaru })
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) {
            document.getElementById('form-password').reset();
        }
    } catch (err) {
        alert('Gagal terhubung ke server.');
    }
    
    btn.textContent = 'Perbarui Kata Sandi';
    btn.disabled = false;
});

async function loadHasilRPLPeserta(userId) {
    try {
        const res = await fetch(`api/get_hasil.php?peserta_id=${userId}`, {
            headers: { 'Authorization': userId }
        });
        const data = await res.json();
        
        const tbody = document.getElementById('tabel-sks');
        if (data.success && data.data.length > 0) {
            let totalSks = 0;
            tbody.innerHTML = data.data.map(h => {
                totalSks += parseInt(h.sks) || 0;
                return `
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">${h.kode_mk}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">${h.nama_mk}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">${h.sks}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #0ea5e9;">${h.nilai}</td>
                </tr>
                `;
            }).join('');
            document.getElementById('total-sks').textContent = totalSks;
        } else {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 1rem; color: #64748b;">Asesor belum memasukkan nilai konversi untuk Anda.</td></tr>';
        }
    } catch (err) {
        console.error("Gagal load hasil RPL", err);
    }
}
