<?php
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('admin');
$query = "SELECT id_guru, nip, nama_guru, created_at FROM guru ORDER BY nama_guru ASC";
$result = mysqli_query($conn, $query);
$title='Kelola Guru'; $base='../'; $role='admin'; $nama=$_SESSION['nama']??'Admin'; $active='guru';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
?>
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
<div><h2 class="text-xl font-extrabold tracking-tight">Kelola Guru</h2><p class="text-sm text-slate-500 dark:text-slate-400"><?= mysqli_num_rows($result); ?> guru terdaftar</p></div>
<div class="flex flex-wrap gap-2"><a href="download_template.php?tipe=guru" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800">⬇ Template</a><a href="import.php?tipe=guru" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow hover:bg-emerald-700">⬆ Import</a><a href="tambah_guru.php" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-400"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>Tambah Guru</a></div></div>
<div class="mt-4 rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
<div class="border-b border-slate-200 p-4 dark:border-slate-800"><input data-table-filter="#tblGuru" type="search" placeholder="Cari nama / NIP..." class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-950 dark:focus:ring-indigo-500/30"></div>
<div class="overflow-x-auto"><table id="tblGuru" class="w-full min-w-[560px] text-left text-sm">
<thead><tr class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-400"><th class="px-4 py-3">No</th><th class="px-4 py-3">NIP</th><th class="px-4 py-3">Nama Guru</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
<tbody class="divide-y divide-slate-100 dark:divide-slate-800">
<?php if(mysqli_num_rows($result)>0): $no=1; while($g=mysqli_fetch_assoc($result)): ?>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
<td class="px-4 py-3 text-slate-400"><?= $no++; ?></td>
<td class="px-4 py-3 font-mono text-xs font-semibold"><?= htmlspecialchars($g['nip']); ?></td>
<td class="px-4 py-3 font-semibold"><?= htmlspecialchars($g['nama_guru']); ?></td>
<td class="px-4 py-3 text-right"><a href="edit_guru.php?id=<?= $g['id_guru']; ?>" class="rounded-lg border border-indigo-200 px-3 py-1.5 text-xs font-bold text-indigo-600 hover:bg-indigo-50 dark:border-indigo-500/30 dark:text-indigo-300 dark:hover:bg-indigo-500/10">Edit</a> <button onclick="return askDelete('hapus_guru.php?id=<?= $g['id_guru']; ?>','Hapus guru <?= htmlspecialchars(addslashes($g['nama_guru'])); ?>? Catatan monitoringnya ikut terhapus.')" class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-400 dark:hover:bg-rose-500/10">Hapus</button></td></tr>
<?php endwhile; else: ?><tr><td colspan="4" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">Belum ada data guru.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
