<?php

session_start();

require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";


// Pastikan user adalah guru
require_login('guru');


// Pastikan request menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: bimbingan.php");
    exit;

}


// Pastikan ID dikirim
if (!isset($_POST['id_monitoring']) || !is_numeric($_POST['id_monitoring'])) {

    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => 'ID monitoring tidak valid.'
    ];

    header("Location: bimbingan.php");
    exit;

}


// Ambil ID monitoring
$id_monitoring = (int) $_POST['id_monitoring'];


// Ambil ID guru yang sedang login
$id_guru = (int) $_SESSION['id_guru'];


// Pastikan ID guru tersedia
if ($id_guru <= 0) {

    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => 'Session guru tidak valid.'
    ];

    header("Location: bimbingan.php");
    exit;

}


// ======================================================
// AMBIL DATA MONITORING
// Hanya monitoring milik guru yang sedang login
// ======================================================

$stmt = mysqli_prepare(
    $conn,
    "SELECT id_monitoring, foto
     FROM monitoring
     WHERE id_monitoring = ?
     AND id_guru = ?
     LIMIT 1"
);

if (!$stmt) {

    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => 'Terjadi kesalahan pada database.'
    ];

    header("Location: bimbingan.php");
    exit;

}


mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $id_monitoring,
    $id_guru
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$monitoring = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


// ======================================================
// DATA TIDAK DITEMUKAN
// ======================================================

if (!$monitoring) {

    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => 'Data monitoring tidak ditemukan atau bukan milik Anda.'
    ];

    header("Location: bimbingan.php");
    exit;

}


// Simpan nama foto sebelum data dihapus
$foto = $monitoring['foto'] ?? '';


// ======================================================
// MULAI TRANSAKSI
// ======================================================

mysqli_begin_transaction($conn);

try {

    // ==================================================
    // HAPUS DATA MONITORING
    // ==================================================

    $delete = mysqli_prepare(
        $conn,
        "DELETE FROM monitoring
         WHERE id_monitoring = ?
         AND id_guru = ?"
    );


    if (!$delete) {

        throw new Exception(
            "Gagal menyiapkan query delete."
        );

    }


    mysqli_stmt_bind_param(
        $delete,
        "ii",
        $id_monitoring,
        $id_guru
    );


    if (!mysqli_stmt_execute($delete)) {

        mysqli_stmt_close($delete);

        throw new Exception(
            "Gagal menghapus data monitoring."
        );

    }


    // Pastikan benar-benar ada data yang dihapus
    if (mysqli_stmt_affected_rows($delete) !== 1) {

        mysqli_stmt_close($delete);

        throw new Exception(
            "Data monitoring tidak berhasil dihapus."
        );

    }


    mysqli_stmt_close($delete);


    // ==================================================
    // COMMIT
    // ==================================================

    mysqli_commit($conn);


    // ==================================================
    // HAPUS FOTO SETELAH DATABASE BERHASIL DIHAPUS
    // ==================================================

    if (!empty($foto)) {

        $foto_path = __DIR__ . "/../uploads/monitoring/" . $foto;

        if (is_file($foto_path)) {

            @unlink($foto_path);

        }

    }


    // ==================================================
    // PESAN BERHASIL
    // ==================================================

    $_SESSION['toast'] = [
        'type' => 'success',
        'message' => 'Data monitoring berhasil dihapus.'
    ];


    header("Location: bimbingan.php");
    exit;


} catch (Throwable $e) {

    // ==================================================
    // ROLLBACK JIKA GAGAL
    // ==================================================

    mysqli_rollback($conn);


    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => 'Data monitoring gagal dihapus.'
    ];


    header("Location: bimbingan.php");
    exit;

}