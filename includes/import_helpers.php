<?php
// includes/import_helpers.php — helper terpusat untuk fitur Import CSV.
// Dipakai oleh admin/proses_import.php. Tanpa dependensi composer,
// cukup upload file .csv (bisa dibuat dari Excel: Save As -> CSV UTF-8).

// Tipe import yang didukung + definisi kolomnya.
function ej_import_config(string $tipe): ?array
{
    $cfg = [
        'siswa' => [
            'label' => 'Siswa',
            'table' => 'siswa',
            'headers' => ['nisn', 'nama_lengkap', 'kelas', 'jurusan', 'dudi_magang', 'nip_pembimbing', 'password'],
            'required' => ['nisn', 'nama_lengkap', 'kelas', 'jurusan', 'dudi_magang', 'nip_pembimbing', 'password'],
            'desc' => 'nisn, nama_lengkap, kelas, jurusan, dudi_magang, nip_pembimbing, password',
            'sample' => [
                ['0071234567', 'Budi Santoso', 'XII RPL 1', 'Rekayasa Perangkat Lunak', 'PT Maju Jaya', '198001012005011001', 'siswa123'],
                ['0071234568', 'Siti Aminah', 'XII TKJ 2', 'Teknik Komputer dan Jaringan', 'CV Digital Nusantara', '198512312010012002', 'siswa123'],
            ],
        ],
        'guru' => [
            'label' => 'Guru',
            'table' => 'guru',
            'headers' => ['nip', 'nama_guru', 'password'],
            'required' => ['nip', 'nama_guru', 'password'],
            'desc' => 'nip, nama_guru, password',
            'sample' => [
                ['198001012005011001', 'Drs. H. Ahmad Yani', 'guru123'],
                ['198512312010012002', 'Sri Wahyuni, S.Pd', 'guru123'],
            ],
        ],
        'admin' => [
            'label' => 'Admin',
            'table' => 'admin',
            'headers' => ['username', 'password'],
            'required' => ['username', 'password'],
            'desc' => 'username, password (password otomatis di-hash ke kolom kode_unik)',
            'sample' => [
                ['admin1', 'admin123'],
                ['operator_pkl', 'operator123'],
            ],
        ],
    ];
    return $cfg[$tipe] ?? null;
}

/**
 * Baca file CSV menjadi array asosiatif berdasar header baris pertama.
 * - Otomatis buang BOM UTF-8, deteksi delimiter (, ; tab |).
 * - Header dinormalisasi: lowercase, trim, spasi -> underscore.
 * Return: ['rows' => [...], 'delimiter' => ..., 'header' => [...]]
 */
function ej_csv_read(string $tmpPath): array
{
    $raw = @file_get_contents($tmpPath);
    if ($raw === false) return ['rows' => [], 'delimiter' => ',', 'header' => [], 'error' => 'Gagal membaca file upload.'];
    // Buang BOM
    $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
    // Normalisasi baris
    $raw = str_replace(["\r\n", "\r"], "\n", $raw);
    $lines = explode("\n", $raw);
    // Ambil baris pertama non-kosong sebagai header untuk deteksi delimiter
    $firstLine = '';
    foreach ($lines as $ln) {
        if (trim($ln) !== '') {
            $firstLine = $ln;
            break;
        }
    }
    if ($firstLine === '') return ['rows' => [], 'delimiter' => ',', 'header' => [], 'error' => 'File CSV kosong.'];
    $candidates = [',', ';', "\t", '|'];
    $best = ',';
    $bestCount = -1;
    foreach ($candidates as $d) {
        $c = count(str_getcsv($firstLine, $d));
        if ($c > $bestCount) {
            $bestCount = $c;
            $best = $d;
        }
    }
    $rows = [];
    $header = [];
    if (($fh = @fopen($tmpPath, 'r')) === false) {
        return ['rows' => [], 'delimiter' => $best, 'header' => [], 'error' => 'Gagal membuka file upload.'];
    }
    $first = true;
    while (($data = fgetcsv($fh, 0, $best)) !== false) {
        // Lewati baris yang sepenuhnya kosong
        $allEmpty = true;
        foreach ($data as $v) {
            if (trim((string) $v) !== '') {
                $allEmpty = false;
                break;
            }
        }
        if ($allEmpty) continue;
        if ($first) {
            $first = false;
            $header = array_map(function ($h) {
                $h = preg_replace('/^\xEF\xBB\xBF/', '', (string) $h);
                $h = strtolower(trim($h));
                $h = str_replace([' ', '-'], '_', $h);
                return $h;
            }, $data);
            continue;
        }
        $assoc = [];
        foreach ($header as $i => $h) {
            $assoc[$h] = isset($data[$i]) ? trim((string) $data[$i]) : '';
        }
        $assoc['_line'] = count($rows) + 2; // nomor baris di file (header = 1)
        $rows[] = $assoc;
    }
    fclose($fh);
    return ['rows' => $rows, 'delimiter' => $best, 'header' => $header];
}

/**
 * Baca file Excel (.xlsx) secara native menggunakan ZipArchive dan SimpleXML.
 * Mengembalikan struktur format yang identik dengan ej_csv_read.
 */
function ej_xlsx_read(string $tmpPath): array
{
    if (!class_exists('ZipArchive')) {
        return ['rows' => [], 'header' => [], 'error' => 'Ekstensi PHP ZipArchive tidak aktif di server.'];
    }
    $zip = new ZipArchive();
    if ($zip->open($tmpPath) !== true) {
        return ['rows' => [], 'header' => [], 'error' => 'Gagal membuka file Excel (.xlsx). Pastikan file valid.'];
    }

    // 1. Baca shared strings
    $sharedStrings = [];
    $ssXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($ssXml !== false) {
        $xml = @simplexml_load_string($ssXml);
        if ($xml && isset($xml->si)) {
            foreach ($xml->si as $si) {
                if (isset($si->t)) {
                    $sharedStrings[] = (string) $si->t;
                } elseif (isset($si->r)) {
                    $str = '';
                    foreach ($si->r as $r) {
                        if (isset($r->t)) $str .= (string) $r->t;
                    }
                    $sharedStrings[] = $str;
                } else {
                    $sharedStrings[] = '';
                }
            }
        }
    }

    // 2. Baca worksheet pertama (xl/worksheets/sheet1.xml)
    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    if ($sheetXml === false) {
        $zip->close();
        return ['rows' => [], 'header' => [], 'error' => 'Format file Excel tidak valid (sheet1.xml tidak ditemukan).'];
    }
    $zip->close();

    $xml = @simplexml_load_string($sheetXml);
    if (!$xml || !isset($xml->sheetData->row)) {
        return ['rows' => [], 'header' => [], 'error' => 'Sheet Excel kosong atau tidak dapat dibaca.'];
    }

    $rawRows = [];
    foreach ($xml->sheetData->row as $row) {
        $rIndex = (int) $row['r'];
        $rowData = [];
        $maxColLetter = 'A';
        foreach ($row->c as $c) {
            $cellRef = (string) $c['r'];
            preg_match('/^([A-Z]+)(\d+)$/', $cellRef, $m);
            $colLetter = $m[1] ?? 'A';
            $maxColLetter = $colLetter;
            
            $val = '';
            $t = isset($c['t']) ? (string) $c['t'] : '';
            if (isset($c->v)) {
                $v = (string) $c->v;
                if ($t === 's') {
                    $idx = (int) $v;
                    $val = $sharedStrings[$idx] ?? '';
                } else {
                    $val = $v;
                }
            } elseif (isset($c->is->t)) {
                $val = (string) $c->is->t;
            }
            $rowData[$colLetter] = trim($val);
        }
        $rawRows[$rIndex] = $rowData;
    }

    if (empty($rawRows)) {
        return ['rows' => [], 'header' => [], 'error' => 'File Excel kosong.'];
    }

    ksort($rawRows);
    $header = [];
    $rows = [];
    $rowIndex = 0;

    foreach ($rawRows as $rNum => $cols) {
        // Cari kolom A sampai Z / AA dsb secara berurutan
        // Ubah cell ref letters ke array berurutan
        // Tentukan kolom maksimal yang terisi
        $keys = array_keys($cols);
        if (empty($keys)) continue;
        
        // Buat baris array berurutan berdasar index kolom
        // Ubah huruf kolom jadi angka kolom (A=0, B=1, dst)
        $maxColIdx = 0;
        foreach ($keys as $colLtr) {
            $colIdx = 0;
            $len = strlen($colLtr);
            for ($i = 0; $i < $len; $i++) {
                $colIdx = $colIdx * 26 + (ord($colLtr[$i]) - 64);
            }
            if ($colIdx > $maxColIdx) $maxColIdx = $colIdx;
        }

        $rowArray = [];
        for ($i = 1; $i <= $maxColIdx; $i++) {
            // Ubah $i kembali ke huruf Excel (1->A, 2->B, dst)
            $n = $i;
            $ltr = '';
            while ($n > 0) {
                $rem = ($n - 1) % 26;
                $ltr = chr(65 + $rem) . $ltr;
                $n = (int) (($n - 1) / 26);
            }
            $rowArray[] = $cols[$ltr] ?? '';
        }

        if ($rowIndex === 0) {
            // Baris pertama sebagai header
            $header = array_map(function ($h) {
                $h = preg_replace('/^\xEF\xBB\xBF/', '', (string) $h);
                $h = strtolower(trim($h));
                $h = str_replace([' ', '-'], '_', $h);
                return $h;
            }, $rowArray);
        } else {
            // Baris data
            $assoc = [];
            foreach ($header as $i => $h) {
                if ($h === '') continue;
                $assoc[$h] = $rowArray[$i] ?? '';
            }
            $assoc['_line'] = $rNum;
            $rows[] = $assoc;
        }
        $rowIndex++;
    }

    return ['rows' => $rows, 'delimiter' => ',', 'header' => $header];
}

/**
 * Membuat file template Excel (.xlsx) secara native dan menyimpannya ke path tertentu.
 */
function ej_create_xlsx_template(array $headers, array $samples, string $outputPath): bool
{
    if (!class_exists('ZipArchive')) return false;

    // Kumpulkan unique strings untuk sharedStrings.xml
    $sharedStrings = [];
    $stringMap = [];
    $addString = function($str) use (&$sharedStrings, &$stringMap) {
        $str = (string)$str;
        if (!isset($stringMap[$str])) {
            $stringMap[$str] = count($sharedStrings);
            $sharedStrings[] = $str;
        }
        return $stringMap[$str];
    };

    // Masukkan header ke shared strings
    $allRows = array_merge([$headers], $samples);
    $sheetRowsXml = '';

    foreach ($allRows as $rIdx => $row) {
        $rowNum = $rIdx + 1;
        $sheetRowsXml .= '<row r="' . $rowNum . '">';
        foreach ($row as $cIdx => $cellVal) {
            $colLetter = '';
            $n = $cIdx + 1;
            while ($n > 0) {
                $rem = ($n - 1) % 26;
                $colLetter = chr(65 + $rem) . $colLetter;
                $n = (int)(($n - 1) / 26);
            }
            $cellRef = $colLetter . $rowNum;
            $strIdx = $addString($cellVal);
            $sheetRowsXml .= '<c r="' . $cellRef . '" t="s"><v>' . $strIdx . '</v></c>';
        }
        $sheetRowsXml .= '</row>';
    }

    // Buat file zip sementara untuk .xlsx
    $zip = new ZipArchive();
    if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return false;
    }

    // [Content_Types].xml
    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
</Types>');

    // _rels/.rels
    $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

    // xl/_rels/workbook.xml.rels
    $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
</Relationships>');

    // xl/workbook.xml
    $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Template" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>');

    // xl/sharedStrings.xml
    $ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
    $ssXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sharedStrings) . '" uniqueCount="' . count($sharedStrings) . '">';
    foreach ($sharedStrings as $str) {
        $ssXml .= '<si><t>' . htmlspecialchars($str, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</t></si>';
    }
    $ssXml .= '</sst>';
    $zip->addFromString('xl/sharedStrings.xml', $ssXml);

    // xl/worksheets/sheet1.xml
    $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
    $sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
    $sheetXml .= $sheetRowsXml;
    $sheetXml .= '</sheetData></worksheet>';
    $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

    $zip->close();
    return true;
}

// Validasi tanggal YYYY-MM-DD (toleran format DD/MM/YYYY dari Excel Indonesia).
function ej_norm_date(string $val): ?string
{
    $val = trim($val);
    if ($val === '') return null;
    // Sudah YYYY-MM-DD
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
        [$y, $m, $d] = explode('-', $val);
        return checkdate((int) $m, (int) $d, (int) $y) ? $val : null;
    }
    // DD/MM/YYYY atau DD-MM-YYYY
    if (preg_match('/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{4})$/', $val, $mm)) {
        $d = (int) $mm[1];
        $m = (int) $mm[2];
        $y = (int) $mm[3];
        return checkdate($m, $d, $y) ? sprintf('%04d-%02d-%02d', $y, $m, $d) : null;
    }
    // Coba strtotime sebagai fallback
    $ts = strtotime($val);
    return $ts ? date('Y-m-d', $ts) : null;
}
