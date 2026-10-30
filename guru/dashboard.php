<?php
session_start(); require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('guru');
$id_guru = $_SESSION['id_guru'];
$stmt_guru = mysqli_prepare($conn,"SELECT * FROM guru WHERE id_guru = ?"); mysqli_stmt_bind_param($stmt_guru,"i",$id_guru); mysqli_stmt_execute($stmt_guru); $guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_guru));
$stmt_siswa = mysqli_prepare($conn,"SELECT COUNT(*) AS total FROM siswa WHERE id_guru = ?"); mysqli_stmt_bind_param($stmt_siswa,"i",$id_guru); mysqli_stmt_execute($stmt_siswa); $total_siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_siswa))['total'] ?? 0;
$stmt_jurnal = mysqli_prepare($conn,"SELECT COUNT(*) AS total FROM jurnal j INNER JOIN siswa s ON j.id_siswa = s.id_siswa WHERE s.id_guru = ?"); mysqli_stmt_bind_param($stmt_jurnal,"i",$id_guru); mysqli_stmt_execute($stmt_jurnal); $total_jurnal = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_jurnal))['total'] ?? 0;
$stmt_today = mysqli_prepare($conn,"SELECT COUNT(*) AS total FROM jurnal j INNER JOIN siswa s ON j.id_siswa = s.id_siswa WHERE s.id_guru = ? AND j.tanggal_kegiatan = CURDATE()"); mysqli_stmt_bind_param($stmt_today,"i",$id_guru); mysqli_stmt_execute($stmt_today); $jurnal_hari_ini = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_today))['total'] ?? 0;
$stmt_b = mysqli_prepare($conn,"SELECT COUNT(*) AS total FROM monitoring WHERE id_guru = ?"); mysqli_stmt_bind_param($stmt_b,"i",$id_guru); mysqli_stmt_execute($stmt_b); $total_monitoring = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_b))['total'] ?? 0;
$stmt_recent = mysqli_prepare($conn,"SELECT j.tanggal_kegiatan, j.kegiatan_magang, s.nama_lengkap FROM jurnal j INNER JOIN siswa s ON j.id_siswa=s.id_siswa WHERE s.id_guru = ? ORDER BY j.tanggal_kegiatan DESC LIMIT 5"); mysqli_stmt_bind_param($stmt_recent,"i",$id_guru); mysqli_stmt_execute($stmt_recent); $recent = mysqli_stmt_get_result($stmt_recent);
$title='Dashboard'; $base='../'; $role='guru'; $nama=$guru['nama_guru']??'Guru'; $active='dashboard';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
$cards=[
 ['label'=>'TOTAL JURNAL monitoring','value'=>$total_jurnal,'sub'=>$jurnal_hari_ini.' masuk hari ini','href'=>'jurnal_semua.php','icon'=>'M9 12h6m-6 4h6M9 5h11a1 1 0 011 1v13a1 1 0 01-1 1H9a1 1 0 01-1-1V6a1 1 0 011-1z'],
 ['label'=>'JURNAL HARI INI','value'=>$jurnal_hari_ini,'sub'=>'tanggal '.date('d M Y'),'href'=>'jurnal_semua.php','icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
  ['label'=>'TOTAL SISWA monitoring','value'=>$total_siswa,'sub'=>$total_monitoring.' dokumentasi monitoring','href'=>'siswa.php','icon'=>'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-8 0 4 4 0 008 0z'],
];
?>
<div class="hero-medical">
  <h2 class="text-xl font-extrabold sm:text-2xl" style="font-family:'Poppins',sans-serif">Selamat Datang, <?= htmlspecialchars($guru['nama_guru'] ?? ''); ?></h2>
  <p class="sub mt-1 text-sm">Kelola jurnal dan aktivitas pembelajaran dengan mudah. Terdata <strong><?= $total_siswa; ?> siswa monitoring Anda</strong>.</p>
  <div class="no-print mt-4 flex flex-wrap gap-2">
    <a href="tambah_monitoring.php" class="rounded-xl bg-white px-3.5 py-2 text-sm font-bold hover:bg-teal-50" style="color:var(--primary-dark)">Tambah monitoring</a>
    <a href="cetak_laporan.php" class="rounded-xl px-3.5 py-2 text-sm font-bold text-white ring-1 ring-inset ring-white/40 hover:bg-white/10">Cetak Laporan</a>
  </div>
</div>
<div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
<?php foreach($cards as $c): ?>
<div class="card stat-card">
  <div class="stat-icon"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $c['icon']; ?>"/></svg></div>
  <p class="stat-label mt-3"><?= $c['label']; ?></p>
  <p class="stat-number"><?= $c['value']; ?></p>
  <p class="stat-sub"><?= htmlspecialchars($c['sub']); ?></p>
  <a href="<?= $c['href']; ?>" class="mt-2 inline-block text-xs font-bold hover:underline" style="color:var(--primary-dark)">Lihat detail</a>
</div>
<?php endforeach; ?>
</div>
<div class="mt-5 grid gap-4 lg:grid-cols-2">
  <div class="card !p-0">
    <div class="border-b px-5 py-4 font-bold" style="border-color:var(--border);font-family:'Poppins',sans-serif">Jurnal Terbaru Siswa</div>
    <div class="divide-y">
    <?php if($recent && mysqli_num_rows($recent)>0): while($r=mysqli_fetch_assoc($recent)): ?>
      <div class="flex items-start gap-3 px-5 py-3">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white" style="background:var(--primary)"><?= strtoupper(mb_substr($r['nama_lengkap'],0,1)); ?></div>
        <div class="min-w-0 flex-1"><p class="text-sm font-semibold"><?= htmlspecialchars($r['nama_lengkap']); ?> <span class="font-normal" style="color:var(--muted)">• <?= htmlspecialchars($r['tanggal_kegiatan']); ?></span></p><p class="truncate text-sm" style="color:var(--muted)"><?= htmlspecialchars($r['kegiatan_magang']); ?></p></div>
      </div>
    <?php endwhile; else: ?><p class="px-5 py-6 text-center text-sm" style="color:var(--muted)">Belum ada jurnal.</p><?php endif; ?>
    </div>
  </div>
  <div class="card">
    <h3 class="font-bold" style="font-family:'Poppins',sans-serif">Menu Cepat</h3>
    <div class="mt-3 grid grid-cols-2 gap-2 text-sm font-semibold">
      <a href="jurnal_semua.php" class="rounded-xl px-3 py-3 text-center transition hover:opacity-90" style="background:var(--primary-soft)">Jurnal<br><span class="text-xs font-normal" style="color:var(--muted)">Siswa</span></a>
      <a href="tambah_monitoring.php" class="rounded-xl px-3 py-3 text-center transition hover:opacity-90" style="background:var(--primary-soft)">Tambah<br><span class="text-xs font-normal" style="color:var(--muted)">monitoring</span></a>
      <a href="riwayat_bimbingan.php" class="rounded-xl px-3 py-3 text-center transition hover:opacity-90" style="background:var(--primary-soft)">Riwayat<br><span class="text-xs font-normal" style="color:var(--muted)">monitoring</span></a>
      <a href="cetak_laporan.php" class="rounded-xl px-3 py-3 text-center transition hover:opacity-90" style="background:var(--primary-soft)">Cetak<br><span class="text-xs font-normal" style="color:var(--muted)">Laporan</span></a>
    </div>
  </div>
</div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
