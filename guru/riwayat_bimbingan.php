<?php
// guru/riwayat_bimbingan.php — Riwayat monitoring milik guru (strict: hanya bimbingan sendiri)
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('guru');
$id_guru = (int) ($_SESSION['id_guru'] ?? 0);
if ($id_guru <= 0) { header("Location: ../auth/login.php"); exit; }

$stmt = mysqli_prepare($conn, "SELECT b.id_monitoring, b.tanggal_monitoring, b.nama_dudi, b.keterangan, b.foto, s.id_siswa, s.nisn, s.nama_lengkap, s.kelas FROM monitoring b INNER JOIN siswa s ON b.id_siswa = s.id_siswa WHERE b.id_guru = ? AND s.id_guru = ? ORDER BY b.tanggal_monitoring DESC");
mysqli_stmt_bind_param($stmt, "ii", $id_guru, $id_guru);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total = $result ? mysqli_num_rows($result) : 0;

$title = 'Riwayat Monitoring'; $base = '../'; $role = 'guru'; $nama = $_SESSION['nama'] ?? 'Guru'; $active = 'monitoring';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/toast.php';
?>
<div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
  <div>
    <h2 class="font-extrabold tracking-tight" style="font-family:'Poppins',sans-serif;font-size:1.35rem">Riwayat Monitoring</h2>
    <p class="text-sm" style="color:var(--muted)">Dokumentasi monitoring untuk siswa bimbingan Anda. <?= $total; ?> data.</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="tambah_monitoring.php" class="btn-primary !py-2 !text-sm">+ Tambah Monitoring</a>
    <a href="bimbingan.php" class="btn-ghost !py-2 !text-sm">Kelola Monitoring</a>
  </div>
</div>

<div class="card mt-4 !p-0">
  <div class="border-b p-4" style="border-color:var(--border)">
    <input id="searchRiwayat" type="search" placeholder="Cari siswa / NISN / DUDI / keterangan..." class="form-input">
  </div>
  <div class="table-wrap">
    <table id="tblRiwayat" data-paginate="10" data-paginate-nav="#pgRiwayat" class="table-medical">
      <thead>
        <tr>
          <th>No</th>
          <th>Tanggal</th>
          <th>Siswa Bimbingan</th>
          <th>DUDI</th>
          <th>Keterangan</th>
          <th>Foto</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && mysqli_num_rows($result) > 0): $no = 1; while ($d = mysqli_fetch_assoc($result)): ?>
        <tr>
          <td style="color:var(--muted)"><?= $no++; ?></td>
          <td class="whitespace-nowrap text-xs font-bold"><?= htmlspecialchars($d['tanggal_monitoring']); ?></td>
          <td>
            <p class="font-semibold"><?= htmlspecialchars($d['nama_lengkap']); ?></p>
            <p class="text-xs" style="color:var(--muted)"><?= htmlspecialchars($d['nisn']); ?> &bull; <?= htmlspecialchars($d['kelas']); ?></p>
          </td>
          <td><?= htmlspecialchars($d['nama_dudi']); ?></td>
          <td class="max-w-[260px]"><p class="line-clamp-2"><?= htmlspecialchars($d['keterangan']); ?></p></td>
          <td>
            <?php if (!empty($d['foto'])): ?>
            <button onclick="openLightbox('../uploads/monitoring/<?= htmlspecialchars($d['foto']); ?>')">
              <img src="../uploads/monitoring/<?= htmlspecialchars($d['foto']); ?>" class="h-12 w-12 rounded-xl border object-cover" style="border-color:var(--border)" alt="Bukti">
            </button>
            <?php else: ?><span class="text-xs" style="color:var(--muted)">-</span><?php endif; ?>
          </td>
          <td><a href="detail_monitoring.php?id=<?= (int) $d['id_monitoring']; ?>" class="btn-ghost !px-2.5 !py-1.5 !text-xs">Detail</a></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="7" class="px-4 py-10 text-center" style="color:var(--muted)">Belum ada dokumentasi monitoring untuk siswa bimbingan Anda.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="flex items-center justify-between gap-2 border-t p-4" style="border-color:var(--border)">
    <p class="text-xs" style="color:var(--muted)"><?= $total; ?> data</p>
    <div id="pgRiwayat" class="flex flex-wrap gap-1.5"></div>
  </div>
</div>

<script>
(function(){
  var input = document.getElementById('searchRiwayat');
  function applyFilter(){
    var q = (input.value || '').toLowerCase();
    document.querySelectorAll('#tblRiwayat tbody tr').forEach(function(tr){
      var ok = !q || tr.textContent.toLowerCase().includes(q);
      tr.setAttribute('data-filtered', ok ? '1' : '0');
      if (!ok) tr.style.display = 'none';
    });
    var tbl = document.getElementById('tblRiwayat');
    tbl.setAttribute('data-page', '1');
    if (window.__ejPaginate) window.__ejPaginate();
  }
  input.addEventListener('input', applyFilter);
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
