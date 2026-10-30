<?php
// guru/profil.php — profil guru (read-only)
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('guru');
$id_guru = $_SESSION['id_guru'];
$stmt = mysqli_prepare($conn,"SELECT id_guru, nip, nama_guru, created_at FROM guru WHERE id_guru = ?"); mysqli_stmt_bind_param($stmt,"i",$id_guru); mysqli_stmt_execute($stmt); $guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$stmt2 = mysqli_prepare($conn,"SELECT COUNT(*) AS total FROM siswa WHERE id_guru = ?"); mysqli_stmt_bind_param($stmt2,"i",$id_guru); mysqli_stmt_execute($stmt2); $total_siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2))['total'] ?? 0;
$title='Profil'; $base='../'; $role='guru'; $nama=$guru['nama_guru']??'Guru'; $active='profil';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
?>
<div class="card mx-auto max-w-xl">
  <div class="flex items-center gap-4">
    <div class="flex h-16 w-16 items-center justify-center rounded-2xl text-xl font-extrabold text-white" style="background:var(--primary);font-family:'Poppins',sans-serif"><?= ej_avatar_initial($guru['nama_guru'] ?? 'G'); ?></div>
    <div><h2 class="font-extrabold" style="font-family:'Poppins',sans-serif;font-size:1.2rem"><?= htmlspecialchars($guru['nama_guru'] ?? 'Guru'); ?></h2><p class="text-sm" style="color:var(--muted)">Guru Pembimbing • <?= $total_siswa; ?> siswa monitoring</p></div>
  </div>
  <div class="mt-5 space-y-2 text-sm">
    <div class="flex justify-between gap-2 border-b pb-2" style="border-color:var(--border)"><span style="color:var(--muted)">NIP</span><span class="font-semibold"><?= htmlspecialchars($guru['nip'] ?? '—'); ?></span></div>
    <div class="flex justify-between gap-2 border-b pb-2" style="border-color:var(--border)"><span style="color:var(--muted)">Nama</span><span class="text-right font-semibold"><?= htmlspecialchars($guru['nama_guru'] ?? '—'); ?></span></div>
    <div class="flex justify-between gap-2"><span style="color:var(--muted)">Terdaftar</span><span class="font-semibold"><?= htmlspecialchars($guru['created_at'] ?? '—'); ?></span></div>
  </div>
  <p class="mt-4 rounded-xl p-3 text-xs leading-relaxed" style="background:var(--primary-soft);color:var(--primary-dark)">Perubahan data akun dilakukan oleh admin.</p>
</div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
