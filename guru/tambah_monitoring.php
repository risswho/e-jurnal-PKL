<?php
session_start(); require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('guru');
$id_guru = (int) ($_SESSION['id_guru'] ?? 0);
if ($id_guru <= 0) { header("Location: ../auth/login.php"); exit; }
$title='Monitoring'; $base='../'; $role='guru'; $nama=$_SESSION['nama']??'Guru'; $active='monitoring';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
$err = $_GET['error'] ?? '';
?>
<div class="card mx-auto max-w-2xl">
  <h2 class="font-extrabold" style="font-family:'Poppins',sans-serif;font-size:1.15rem">Tambah Dokumentasi Monitoring</h2>
  <p class="text-sm" style="color:var(--muted)">Catat kunjungan pembinaan ke siswa PKL (foto maks 20 MB, JPG/PNG).</p>
  <?php if($err): ?><div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-sm font-medium text-rose-700"><?= htmlspecialchars($err); ?></div><?php endif; ?>
  <form action="proses_monitoring.php" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
    <div><label class="form-label">DUDI</label>
      <select name="dudi" required class="form-input">
        <option value="">-- Pilih DUDI --</option>
        <?php
        $dudi_stmt = mysqli_prepare($conn, "SELECT DISTINCT dudi_magang FROM siswa WHERE id_guru = ? ORDER BY dudi_magang ASC");
        mysqli_stmt_bind_param($dudi_stmt, "i", $id_guru);
        mysqli_stmt_execute($dudi_stmt);
        $dudi_list = mysqli_stmt_get_result($dudi_stmt);
        while($d = mysqli_fetch_assoc($dudi_list)): ?>
          <option value="<?= htmlspecialchars($d['dudi_magang']); ?>"><?= htmlspecialchars($d['dudi_magang']); ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div><label class="form-label">Tanggal Monitoring</label><input type="date" name="tanggal_monitoring" required value="<?= date('Y-m-d'); ?>" class="form-input"></div>
    <div><label class="form-label">Keterangan / Hasil Monitoring</label><textarea name="keterangan" rows="4" required placeholder="Contoh: monitoring progres minggu ke-3, cek logbook, arahan K3..." class="form-input"></textarea></div>
    <div><label class="form-label">Foto Dokumentasi (opsional)</label>
      <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-4 py-6 text-center" style="border-color:var(--border);background:var(--primary-soft)">
        <img id="prevMon" src="" alt="" class="mb-2 hidden h-24 w-24 rounded-xl object-cover">
        <span class="text-sm font-semibold">Klik untuk pilih foto</span><span class="text-xs" style="color:var(--muted)">JPG / JPEG / PNG • maks 20 MB</span>
        <input type="file" name="foto" accept=".jpg,.jpeg,.png" data-img-preview="#prevMon" class="hidden">
      </label>
    </div>
    <div class="flex gap-2"><a href="riwayat_bimbingan.php" class="btn-ghost flex-1">Batal</a><button class="btn-primary flex-1">Simpan Monitoring</button></div>
  </form>
</div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>