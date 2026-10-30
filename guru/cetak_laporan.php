<?php
session_start(); require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('guru');
$id_guru = (int) ($_SESSION['id_guru'] ?? 0);
if ($id_guru <= 0) { header("Location: ../auth/login.php"); exit; }
$stmtGuru = mysqli_prepare($conn,"SELECT * FROM guru WHERE id_guru = ?"); mysqli_stmt_bind_param($stmtGuru,"i",$id_guru); mysqli_stmt_execute($stmtGuru); $guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGuru));
// Filter siswa via GET: 0 = semua siswa monitoring guru ini (strict)
$fSiswa = (int) ($_GET['siswa'] ?? 0);
$listStmt = mysqli_prepare($conn,"SELECT id_siswa, nama_lengkap, nisn, kelas FROM siswa WHERE id_guru = ? ORDER BY nama_lengkap ASC"); mysqli_stmt_bind_param($listStmt,"i",$id_guru); mysqli_stmt_execute($listStmt); $listSiswa = mysqli_stmt_get_result($listStmt);
$siswaDipilih = null;
$bukanmonitoring = false;
if ($fSiswa > 0) {
    $stF = mysqli_prepare($conn,"SELECT * FROM siswa WHERE id_siswa = ? AND id_guru = ?"); mysqli_stmt_bind_param($stF,"ii",$fSiswa,$id_guru); mysqli_stmt_execute($stF);
    $siswaDipilih = mysqli_fetch_assoc(mysqli_stmt_get_result($stF)); mysqli_stmt_close($stF);
    if ($siswaDipilih) { $rowsSiswa = [$siswaDipilih]; }
    else { $rowsSiswa = []; $bukanmonitoring = true; }
} else {
    $stmtSiswa = mysqli_prepare($conn,"SELECT * FROM siswa WHERE id_guru = ? ORDER BY nama_lengkap ASC"); mysqli_stmt_bind_param($stmtSiswa,"i",$id_guru); mysqli_stmt_execute($stmtSiswa);
    $rowsSiswa = [];
    $resS = mysqli_stmt_get_result($stmtSiswa);
    while ($rS = mysqli_fetch_assoc($resS)) $rowsSiswa[] = $rS;
}
$title='Cetak Laporan'; $base='../'; $role='guru'; $nama=$guru['nama_guru']??'Guru'; $active='cetak';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
?>
<div class="print:hidden mb-4 flex flex-wrap items-center gap-2">
<form action="cetak_laporan.php" method="GET" class="flex flex-wrap items-center gap-2">
<select name="siswa" class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-900"><option value="0">Semua siswa</option><?php if($listSiswa): mysqli_data_seek($listSiswa,0); while($ls=mysqli_fetch_assoc($listSiswa)): ?><option value="<?= $ls['id_siswa']; ?>" <?= ($fSiswa===(int)$ls['id_siswa'])?'selected':''; ?>><?= htmlspecialchars($ls['nama_lengkap']); ?> • <?= htmlspecialchars($ls['nisn']); ?> • <?= htmlspecialchars($ls['kelas']); ?></option><?php endwhile; endif; ?></select>
<button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-700 dark:bg-white dark:text-slate-900">Tampilkan</button>
<?php if($fSiswa>0): ?><a href="cetak_laporan.php" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800">Reset</a><?php endif; ?>
</form>
<button onclick="window.print()" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 dark:bg-indigo-500">🖨 Cetak Laporan</button>
<a href="dashboard.php" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800">← Kembali</a>
<span class="text-xs text-slate-500 dark:text-slate-400">Mode cetak otomatis menyembunyikan sidebar &amp; tombol.</span></div>
<div class="rounded-2xl border border-slate-200 bg-white p-6 text-center dark:border-slate-800 dark:bg-slate-900 print:border-black print:shadow-none">
<h1 class="text-xl font-extrabold tracking-wide">LAPORAN JURNAL PKL</h1>
<p class="text-sm text-slate-500 dark:text-slate-400 print:text-black">E-Jurnal SMK • Dicetak oleh: <strong><?= htmlspecialchars($guru['nama_guru']); ?></strong> • NIP: <?= htmlspecialchars($guru['nip']); ?><?php if($fSiswa>0 && $siswaDipilih): ?> • Siswa: <strong><?= htmlspecialchars($siswaDipilih['nama_lengkap']); ?></strong> (<?= htmlspecialchars($siswaDipilih['nisn']); ?> • <?= htmlspecialchars($siswaDipilih['kelas']); ?>)<?php endif; ?></p></div>
<style>@media print{ aside,header,footer,.print\:hidden{display:none!important} main{padding:0!important;max-width:none!important} body{background:#fff!important;color:#000!important} .print-card{border:1px solid #000!important;border-radius:0!important} table{font-size:12px} }</style>
<?php if(count($rowsSiswa)>0): foreach($rowsSiswa as $siswa): $id_siswa=$siswa['id_siswa'];
$stmtJ=mysqli_prepare($conn,"SELECT j.* FROM jurnal j INNER JOIN siswa s ON j.id_siswa = s.id_siswa WHERE j.id_siswa = ? AND s.id_guru = ? ORDER BY j.tanggal_kegiatan ASC"); mysqli_stmt_bind_param($stmtJ,"ii",$id_siswa,$id_guru); mysqli_stmt_execute($stmtJ); $resultJurnal=mysqli_stmt_get_result($stmtJ); ?>
<div class="print-card mt-4 rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900 print:bg-white">
<h2 class="font-extrabold"><?= htmlspecialchars($siswa['nama_lengkap']); ?></h2>
<p class="text-xs text-slate-500 dark:text-slate-400 print:text-black">NISN: <?= htmlspecialchars($siswa['nisn']); ?> • Kelas: <?= htmlspecialchars($siswa['kelas']); ?> • Jurusan: <?= htmlspecialchars($siswa['jurusan']); ?> • DUDI: <?= htmlspecialchars($siswa['dudi_magang']); ?></p>
<?php if(mysqli_num_rows($resultJurnal)>0): ?>
<div class="mt-3 overflow-x-auto"><table class="w-full border-collapse text-left text-sm"><thead><tr class="bg-slate-50 dark:bg-slate-800 print:bg-gray-100"><th class="border border-slate-300 px-2 py-1.5 print:border-black">No</th><th class="border border-slate-300 px-2 py-1.5 print:border-black">Tanggal</th><th class="border border-slate-300 px-2 py-1.5 print:border-black">DUDI</th><th class="border border-slate-300 px-2 py-1.5 print:border-black">Kegiatan</th><th class="border border-slate-300 px-2 py-1.5 print:border-black">Keterangan</th><th class="border border-slate-300 px-2 py-1.5 print:border-black">Foto</th></tr></thead><tbody>
<?php $no=1; while($j=mysqli_fetch_assoc($resultJurnal)): ?><tr>
<td class="border border-slate-200 px-2 py-1.5 dark:border-slate-700 print:border-black"><?= $no++; ?></td>
<td class="border border-slate-200 px-2 py-1.5 dark:border-slate-700 print:border-black"><?= htmlspecialchars($j['tanggal_kegiatan']); ?></td>
<td class="border border-slate-200 px-2 py-1.5 dark:border-slate-700 print:border-black"><?= htmlspecialchars($j['nama_dudi']); ?><br><span class="text-xs"><?= htmlspecialchars($j['pembimbing']); ?></span></td>
<td class="border border-slate-200 px-2 py-1.5 dark:border-slate-700 print:border-black"><?= nl2br(htmlspecialchars($j['kegiatan_magang'])); ?></td>
<td class="border border-slate-200 px-2 py-1.5 dark:border-slate-700 print:border-black"><?= nl2br(htmlspecialchars($j['keterangan'])); ?></td>
<td class="border border-slate-200 px-2 py-1.5 dark:border-slate-700 print:border-black"><?php if(!empty($j['foto'])): ?><img src="../uploads/jurnal/<?= htmlspecialchars($j['foto']); ?>" style="width:80px" alt="Foto"><?php else: ?>—<?php endif; ?></td></tr><?php endwhile; ?>
</tbody></table></div>
<?php else: ?><p class="mt-2 text-sm text-slate-500">Belum ada jurnal untuk siswa ini.</p><?php endif; ?></div>
<?php endforeach; else: ?><?php if($bukanmonitoring): ?><p class="mt-4 rounded-xl border bg-white p-6 text-center text-sm dark:bg-slate-900">Siswa ini bukan monitoring Anda.</p><?php elseif($fSiswa>0): ?><p class="mt-4 rounded-xl border bg-white p-6 text-center text-sm dark:bg-slate-900">Siswa tidak ditemukan.</p><?php else: ?><p class="mt-4 rounded-xl border bg-white p-6 text-center text-sm dark:bg-slate-900">Belum ada data siswa monitoring. Minta admin untuk memasangkan siswa ke akun Anda.</p><?php endif; ?><?php endif; ?>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
