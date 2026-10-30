<?php
// admin/laporan.php — pintu masuk laporan (data tetap dari halaman yang sudah ada)
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('admin');
$totalJurnal = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM jurnal"))['total'] ?? 0;
$totalmonitoring = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM monitoring"))['total'] ?? 0;
$title='Laporan'; $base='../'; $role='admin'; $nama=$_SESSION['nama']??'Admin'; $active='laporan';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
?>
<div><h2 class="font-extrabold tracking-tight" style="font-family:'Poppins',sans-serif;font-size:1.35rem">Laporan</h2><p class="text-sm" style="color:var(--muted)">Akses rekap jurnal &amp; monitoring untuk monitoring dan cetak.</p></div>
<div class="mt-4 grid gap-4 sm:grid-cols-2">
  <div class="card stat-card">
    <div class="stat-icon"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M9 5h11a1 1 0 011 1v13a1 1 0 01-1 1H9a1 1 0 01-1-1V6a1 1 0 011-1z"/></svg></div>
    <p class="stat-label mt-3">LAPORAN JURNAL</p><p class="stat-number"><?= $totalJurnal; ?></p>
    <p class="stat-sub">seluruh jurnal siswa</p>
    <a href="jurnal.php" class="btn-primary mt-3 !py-2 !text-xs">Buka Data Jurnal</a>
  </div>
  <div class="card stat-card">
    <div class="stat-icon"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8m-8 4h8M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg></div>
    <p class="stat-label mt-3">LAPORAN monitoring</p><p class="stat-number"><?= $totalmonitoring; ?></p>
    <p class="stat-sub">dokumentasi guru pembimbing</p>
    <a href="bimbingan.php" class="btn-primary mt-3 !py-2 !text-xs">Buka Data monitoring</a>
  </div>
</div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
