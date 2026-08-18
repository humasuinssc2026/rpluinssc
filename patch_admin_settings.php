<?php
$file = 'c:\\Users\\info\\rpl\\admin.html';
$html = file_get_contents($file);

$card_jadwal = '            <div class="dashboard-card" style="max-width: 600px;">
                <h3 style="margin-top:0; color: #1e293b;"><i class="far fa-calendar-alt"></i> Jadwal Pendaftaran</h3>
                <form id="form-settings" onsubmit="saveSettings(event)" style="margin-top:1.5rem;">
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #475569;">Buka Pendaftaran</label>
                        <input type="datetime-local" id="set-mulai" required style="width: 100%; padding: 0.8rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-family: inherit;">
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #475569;">Tutup Pendaftaran (Batas Akhir)</label>
                        <input type="datetime-local" id="set-selesai" required style="width: 100%; padding: 0.8rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-family: inherit;">
                    </div>
                    <div style="margin-bottom: 2rem;">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #475569;">Status Gelombang</label>
                        <select id="set-isopen" required style="width: 100%; padding: 0.8rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-family: inherit;">
                            <option value="true">DIBUKA (Menerima Pendaftar)</option>
                            <option value="false">DITUTUP</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-action btn-verify" style="padding: 0.8rem 2rem; border-radius: 6px; width: 100%;">Simpan Jadwal</button>
                </form>
            </div>';

$new_layout = '            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
' . $card_jadwal . '
                <!-- Pengaturan Kontak & Web -->
                <div class="dashboard-card">
                    <h3 style="margin-top:0; color: #1e293b;"><i class="fas fa-globe"></i> Kontak & Pengumuman Web</h3>
                    <form id="form-web-settings" onsubmit="saveWebSettings(event)" style="margin-top:1.5rem;">
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #475569;">Teks Berjalan (Topbar)</label>
                            <input type="text" id="set-teks-berjalan" required style="width: 100%; padding: 0.8rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-family: inherit;">
                        </div>
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #475569;">Nomor WhatsApp (Mulai dengan 62)</label>
                            <input type="text" id="set-wa" required style="width: 100%; padding: 0.8rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-family: inherit;">
                        </div>
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #475569;">Alamat Email</label>
                            <input type="text" id="set-email" required style="width: 100%; padding: 0.8rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-family: inherit;">
                        </div>
                        <div style="margin-bottom: 2rem;">
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #475569;">Nomor Telepon Footer</label>
                            <input type="text" id="set-telepon" required style="width: 100%; padding: 0.8rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-family: inherit;">
                        </div>
                        <button type="submit" class="btn-action btn-verify" style="padding: 0.8rem 2rem; border-radius: 6px; width: 100%;">Simpan Kontak</button>
                    </form>
                </div>
            </div>';

$html = str_replace(
    '<div class="dashboard-card" style="max-width: 600px;">
                <h3 style="margin-top:0; color: #1e293b;"><i class="far fa-calendar-alt"></i> Jadwal Pendaftaran</h3>
                <form id="form-settings" onsubmit="saveSettings(event)" style="margin-top:1.5rem;">
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #475569;">Buka Pendaftaran</label>
                        <input type="datetime-local" id="set-mulai" required style="width: 100%; padding: 0.8rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-family: inherit;">
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #475569;">Tutup Pendaftaran (Batas Akhir)</label>
                        <input type="datetime-local" id="set-selesai" required style="width: 100%; padding: 0.8rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-family: inherit;">
                    </div>
                    <div style="margin-bottom: 2rem;">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #475569;">Status Gelombang</label>
                        <select id="set-isopen" required style="width: 100%; padding: 0.8rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-family: inherit;">
                            <option value="true">DIBUKA (Menerima Pendaftar)</option>
                            <option value="false">DITUTUP</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-action btn-verify" style="padding: 0.8rem 2rem; border-radius: 6px; width: 100%;">Simpan Pengaturan</button>
                </form>
            </div>',
    $new_layout,
    $html
);

file_put_contents($file, $html);
echo "admin.html settings patched\n";
