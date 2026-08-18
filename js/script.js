document.addEventListener('DOMContentLoaded', () => {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    const darkModeBtn = document.getElementById('dark-mode-toggle');
    if (darkModeBtn) {
        // Cek localStorage
        if (localStorage.getItem('dark_mode') === 'true') {
            document.body.classList.add('dark-mode');
            darkModeBtn.innerHTML = '<i class="fas fa-sun"></i>';
        }
        
        darkModeBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('dark_mode', isDark);
            
            if (isDark) {
                darkModeBtn.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                darkModeBtn.innerHTML = '<i class="far fa-moon"></i>';
            }
        });
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active class from all buttons and contents
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            // Add active class to clicked button
            btn.classList.add('active');

            // Show corresponding content
            const targetId = btn.getAttribute('data-target');
            const targetContent = document.getElementById(targetId);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });

    // Accordion Logic
    const accordionHeaders = document.querySelectorAll('.accordion-header');
    
    accordionHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const item = header.parentElement;
            const body = item.querySelector('.accordion-body');
            
            // Toggle current item
            if (item.classList.contains('active')) {
                item.classList.remove('active');
                body.style.maxHeight = null;
            } else {
                // Optionally close other items
                document.querySelectorAll('.accordion-item.active').forEach(activeItem => {
                    activeItem.classList.remove('active');
                    activeItem.querySelector('.accordion-body').style.maxHeight = null;
                });
                
                item.classList.add('active');
                body.style.maxHeight = body.scrollHeight + 'px';
            }
        });
    });

    loadKontenPublic();
    loadProdiPublic();
    initCountdown();
    loadPublicStats();
    loadSlidersPublic();
    loadGaleriPublic();
});

let currentSlide = 0;
let slideInterval;

async function loadSlidersPublic() {
    try {
        const res = await fetch('api/get_sliders.php');
        const data = await res.json();
        
        const track = document.getElementById('slider-track');
        const dotsContainer = document.getElementById('slider-dots');
        
        if (!track || !data.success || data.data.length === 0) {
            if(track) {
                track.innerHTML = `
                <div class="slide" style="background-image: url('header.png');">
                    <div class="hero-overlay"></div>
                    <div class="hero-content" style="z-index: 2; position: relative;">
                        <h1 class="hero-title">Rekognisi Pembelajaran<br>Lampau</h1>
                        <div class="hero-line"></div>
                        <p class="hero-desc">Portal Sistem Penerimaan Mahasiswa Baru Jalur Rekognisi Pembelajaran Lampau (RPL).</p>
                    </div>
                </div>`;
            }
            return;
        }

        let slidesHtml = '';
        let dotsHtml = '';
        
        data.data.forEach((s, index) => {
            let btnHtml = '';
            if (s.link_url && s.link_text) {
                btnHtml = `<a href="${s.link_url}" class="btn-hero btn-green" target="_blank" style="margin-top:1rem;">${s.link_text}</a>`;
            }
            
            slidesHtml += `
                <div class="slide" style="background-image: url('${s.image_path}');">
                    <div class="hero-overlay"></div>
                    <div class="hero-content">
                        ${s.title ? `<h1 class="hero-title">${s.title}</h1>` : ''}
                        ${s.title ? `<div class="hero-line"></div>` : ''}
                        ${s.subtitle ? `<p class="hero-desc">${s.subtitle}</p>` : ''}
                        ${btnHtml}
                    </div>
                </div>
            `;
            dotsHtml += `<div class="slider-dot ${index === 0 ? 'active' : ''}" data-index="${index}"></div>`;
        });
        
        track.innerHTML = slidesHtml;
        dotsContainer.innerHTML = dotsHtml;
        
        initSlider(data.data.length);
    } catch(e) {
        console.error("Gagal load sliders", e);
    }
}

function initSlider(totalSlides) {
    const track = document.getElementById('slider-track');
    const dots = document.querySelectorAll('.slider-dot');
    
    function goToSlide(index) {
        currentSlide = (index + totalSlides) % totalSlides;
        track.style.transform = `translateX(-${currentSlide * 100}%)`;
        
        dots.forEach(d => d.classList.remove('active'));
        if(dots[currentSlide]) dots[currentSlide].classList.add('active');
    }
    
    document.getElementById('slider-prev')?.addEventListener('click', () => {
        goToSlide(currentSlide - 1);
        resetInterval();
    });
    
    document.getElementById('slider-next')?.addEventListener('click', () => {
        goToSlide(currentSlide + 1);
        resetInterval();
    });
    
    dots.forEach(dot => {
        dot.addEventListener('click', (e) => {
            goToSlide(parseInt(e.target.dataset.index));
            resetInterval();
        });
    });
    
    function resetInterval() {
        clearInterval(slideInterval);
        slideInterval = setInterval(() => goToSlide(currentSlide + 1), 10000);
    }
    
    resetInterval();
}

async function initCountdown() {
    try {
        const res = await fetch('api/settings.php');
        const data = await res.json();
        
        let targetDateStr = '2026-07-30T23:59:00'; // default fallback
        if (data.success && data.data.jadwal_selesai) {
            targetDateStr = data.data.jadwal_selesai;
        }

        const targetDate = new Date(targetDateStr).getTime();
        const interval = setInterval(() => {
            const now = new Date().getTime();
            const distance = targetDate - now;

            if (distance < 0 || (data.success && data.data.is_open === 'false')) {
                clearInterval(interval);
                document.getElementById('countdown').innerHTML = '<div style="color:white; font-weight:bold; font-size:1.2rem;">Pendaftaran Ditutup</div>';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('cd-days').textContent = days < 10 ? '0' + days : days;
            document.getElementById('cd-hours').textContent = hours < 10 ? '0' + hours : hours;
            document.getElementById('cd-minutes').textContent = minutes < 10 ? '0' + minutes : minutes;
            document.getElementById('cd-seconds').textContent = seconds < 10 ? '0' + seconds : seconds;
        }, 1000);
    } catch(e) {
        console.error("Gagal memuat jadwal dari server:", e);
    }
}

function animateValue(obj, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        obj.innerHTML = Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        } else {
            obj.innerHTML = end + '+'; // Add plus sign at end
        }
    };
    window.requestAnimationFrame(step);
}

async function loadPublicStats() {
    try {
        const res = await fetch('api/get_stats_public.php');
        const data = await res.json();
        
        if (data.success) {
            animateValue(document.getElementById('stat-pendaftar'), 0, data.data.pendaftar, 2000);
            animateValue(document.getElementById('stat-prodi'), 0, data.data.prodi, 2000);
            animateValue(document.getElementById('stat-sks'), 0, data.data.sks, 2000);
        }
    } catch(e) {
        console.error("Gagal load statistik", e);
    }
}

async function loadKontenPublic() {
    try {
        const res = await fetch('api/get_konten.php');
        const data = await res.json();
        
        if (data.success) {
            // Rektor
            if (data.data.rektor) {
                if (document.getElementById('rektor-foto') && data.data.rektor.rektor_foto) {
                    document.getElementById('rektor-foto').src = data.data.rektor.rektor_foto;
                }
                if (document.getElementById('rektor-teks') && data.data.rektor.rektor_teks) {
                    document.getElementById('rektor-teks').textContent = data.data.rektor.rektor_teks;
                }
                if (document.getElementById('rektor-nama') && data.data.rektor.rektor_nama) {
                    document.getElementById('rektor-nama').textContent = data.data.rektor.rektor_nama;
                }
                if (document.getElementById('rektor-jabatan') && data.data.rektor.rektor_jabatan) {
                    document.getElementById('rektor-jabatan').textContent = data.data.rektor.rektor_jabatan;
                }
            }

            const pContainer = document.getElementById('pengumuman-container');
            if (data.data.pengumuman.length === 0) {
                pContainer.innerHTML = '<div style="text-align:center; padding:2rem; color:#64748b;">Belum ada pengumuman.</div>';
            } else {
                pContainer.innerHTML = '';
                data.data.pengumuman.forEach(p => {
                    pContainer.innerHTML += `
                        <div class="news-card">
                            <div class="news-date"><i class="far fa-clock"></i> ${p.tanggal}</div>
                            <h3 class="news-title">${p.judul}</h3>
                            <div class="news-content">${p.isi.replace(/\n/g, '<br>')}</div>
                        </div>
                    `;
                });
            }

            const uContainer = document.getElementById('unduhan-container');
            if (data.data.unduhan.length === 0) {
                uContainer.innerHTML = '<div style="text-align:center; padding:2rem; color:white; grid-column: 1 / -1;">Belum ada dokumen unduhan.</div>';
            } else {
                uContainer.innerHTML = '';
                data.data.unduhan.forEach((u, index) => {
                    // Fallback description since it's not in the DB
                    let desc = "Dokumen resmi panduan dan petunjuk teknis penyelenggaraan Rekognisi Pembelajaran Lampau (RPL).";
                    if (u.nama_dokumen.toLowerCase().includes('peraturan menteri') || u.nama_dokumen.toLowerCase().includes('41 tahun 2021')) {
                        desc = "RPL skema Transfer SKS (pengakuan Capaian Pembelajaran dari Program Studi pada Perguruan Tinggi sebelumnya) diselenggarakan oleh program studi yang terakreditasi dan telah menghasilkan lulusan.";
                    } else if (u.nama_dokumen.toLowerCase().includes('keputusan direktorat') || u.nama_dokumen.toLowerCase().includes('162')) {
                        desc = "Kepdirjen Diktiristek tentang Petunjuk Teknik Penyelenggaraan Rekognisi Pembelajaran Lampau (RPL) pada Perguruan Tinggi yang Menyelenggarakan Pendidikan Akademik";
                    }

                    uContainer.innerHTML += `
                        <div class="download-card">
                            <div>
                                <h3 class="download-title">${u.nama_dokumen}</h3>
                                <p class="download-desc">${desc}</p>
                            </div>
                            <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                <a href="uploads/dokumen_landing/${u.file_path}" class="btn-download-blue" target="_blank" style="flex: 1;"><i class="fas fa-download"></i> Download</a>
                                <button type="button" class="btn-download-blue" onclick="openPdfPreview('uploads/dokumen_landing/${u.file_path}')" style="width: auto; background: #64748b;" title="Pratinjau Dokumen">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
            }
        }
    } catch (e) {
        console.error('Gagal memuat konten:', e);
        document.getElementById('pengumuman-container').innerHTML = '<div style="text-align:center; padding:2rem; color:#ef4444;">Gagal memuat pengumuman.</div>';
    }
}

async function loadProdiPublic() {
    try {
        const res = await fetch('api/get_prodi.php');
        const data = await res.json();
        const tbody = document.getElementById('prodi-public-body');
        
        if (!tbody) return;

        if (data.success && data.data.length > 0) {
            tbody.innerHTML = data.data.map((p, index) => {
                let certHtml = p.sertifikat_path 
                    ? `<div style="display: flex; gap: 10px; justify-content: center;">
                           <a href="#" onclick="openPdfPreview('${p.sertifikat_path}'); return false;" style="color: #3b82f6; font-weight: bold; text-decoration: none;"><i class="fas fa-eye"></i> Lihat</a>
                           <a href="${p.sertifikat_path}" download style="color: #10b981; font-weight: bold; text-decoration: none;"><i class="fas fa-download"></i> Unduh</a>
                       </div>` 
                    : `<span style="color: #94a3b8; font-size: 0.85rem;">Belum ada</span>`;
                
                let pedomanHtml = (p.pedoman_count && parseInt(p.pedoman_count) > 0)
                    ? `<div style="display: flex; justify-content: center;"><button class="btn-sso" style="background: #10b981; color: white; border: none; padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 4px; cursor: pointer; white-space: nowrap;" onclick="openPedomanPublic(${p.id}, '${p.nama_prodi}')"><i class="fas fa-folder-open"></i> Lihat File (${p.pedoman_count})</button></div>` 
                    : `<span style="color: #94a3b8; font-size: 0.85rem;">Belum ada</span>`;
                
                return `
                    <tr>
                        <td style="text-align: center;">${index + 1}</td>
                        <td style="font-weight: 500;">${p.nama_prodi}</td>
                        <td style="text-align: center;">${p.nomor_penyelenggara}</td>
                        <td style="text-align: center;">${certHtml}</td>
                        <td style="text-align: center;">${pedomanHtml}</td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: #64748b;">Belum ada data program studi.</td></tr>`;
        }
    } catch(e) {
        console.error("Gagal load prodi", e);
        const tbody = document.getElementById('prodi-public-body');
        if(tbody) tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: #ef4444;">Gagal memuat data program studi.</td></tr>`;
    }
}

function openPdfPreview(url, title = 'Preview Dokumen') {
    const titleEl = document.getElementById('pdf-preview-title');
    if(titleEl) titleEl.textContent = title;
    
    document.getElementById('pdfPreviewFrame').src = url;
    document.getElementById('pdfPreviewModal').style.display = 'flex';
}

function closePdfPreview() {
    document.getElementById('pdfPreviewModal').style.display = 'none';
    document.getElementById('pdfPreviewFrame').src = '';
}

// ================= PEDOMAN PUBLIC =================
async function openPedomanPublic(prodiId, prodiName) {
    document.getElementById('pedoman-public-nama').textContent = prodiName;
    document.getElementById('pedomanPublicModal').style.display = 'flex';
    
    const tbody = document.getElementById('pedoman-public-body');
    tbody.innerHTML = '<tr><td colspan="3" style="text-align: center;">Memuat file...</td></tr>';
    
    try {
        const res = await fetch(`api/get_pedoman_files.php?prodi_id=${prodiId}`);
        const data = await res.json();
        
        if (data.success && data.data.length > 0) {
            tbody.innerHTML = data.data.map((f, i) => {
                const isPdf = f.file_path.toLowerCase().endsWith('.pdf');
                const lihatBtn = isPdf 
                    ? `<a href="#" onclick="openPdfPreview('${f.file_path}', 'Preview: ${f.nama_dokumen}'); return false;" style="color: #3b82f6; text-decoration: none; padding: 4px 8px; border: 1px solid #bfdbfe; border-radius: 4px; font-size: 0.85rem;"><i class="fas fa-eye"></i> Lihat</a>`
                    : `<span style="color: #94a3b8; font-size: 0.8rem; padding: 4px 8px; background: #f1f5f9; border-radius: 4px; border: 1px solid #e2e8f0;" title="Dokumen ini harus diunduh (bukan PDF)"><i class="fas fa-file-word"></i> HANYA UNDUH</span>`;
                
                return `
                <tr>
                    <td style="text-align:center;">${i + 1}</td>
                    <td style="font-weight: 500;">${f.nama_dokumen}</td>
                    <td style="text-align:center;">
                        <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                            ${lihatBtn}
                            <a href="${f.file_path}" download style="color: #10b981; text-decoration: none; padding: 4px 8px; border: 1px solid #bbf7d0; border-radius: 4px; font-size: 0.85rem;"><i class="fas fa-download"></i> Unduh</a>
                        </div>
                    </td>
                </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="3" style="text-align: center; color: #64748b;">Belum ada file pedoman.</td></tr>';
        }
    } catch (e) {
        console.error(e);
        tbody.innerHTML = '<tr><td colspan="3" style="text-align: center; color: #ef4444;">Gagal memuat file.</td></tr>';
    }
}

function closePedomanPublic() {
    document.getElementById('pedomanPublicModal').style.display = 'none';
}

let galeriInterval;

async function loadGaleriPublic() {
    const container = document.getElementById('galeri-public-container');
    if (!container) return;
    
    try {
        const res = await fetch('api/get_galeri.php');
        const data = await res.json();
        
        if (data.success && data.data.length > 0) {
            container.innerHTML = data.data.map(g => `
                <div class="galeri-item">
                    <img src="${g.image_path}" alt="Galeri RPL" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                </div>
            `).join('');
            
            // Logika Auto-slide (5 detik)
            if (galeriInterval) clearInterval(galeriInterval);
            galeriInterval = setInterval(() => {
                const maxScrollLeft = container.scrollWidth - container.clientWidth;
                // Jika sudah mentok di kanan, kembalikan ke awal
                if (container.scrollLeft >= maxScrollLeft - 10) {
                    container.scrollLeft = 0;
                } else {
                    const item = container.querySelector('.galeri-item');
                    if (item) {
                        const gap = parseFloat(getComputedStyle(container).gap) || 0;
                        container.scrollBy({ left: item.offsetWidth + gap, behavior: 'smooth' });
                    }
                }
            }, 5000);
            
        } else {
            container.innerHTML = '<div style="text-align: center; width: 100%; color: #64748b; padding: 2rem;">Belum ada foto galeri.</div>';
        }
    } catch (e) {
        console.error("Gagal memuat galeri:", e);
        container.innerHTML = '<div style="text-align: center; width: 100%; color: #ef4444; padding: 2rem;">Gagal memuat galeri.</div>';
    }
}
