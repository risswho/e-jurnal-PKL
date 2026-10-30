<?php
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('admin');
$query = "SELECT s.*, g.nama_guru, g.nip FROM siswa s LEFT JOIN guru g ON s.id_guru = g.id_guru ORDER BY s.nama_lengkap ASC";
$result = mysqli_query($conn, $query);
$linkSetup = "";
if ($result) {
    $cekCol = @mysqli_query($conn, "SHOW COLUMNS FROM siswa LIKE 'id_guru'");
    if (!$cekCol || mysqli_num_rows($cekCol) === 0) $linkSetup = "setup_pembimbing.php";
}
$title='Kelola Siswa'; $base='../'; $role='admin'; $nama=$_SESSION['nama']??'Admin'; $active='siswa';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
?>
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
<div><h2 class="text-xl font-extrabold tracking-tight">Kelola Siswa</h2><p class="text-sm text-slate-500 dark:text-slate-400"><?= mysqli_num_rows($result); ?> siswa terdaftar</p></div><div class="flex flex-wrap gap-2"><a href="download_template.php?tipe=siswa" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800">⬇ Template</a><a href="import.php?tipe=siswa" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow hover:bg-emerald-700">⬆ Import</a><a href="tambah_siswa.php" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-400"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>Tambah Siswa</a></div></div>
<?php if ($linkSetup !== ""): ?>
<div class="mt-4 flex flex-wrap items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 dark:border-amber-500/30 dark:bg-amber-500/10">
<div class="flex-1"><p class="text-sm font-bold text-amber-800 dark:text-amber-300">Fitur guru pembimbing belum aktif</p><p class="text-xs text-amber-700 dark:text-amber-400">Kolom <code class="rounded bg-amber-100 px-1 dark:bg-amber-500/20">siswa.id_guru</code> belum ada. Aktifkan dulu agar tiap siswa bisa dipasangkan ke guru pembimbing.</p></div>
<a href="setup_pembimbing.php" class="shrink-0 rounded-xl bg-amber-500 px-4 py-2 text-sm font-bold text-white hover:bg-amber-600">Aktifkan Sekarang</a>
</div>
<?php endif; ?>
<div class="mt-4 rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
<div class="border-b border-slate-200 p-4 dark:border-slate-800"><input data-table-filter="#tblSiswa" type="search" placeholder="Cari nama / NISN / kelas / DUDI / pembimbing..." class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-950 dark:focus:ring-indigo-500/30"></div>
<div class="overflow-x-auto"><table id="tblSiswa" class="w-full min-w-[960px] text-left text-sm">
<thead><tr class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-400"><th class="px-4 py-3">No</th><th class="px-4 py-3">NISN</th><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Kelas</th><th class="px-4 py-3">DUDI</th><th class="px-4 py-3">Pembimbing</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
<tbody class="divide-y divide-slate-100 dark:divide-slate-800">
<?php if(mysqli_num_rows($result)>0): $no=1; while($s=mysqli_fetch_assoc($result)): ?>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
<td class="px-4 py-3 text-slate-400"><?= $no++; ?></td>
<td class="px-4 py-3 font-mono text-xs font-semibold"><?= htmlspecialchars($s['nisn']); ?></td>
<td class="px-4 py-3"><p class="font-semibold"><?= htmlspecialchars($s['nama_lengkap']); ?></p><p class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($s['jurusan']); ?></p></td>
<td class="px-4 py-3"><span class="rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold dark:bg-slate-800"><?= htmlspecialchars($s['kelas']); ?></span></td>
<td class="max-w-[180px] truncate px-4 py-3 text-slate-600 dark:text-slate-300"><?= htmlspecialchars($s['dudi_magang']); ?></td>
<td class="px-4 py-3"><?php if (!empty($s['nama_guru'])): ?><p class="font-semibold"><?= htmlspecialchars($s['nama_guru']); ?></p><p class="font-mono text-xs text-slate-500"><?= htmlspecialchars($s['nip']); ?></p><?php else: ?><span class="rounded-md bg-rose-100 px-2 py-1 text-xs font-bold text-rose-700 dark:bg-rose-500/15 dark:text-rose-300">Belum ada</span><?php endif; ?></td>
<td class="px-4 py-3 text-right"><a href="edit_siswa.php?id=<?= $s['id_siswa']; ?>" class="rounded-lg border border-indigo-200 px-3 py-1.5 text-xs font-bold text-indigo-600 hover:bg-indigo-50 dark:border-indigo-500/30 dark:text-indigo-300 dark:hover:bg-indigo-500/10">Edit</a> <button onclick="return askDelete('hapus_siswa.php?id=<?= $s['id_siswa']; ?>','Hapus siswa <?= htmlspecialchars(addslashes($s['nama_lengkap'])); ?>? Jurnal terkait ikut terpengaruh.')" class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-400 dark:hover:bg-rose-500/10">Hapus</button></td></tr>
<?php endwhile; else: ?><tr><td colspan="7" class="px-4 py-10 text-center"><p class="font-semibold">Belum ada data siswa</p><p class="text-slate-500 dark:text-slate-400">Klik Tambah Siswa untuk menambah data.</p></td></tr><?php endif; ?>
</tbody></table></div></div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
