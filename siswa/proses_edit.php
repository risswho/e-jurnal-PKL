<?php

session_start();

require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('siswa');


$id_siswa = $_SESSION['id_siswa'];

$id_jurnal = $_POST['id_jurnal'] ?? 0;
$tanggal_kegiatan = $_POST['tanggal_kegiatan'] ?? '';
$nama_dudi = $_POST['nama_dudi'] ?? '';
$pembimbing = $_POST['pembimbing'] ?? '';
$kegiatan_magang = $_POST['kegiatan_magang'] ?? '';
$keterangan = $_POST['keterangan'] ?? '';


// Validasi
if (
    empty($id_jurnal) ||
    empty($tanggal_kegiatan) ||
    empty($nama_dudi) ||
    empty($pembimbing) ||
    empty($kegiatan_magang)
) {
    header("Location: edit_jurnal.php?id=" . (int)$id_jurnal . "&error=" . urlencode("Data jurnal belum lengkap."));
    exit;
}


// Ambil data jurnal lama
$query = "SELECT *
          FROM jurnal
          WHERE id_jurnal = ?
          AND id_siswa = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $id_jurnal, $id_siswa);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$jurnal_lama = mysqli_fetch_assoc($result);


if (!$jurnal_lama) abort("Jurnal tidak ditemukan", "Data jurnal tidak ada atau bukan milik Anda.", "dashboard.php");


$nama_foto = $jurnal_lama['foto'];


// ============================
// JIKA ADA FOTO BARU
// ============================

if (
    isset($_FILES['foto']) &&
    $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
) {

    $foto = $_FILES['foto'];


    if ($foto['error'] !== UPLOAD_ERR_OK) {
        header("Location: edit_jurnal.php?id=" . (int)$id_jurnal . "&error=" . urlencode("Foto gagal diupload."));
        exit;
    }


    // Maksimal 20 MB
    if ($foto['size'] > 20 * 1024 * 1024) {
        header("Location: edit_jurnal.php?id=" . (int)$id_jurnal . "&error=" . urlencode("Ukuran foto maksimal 20 MB."));
        exit;
    }


    // Ekstensi
    $ekstensi_diperbolehkan = [
        'jpg',
        'jpeg',
        'png'
    ];

    $ekstensi = strtolower(
        pathinfo(
            $foto['name'],
            PATHINFO_EXTENSION
        )
    );


    if (!in_array($ekstensi, $ekstensi_diperbolehkan)) {
        header("Location: edit_jurnal.php?id=" . (int)$id_jurnal . "&error=" . urlencode("Format foto harus JPG, JPEG, atau PNG."));
        exit;
    }


    // Nama baru
    $nama_foto_baru =
        uniqid('jurnal_', true)
        . '.'
        . $ekstensi;


    $tujuan =
        "../uploads/jurnal/"
        . $nama_foto_baru;


    if (!move_uploaded_file(
        $foto['tmp_name'],
        $tujuan
    )) {

        header("Location: edit_jurnal.php?id=" . (int)$id_jurnal . "&error=" . urlencode("Foto gagal disimpan."));
        exit;

    }


    // Hapus foto lama
    if (!empty($jurnal_lama['foto'])) {

        $foto_lama =
            "../uploads/jurnal/"
            . $jurnal_lama['foto'];

        if (file_exists($foto_lama)) {
            unlink($foto_lama);
        }

    }


    $nama_foto = $nama_foto_baru;
}


// ============================
// UPDATE DATABASE
// ============================

$query_update = "UPDATE jurnal
                 SET
                    tanggal_kegiatan = ?,
                    nama_dudi = ?,
                    pembimbing = ?,
                    kegiatan_magang = ?,
                    keterangan = ?,
                    foto = ?
                 WHERE id_jurnal = ?
                 AND id_siswa = ?";


$stmt_update = mysqli_prepare(
    $conn,
    $query_update
);

mysqli_stmt_bind_param(
    $stmt_update,
    "ssssssii",
    $tanggal_kegiatan,
    $nama_dudi,
    $pembimbing,
    $kegiatan_magang,
    $keterangan,
    $nama_foto,
    $id_jurnal,
    $id_siswa
);


if (mysqli_stmt_execute($stmt_update)) {

    header(
        "Location: dashboard.php?success=Jurnal berhasil diperbarui"
    );

    exit;

} else {

    header(
        "Location: edit_jurnal.php?id=" . (int)$id_jurnal . "&error=" . urlencode("Jurnal gagal diperbarui. Coba lagi.")
    );
    exit;
}