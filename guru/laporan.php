<?php
// guru/laporan.php — pintu masuk laporan monitoring (data tetap dari cetak_laporan.php)
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('guru');
$id_guru = $_SESSION['id_guru'];
$stmt = mysqli_prepare($conn,"SELECT COUNT(*) AS total FROM siswa WHERE id_guru = ?"); mysqli_stmt_bind_param($stmt,"i",$id_guru); mysqli_stmt_execute($stmt); $total_siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'] ?? 0;
$title='Laporan'; $base='../'; $role='guru'; $nama=$_SESSION['nama']??'Guru'; $active='laporan';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
?>
<div><h2 class="font-extrabold tracking-tight" style="font-family:'Poppins',sans-serif;font-size:1.35rem">Laporan</h2><p class="text-sm" style="color:var(--muted)">Cetak laporan jurnal siswa monitoring Anda (<?= $total_siswa; ?> siswa).</p></div>
<div class="card mt-4">
  <h3 class="font-bold" style="font-family:'Poppins',sans-serif">Cetak Laporan PKL</h3>
  <p class="mt-1 text-sm" style="color:var(--muted)">Laporan dapat difilter per siswa dan langsung dicetak dari halaman cetak.</p>
  <div class="mt-3 flex flex-wrap gap-2">
    <a href="cetak_laporan.php" class="btn-primary">Buka Cetak Laporan</a>
    <a href="jurnal_semua.php" class="btn-ghost">Lihat Jurnal</a>
    <a href="riwayat_bimbingan.php" class="btn-ghost">Riwayat monitoring</a>
  </div>
</div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
