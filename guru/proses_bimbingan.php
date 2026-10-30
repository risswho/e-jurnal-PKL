<?php

session_start();

require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('guru');

$id_guru = (int) ($_SESSION['id_guru'] ?? 0);
if ($id_guru <= 0) { header("Location: ../auth/login.php"); exit; }

$id_siswa = (int) ($_POST['id_siswa'] ?? 0);
$tanggal_monitoring = $_POST['tanggal_monitoring'] ?? '';
$dudi = trim($_POST['dudi'] ?? '');
$nama_dudi = trim($_POST['nama_dudi'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');

// === TAMPILKAN NAMA DUDI Dari siswa yang dipilih (strict: hanya bimbingan sendiri) ===
$siswa_stmt0 = mysqli_prepare($conn, "SELECT dudi_magang FROM siswa WHERE id_siswa = ? AND id_guru = ? LIMIT 1");
mysqli_stmt_bind_param($siswa_stmt0, "ii", $id_siswa, $id_guru);
mysqli_stmt_execute($siswa_stmt0);
$siswa_row = mysqli_fetch_assoc(mysqli_stmt_get_result($siswa_stmt0));
mysqli_stmt_close($siswa_stmt0);
$dudi_from_siswa = $siswa_row['dudi_magang'] ?? '';
// Gunakan dudi dari form jika ada, kalau tidak pakai dari siswa
$final_dudi = (!empty($dudi)) ? $dudi : $dudi_from_siswa;


// ==============================
// VALIDASI DATA
// ==============================

if (
    empty($id_siswa) ||
    empty($tanggal_monitoring) ||
    empty($final_dudi) ||
    empty($keterangan)
) {
    header("Location: tambah_monitoring.php?error=" . urlencode("Semua data wajib diisi."));
    exit;
}


// ==============================
// CEK SISWA
// STRICT: hanya siswa monitoring guru ini
// ==============================

$query = "SELECT id_siswa
          FROM siswa
          WHERE id_siswa = ?
          AND id_guru = ?";

$stmt = mysqli_prepare($conn, $query);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $id_siswa,
    $id_guru
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) abort("Siswa tidak valid", "Siswa bukan monitoring Anda.", "bimbingan.php");


// ==============================
// UPLOAD FOTO
// ==============================

$nama_foto = null;

if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {

    if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        header("Location: tambah_monitoring.php?error=" . urlencode("Upload foto gagal."));
        exit;
    }

    // Maksimal 20 MB
    if ($_FILES['foto']['size'] > 20 * 1024 * 1024) {
        header("Location: tambah_monitoring.php?error=" . urlencode("Ukuran foto maksimal 20 MB."));
        exit;
    }

    $nama_file_asli = $_FILES['foto']['name'];

    $ekstensi = strtolower(
        pathinfo($nama_file_asli, PATHINFO_EXTENSION)
    );

    $ekstensi_diizinkan = ['jpg', 'jpeg', 'png'];

    if (!in_array($ekstensi, $ekstensi_diizinkan)) {
        header("Location: tambah_monitoring.php?error=" . urlencode("Format foto harus JPG, JPEG, atau PNG."));
        exit;
    }

    $nama_foto = uniqid('monitoring_', true) . '.' . $ekstensi;

    $folder_upload = "../uploads/monitoring/";

    if (!is_dir($folder_upload)) {
        mkdir($folder_upload, 0777, true);
    }

    $tujuan = $folder_upload . $nama_foto;

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan)) {
        header("Location: tambah_monitoring.php?error=" . urlencode("Foto gagal disimpan."));
        exit;
    }
}


// ==============================
// SIMPAN KE DATABASE
// ==============================

$query = "INSERT INTO monitoring
          (
              id_guru,
              id_siswa,
              tanggal_monitoring,
              nama_dudi,
              keterangan,
              foto
          )
          VALUES (?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);

mysqli_stmt_bind_param(
    $stmt,
    "iissss",
    $id_guru,
    $id_siswa,
    $tanggal_monitoring,
    $final_dudi,
    $keterangan,
    $nama_foto
);

if (mysqli_stmt_execute($stmt)) {

    header("Location: riwayat_bimbingan.php?success=" . urlencode("Dokumentasi monitoring berhasil disimpan."));
    exit;

} else {

    // Kalau database gagal, hapus foto yang sudah diupload
    if ($nama_foto && file_exists("../uploads/monitoring/" . $nama_foto)) {
        unlink("../uploads/monitoring/" . $nama_foto);
    }

    abort("Gagal menyimpan", "Data gagal disimpan. Silakan coba lagi.", "tambah_monitoring.php");
}
