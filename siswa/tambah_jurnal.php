<?php
session_start(); require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('siswa');
$id_siswa = $_SESSION['id_siswa'];
$stmt = mysqli_prepare($conn,"SELECT s.*, g.nama_guru, g.nip FROM siswa s LEFT JOIN guru g ON s.id_guru = g.id_guru WHERE s.id_siswa = ?"); mysqli_stmt_bind_param($stmt,"i",$id_siswa); mysqli_stmt_execute($stmt); $siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$title='Jurnal'; $base='../'; $role='siswa'; $nama=$siswa['nama_lengkap']??'Siswa'; $active='tambah';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
$err = $_GET['error'] ?? '';
?>
<a href="dashboard.php" class="text-sm font-semibold hover:underline" style="color:var(--muted)">← Kembali ke Dashboard</a>
<div class="mt-3 grid gap-4 lg:grid-cols-3">
  <div class="card h-fit">
    <h3 class="font-bold" style="font-family:'Poppins',sans-serif">Profil PKL</h3>
    <div class="mt-3 space-y-2 text-sm">
      <div class="flex justify-between gap-2"><span style="color:var(--muted)">Nama</span><span class="text-right font-semibold"><?= htmlspecialchars($siswa['nama_lengkap']); ?></span></div>
      <div class="flex justify-between gap-2"><span style="color:var(--muted)">Kelas</span><span class="font-semibold"><?= htmlspecialchars($siswa['kelas']); ?></span></div>
      <div class="flex justify-between gap-2"><span style="color:var(--muted)">Jurusan</span><span class="text-right font-semibold"><?= htmlspecialchars($siswa['jurusan']); ?></span></div>
      <div class="flex justify-between gap-2"><span style="color:var(--muted)">DUDI</span><span class="text-right font-semibold"><?= htmlspecialchars($siswa['dudi_magang']); ?></span></div>
      <div class="flex justify-between gap-2"><span style="color:var(--muted)">Pembimbing</span><span class="text-right font-semibold"><?= htmlspecialchars($siswa['nama_guru'] ?? 'Belum ditentukan'); ?></span></div>
    </div>
    <div class="mt-4 rounded-xl p-3 text-xs leading-relaxed" style="background:var(--primary-soft);color:var(--primary-dark)">Isi jurnal setiap hari kegiatan. Foto bukti <strong>JPG/PNG ≤ 20 MB</strong> sebagai dokumentasi kegiatan Anda.</div>
  </div>
  <div class="card lg:col-span-2">
    <h2 class="font-extrabold" style="font-family:'Poppins',sans-serif;font-size:1.15rem">Isi Jurnal Magang</h2>
    <p class="text-sm" style="color:var(--muted)">Lengkapi materi dan kegiatan pembelajaran hari ini (tanggal tercatat otomatis sesuai waktu penginputan).</p>
    <?php if($err): ?><div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-sm font-medium text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300"><?= htmlspecialchars($err); ?></div><?php endif; ?>
    <form action="proses_tambah.php" method="POST" enctype="multipart/form-data" class="mt-4 grid gap-4 sm:grid-cols-2">
      <div class="sm:col-span-2"><label class="form-label" for="nama_dudi">Kelas / Nama DUDI</label><input id="nama_dudi" type="text" name="nama_dudi" required value="<?= htmlspecialchars($siswa['dudi_magang']); ?>" class="form-input"></div>
      <div class="sm:col-span-2"><label class="form-label" for="pembimbing">Guru / Pembimbing Lapangan</label><input id="pembimbing" type="text" name="pembimbing" required placeholder="Nama pembimbing di tempat magang" class="form-input"></div>
      <div class="sm:col-span-2"><label class="form-label" for="kegiatan_magang">Materi &amp; Kegiatan Pembelajaran</label><textarea id="kegiatan_magang" name="kegiatan_magang" rows="4" required placeholder="Contoh: Membuat layout web company profile dengan HTML & Tailwind..." class="form-input"></textarea></div>
      <div class="sm:col-span-2"><label class="form-label" for="keterangan">Catatan <span class="font-normal" style="color:var(--muted)">(opsional)</span></label><textarea id="keterangan" name="keterangan" rows="3" placeholder="Kendala, capaian, alat yang dipakai..." class="form-input"></textarea></div>
      <div class="sm:col-span-2"><label class="form-label">Foto Kegiatan (png/jpg)</label>
        <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-4 py-6 text-center transition" style="border-color:var(--border);background:var(--primary-soft)">
          <img id="prevFoto" src="" class="mb-2 hidden h-28 w-28 rounded-xl object-cover" alt="">
          <span class="text-sm font-semibold">Klik / ketuk untuk pilih foto</span><span class="text-xs" style="color:var(--muted)">Format JPG, JPEG, PNG • maks 20 MB</span>
          <input type="file" name="foto" accept=".jpg,.jpeg,.png" data-img-preview="#prevFoto" class="hidden">
        </label>
      </div>
      <div class="flex gap-2 sm:col-span-2"><a href="dashboard.php" class="btn-ghost flex-1">Batal</a><button class="btn-primary flex-1">Simpan Jurnal</button></div>
    </form>
  </div>
</div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
