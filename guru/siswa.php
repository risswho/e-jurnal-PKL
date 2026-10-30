<?php
// guru/siswa.php — Daftar siswa bimbingan (strict: hanya id_guru milik sendiri)
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('guru');
$id_guru = (int) ($_SESSION['id_guru'] ?? 0);
if ($id_guru <= 0) { header("Location: ../auth/login.php"); exit; }

$stmt = mysqli_prepare($conn, "SELECT id_siswa, nisn, nama_lengkap, kelas, jurusan, dudi_magang FROM siswa WHERE id_guru = ? ORDER BY nama_lengkap ASC");
mysqli_stmt_bind_param($stmt, "i", $id_guru);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total = $result ? mysqli_num_rows($result) : 0;

$title = 'Siswa Bimbingan'; $base = '../'; $role = 'guru'; $nama = $_SESSION['nama'] ?? 'Guru'; $active = 'siswa';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/toast.php';
?>
<div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
  <div>
    <h2 class="font-extrabold tracking-tight" style="font-family:'Poppins',sans-serif;font-size:1.35rem">Siswa Bimbingan</h2>
    <p class="text-sm" style="color:var(--muted)">Hanya siswa yang dibimbing oleh Anda. <?= $total; ?> siswa.</p>
  </div>
</div>

<div class="card mt-4 !p-0">
  <div class="border-b p-4" style="border-color:var(--border)">
    <input id="searchSiswaBim" type="search" placeholder="Cari nama / NISN / kelas / DUDI..." class="form-input">
  </div>
  <div class="table-wrap">
    <table id="tblSiswaBim" data-paginate="10" data-paginate-nav="#pgSiswaBim" class="table-medical">
      <thead>
        <tr>
          <th>No</th>
          <th>Siswa</th>
          <th>Kelas / Jurusan</th>
          <th>DUDI</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && mysqli_num_rows($result) > 0): $no = 1; while ($s = mysqli_fetch_assoc($result)): ?>
        <tr>
          <td style="color:var(--muted)"><?= $no++; ?></td>
          <td>
            <p class="font-semibold"><?= htmlspecialchars($s['nama_lengkap']); ?></p>
            <p class="text-xs" style="color:var(--muted)"><?= htmlspecialchars($s['nisn']); ?></p>
          </td>
          <td>
            <p class="font-semibold"><?= htmlspecialchars($s['kelas']); ?></p>
            <p class="text-xs" style="color:var(--muted)"><?= htmlspecialchars($s['jurusan']); ?></p>
          </td>
          <td><?= htmlspecialchars($s['dudi_magang']); ?></td>
          <td>
            <div class="flex flex-wrap gap-1.5">
              <a href="jurnal_siswa.php?id=<?= (int) $s['id_siswa']; ?>" class="btn-ghost !px-2.5 !py-1.5 !text-xs">Lihat Jurnal</a>
              <a href="tambah_monitoring.php?id=<?= (int) $s['id_siswa']; ?>" class="btn-ghost !px-2.5 !py-1.5 !text-xs">+ Monitoring</a>
            </div>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="5" class="px-4 py-10 text-center" style="color:var(--muted)">Belum ada siswa bimbingan. Minta admin untuk memasangkan siswa ke akun Anda.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="flex items-center justify-between gap-2 border-t p-4" style="border-color:var(--border)">
    <p class="text-xs" style="color:var(--muted)"><?= $total; ?> siswa bimbingan</p>
    <div id="pgSiswaBim" class="flex flex-wrap gap-1.5"></div>
  </div>
</div>

<script>
(function(){
  var input = document.getElementById('searchSiswaBim');
  function applyFilter(){
    var q = (input.value || '').toLowerCase();
    document.querySelectorAll('#tblSiswaBim tbody tr').forEach(function(tr){
      var ok = !q || tr.textContent.toLowerCase().includes(q);
      tr.setAttribute('data-filtered', ok ? '1' : '0');
      if (!ok) tr.style.display = 'none';
    });
    var tbl = document.getElementById('tblSiswaBim');
    tbl.setAttribute('data-page', '1');
    if (window.__ejPaginate) window.__ejPaginate();
  }
  input.addEventListener('input', applyFilter);
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
