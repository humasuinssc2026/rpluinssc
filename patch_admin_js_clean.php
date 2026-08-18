<?php
$file = 'c:\\Users\\info\\rpl\\js\\admin.js';
$js = file_get_contents($file);

// Remove PEMBAYARAN VALIDATION FUNCTIONS
$js = preg_replace('/\/\/ ======================= PEMBAYARAN VALIDATION FUNCTIONS =======================.*?(\/\/ ======================= AUDIT LOGS FUNCTIONS =======================)/s', '$1', $js);

// Remove HASIL RPL FUNCTIONS
$js = preg_replace('/\/\/ ======================= HASIL RPL FUNCTIONS =======================.*?(\/\/ ======================= CMS FUNCTIONS =======================|async function initAdminCharts)/s', '$1', $js);

// Also remove any stray openHasilModal, loadHasilRpl, saveHasilRpl, cetakSKKonversi
$js = preg_replace('/function openHasilModal\([^)]*\)\s*\{[^\}]*\}/s', '', $js);
$js = preg_replace('/function closeHasilModal\(\)\s*\{[^\}]*\}/s', '', $js);
$js = preg_replace('/function addMkRow\([^)]*\)\s*\{[^\}]*\}/s', '', $js);
$js = preg_replace('/async function loadHasilRpl\([^)]*\)\s*\{.*?\n\}\n/s', '', $js);
$js = preg_replace('/async function saveHasilRpl\([^)]*\)\s*\{.*?\n\}\n/s', '', $js);
$js = preg_replace('/function cetakSKKonversi\([^)]*\)\s*\{.*?\n\}\n/s', '', $js);

file_put_contents($file, $js);
echo "js/admin.js cleaned\n";
