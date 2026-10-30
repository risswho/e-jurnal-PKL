<?php

session_start();

require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('admin');

$id_siswa = $_GET['id'] ?? '';

if (empty($id_siswa)) abort("ID tidak valid", "ID siswa tidak ditemukan.", "siswa.php");

$id_siswa = (int) $id_siswa;


// Ambil foto jurnal siswa
$queryFoto = "SELECT foto
              FROM jurnal
              WHERE id_siswa = ?";

$stmtFoto = mysqli_prepare($conn, $queryFoto);

mysqli_stmt_bind_param(
    $stmtFoto,
    "i",
    $id_siswa
);

mysqli_stmt_execute($stmtFoto);

$resultFoto = mysqli_stmt_get_result($stmtFoto);

while ($data = mysqli_fetch_assoc($resultFoto)) {

    if (!empty($data['foto'])) {

        $file = "../uploads/jurnal/" . $data['foto'];

        if (file_exists($file)) {
            unlink($file);
        }

    }

}


// Ambil foto monitoring
$queryFotomonitoring = "SELECT foto
                       FROM monitoring
                       WHERE id_siswa = ?";

$stmtFotomonitoring = mysqli_prepare(
    $conn,
    $queryFotomonitoring
);

mysqli_stmt_bind_param(
    $stmtFotomonitoring,
    "i",
    $id_siswa
);

mysqli_stmt_execute($stmtFotomonitoring);

$resultFotomonitoring = mysqli_stmt_get_result(
    $stmtFotomonitoring
);

while ($data = mysqli_fetch_assoc($resultFotomonitoring)) {

    if (!empty($data['foto'])) {

        $file = "../uploads/monitoring/" . $data['foto'];

        if (file_exists($file)) {
            unlink($file);
        }

    }

}


// Hapus siswa
$query = "DELETE FROM siswa
          WHERE id_siswa = ?";

$stmt = mysqli_prepare($conn, $query);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_siswa
);

if (mysqli_stmt_execute($stmt)) {

    header("Location: siswa.php?deleted=1");
    exit;

} else {

    abort("Gagal menghapus", "Gagal menghapus siswa. Coba lagi.", "siswa.php");

}
