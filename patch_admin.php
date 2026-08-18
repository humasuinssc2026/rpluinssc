<?php
$file = 'c:\\Users\\info\\rpl\\admin.html';
$html = file_get_contents($file);

// 1. Add Sidebar Menu
$menu_konten = '<li><a href="#" class="active" id="menu-konten" onclick="showSection(\'konten\')"><i class="fas fa-edit"></i> Kelola Konten Web</a></li>';
$menu_jadwal = "\n            <li><a href=\"#\" id=\"menu-jadwal\" onclick=\"showSection('jadwal')\"><i class=\"fas fa-calendar-alt\"></i> Kelola Jadwal</a></li>";
$html = str_replace($menu_konten, $menu_konten . $menu_jadwal, $html);

// 2. Add Section Jadwal before section-prodi
// Actually, since I don't know the exact ID of the prodi section (it might be "section-prodi"), let me just inject it before closing tag of admin-main
$section_jadwal = '
        <!-- SECTION JADWAL -->
        <div id="section-jadwal" style="display: none;">
            <div class="dashboard-header">
                <div style="display: flex; align-items: center;">
                    <button class="mobile-toggle" onclick="document.querySelector(\'.admin-sidebar\').classList.toggle(\'open\')"><i class="fas fa-bars"></i></button>
                    <div>
                        <h1 style="font-size: 1.5rem; color: #1e293b;">Kelola Jadwal Dinamis</h1>
                        <p style="color: #64748b; font-size: 0.9rem;">Atur jadwal kegiatan pendaftaran yang akan tampil di halaman depan</p>
                    </div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
                <div class="dashboard-card">
                    <h3 style="margin-top:0; color: #1e293b;"><i class="fas fa-plus"></i> Form Jadwal</h3>
                    <form id="form-jadwal" onsubmit="saveJadwal(event)">
                        <input type="hidden" id="j-id">
                        <div style="margin-bottom: 1rem;">
                            <label style="display:block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 600;">Nama Kegiatan</label>
                            <input type="text" id="j-kegiatan" required style="width: 100%; padding: 0.8rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" placeholder="Misal: Pendaftaran & Unggah Portofolio">
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <label style="display:block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 600;">Tanggal / Rentang Waktu</label>
                            <input type="text" id="j-tanggal" required style="width: 100%; padding: 0.8rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" placeholder="Misal: 18 Mei - 14 Juli 2026">
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <label style="display:block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 600;">Urutan Tampil (Opsional)</label>
                            <input type="number" id="j-urutan" value="0" style="width: 100%; padding: 0.8rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                        <button type="submit" class="btn-sso" style="background: #10b981; color: white; border: none; width: 100%;"><i class="fas fa-save"></i> Simpan Jadwal</button>
                        <button type="button" class="btn-sso" style="background: #f1f5f9; color: #475569; border: none; width: 100%; margin-top: 0.5rem;" onclick="resetFormJadwal()"><i class="fas fa-undo"></i> Batal / Baru</button>
                    </form>
                </div>
                
                <div class="dashboard-card">
                    <h3 style="margin-top:0; color: #1e293b;"><i class="fas fa-list"></i> Daftar Jadwal</h3>
                    <div style="overflow-x: auto;">
                        <table class="admin-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th width="10%">No/Urutan</th>
                                    <th width="40%">Kegiatan</th>
                                    <th width="35%">Tanggal</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="list-jadwal-body">
                                <tr><td colspan="4" style="text-align: center;">Memuat jadwal...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
';

$html = str_replace('</main>', $section_jadwal . "\n    </main>", $html);
file_put_contents($file, $html);
echo "admin.html patched\n";
