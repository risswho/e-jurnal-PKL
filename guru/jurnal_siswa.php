<?php
session_start(); require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('guru');
$id_guru = (int) ($_SESSION['id_guru'] ?? 0);
$id_siswa = (int) ($_GET['id'] ?? 0);
// Strict: hanya siswa bimbingan guru ini yang boleh dibuka
$siswa = assert_siswa_bimbingan($conn, $id_siswa, $id_guru, 'siswa.php');
$stmt_jurnal = mysqli_prepare($conn,"SELECT j.* FROM jurnal j INNER JOIN siswa s ON j.id_siswa = s.id_siswa WHERE j.id_siswa = ? AND s.id_guru = ? ORDER BY j.tanggal_kegiatan DESC"); mysqli_stmt_bind_param($stmt_jurnal,"ii",$id_siswa,$id_guru); mysqli_stmt_execute($stmt_jurnal); $result_jurnal = mysqli_stmt_get_result($stmt_jurnal);
$title='Jurnal '.$siswa['nama_lengkap']; $base='../'; $role='guru'; $nama=$_SESSION['nama']??'Guru'; $active='jurnal_semua';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
?>
<a href="jurnal_semua.php" class="text-sm font-semibold text-slate-500 hover:text-indigo-600 dark:text-slate-400">← Semua Jurnal</a>
<div class="mt-3 rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
<div class="flex flex-col gap-3 sm:flex-row sm:items-center"><div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-600 text-lg font-extrabold text-white dark:bg-indigo-500"><?= strtoupper(mb_substr($siswa['nama_lengkap'],0,1)); ?></div>
<div class="flex-1"><h2 class="text-lg font-extrabold"><?= htmlspecialchars($siswa['nama_lengkap']); ?></h2><p class="text-sm text-slate-500 dark:text-slate-400"><?= htmlspecialchars($siswa['nisn']); ?> • <?= htmlspecialchars($siswa['kelas']); ?> • <?= htmlspecialchars($siswa['jurusan']); ?> • <?= htmlspecialchars($siswa['dudi_magang']); ?></p></div>
<a href="tambah_monitoring.php?id=<?= $siswa['id_siswa']; ?>" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 dark:bg-indigo-500">+ monitoring</a></div></div>
<div class="mt-4 rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
<div class="flex flex-col gap-2 border-b border-slate-200 p-4 sm:flex-row sm:items-center dark:border-slate-800"><select id="filterSiswa" class="rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-950"><option value="">Semua siswa</option><option value="<?= htmlspecialchars($siswa['nama_lengkap']); ?>"><?= htmlspecialchars($siswa['nama_lengkap']); ?></option></select><input id="searchJurnalSiswa" type="search" placeholder="Cari tanggal / DUDI / kegiatan..." class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-950"></div>
<div class="overflow-x-auto">
<table id="tblJurnalSiswa" class="w-full min-w-[760px] text-left text-sm"><thead><tr class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-400"><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">DUDI / Pembimbing</th><th class="px-4 py-3">Kegiatan</th><th class="px-4 py-3">Foto</th></tr></thead>
<tbody class="divide-y divide-slate-100 dark:divide-slate-800">
<?php if(mysqli_num_rows($result_jurnal)>0): while($j=mysqli_fetch_assoc($result_jurnal)): ?>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50" data-siswa="<?= htmlspecialchars($siswa['nama_lengkap']); ?>">
<td class="whitespace-nowrap px-4 py-3 text-xs font-bold"><?= htmlspecialchars($j['tanggal_kegiatan']); ?></td>
<td class="px-4 py-3"><p class="font-semibold"><?= htmlspecialchars($j['nama_dudi']); ?></p><p class="text-xs text-slate-500"><?= htmlspecialchars($j['pembimbing']); ?></p></td>
<td class="max-w-[300px] px-4 py-3"><p class="font-medium"><?= nl2br(htmlspecialchars($j['kegiatan_magang'])); ?></p><?php if(!empty($j['keterangan'])): ?><p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?= nl2br(htmlspecialchars($j['keterangan'])); ?></p><?php endif; ?></td>
<td class="px-4 py-3"><?php if(!empty($j['foto'])): ?><button onclick="openLightbox('../uploads/jurnal/<?= htmlspecialchars($j['foto']); ?>')"><img src="../uploads/jurnal/<?= htmlspecialchars($j['foto']); ?>" class="h-14 w-14 rounded-xl border border-slate-200 object-cover dark:border-slate-700" alt="Bukti"></button><?php else: ?><span class="text-xs text-slate-400">—</span><?php endif; ?></td></tr>
<?php endwhile; else: ?><tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">Siswa belum memiliki jurnal.</td></tr><?php endif; ?>
</tbody></table></div></div>
<script>function applyJurnalSiswaFilter(){var q=(document.getElementById('searchJurnalSiswa').value||'').toLowerCase();var sw=document.getElementById('filterSiswa').value;document.querySelectorAll('#tblJurnalSiswa tbody tr').forEach(function(tr){var okText=!q||tr.textContent.toLowerCase().includes(q);var okSiswa=!sw||tr.dataset.siswa===sw;tr.style.display=(okText&&okSiswa)?'':'none';});}document.getElementById('searchJurnalSiswa').addEventListener('input',applyJurnalSiswaFilter);document.getElementById('filterSiswa').addEventListener('change',applyJurnalSiswaFilter);</script>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
