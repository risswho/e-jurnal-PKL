<?php

session_start();

require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('admin');


$nisn = trim($_POST['nisn'] ?? '');
$nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
$kelas = trim($_POST['kelas'] ?? '');
$jurusan = trim($_POST['jurusan'] ?? '');
$dudi_magang = trim($_POST['dudi_magang'] ?? '');
$password = $_POST['password'] ?? '';
$id_guru = (int) ($_POST['id_guru'] ?? 0);


// Validasi
if (
    empty($nisn) ||
    empty($nama_lengkap) ||
    empty($kelas) ||
    empty($jurusan) ||
    empty($dudi_magang) ||
    empty($password) ||
    $id_guru <= 0
) {
    header("Location: tambah_siswa.php?error=" . urlencode("Semua data wajib diisi (termasuk guru pembimbing)."));
    exit;
}

// Cek guru pembimbing ada?
$stmtGuru = mysqli_prepare($conn, "SELECT id_guru FROM guru WHERE id_guru = ?");
mysqli_stmt_bind_param($stmtGuru, "i", $id_guru);
mysqli_stmt_execute($stmtGuru);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGuru))) {
    header("Location: tambah_siswa.php?error=" . urlencode("Guru pembimbing tidak valid."));
    exit;
}
mysqli_stmt_close($stmtGuru);


// Cek NISN
$queryCek = "SELECT id_siswa
             FROM siswa
             WHERE nisn = ?";

$stmtCek = mysqli_prepare($conn, $queryCek);

mysqli_stmt_bind_param(
    $stmtCek,
    "s",
    $nisn
);

mysqli_stmt_execute($stmtCek);

$resultCek = mysqli_stmt_get_result($stmtCek);

if (mysqli_num_rows($resultCek) > 0) {
    header("Location: tambah_siswa.php?error=" . urlencode("NISN sudah terdaftar."));
    exit;
}


// Hash password
$password_hash = password_hash(
    $password,
    PASSWORD_DEFAULT
);


// Simpan siswa
$query = "INSERT INTO siswa
          (
              nisn,
              nama_lengkap,
              kelas,
              jurusan,
              dudi_magang,
              id_guru,
              password
          )
          VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);

mysqli_stmt_bind_param(
    $stmt,
    "sssssis",
    $nisn,
    $nama_lengkap,
    $kelas,
    $jurusan,
    $dudi_magang,
    $id_guru,
    $password_hash
);


if (mysqli_stmt_execute($stmt)) {

    header("Location: siswa.php?success=" . urlencode("Data siswa berhasil ditambahkan."));
    exit;

} else {

    header("Location: tambah_siswa.php?error=" . urlencode("Gagal menambahkan siswa. Coba lagi."));
    exit;

}
