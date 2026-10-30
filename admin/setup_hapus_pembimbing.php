<?php
/**
 * admin/setup_hapus_pembimbing.php — migrasi one-click: melepas assignment
 * pembimbing dari data siswa (kolom siswa.id_guru + FK-nya).
 * Setelah ini bimbingan bekerja bebas seperti jurnal: guru mencatat
 * untuk siswa mana saja tanpa assignment terlebih dahulu.
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
        // 1. Hapus FK bila ada
        $fk = ej_fk_on_col($conn, 'siswa', 'id_guru');
        if ($fk) {
            if (mysqli_query($conn, "ALTER TABLE `siswa` DROP FOREIGN KEY `$fk`")) {
                $done[] = "Foreign key `$fk` dihapus.";
            } else {
                throw new Exception("Gagal hapus FK: " . mysqli_error($conn));
            }
        } else {
            $already[] = "Foreign key sudah tidak ada.";
        }
        // 2. Hapus kolom bila ada
        if (ej_col_exists($conn, 'siswa', 'id_guru')) {
            if (mysqli_query($conn, "ALTER TABLE `siswa` DROP COLUMN `id_guru`")) {
                $done[] = "Kolom `siswa.id_guru` dihapus.";
            } else {
                throw new Exception("Gagal hapus kolom: " . mysqli_error($conn));
            }
        } else {
            $already[] = "Kolom `siswa.id_guru` sudah tidak ada.";
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$migrated = !ej_col_exists($conn, 'siswa', 'id_guru');
$title = 'Migrasi: Lepas Pembimbing';
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
<h2 class="text-lg font-extrabold">Lepas Assignment Pembimbing</h2>
<p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Menghapus kolom <code class="font-mono">siswa.id_guru</code> beserta foreign key-nya. Setelah ini guru mencatat bimbingan untuk <strong>siswa mana saja</strong> (seperti jurnal). Pastikan backup database sudah dibuat.</p>
<?php if ($migrated): ?>
<div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">✓ Migrasi sudah diterapkan — kolom <code class="font-mono">id_guru</code> tidak ada di tabel siswa.</div>
<?php else: ?>
<?php if ($done): ?><div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"><?php foreach ($done as $d): ?><p>✓ <?= htmlspecialchars($d); ?></p><?php endforeach; ?></div><?php endif; ?>
<?php if ($already): ?><div class="mt-2 text-xs text-slate-500"><?php foreach ($already as $a): ?><p><?= htmlspecialchars($a); ?></p><?php endforeach; ?></div><?php endif; ?>
<?php if ($error): ?><div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">Gagal: <?= htmlspecialchars($error); ?></div><?php endif; ?>
<?php if (!$done && !$error): ?>
<form action="setup_hapus_pembimbing.php" method="POST" onsubmit="return confirm('Hapus kolom id_guru dari tabel siswa? Pastikan backup sudah dibuat.');" class="mt-4">
<input type="hidden" name="konfirmasi" value="1">
<button class="w-full rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-rose-700">Jalankan Migrasi</button>
</form>
<?php endif; ?>
<?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
