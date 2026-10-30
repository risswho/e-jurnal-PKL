<?php

session_start();

require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('admin');


$id_guru = (int) ($_POST['id_guru'] ?? 0);

if ($id_guru <= 0) abort("ID tidak valid", "ID guru tidak ditemukan.", "guru.php");


// Ambil data guru saat ini
$queryGuru = "SELECT id_guru, nip, nama_guru
              FROM guru
              WHERE id_guru = ?";

$stmtGuru = mysqli_prepare($conn, $queryGuru);

mysqli_stmt_bind_param(
    $stmtGuru,
    "i",
    $id_guru
);

mysqli_stmt_execute($stmtGuru);

$guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGuru));
mysqli_stmt_close($stmtGuru);

if (!$guru) abort("Guru tidak ditemukan", "Data guru tidak ada.", "guru.php");


/* =========================
   UPDATE DATA GURU
========================= */

$nip = trim($_POST['nip'] ?? '');
$nama_guru = trim($_POST['nama_guru'] ?? '');
$password = $_POST['password'] ?? ''; // kosong = tidak diubah


// Validasi
if (
    empty($nip) ||
    empty($nama_guru)
) {
    back_with_error("edit_guru.php?id=" . $id_guru, "Semua data wajib diisi.");
}


// Cek NIP dipakai guru lain?
$queryCek = "SELECT id_guru
             FROM guru
             WHERE nip = ?
             AND id_guru != ?";

$stmtCek = mysqli_prepare($conn, $queryCek);

mysqli_stmt_bind_param(
    $stmtCek,
    "si",
    $nip,
    $id_guru
);

mysqli_stmt_execute($stmtCek);

$resultCek = mysqli_stmt_get_result($stmtCek);

if (mysqli_num_rows($resultCek) > 0) {
    back_with_error("edit_guru.php?id=" . $id_guru, "NIP sudah dipakai guru lain.");
}


// Password hanya disertakan bila diisi
if ($password !== '') {

    $password_hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $query = "UPDATE guru
              SET nip = ?,
                  nama_guru = ?,
                  password = ?
              WHERE id_guru = ?";

    $stmt = mysqli_prepare($conn, $query);

    mysqli_stmt_bind_param(
        $stmt,
        "sssi",
        $nip,
        $nama_guru,
        $password_hash,
        $id_guru
    );

} else {

    $query = "UPDATE guru
              SET nip = ?,
                  nama_guru = ?
              WHERE id_guru = ?";

    $stmt = mysqli_prepare($conn, $query);

    mysqli_stmt_bind_param(
        $stmt,
        "ssi",
        $nip,
        $nama_guru,
        $id_guru
    );

}


if (mysqli_stmt_execute($stmt)) {

    header("Location: guru.php?success=" . urlencode("Data guru berhasil diperbarui."));
    exit;

} else {

    back_with_error("edit_guru.php?id=" . $id_guru, "Gagal memperbarui guru. Coba lagi.");

}
