<?php
// admin/profil.php — profil akun admin (read-only)
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('admin');
$id = $_SESSION['id_admin'];
$stmt = mysqli_prepare($conn,"SELECT id_admin, username, created_at FROM admin WHERE id_admin = ?"); mysqli_stmt_bind_param($stmt,"i",$id); mysqli_stmt_execute($stmt); $admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$title='Profil'; $base='../'; $role='admin'; $nama=$_SESSION['nama']??'Admin'; $active='profil';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
?>
<div class="card mx-auto max-w-xl">
  <div class="flex items-center gap-4">
    <div class="flex h-16 w-16 items-center justify-center rounded-2xl text-xl font-extrabold text-white" style="background:var(--primary);font-family:'Poppins',sans-serif"><?= ej_avatar_initial($admin['username'] ?? 'A'); ?></div>
    <div><h2 class="font-extrabold" style="font-family:'Poppins',sans-serif;font-size:1.2rem"><?= htmlspecialchars($admin['username'] ?? 'Admin'); ?></h2><p class="text-sm" style="color:var(--muted)">Administrator</p></div>
  </div>
  <div class="mt-5 space-y-2 text-sm">
    <div class="flex justify-between gap-2 border-b pb-2" style="border-color:var(--border)"><span style="color:var(--muted)">Username</span><span class="font-semibold"><?= htmlspecialchars($admin['username'] ?? '—'); ?></span></div>
    <div class="flex justify-between gap-2 border-b pb-2" style="border-color:var(--border)"><span style="color:var(--muted)">Terdaftar</span><span class="font-semibold"><?= htmlspecialchars($admin['created_at'] ?? '—'); ?></span></div>
    <div class="flex justify-between gap-2"><span style="color:var(--muted)">Peran</span><span class="font-semibold">Admin (kelola penuh)</span></div>
  </div>
  <p class="mt-4 rounded-xl p-3 text-xs leading-relaxed" style="background:var(--primary-soft);color:var(--primary-dark)">Perubahan akun admin dilakukan di halaman Kelola Admin.</p>
  <a href="admin.php" class="btn-primary mt-3 w-full">Kelola Admin</a>
</div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
