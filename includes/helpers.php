<?php
// includes/helpers.php — helper terpusat agar kode rapi & konsisten.
// Dipakai semua halaman view + proses.

const UI_INPUT = 'w-full rounded-xl border px-3.5 py-2.5 text-sm outline-none placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:focus:ring-teal-500/30 form-input';
const UI_LABEL = 'mb-1.5 block text-sm font-semibold form-label';

/**
 * Pastikan pengguna sudah login dengan role tertentu.
 * Gagal -> redirect ke login. Tidak me-return apa pun saat sukses.
 */
function require_login(string $role): void
{
    if (!isset($_SESSION['login']) || ($_SESSION['role'] ?? '') !== $role) {
        header('Location: ../auth/login.php');
        exit;
    }
}

/**
 * Tampilkan halaman error styled lalu hentikan eksekusi.
 */
function abort(string $title, string $msg, string $back = 'javascript:history.back()'): void
{
    $errorTitle = $title;
    $errorMsg = $msg;
    $backHref = $back;
    $base = '../';
    require __DIR__ . '/error.php';
    exit;
}

/** Redirect kembali ke form dengan pesan error. */
function back_with_error(string $url, string $msg): void
{
    header('Location: ' . $url . (str_contains($url, '?') ? '&' : '?') . 'error=' . urlencode($msg));
    exit;
}

/**
 * Pastikan siswa adalah bimbingan guru yang login.
 * Return array siswa bila valid, abort 403 bila bukan bimbingan.
 */
function assert_siswa_bimbingan(mysqli $conn, int $id_siswa, int $id_guru, string $back = 'siswa.php'): array
{
    if ($id_siswa <= 0 || $id_guru <= 0) {
        abort('Bukan siswa bimbingan Anda', 'Siswa ini dibimbing oleh guru lain atau belum punya pembimbing.', $back);
    }
    $stmt = mysqli_prepare($conn, 'SELECT id_siswa, nisn, nama_lengkap, kelas, jurusan, dudi_magang, id_guru FROM siswa WHERE id_siswa = ? AND id_guru = ? LIMIT 1');
    if (!$stmt) {
        abort('Terjadi kesalahan', 'Query database gagal diproses.', $back);
    }
    mysqli_stmt_bind_param($stmt, 'ii', $id_siswa, $id_guru);
    mysqli_stmt_execute($stmt);
    $siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$siswa) {
        http_response_code(403);
        abort('Bukan siswa bimbingan Anda', 'Siswa ini dibimbing oleh guru lain atau belum punya pembimbing.', $back);
    }
    return $siswa;
}
