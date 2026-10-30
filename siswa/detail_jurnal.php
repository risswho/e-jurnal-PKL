<?php
session_start(); require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('siswa');
$id_siswa = $_SESSION['id_siswa']; $id_jurnal = $_GET['id'] ?? 0;
$stmt = mysqli_prepare($conn,"SELECT * FROM jurnal WHERE id_jurnal = ? AND id_siswa = ?"); mysqli_stmt_bind_param($stmt,"ii",$id_jurnal,$id_siswa); mysqli_stmt_execute($stmt); $jurnal = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$jurnal) abort("Jurnal tidak ditemukan", "Data jurnal tidak ada atau bukan milik Anda.", "dashboard.php");
$title='Detail Jurnal'; $base='../'; $role='siswa'; $nama=$_SESSION['nama']??'Siswa'; $active='dashboard';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
?>
<a href="dashboard.php" class="text-sm font-semibold text-slate-500 hover:text-indigo-600 dark:text-slate-400">← Kembali ke Dashboard</a>
<div class="mt-3 grid gap-4 lg:grid-cols-2">
<div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
<div class="flex items-center justify-between"><h2 class="text-lg font-extrabold">Detail Jurnal</h2></div>
<dl class="mt-4 space-y-3 text-sm">
<div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tanggal</dt><dd class="font-semibold"><?= htmlspecialchars($jurnal['tanggal_kegiatan']); ?></dd></div>
<div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nama DUDI</dt><dd><?= htmlspecialchars($jurnal['nama_dudi']); ?></dd></div>
<div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Pembimbing</dt><dd><?= htmlspecialchars($jurnal['pembimbing']); ?></dd></div>
<div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kegiatan Magang</dt><dd class="rounded-xl bg-slate-50 p-3 leading-relaxed dark:bg-slate-800"><?= nl2br(htmlspecialchars($jurnal['kegiatan_magang'])); ?></dd></div>
<div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Keterangan</dt><dd class="rounded-xl bg-slate-50 p-3 leading-relaxed dark:bg-slate-800"><?= nl2br(htmlspecialchars($jurnal['keterangan'] ?: '-')); ?></dd></div></dl>
<div class="mt-5 flex gap-2"><a href="edit_jurnal.php?id=<?= $jurnal['id_jurnal']; ?>" class="flex-1 rounded-xl bg-indigo-600 px-4 py-2.5 text-center text-sm font-bold text-white hover:bg-indigo-700 dark:bg-indigo-500">Edit Jurnal</a><button onclick="return askDelete('hapus_jurnal.php?id=<?= $jurnal['id_jurnal']; ?>','Hapus jurnal ini?')" class="flex-1 rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-bold text-rose-600 hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-400">Hapus</button></div></div>
<div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
<h3 class="font-bold">Bukti Kegiatan</h3>
<?php if(!empty($jurnal['foto'])): ?><button onclick="openLightbox('../uploads/jurnal/<?= htmlspecialchars($jurnal['foto']); ?>')" class="mt-3 block w-full"><img src="../uploads/jurnal/<?= htmlspecialchars($jurnal['foto']); ?>" alt="Bukti kegiatan" class="max-h-[480px] w-full rounded-2xl border border-slate-200 object-cover dark:border-slate-700"></button><p class="mt-2 text-center text-xs text-slate-400">Klik foto untuk perbesar • PNG/JPG</p><?php else: ?><div class="mt-3 flex h-48 flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 text-sm text-slate-400 dark:border-slate-700">Tidak ada foto bukti</div><?php endif; ?></div></div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
