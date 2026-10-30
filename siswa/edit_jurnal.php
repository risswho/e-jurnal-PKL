<?php
session_start(); require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('siswa');
$id_siswa = $_SESSION['id_siswa']; $id_jurnal = $_GET['id'] ?? 0;
$stmt = mysqli_prepare($conn,"SELECT * FROM jurnal WHERE id_jurnal = ? AND id_siswa = ?"); mysqli_stmt_bind_param($stmt,"ii",$id_jurnal,$id_siswa); mysqli_stmt_execute($stmt); $jurnal = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$jurnal) abort("Jurnal tidak ditemukan", "Data jurnal tidak ada atau bukan milik Anda.", "dashboard.php");
$title='Jurnal'; $base='../'; $role='siswa'; $nama=$_SESSION['nama']??'Siswa'; $active='dashboard';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
$err = $_GET['error'] ?? '';
?>
<a href="dashboard.php" class="text-sm font-semibold hover:underline" style="color:var(--muted)">← Kembali</a>
<div class="card mx-auto mt-3 max-w-2xl">
  <h2 class="font-extrabold" style="font-family:'Poppins',sans-serif;font-size:1.15rem">Edit Jurnal <span class="text-sm font-normal" style="color:var(--muted)"><?= htmlspecialchars($jurnal['tanggal_kegiatan']); ?></span></h2>
  <?php if($err): ?><div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-sm font-medium text-rose-700"><?= htmlspecialchars($err); ?></div><?php endif; ?>
  <form action="proses_edit.php" method="POST" enctype="multipart/form-data" class="mt-4 grid gap-4 sm:grid-cols-2">
    <input type="hidden" name="id_jurnal" value="<?= $jurnal['id_jurnal']; ?>">
    <div><label class="form-label">Tanggal</label><input type="date" name="tanggal_kegiatan" required value="<?= htmlspecialchars($jurnal['tanggal_kegiatan']); ?>" class="form-input"></div>
    <div><label class="form-label">Kelas / Nama DUDI</label><input type="text" name="nama_dudi" required value="<?= htmlspecialchars($jurnal['nama_dudi']); ?>" class="form-input"></div>
    <div class="sm:col-span-2"><label class="form-label">Guru / Pembimbing</label><input type="text" name="pembimbing" required value="<?= htmlspecialchars($jurnal['pembimbing']); ?>" class="form-input"></div>
    <div class="sm:col-span-2"><label class="form-label">Materi &amp; Kegiatan Pembelajaran</label><textarea name="kegiatan_magang" rows="4" required class="form-input"><?= htmlspecialchars($jurnal['kegiatan_magang']); ?></textarea></div>
    <div class="sm:col-span-2"><label class="form-label">Catatan</label><textarea name="keterangan" rows="3" class="form-input"><?= htmlspecialchars($jurnal['keterangan']); ?></textarea></div>
    <div class="sm:col-span-2"><label class="form-label">Foto</label>
      <div class="flex flex-col gap-3 rounded-xl p-4 sm:flex-row sm:items-center" style="background:var(--primary-soft)">
        <?php if(!empty($jurnal['foto'])): ?><button type="button" onclick="openLightbox('../uploads/jurnal/<?= htmlspecialchars($jurnal['foto']); ?>')"><img src="../uploads/jurnal/<?= htmlspecialchars($jurnal['foto']); ?>" class="h-20 w-20 rounded-xl border object-cover" style="border-color:var(--border)" alt="Foto saat ini"></button><p class="text-xs" style="color:var(--muted)">Foto saat ini — klik untuk perbesar.<br>Kosongkan input di samping jika tidak ingin mengganti.</p><?php else: ?><p class="text-xs" style="color:var(--muted)">Belum ada foto.</p><?php endif; ?>
        <label class="flex-1 cursor-pointer rounded-xl border-2 border-dashed px-4 py-4 text-center text-sm" style="border-color:var(--border);background:var(--surface)"><span class="font-semibold">Ganti foto (opsional)</span><br><span class="text-xs" style="color:var(--muted)">JPG/PNG ≤ 20MB</span><input type="file" name="foto" accept=".jpg,.jpeg,.png" class="hidden"></label>
      </div>
    </div>
    <div class="flex gap-2 sm:col-span-2"><a href="dashboard.php" class="btn-ghost flex-1">Batal</a><button class="btn-primary flex-1">Simpan Perubahan</button></div>
  </form>
</div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
