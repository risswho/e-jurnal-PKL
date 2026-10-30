<?php
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('admin');
$totalSiswa = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM siswa"))['total'] ?? 0;
$totalGuru = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM guru"))['total'] ?? 0;
$totalJurnal = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM jurnal"))['total'] ?? 0;
$jurnalHariIni = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM jurnal WHERE tanggal_kegiatan = CURDATE()"))['total'] ?? 0;
$jurnalBulanIni = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM jurnal WHERE MONTH(tanggal_kegiatan)=MONTH(CURDATE()) AND YEAR(tanggal_kegiatan)=YEAR(CURDATE())"))['total'] ?? 0;
$totalmonitoring = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM monitoring"))['total'] ?? 0;
$qRecent = "SELECT j.id_jurnal, j.tanggal_kegiatan, j.kegiatan_magang, s.nama_lengkap, s.kelas FROM jurnal j INNER JOIN siswa s ON j.id_siswa=s.id_siswa ORDER BY j.tanggal_kegiatan DESC LIMIT 5";
$rRecent = mysqli_query($conn,$qRecent);
$title='Dashboard'; $base='../'; $role='admin'; $nama=$_SESSION['nama']??'Admin'; $active='dashboard';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
$cards=[
 ['label'=>'TOTAL JURNAL','value'=>$totalJurnal,'sub'=>'+'.$jurnalBulanIni.' bulan ini','href'=>'jurnal.php','icon'=>'M9 12h6m-6 4h6M9 5h11a1 1 0 011 1v13a1 1 0 01-1 1H9a1 1 0 01-1-1V6a1 1 0 011-1zM4 5H3a1 1 0 00-1 1v13a1 1 0 001 1h1'],
 ['label'=>'JURNAL HARI INI','value'=>$jurnalHariIni,'sub'=>'tanggal '.date('d M Y'),'href'=>'jurnal.php','icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
 ['label'=>'TOTAL SISWA','value'=>$totalSiswa,'sub'=>'Tercatat di sistem','href'=>'siswa.php','icon'=>'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-8 0 4 4 0 008 0z'],
 ['label'=>'TOTAL GURU','value'=>$totalGuru,'sub'=>$totalmonitoring.' dokumentasi monitoring','href'=>'guru.php','icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
];
?>
<div class="hero-medical">
  <h2 class="text-xl font-extrabold sm:text-2xl" style="font-family:'Poppins',sans-serif">Selamat Datang, <?= htmlspecialchars($nama); ?></h2>
  <p class="sub mt-1 text-sm">Kelola jurnal dan aktivitas pembelajaran dengan mudah.</p>
  <div class="no-print mt-4 flex flex-wrap gap-2">
    <a href="tambah_siswa.php" class="rounded-xl bg-white px-3.5 py-2 text-sm font-bold hover:bg-teal-50" style="color:var(--primary-dark)">Tambah Siswa</a>
    <a href="tambah_guru.php" class="rounded-xl px-3.5 py-2 text-sm font-bold text-white ring-1 ring-inset ring-white/40 hover:bg-white/10">Tambah Guru</a>
    <a href="import.php" class="rounded-xl px-3.5 py-2 text-sm font-bold text-white ring-1 ring-inset ring-white/40 hover:bg-white/10">Import Data</a>
    <a href="jurnal.php" class="rounded-xl px-3.5 py-2 text-sm font-semibold text-white underline-offset-4 hover:underline">Lihat semua jurnal</a>
  </div>
</div>
<div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
<?php foreach($cards as $c): ?>
<div class="card stat-card">
  <div class="stat-icon"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $c['icon']; ?>"/></svg></div>
  <p class="stat-label mt-3"><?= $c['label']; ?></p>
  <p class="stat-number"><?= $c['value']; ?></p>
  <p class="stat-sub"><?= htmlspecialchars($c['sub']); ?></p>
  <a href="<?= $c['href']; ?>" class="mt-2 inline-block text-xs font-bold hover:underline" style="color:var(--primary-dark)">Kelola</a>
</div>
<?php endforeach; ?>
</div>
<div class="card mt-5 !p-0">
  <div class="flex items-center justify-between border-b px-5 py-4" style="border-color:var(--border)"><h3 class="font-bold" style="font-family:'Poppins',sans-serif">Aktivitas Jurnal Terbaru</h3><a href="jurnal.php" class="text-xs font-bold hover:underline" style="color:var(--primary-dark)">Semua jurnal</a></div>
  <div class="divide-y" style="--tw-divide-opacity:1">
  <?php if($rRecent && mysqli_num_rows($rRecent)>0): while($r=mysqli_fetch_assoc($rRecent)): ?>
    <div class="flex items-start gap-3 px-5 py-3.5">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white" style="background:var(--primary)"><?= strtoupper(mb_substr($r['nama_lengkap'],0,1)); ?></div>
      <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-semibold"><?= htmlspecialchars($r['nama_lengkap']); ?> <span class="font-normal" style="color:var(--muted)">• <?= htmlspecialchars($r['kelas']); ?> • <?= htmlspecialchars($r['tanggal_kegiatan']); ?></span></p>
        <p class="truncate text-sm" style="color:var(--muted)"><?= htmlspecialchars($r['kegiatan_magang']); ?></p>
      </div>
    </div>
  <?php endwhile; else: ?><p class="px-5 py-6 text-center text-sm" style="color:var(--muted)">Belum ada jurnal.</p><?php endif; ?>
  </div>
</div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
