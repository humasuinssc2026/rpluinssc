function hitungSKS(e) {
    e.preventDefault();
    const tahun = parseInt(document.getElementById('calc-tahun').value) || 0;
    const sertifikat = parseInt(document.getElementById('calc-sertifikat').value) || 0;
    const pendidikan = document.getElementById('calc-pendidikan').value;

    let estimasiSks = 0;

    // Base SKS dari pendidikan
    if (pendidikan === 'D1') estimasiSks += 30;
    if (pendidikan === 'D2') estimasiSks += 60;
    if (pendidikan === 'D3') estimasiSks += 90;
    if (pendidikan === 'S1') estimasiSks += 40; // Asumsi pindahan

    // Tambahan dari pengalaman kerja (maksimal 40 SKS, asumsi 1 tahun = 4 SKS)
    let sksKerja = tahun * 4;
    if (sksKerja > 40) sksKerja = 40;
    estimasiSks += sksKerja;

    // Tambahan dari sertifikat (maksimal 20 SKS, asumsi 1 sertifikat = 2 SKS)
    let sksSertifikat = sertifikat * 2;
    if (sksSertifikat > 20) sksSertifikat = 20;
    estimasiSks += sksSertifikat;

    // Cap at 110 SKS (maksimal yang bisa di-RPL-kan untuk S1 biasanya sekitar 110-114)
    if (estimasiSks > 110) estimasiSks = 110;

    document.getElementById('calc-result').textContent = estimasiSks + ' SKS';
    document.getElementById('hasil-kalkulator').style.display = 'block';
}
