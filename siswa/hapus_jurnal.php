<?php

session_start();

require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('siswa');


$id_siswa = $_SESSION['id_siswa'];

$id_jurnal = $_GET['id'] ?? 0;


// Ambil jurnal
$query = "SELECT *
          FROM jurnal
          WHERE id_jurnal = ?
          AND id_siswa = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $id_jurnal,
    $id_siswa
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$jurnal = mysqli_fetch_assoc($result);


if (!$jurnal) abort("Jurnal tidak ditemukan", "Data jurnal tidak ada atau bukan milik Anda.", "dashboard.php");


// Hapus foto jika ada
if (!empty($jurnal['foto'])) {

    $foto =
        "../uploads/jurnal/"
        . $jurnal['foto'];

    if (file_exists($foto)) {
        unlink($foto);
    }
}


// Hapus database
$query_delete = "DELETE FROM jurnal
                 WHERE id_jurnal = ?
                 AND id_siswa = ?";

$stmt_delete = mysqli_prepare(
    $conn,
    $query_delete
);

mysqli_stmt_bind_param(
    $stmt_delete,
    "ii",
    $id_jurnal,
    $id_siswa
);

mysqli_stmt_execute($stmt_delete);


header(
    "Location: dashboard.php?success=Jurnal berhasil dihapus"
);

exit;