<?php

session_start();

require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('admin');


$id_siswa = (int) ($_POST['id_siswa'] ?? 0);

if ($id_siswa <= 0) abort("ID tidak valid", "ID siswa tidak ditemukan.", "siswa.php");


// Ambil data siswa saat ini
$querySiswa = "SELECT id_siswa, nisn, nama_lengkap
               FROM siswa
               WHERE id_siswa = ?";

$stmtSiswa = mysqli_prepare($conn, $querySiswa);

mysqli_stmt_bind_param(
    $stmtSiswa,
    "i",
    $id_siswa
);

mysqli_stmt_execute($stmtSiswa);

$siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtSiswa));
mysqli_stmt_close($stmtSiswa);

if (!$siswa) abort("Siswa tidak ditemukan", "Data siswa tidak ada.", "siswa.php");


/* =========================
   UPDATE DATA SISWA
========================= */

$nisn = trim($_POST['nisn'] ?? '');
$nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
$kelas = trim($_POST['kelas'] ?? '');
$jurusan = trim($_POST['jurusan'] ?? '');
$dudi_magang = trim($_POST['dudi_magang'] ?? '');
$password = $_POST['password'] ?? ''; // kosong = tidak diubah
$id_guru = (int) ($_POST['id_guru'] ?? 0);


// Validasi
if (
    empty($nisn) ||
    empty($nama_lengkap) ||
    empty($kelas) ||
    empty($jurusan) ||
    empty($dudi_magang) ||
    $id_guru <= 0
) {
    back_with_error("edit_siswa.php?id=" . $id_siswa, "Semua data wajib diisi (termasuk guru pembimbing).");
}

// Cek guru pembimbing ada?
$stmtGuru = mysqli_prepare($conn, "SELECT id_guru FROM guru WHERE id_guru = ?");
mysqli_stmt_bind_param($stmtGuru, "i", $id_guru);
mysqli_stmt_execute($stmtGuru);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGuru))) {
    back_with_error("edit_siswa.php?id=" . $id_siswa, "Guru pembimbing tidak valid.");
}
mysqli_stmt_close($stmtGuru);


// Cek NISN dipakai siswa lain?
$queryCek = "SELECT id_siswa
             FROM siswa
             WHERE nisn = ?
             AND id_siswa != ?";

$stmtCek = mysqli_prepare($conn, $queryCek);

mysqli_stmt_bind_param(
    $stmtCek,
    "si",
    $nisn,
    $id_siswa
);

mysqli_stmt_execute($stmtCek);

$resultCek = mysqli_stmt_get_result($stmtCek);

if (mysqli_num_rows($resultCek) > 0) {
    back_with_error("edit_siswa.php?id=" . $id_siswa, "NISN sudah dipakai siswa lain.");
}


// Susun UPDATE: password hanya disertakan bila diisi
if ($password !== '') {

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $query = "UPDATE siswa
              SET nisn = ?,
                  nama_lengkap = ?,
                  kelas = ?,
                  jurusan = ?,
                  dudi_magang = ?,
                  id_guru = ?,
                  password = ?
              WHERE id_siswa = ?";

    $stmt = mysqli_prepare($conn, $query);

    mysqli_stmt_bind_param(
        $stmt,
        "sssssisi",
        $nisn,
        $nama_lengkap,
        $kelas,
        $jurusan,
        $dudi_magang,
        $id_guru,
        $password_hash,
        $id_siswa
    );

} else {

    $query = "UPDATE siswa
              SET nisn = ?,
                  nama_lengkap = ?,
                  kelas = ?,
                  jurusan = ?,
                  dudi_magang = ?,
                  id_guru = ?
              WHERE id_siswa = ?";

    $stmt = mysqli_prepare($conn, $query);

    mysqli_stmt_bind_param(
        $stmt,
        "sssssii",
        $nisn,
        $nama_lengkap,
        $kelas,
        $jurusan,
        $dudi_magang,
        $id_guru,
        $id_siswa
    );

}


if (mysqli_stmt_execute($stmt)) {

    header("Location: siswa.php?success=" . urlencode("Data siswa berhasil diperbarui."));
    exit;

} else {

    back_with_error("edit_siswa.php?id=" . $id_siswa, "Gagal memperbarui siswa. Coba lagi.");

}
