<?php
// siswa/laporan.php — ringkasan laporan sendiri + cetak (print-friendly)
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('siswa');
$id_siswa = $_SESSION['id_siswa'];
$stmt = mysqli_prepare($conn,"SELECT s.*, g.nama_guru FROM siswa s LEFT JOIN guru g ON s.id_guru = g.id_guru WHERE s.id_siswa = ?"); mysqli_stmt_bind_param($stmt,"i",$id_siswa); mysqli_stmt_execute($stmt); $siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$stmt2 = mysqli_prepare($conn,"SELECT COUNT(*) AS total, COUNT(DISTINCT tanggal_kegiatan) AS hari FROM jurnal WHERE id_siswa = ?"); mysqli_stmt_bind_param($stmt2,"i",$id_siswa); mysqli_stmt_execute($stmt2); $stat = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
$stmt3 = mysqli_prepare($conn,"SELECT tanggal_kegiatan, kegiatan_magang FROM jurnal WHERE id_siswa = ? ORDER BY tanggal_kegiatan ASC"); mysqli_stmt_bind_param($stmt3,"i",$id_siswa); mysqli_stmt_execute($stmt3); $list = mysqli_stmt_get_result($stmt3);
$title='Laporan'; $base='../'; $role='siswa'; $nama=$siswa['nama_lengkap']??'Siswa'; $active='laporan';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
?>
<div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
  <div><h2 class="font-extrabold tracking-tight" style="font-family:'Poppins',sans-serif;font-size:1.35rem">Laporan Saya</h2><p class="text-sm" style="color:var(--muted)"><?= (int)($stat['total'] ?? 0); ?> jurnal • <?= (int)($stat['hari'] ?? 0); ?> hari hadir.</p></div>
  <button onclick="window.print()" class="btn-primary no-print">Cetak Laporan</button>
</div>
<div class="card mt-4">
  <p class="text-sm"><strong><?= htmlspecialchars($siswa['nama_lengkap']); ?></strong> • <?= htmlspecialchars($siswa['kelas']); ?> • <?= htmlspecialchars($siswa['jurusan']); ?></p>
  <p class="text-xs" style="color:var(--muted)">DUDI: <?= htmlspecialchars($siswa['dudi_magang']); ?> • Pembimbing: <?= htmlspecialchars($siswa['nama_guru'] ?? 'Belum ditentukan'); ?></p>
</div>
<div class="card mt-4 !p-0">
  <div class="table-wrap"><table class="table-medical">
    <thead><tr><th>No</th><th>Tanggal</th><th>Kegiatan</th></tr></thead>
    <tbody>
    <?php if($list && mysqli_num_rows($list)>0): $no=1; while($r=mysqli_fetch_assoc($list)): ?>
      <tr><td style="color:var(--muted)"><?= $no++; ?></td><td class="whitespace-nowrap text-xs font-bold"><?= htmlspecialchars($r['tanggal_kegiatan']); ?></td><td><?= htmlspecialchars($r['kegiatan_magang']); ?></td></tr>
    <?php endwhile; else: ?><tr><td colspan="3" class="px-4 py-10 text-center" style="color:var(--muted)">Belum ada jurnal.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
