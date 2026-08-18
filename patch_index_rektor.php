<?php
$file = 'c:\\Users\\info\\rpl\\index.html';
$html = file_get_contents($file);

$old_script = '                    if (data.data.kontak_wa) {
                        const cleanWa = data.data.kontak_wa.replace(/\D/g, "");
                        document.getElementById("public-wa-btn").href = `https://wa.me/${cleanWa}?text=Halo%20Admin%20RPL%20UINSSC,%20saya%20butuh%20bantuan.`;
                    }';

$new_script = '                    if (data.data.kontak_wa) {
                        const cleanWa = data.data.kontak_wa.replace(/\D/g, "");
                        document.getElementById("public-wa-btn").href = `https://wa.me/${cleanWa}?text=Halo%20Admin%20RPL%20UINSSC,%20saya%20butuh%20bantuan.`;
                    }
                    if (data.data.rektor_nama && document.getElementById("rektor-nama")) document.getElementById("rektor-nama").innerText = data.data.rektor_nama;
                    if (data.data.rektor_jabatan && document.getElementById("rektor-jabatan")) document.getElementById("rektor-jabatan").innerText = data.data.rektor_jabatan;
                    if (data.data.rektor_teks && document.getElementById("rektor-teks")) document.getElementById("rektor-teks").innerText = data.data.rektor_teks;
                    if (data.data.rektor_foto && document.getElementById("rektor-foto")) document.getElementById("rektor-foto").src = data.data.rektor_foto;';

$html = str_replace($old_script, $new_script, $html);
file_put_contents($file, $html);
echo "index.html rektor public patched\n";
