<?php
/**
 * admin/setup_pembimbing.php — migrasi one-click: mengembalikan assignment
 * guru pembimbing ke data siswa (kolom siswa.id_guru + FK ke guru).
 * Model: 1 guru membimbing banyak siswa (seperti walikelas), 1 siswa = 1 guru.
 * Assignment diatur Admin (Tambah/Edit Siswa atau Import CSV).
 * Guru yang login hanya melihat jurnal siswa bimbingannya (filter strict).
 * Idempoten: aman diklik berulang (cek keberadaan dulu).
 */
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('admin');

$done = [];
$already = [];
$error = null;

function ej_col_exists($conn, $table, $col): bool
{
    $c = @mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$col'");
    return ($c && mysqli_num_rows($c) > 0);
}

function ej_fk_on_col($conn, $table, $col): ?string
{
    $q = "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . mysqli_real_escape_string($conn, $table) . "'
          AND COLUMN_NAME = '" . mysqli_real_escape_string($conn, $col) . "'
          AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1";
    $r = @mysqli_query($conn, $q);
    if ($r && ($row = mysqli_fetch_assoc($r))) return $row['CONSTRAINT_NAME'];
    return null;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['konfirmasi'] ?? '') === '1') {
    try {
        // 1. Tambah kolom bila belum ada (NULL = belum ada pembimbing)
        if (!ej_col_exists($conn, 'siswa', 'id_guru')) {
            $sql = "ALTER TABLE `siswa` ADD COLUMN `id_guru` INT NULL AFTER `dudi_magang`, ADD KEY `id_guru` (`id_guru`)";
            if (mysqli_query($conn, $sql)) {
                $done[] = "Kolom `siswa.id_guru` ditambahkan (NULL = belum ada pembimbing).";
            } else {
                throw new Exception("Gagal tambah kolom: " . mysqli_error($conn));
            }
        } else {
            $already[] = "Kolom `siswa.id_guru` sudah ada.";
        }
        // 2. Tambah FK bila belum ada
        $fk = ej_fk_on_col($conn, 'siswa', 'id_guru');
        if (!$fk) {
            $sqlFk = "ALTER TABLE `siswa` ADD CONSTRAINT `fk_siswa_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE SET NULL ON UPDATE CASCADE";
            if (mysqli_query($conn, $sqlFk)) {
                $done[] = "Foreign key `fk_siswa_guru` ditambahkan (hapus guru → siswa jadi tanpa pembimbing, bukan ikut terhapus).";
            } else {
                throw new Exception("Gagal tambah FK: " . mysqli_error($conn));
            }
        } else {
            $already[] = "Foreign key `$fk` sudah ada.";
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$migrated = ej_col_exists($conn, 'siswa', 'id_guru') && (bool) ej_fk_on_col($conn, 'siswa', 'id_guru');
// Statistik untuk admin
$stat_total = 0; $stat_terisi = 0; $stat_kosong = 0;
if (ej_col_exists($conn, 'siswa', 'id_guru')) {
    $rt = @mysqli_query($conn, "SELECT COUNT(*) t FROM siswa");
    if ($rt) $stat_total = (int) (mysqli_fetch_assoc($rt)['t'] ?? 0);
    $rf = @mysqli_query($conn, "SELECT COUNT(*) t FROM siswa WHERE id_guru IS NOT NULL");
    if ($rf) $stat_terisi = (int) (mysqli_fetch_assoc($rf)['t'] ?? 0);
    $stat_kosong = $stat_total - $stat_terisi;
}
$title = 'Migrasi: Guru Pembimbing';
$base = '../';
$role = 'admin';
$nama = $_SESSION['nama'] ?? 'Admin';
$active = 'siswa';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/toast.php';
?>
<a href="siswa.php" class="inline-flex items-center gap-1 text-sm font-semibold text-slate-500 hover:text-indigo-600 dark:text-slate-400">← Kembali ke Kelola Siswa</a>
<div class="mx-auto mt-4 max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
<h2 class="text-lg font-extrabold">Aktifkan Guru Pembimbing</h2>
<p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Menambahkan kolom <code class="font-mono">siswa.id_guru</code> + relasi ke <code class="font-mono">guru</code>. Setelah ini setiap siswa punya <strong>1 guru pembimbing</strong> (diatur admin, seperti walikelas) dan guru hanya melihat jurnal siswa bimbingannya.</p>
<?php if ($migrated): ?>
<div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">✓ Migrasi sudah diterapkan — kolom <code class="font-mono">id_guru</code> + FK tersedia.</div>
<div class="mt-3 grid grid-cols-3 gap-2 text-center text-sm">
<div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800"><p class="text-xl font-extrabold"><?= $stat_total; ?></p><p class="text-xs text-slate-500">Total siswa</p></div>
<div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800"><p class="text-xl font-extrabold text-emerald-600"><?= $stat_terisi; ?></p><p class="text-xs text-slate-500">Ada pembimbing</p></div>
<div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800"><p class="text-xl font-extrabold text-rose-600"><?= $stat_kosong; ?></p><p class="text-xs text-slate-500">Belum ada</p></div>
</div>
<?php if ($stat_kosong > 0): ?><p class="mt-3 text-xs leading-relaxed text-slate-500 dark:text-slate-400">Siswa tanpa pembimbing <strong>tidak muncul di akun guru manapun</strong>. Lengkapi via <a href="siswa.php" class="font-bold text-indigo-600 hover:underline">Kelola Siswa → Edit</a>.</p><?php endif; ?>
<?php else: ?>
<?php if ($done): ?><div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"><?php foreach ($done as $d): ?><p>✓ <?= htmlspecialchars($d); ?></p><?php endforeach; ?></div><?php endif; ?>
<?php if ($already): ?><div class="mt-2 text-xs text-slate-500"><?php foreach ($already as $a): ?><p><?= htmlspecialchars($a); ?></p><?php endforeach; ?></div><?php endif; ?>
<?php if ($error): ?><div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">Gagal: <?= htmlspecialchars($error); ?></div><?php endif; ?>
<?php if (!$done && !$error): ?>
<p class="mt-4 rounded-xl bg-amber-50 p-3 text-xs leading-relaxed text-amber-800 ring-1 ring-inset ring-amber-200 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/30"><strong>Catatan:</strong> pastikan backup database sudah dibuat. Kolom baru bernilai <code class="font-mono">NULL</code> untuk semua siswa lama — wajib dilengkapi admin setelah migrasi.</p>
<form action="setup_pembimbing.php" method="POST" onsubmit="return confirm('Aktifkan kolom guru pembimbing di tabel siswa?');" class="mt-4">
<input type="hidden" name="konfirmasi" value="1">
<button class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Jalankan Migrasi</button>
</form>
<?php endif; ?>
<?php endif; ?>
</div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
