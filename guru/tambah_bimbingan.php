<?php
session_start(); require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('guru');
$id_guru = (int) ($_SESSION['id_guru'] ?? 0);
if ($id_guru <= 0) { header("Location: ../auth/login.php"); exit; }
$stmt_list = mysqli_prepare($conn,"SELECT id_siswa, nama_lengkap, nisn, dudi_magang FROM siswa WHERE id_guru = ? ORDER BY nama_lengkap ASC"); mysqli_stmt_bind_param($stmt_list,"i",$id_guru); mysqli_stmt_execute($stmt_list); $list = mysqli_stmt_get_result($stmt_list);
$preselect = $_GET['id'] ?? '';
$selected_siswa = null;
if($preselect!==''){ $s0=mysqli_prepare($conn,"SELECT * FROM siswa WHERE id_siswa=? AND id_guru=?"); mysqli_stmt_bind_param($s0,"ii",$preselect,$id_guru); mysqli_stmt_execute($s0); $selected_siswa=mysqli_fetch_assoc(mysqli_stmt_get_result($s0)); }
$title='monitoring'; $base='../'; $role='guru'; $nama=$_SESSION['nama']??'Guru'; $active='monitoring';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
$err = $_GET['error'] ?? '';
?>
<div class="card mx-auto max-w-2xl">
  <h2 class="font-extrabold" style="font-family:'Poppins',sans-serif;font-size:1.15rem">Tambah Dokumentasi monitoring</h2>
  <p class="text-sm" style="color:var(--muted)">Catat kunjungan / pembinaan ke siswa PKL (foto maks 20 MB, JPG/PNG).</p>
  <?php if($err): ?><div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-sm font-medium text-rose-700"><?= htmlspecialchars($err); ?></div><?php endif; ?>
  <form action="proses_monitoring.php" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
    <div class="grid gap-4 sm:grid-cols-2">
      <div><label class="form-label">DUDI</label>
        <select name="dudi" required class="form-input">
          <option value="">-- Pilih DUDI --</option>
          <?php
          $dudi_stmt = mysqli_prepare($conn, "SELECT DISTINCT dudi_magang FROM siswa WHERE id_guru = ? ORDER BY dudi_magang ASC");
          mysqli_stmt_bind_param($dudi_stmt, "i", $id_guru);
          mysqli_stmt_execute($dudi_stmt);
          $dudi_list = mysqli_stmt_get_result($dudi_stmt);
          while($d = mysqli_fetch_assoc($dudi_list)): ?>
            <option value="<?= htmlspecialchars($d['dudi_magang']); ?>" <?= (($preselect_dudi??'')===$d['dudi_magang'])?'selected':''; ?>><?= htmlspecialchars($d['dudi_magang']); ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div><label class="form-label">Siswa</label>
        <select name="id_siswa" required class="form-input"><option value="">-- Pilih Siswa --</option>
        <?php
        $dudi_chosen = $_POST['dudi'] ?? ($preselect_dudi ?? '');
        if($dudi_chosen !== ''){
          $siswa_stmt = mysqli_prepare($conn, "SELECT id_siswa, nama_lengkap, nisn FROM siswa WHERE id_guru = ? AND dudi_magang = ? ORDER BY nama_lengkap ASC");
          mysqli_stmt_bind_param($siswa_stmt, "is", $id_guru, $dudi_chosen);
        } else {
          $siswa_stmt = mysqli_prepare($conn, "SELECT id_siswa, nama_lengkap, nisn FROM siswa WHERE id_guru = ? ORDER BY nama_lengkap ASC");
          mysqli_stmt_bind_param($siswa_stmt, "i", $id_guru);
        }
        mysqli_stmt_execute($siswa_stmt);
        $siswa_res = mysqli_stmt_get_result($siswa_stmt);
        while($s=mysqli_fetch_assoc($siswa_res)): ?>
          <option value="<?= $s['id_siswa']; ?>" <?= ((string)$preselect===(string)$s['id_siswa'])?'selected':''; ?>><?= htmlspecialchars($s['nama_lengkap']); ?> • <?= htmlspecialchars($s['nisn']); ?></option>
        <?php endwhile; ?>
        <?php if(mysqli_num_rows($siswa_res) === 0): ?><p class="form-hint">Tidak ada siswa di DUDI ini.</p><?php endif; ?>
        </select>
      </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
      <div><label class="form-label">Tanggal monitoring</label><input type="date" name="tanggal_monitoring" required value="<?= date('Y-m-d'); ?>" class="form-input"></div>
      <div><label class="form-label">Nama DUDI</label><input type="text" name="nama_dudi" required value="<?= htmlspecialchars($dudi_chosen??$selected_siswa['dudi_magang']??''); ?>" placeholder="Tempat magang" class="form-input"></div>
    </div>
    <div><label class="form-label">Keterangan / Hasil monitoring</label><textarea name="keterangan" rows="4" required placeholder="Contoh: monitoring progres minggu ke-3, cek logbook, arahan K3..." class="form-input"></textarea></div>
    <div><label class="form-label">Foto Dokumentasi (opsional)</label>
      <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-4 py-6 text-center" style="border-color:var(--border);background:var(--primary-soft)">
        <img id="prevBim" src="" alt="" class="mb-2 hidden h-24 w-24 rounded-xl object-cover">
        <span class="text-sm font-semibold">Klik untuk pilih foto</span><span class="text-xs" style="color:var(--muted)">JPG / JPEG / PNG • maks 20 MB</span>
        <input type="file" name="foto" accept=".jpg,.jpeg,.png" data-img-preview="#prevBim" class="hidden">
      </label>
    </div>
    <div class="flex gap-2"><a href="riwayat_bimbingan.php" class="btn-ghost flex-1">Batal</a><button class="btn-primary flex-1">Simpan monitoring</button></div>
  </form>
</div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
