<?php
$file = 'c:\\Users\\info\\rpl\\index.html';
$html = file_get_contents($file);

// Replace hardcoded table rows with a dynamic tbody
$start = strpos($html, '<tbody>', strpos($html, '<div id="jadwal"'));
$end = strpos($html, '</tbody>', $start) + 8; // length of '</tbody>'

$old_tbody = substr($html, $start, $end - $start);
$new_tbody = '<tbody id="jadwal-public-body">
                        <tr><td colspan="3" style="padding: 2rem; text-align: center; color: #64748b;"><i class="fas fa-spinner fa-spin"></i> Memuat jadwal...</td></tr>
                    </tbody>';

$html = str_replace($old_tbody, $new_tbody, $html);

// Add JS logic to fetch and render
$script_jadwal = '
        async function loadJadwalPublic() {
            try {
                const res = await fetch("api/get_jadwal.php");
                const data = await res.json();
                const tbody = document.getElementById("jadwal-public-body");
                
                if (data.success && data.data.length > 0) {
                    tbody.innerHTML = data.data.map((j, index) => {
                        const bgClass = index % 2 === 1 ? "background: #fafaf9;" : "";
                        return `
                        <tr style="border-bottom: 1px solid #e2e8f0; ${bgClass}">
                            <td style="padding: 1rem; text-align: center; border-right: 1px solid #e2e8f0;">${index + 1}</td>
                            <td style="padding: 1rem; border-right: 1px solid #e2e8f0;">${j.kegiatan}</td>
                            <td style="padding: 1rem;">${j.tanggal}</td>
                        </tr>
                        `;
                    }).join("");
                } else {
                    tbody.innerHTML = "<tr><td colspan=\'3\' style=\'padding: 2rem; text-align: center; color: #64748b;\'>Belum ada jadwal yang dipublikasikan.</td></tr>";
                }
            } catch (e) {
                console.error("Gagal memuat jadwal:", e);
                document.getElementById("jadwal-public-body").innerHTML = "<tr><td colspan=\'3\' style=\'padding: 2rem; text-align: center; color: #ef4444;\'>Gagal memuat jadwal.</td></tr>";
            }
        }
';

// Add the call to DOMContentLoaded
$js_hook = "loadProdiPublic();\n            loadJadwalPublic();";
$html = str_replace('loadProdiPublic();', $js_hook, $html);

// Add the function definition before closing </script>
$html = str_replace('function closePdfPreview() {', $script_jadwal . "\n        function closePdfPreview() {", $html);

file_put_contents($file, $html);
echo "index.html patched\n";
