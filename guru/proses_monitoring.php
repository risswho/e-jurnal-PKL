<?php
session_start();

require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('guru');

$id_guru = (int) ($_SESSION['id_guru'] ?? 0);
if ($id_guru <= 0) { header("Location: ../auth/login.php"); exit; }

$dudi = trim($_POST['dudi'] ?? '');
$tanggal_monitoring = $_POST['tanggal_monitoring'] ?? '';
$keterangan = trim($_POST['keterangan'] ?? '');

// === VALIDASI DUDI ADA UNTUK GURU INI (strict: hanya bimbingan sendiri) ===
$dudi_stmt = mysqli_prepare($conn, "SELECT id_siswa FROM siswa WHERE dudi_magang = ? AND id_guru = ? LIMIT 1");
mysqli_stmt_bind_param($dudi_stmt, "si", $dudi, $id_guru);
mysqli_stmt_execute($dudi_stmt);
$dudi_check = mysqli_stmt_get_result($dudi_stmt);
if (mysqli_num_rows($dudi_check) === 0) {
    header("Location: tambah_monitoring.php?error=" . urlencode("DUDI tidak ditemukan di akun Anda."));
    exit;
}
mysqli_stmt_close($dudi_stmt);

// === AMBIL SEMUA SISWA di DUDI INI (hanya bimbingan sendiri) ===
$siswa_stmt = mysqli_prepare($conn, "SELECT id_siswa, nama_lengkap, nisn FROM siswa WHERE dudi_magang = ? AND id_guru = ? ORDER BY nama_lengkap ASC");
mysqli_stmt_bind_param($siswa_stmt, "si", $dudi, $id_guru);
mysqli_stmt_execute($siswa_stmt);
$siswa_list = mysqli_stmt_get_result($siswa_stmt);
$siswa_rows = mysqli_fetch_all($siswa_list, MYSQLI_ASSOC);
mysqli_stmt_close($siswa_stmt);

// === UPLOAD FOTO ===
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

// === MASUKAN KE DATABASE: 1 baris per siswa di DUDI ===
// Mulai transaksi agar semua insert atau tidak ada satupun
$conn->begin_transaction;

try {
    foreach ($siswa_rows as $siswa) {
        $id_siswa = $siswa['id_siswa'];

        $query = "INSERT INTO monitoring
                  (id_guru, id_siswa, tanggal_monitoring, nama_dudi, keterangan, foto)
                  VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $query);

        mysqli_stmt_bind_param(
            $stmt,
            "iissss",
            $id_guru,
            $id_siswa,
            $tanggal_monitoring,
            $dudi,
            $keterangan,
            $nama_foto
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal insert siswa " . $id_siswa);
        }
    }

    $conn->commit;

    header("Location: riwayat_bimbingan.php?success=" . urlencode("Dokumentasi monitoring berhasil disimpan untuk " . count($siswa_rows) . " siswa."));
    exit;

} catch (Exception $e) {
    $conn->rollback;

    // Hapus foto jika insert gagal
    if ($nama_foto && file_exists("../uploads/monitoring/" . $nama_foto)) {
        unlink("../uploads/monitoring/" . $nama_foto);
    }

    abort("Gagal menyimpan", "Data gagal disimpan. Silakan coba lagi.", "tambah_monitoring.php");
}