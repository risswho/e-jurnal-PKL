<?php
// admin/proses_import.php — proses upload CSV untuk 3 tipe:
// siswa, guru, admin.
// Cara pakai: form POST enctype=multipart/form-data ke file ini dengan
// field `tipe` + file `file_csv`. Hasil diringkas ke session lalu
// redirect kembali ke import.php?tipe=... (atau ke halaman kelola terkait).
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/import_helpers.php";
require_login('admin');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: import.php');
    exit;
}

$tipe = $_POST['tipe'] ?? '';
$cfg = ej_import_config($tipe);
if (!$cfg) {
    back_with_error('import.php', 'Tipe import tidak dikenal.');
}
$redirect = $_POST['redirect'] ?? ('import.php?tipe=' . urlencode($tipe));

// --- Validasi file ---
if (!isset($_FILES['file_csv']) || ($_FILES['file_csv']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    back_with_error($redirect, 'Pilih file terlebih dahulu.');
}
$f = $_FILES['file_csv'];
if ($f['size'] <= 0 || $f['size'] > 5 * 1024 * 1024) {
    back_with_error($redirect, 'Ukuran file maksimal 5 MB.');
}
$ext = strtolower(pathinfo($f['name'] ?? '', PATHINFO_EXTENSION));
if (!in_array($ext, ['csv', 'xlsx', 'xls'], true)) {
    back_with_error($redirect, 'Format file harus .xlsx, .xls, atau .csv.');
}

if ($ext === 'xlsx') {
    $parsed = ej_xlsx_read($f['tmp_name']);
} else {
    $parsed = ej_csv_read($f['tmp_name']);
}
if (!empty($parsed['error'])) {
    back_with_error($redirect, $parsed['error']);
}
$rows = $parsed['rows'];
$header = $parsed['header'];
if (empty($rows)) {
    back_with_error($redirect, 'File kosong atau hanya berisi header.');
}
// Cek kolom wajib ada di header (normalisasi spasi/underscore agar lebih toleran)
$headerNormalized = array_map(function($h) {
    return strtolower(trim(str_replace([' ', '-'], '_', $h)));
}, $header);

$missing = [];
foreach ($cfg['headers'] as $h) {
    $hNorm = strtolower(trim(str_replace([' ', '-'], '_', $h)));
    if (!in_array($hNorm, $headerNormalized, true)) {
        $missing[] = $h;
    }
}
if ($missing) {
    back_with_error($redirect, 'Header file tidak sesuai template. Kolom kurang: ' . implode(', ', $missing) . '. Unduh template dulu.');
}
if (count($rows) > 2000) {
    back_with_error($redirect, 'Maksimal 2000 baris per import. Bagi file menjadi beberapa bagian.');
}

$ok = 0;
$skip = 0;
$errors = []; // ['baris' => N, 'pesan' => ...]

foreach ($rows as $r) {
    $line = $r['_line'] ?? '?';
    $err = function ($msg) use (&$errors, &$skip, $line) {
        $skip++;
        if (count($errors) < 50) $errors[] = ['baris' => $line, 'pesan' => $msg];
    };

    if ($tipe === 'siswa') {
        $nisn = trim($r['nisn'] ?? '');
        $nama = trim($r['nama_lengkap'] ?? '');
        $kelas = trim($r['kelas'] ?? '');
        $jurusan = trim($r['jurusan'] ?? '');
        $dudi = trim($r['dudi_magang'] ?? '');
        $nipBimbing = trim($r['nip_pembimbing'] ?? '');
        $pass = $r['password'] ?? '';
        if ($nisn === '' || $nama === '' || $kelas === '' || $jurusan === '' || $dudi === '' || $nipBimbing === '' || $pass === '') {
            $err('Kolom wajib kosong (nisn/nama_lengkap/kelas/jurusan/dudi_magang/nip_pembimbing/password).');
            continue;
        }
        // Duplikat NISN?
        $cek = mysqli_prepare($conn, "SELECT id_siswa FROM siswa WHERE nisn = ?");
        mysqli_stmt_bind_param($cek, "s", $nisn);
        mysqli_stmt_execute($cek);
        $ada = mysqli_fetch_assoc(mysqli_stmt_get_result($cek));
        mysqli_stmt_close($cek);
        if ($ada) {
            $err("NISN $nisn sudah terdaftar (dilewati).");
            continue;
        }
        // Resolve NIP pembimbing -> id_guru (wajib valid)
        $cekG = mysqli_prepare($conn, "SELECT id_guru FROM guru WHERE nip = ?");
        mysqli_stmt_bind_param($cekG, "s", $nipBimbing);
        mysqli_stmt_execute($cekG);
        $rowG = mysqli_fetch_assoc(mysqli_stmt_get_result($cekG));
        mysqli_stmt_close($cekG);
        if (!$rowG) {
            $err("NIP pembimbing $nipBimbing tidak ditemukan (tambah dulu di Kelola Guru).");
            continue;
        }
        $idGuruCsv = (int) $rowG['id_guru'];
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $st = mysqli_prepare($conn, "INSERT INTO siswa (nisn, nama_lengkap, kelas, jurusan, dudi_magang, id_guru, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($st, "sssssis", $nisn, $nama, $kelas, $jurusan, $dudi, $idGuruCsv, $hash);
        if (mysqli_stmt_execute($st)) $ok++;
        else $err("Gagal simpan NISN $nisn: " . mysqli_error($conn));
        mysqli_stmt_close($st);

    } elseif ($tipe === 'guru') {
        $nip = trim($r['nip'] ?? '');
        $namaG = trim($r['nama_guru'] ?? '');
        $pass = $r['password'] ?? '';
        if ($nip === '' || $namaG === '' || $pass === '') {
            $err('Kolom wajib kosong (nip/nama_guru/password).');
            continue;
        }
        $cek = mysqli_prepare($conn, "SELECT id_guru FROM guru WHERE nip = ?");
        mysqli_stmt_bind_param($cek, "s", $nip);
        mysqli_stmt_execute($cek);
        $ada = mysqli_fetch_assoc(mysqli_stmt_get_result($cek));
        mysqli_stmt_close($cek);
        if ($ada) {
            $err("NIP $nip sudah terdaftar (dilewati).");
            continue;
        }
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $st = mysqli_prepare($conn, "INSERT INTO guru (nip, nama_guru, password) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($st, "sss", $nip, $namaG, $hash);
        if (mysqli_stmt_execute($st)) $ok++;
        else $err("Gagal simpan NIP $nip: " . mysqli_error($conn));
        mysqli_stmt_close($st);

    } elseif ($tipe === 'admin') {
        $user = trim($r['username'] ?? '');
        $pass = $r['password'] ?? '';
        if ($user === '' || $pass === '') {
            $err('Kolom wajib kosong (username/password).');
            continue;
        }
        $cek = mysqli_prepare($conn, "SELECT id_admin FROM admin WHERE username = ?");
        mysqli_stmt_bind_param($cek, "s", $user);
        mysqli_stmt_execute($cek);
        $ada = mysqli_fetch_assoc(mysqli_stmt_get_result($cek));
        mysqli_stmt_close($cek);
        if ($ada) {
            $err("Username $user sudah terdaftar (dilewati).");
            continue;
        }
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $st = mysqli_prepare($conn, "INSERT INTO admin (username, kode_unik) VALUES (?, ?)");
        mysqli_stmt_bind_param($st, "ss", $user, $hash);
        if (mysqli_stmt_execute($st)) $ok++;
        else $err("Gagal simpan username $user: " . mysqli_error($conn));
        mysqli_stmt_close($st);
    }
}

// Simpan ringkasan ke session untuk ditampilkan di halaman tujuan
$_SESSION['import_result'] = [
    'tipe' => $tipe,
    'label' => $cfg['label'],
    'ok' => $ok,
    'skip' => $skip,
    'total' => count($rows),
    'errors' => $errors,
    'at' => date('Y-m-d H:i:s'),
];

if ($ok > 0 && $skip === 0) {
    $msg = "Import {$cfg['label']} berhasil: $ok data ditambahkan.";
    header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'success=' . urlencode($msg));
} elseif ($ok > 0) {
    $msg = "Import {$cfg['label']}: $ok berhasil, $skip dilewati. Lihat rincian di bawah.";
    header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'success=' . urlencode($msg));
} else {
    $msg = "Import {$cfg['label']} gagal: tidak ada data ditambahkan, $skip baris bermasalah. Lihat rincian di bawah.";
    header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'error=' . urlencode($msg));
}
exit;
