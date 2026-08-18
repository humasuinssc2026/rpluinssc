<?php
$file = 'c:\\Users\\info\\rpl\\js\\admin.js';
$js = file_get_contents($file);

// Safely cut out PEMBAYARAN VALIDATION FUNCTIONS
$start1 = strpos($js, '// ======================= PEMBAYARAN VALIDATION FUNCTIONS =======================');
$end1 = strpos($js, '// ======================= AUDIT LOGS FUNCTIONS =======================');
if ($start1 !== false && $end1 !== false) {
    $js = substr($js, 0, $start1) . substr($js, $end1);
}

// Safely cut out HASIL RPL FUNCTIONS up to the next block
$start2 = strpos($js, '// ======================= HASIL RPL FUNCTIONS =======================');
$end2 = strpos($js, 'async function initAdminCharts');
if ($start2 !== false && $end2 !== false) {
    // Leave a couple newlines before initAdminCharts
    $js = substr($js, 0, $start2) . substr($js, $end2);
}

// Safely cut out the duplicate HASIL KONVERSI SKS / Hasil RPL if any
$start3 = strpos($js, 'function openHasilModal(id, nama, prodi, tgl, lokasi) {');
if ($start3 !== false) {
    $end3 = strpos($js, '// EDIT UNDUHAN MODAL');
    if ($end3 !== false) {
        // Need to backtrack from end3 to the previous line `// ==============================`
        $end3_real = strrpos(substr($js, 0, $end3), '// ==============================');
        if ($end3_real !== false) {
            $js = substr($js, 0, $start3) . substr($js, $end3_real);
        }
    }
}

file_put_contents($file, $js);
echo "js/admin.js cleaned safely\n";
