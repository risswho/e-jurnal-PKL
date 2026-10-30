<?php
// siswa/profil.php — profil PKL sendiri (read-only)
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('siswa');
$id_siswa = $_SESSION['id_siswa'];
$stmt = mysqli_prepare($conn,"SELECT s.*, g.nama_guru, g.nip FROM siswa s LEFT JOIN guru g ON s.id_guru = g.id_guru WHERE s.id_siswa = ?"); mysqli_stmt_bind_param($stmt,"i",$id_siswa); mysqli_stmt_execute($stmt); $siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$title='Profil'; $base='../'; $role='siswa'; $nama=$siswa['nama_lengkap']??'Siswa'; $active='profil';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
?>
<div class="card mx-auto max-w-xl">
  <div class="flex items-center gap-4">
    <div class="flex h-16 w-16 items-center justify-center rounded-2xl text-xl font-extrabold text-white" style="background:var(--primary);font-family:'Poppins',sans-serif"><?= strtoupper(mb_substr($siswa['nama_lengkap'] ?? 'S',0,1)); ?></div>
    <div><h2 class="font-extrabold" style="font-family:'Poppins',sans-serif;font-size:1.2rem"><?= htmlspecialchars($siswa['nama_lengkap'] ?? 'Siswa'); ?></h2><p class="text-sm" style="color:var(--muted)"><?= htmlspecialchars($siswa['kelas'] ?? ''); ?> • <?= htmlspecialchars($siswa['jurusan'] ?? ''); ?></p></div>
  </div>
  <div class="mt-5 space-y-2 text-sm">
    <div class="flex justify-between gap-2 border-b pb-2" style="border-color:var(--border)"><span style="color:var(--muted)">NISN</span><span class="font-semibold"><?= htmlspecialchars($siswa['nisn'] ?? '—'); ?></span></div>
    <div class="flex justify-between gap-2 border-b pb-2" style="border-color:var(--border)"><span style="color:var(--muted)">Kelas</span><span class="font-semibold"><?= htmlspecialchars($siswa['kelas'] ?? '—'); ?></span></div>
    <div class="flex justify-between gap-2 border-b pb-2" style="border-color:var(--border)"><span style="color:var(--muted)">Jurusan</span><span class="font-semibold"><?= htmlspecialchars($siswa['jurusan'] ?? '—'); ?></span></div>
    <div class="flex justify-between gap-2 border-b pb-2" style="border-color:var(--border)"><span style="color:var(--muted)">DUDI</span><span class="text-right font-semibold"><?= htmlspecialchars($siswa['dudi_magang'] ?? '—'); ?></span></div>
    <div class="flex justify-between gap-2"><span style="color:var(--muted)">Guru Pembimbing</span><span class="text-right font-semibold"><?= htmlspecialchars($siswa['nama_guru'] ?? 'Belum ditentukan'); ?><?php if(!empty($siswa['nip'])): ?> (<?= htmlspecialchars($siswa['nip']); ?>)<?php endif; ?></span></div>
  </div>
  <p class="mt-4 rounded-xl p-3 text-xs leading-relaxed" style="background:var(--primary-soft);color:var(--primary-dark)">Perubahan data profil dilakukan oleh admin. Hubungi admin jika ada data yang salah.</p>
</div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
