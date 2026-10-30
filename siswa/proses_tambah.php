<?php

session_start();

require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('siswa');


$id_siswa = $_SESSION['id_siswa'];

// Tanggal kegiatan diisi otomatis dari waktu server saat penginputan agar siswa tidak bisa memanipulasi tanggal
$tanggal_kegiatan = date('Y-m-d');
$nama_dudi = $_POST['nama_dudi'] ?? '';
$pembimbing = $_POST['pembimbing'] ?? '';
$kegiatan_magang = $_POST['kegiatan_magang'] ?? '';
$keterangan = $_POST['keterangan'] ?? '';


// Validasi input
if (
    empty($nama_dudi) ||
    empty($pembimbing) ||
    empty($kegiatan_magang)
) {
    header("Location: tambah_jurnal.php?error=" . urlencode("Data jurnal wajib diisi."));
    exit;
}


// ============================
// PROSES UPLOAD FOTO
// ============================

$nama_foto = null;

if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {

    $foto = $_FILES['foto'];

    // Cek error upload
    if ($foto['error'] !== UPLOAD_ERR_OK) {
        header("Location: tambah_jurnal.php?error=" . urlencode("Foto gagal diupload."));
        exit;
    }

    // Batas ukuran 20 MB
    if ($foto['size'] > 20 * 1024 * 1024) {
        header("Location: tambah_jurnal.php?error=" . urlencode("Ukuran foto maksimal 20 MB."));
        exit;
    }

    // Ekstensi yang diperbolehkan
    $ekstensi_diperbolehkan = ['jpg', 'jpeg', 'png'];

    $nama_asli = $foto['name'];
    $ekstensi = strtolower(
        pathinfo($nama_asli, PATHINFO_EXTENSION)
    );

    if (!in_array($ekstensi, $ekstensi_diperbolehkan)) {
        header("Location: tambah_jurnal.php?error=" . urlencode("Format foto harus JPG, JPEG, atau PNG."));
        exit;
    }

    // Buat nama file unik
    $nama_foto = uniqid('jurnal_', true) . '.' . $ekstensi;

    $folder_upload = "../uploads/jurnal/";

    if (!is_dir($folder_upload)) {
        mkdir($folder_upload, 0777, true);
    }

    $tujuan = $folder_upload . $nama_foto;

    if (!move_uploaded_file($foto['tmp_name'], $tujuan)) {
        header("Location: tambah_jurnal.php?error=" . urlencode("Foto gagal disimpan."));
        exit;
    }
}


// ============================
// SIMPAN KE DATABASE
// ============================

$query = "INSERT INTO jurnal
          (
              id_siswa,
              tanggal_kegiatan,
              nama_dudi,
              pembimbing,
              kegiatan_magang,
              keterangan,
              foto
          )
          VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);

mysqli_stmt_bind_param(
    $stmt,
    "issssss",
    $id_siswa,
    $tanggal_kegiatan,
    $nama_dudi,
    $pembimbing,
    $kegiatan_magang,
    $keterangan,
    $nama_foto
);


if (mysqli_stmt_execute($stmt)) {

    header("Location: dashboard.php?success=Jurnal berhasil disimpan");
    exit;

} else {

    // Jika database gagal dan foto sudah terupload,
    // hapus kembali fotonya
    if ($nama_foto !== null) {
        $file = "../uploads/jurnal/" . $nama_foto;

        if (file_exists($file)) {
            unlink($file);
        }
    }

    abort("Gagal menyimpan", "Jurnal gagal disimpan. Silakan coba lagi.", "tambah_jurnal.php");
}