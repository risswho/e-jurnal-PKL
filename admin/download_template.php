<?php
// admin/download_template.php — unduh template Excel (.xlsx) untuk setiap tipe import.
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/import_helpers.php";
require_login('admin');

$tipe = $_GET['tipe'] ?? '';
$cfg = ej_import_config($tipe);
if (!$cfg) {
    back_with_error('import.php', 'Tipe template tidak dikenal.');
}

$tmpFile = tempnam(sys_get_temp_dir(), 'ej_tmpl_');
if (!ej_create_xlsx_template($cfg['headers'], $cfg['sample'], $tmpFile)) {
    // Fallback ke CSV jika zip/xlsx gagal
    $filename = 'template_import_' . $tipe . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, $cfg['headers']);
    foreach ($cfg['sample'] as $row) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

$filename = 'template_import_' . $tipe . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($tmpFile));
header('Pragma: no-cache');
header('Expires: 0');
readfile($tmpFile);
@unlink($tmpFile);
exit;
