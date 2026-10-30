<?php
session_start();

require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";

require_login('guru');

$id_guru = (int)($_SESSION['id_guru'] ?? 0);
$id_monitoring = (int)($_POST['id_monitoring'] ?? 0);
$id_siswa = (int)($_POST['id_siswa'] ?? 0);

$dudi = trim($_POST['dudi'] ?? '');
$tanggal = trim($_POST['tanggal_monitoring'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');

if ($id_guru <= 0 || $id_monitoring <= 0 || $id_siswa <= 0) {
    header("Location: bimbingan.php?error=" . urlencode("Data monitoring tidak valid."));
    exit;
}

if ($dudi === '' || $tanggal === '' || $keterangan === '') {
    header("Location: edit_monitoring.php?id=$id_monitoring&error=" . urlencode("Semua field wajib diisi."));
    exit;
}

/*
 * Validasi tanggal benar-benar berformat YYYY-MM-DD.
 */
$date_obj = DateTime::createFromFormat('Y-m-d', $tanggal);
if (!$date_obj || $date_obj->format('Y-m-d') !== $tanggal) {
    header("Location: edit_monitoring.php?id=$id_monitoring&error=" . urlencode("Format tanggal tidak valid."));
    exit;
}

/*
 * Ambil data monitoring sekaligus memastikan:
 * - monitoring tersebut milik guru yang sedang login
 * - monitoring tersebut memang untuk siswa yang dikirim
 */
$get_stmt = mysqli_prepare($conn, "
    SELECT
        b.id_monitoring,
        b.id_siswa,
        b.id_guru,
        b.foto,
        s.dudi_magang
    FROM monitoring b
    INNER JOIN siswa s ON b.id_siswa = s.id_siswa
    WHERE b.id_monitoring = ?
      AND b.id_guru = ?
      AND b.id_siswa = ?
    LIMIT 1
");

if (!$get_stmt) {
    header("Location: edit_monitoring.php?id=$id_monitoring&error=" . urlencode("Gagal memeriksa data monitoring."));
    exit;
}

mysqli_stmt_bind_param($get_stmt, "iii", $id_monitoring, $id_guru, $id_siswa);
mysqli_stmt_execute($get_stmt);

$get_result = mysqli_stmt_get_result($get_stmt);
$lama = mysqli_fetch_assoc($get_result);

mysqli_stmt_close($get_stmt);

if (!$lama) {
    header("Location: bimbingan.php?error=" . urlencode("Monitoring tidak ditemukan atau bukan milik Anda."));
    exit;
}

/*
 * DUDI harus berasal dari siswa yang dibimbing guru ini.
 * Kita tidak memakai id_guru dari POST karena bisa dimanipulasi.
 */
$dudi_stmt = mysqli_prepare($conn, "
    SELECT id_siswa
    FROM siswa
    WHERE id_siswa = ?
      AND id_guru = ?
      AND dudi_magang = ?
    LIMIT 1
");

if (!$dudi_stmt) {
    header("Location: edit_monitoring.php?id=$id_monitoring&error=" . urlencode("Gagal memvalidasi DUDI."));
    exit;
}

mysqli_stmt_bind_param($dudi_stmt, "iis", $id_siswa, $id_guru, $dudi);
mysqli_stmt_execute($dudi_stmt);

$dudi_result = mysqli_stmt_get_result($dudi_stmt);
$dudi_valid = mysqli_num_rows($dudi_result) > 0;

mysqli_stmt_close($dudi_stmt);

if (!$dudi_valid) {
    header("Location: edit_monitoring.php?id=$id_monitoring&error=" . urlencode("DUDI tidak sesuai dengan siswa yang dipilih."));
    exit;
}

/*
 * === UPLOAD FOTO BARU ===
 */
$foto_baru = null;
$foto_lama = $lama['foto'] ?? null;
$path_foto_baru = null;

if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {

    if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        header("Location: edit_monitoring.php?id=$id_monitoring&error=" . urlencode("Upload foto gagal."));
        exit;
    }

    // Maksimal 20 MB
    if ($_FILES['foto']['size'] > 20 * 1024 * 1024) {
        header("Location: edit_monitoring.php?id=$id_monitoring&error=" . urlencode("Ukuran foto maksimal 20 MB."));
        exit;
    }

    $tmp_file = $_FILES['foto']['tmp_name'];
    $nama_file_asli = $_FILES['foto']['name'];
    $ekstensi = strtolower(pathinfo($nama_file_asli, PATHINFO_EXTENSION));

    $ekstensi_diizinkan = ['jpg', 'jpeg', 'png'];

    if (!in_array($ekstensi, $ekstensi_diizinkan, true)) {
        header("Location: edit_monitoring.php?id=$id_monitoring&error=" . urlencode("Format foto harus JPG, JPEG, atau PNG."));
        exit;
    }

    /*
     * Cek MIME file sebenarnya, bukan hanya nama ekstensi.
     */
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp_file);

    $mime_diizinkan = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png'  => ['png'],
    ];

    if (!isset($mime_diizinkan[$mime]) || !in_array($ekstensi, $mime_diizinkan[$mime], true)) {
        header("Location: edit_monitoring.php?id=$id_monitoring&error=" . urlencode("File yang dipilih bukan gambar JPG/JPEG/PNG yang valid."));
        exit;
    }

    $folder_upload = __DIR__ . "/../uploads/monitoring/";

    if (!is_dir($folder_upload) && !mkdir($folder_upload, 0775, true)) {
        header("Location: edit_monitoring.php?id=$id_monitoring&error=" . urlencode("Folder upload tidak dapat dibuat."));
        exit;
    }

    $foto_baru = uniqid('monitoring_', true) . '.' . $ekstensi;
    $path_foto_baru = $folder_upload . $foto_baru;

    if (!move_uploaded_file($tmp_file, $path_foto_baru)) {
        header("Location: edit_monitoring.php?id=$id_monitoring&error=" . urlencode("Foto gagal disimpan."));
        exit;
    }
}

/*
 * === UPDATE DATABASE ===
 * Prepared statement dipakai agar input tidak langsung masuk ke SQL.
 */
try {
    mysqli_begin_transaction($conn);

    if ($foto_baru !== null) {
        $update_stmt = mysqli_prepare($conn, "
            UPDATE monitoring
            SET
                tanggal_monitoring = ?,
                nama_dudi = ?,
                keterangan = ?,
                foto = ?
            WHERE id_monitoring = ?
              AND id_guru = ?
              AND id_siswa = ?
        ");

        if (!$update_stmt) {
            throw new Exception("Gagal menyiapkan query update.");
        }

        mysqli_stmt_bind_param(
            $update_stmt,
            "ssssiii",
            $tanggal,
            $dudi,
            $keterangan,
            $foto_baru,
            $id_monitoring,
            $id_guru,
            $id_siswa
        );
    } else {
        $update_stmt = mysqli_prepare($conn, "
            UPDATE monitoring
            SET
                tanggal_monitoring = ?,
                nama_dudi = ?,
                keterangan = ?
            WHERE id_monitoring = ?
              AND id_guru = ?
              AND id_siswa = ?
        ");

        if (!$update_stmt) {
            throw new Exception("Gagal menyiapkan query update.");
        }

        mysqli_stmt_bind_param(
            $update_stmt,
            "sssiii",
            $tanggal,
            $dudi,
            $keterangan,
            $id_monitoring,
            $id_guru,
            $id_siswa
        );
    }

    if (!mysqli_stmt_execute($update_stmt)) {
        mysqli_stmt_close($update_stmt);
        throw new Exception("Gagal memperbarui data monitoring.");
    }

    mysqli_stmt_close($update_stmt);

    mysqli_commit($conn);

    /*
     * Hapus foto lama SETELAH database berhasil di-commit.
     * Jika database gagal, foto lama tetap aman.
     */
    if ($foto_baru !== null && !empty($foto_lama)) {
        $foto_lama_path = __DIR__ . "/../uploads/monitoring/" . basename($foto_lama);

        if (is_file($foto_lama_path)) {
            @unlink($foto_lama_path);
        }
    }

    header("Location: bimbingan.php?success=" . urlencode("Dokumentasi monitoring berhasil diperbarui."));
    exit;

} catch (Throwable $e) {

    mysqli_rollback($conn);

    /*
     * Jika DB gagal tetapi foto baru sudah ter-upload,
     * hapus foto baru supaya tidak menjadi file yatim.
     */
    if ($path_foto_baru !== null && is_file($path_foto_baru)) {
        @unlink($path_foto_baru);
    }

    header("Location: edit_monitoring.php?id=$id_monitoring&error=" . urlencode("Gagal menyimpan perubahan monitoring."));
    exit;
}
