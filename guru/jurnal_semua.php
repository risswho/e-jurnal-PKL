<?php
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('guru');
$id_guru = $_SESSION['id_guru'];

$cols  = "j.id_jurnal, j.tanggal_kegiatan, j.nama_dudi, j.pembimbing, j.kegiatan_magang, j.keterangan, j.foto, s.id_siswa, s.nisn, s.nama_lengkap, s.kelas, s.jurusan";
$stmt  = mysqli_prepare($conn,
    "SELECT $cols
     FROM jurnal j
     INNER JOIN siswa s ON j.id_siswa = s.id_siswa
     WHERE s.id_guru = ?
     ORDER BY j.tanggal_kegiatan DESC");
mysqli_stmt_bind_param($stmt, "i", $id_guru);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$stmt_fs = mysqli_prepare($conn, "SELECT DISTINCT s.nama_lengkap FROM jurnal j INNER JOIN siswa s ON j.id_siswa = s.id_siswa WHERE s.id_guru = ? ORDER BY s.nama_lengkap ASC");
mysqli_stmt_bind_param($stmt_fs, "i", $id_guru);
mysqli_stmt_execute($stmt_fs);
$filterSiswa = mysqli_stmt_get_result($stmt_fs);
$total = $result ? mysqli_num_rows($result) : 0;

$title = 'Jurnal'; $base = '../'; $role = 'guru'; $nama = $_SESSION['nama'] ?? 'Guru'; $active = 'jurnal_semua';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/toast.php';
?>
<div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
  <div>
    <h2 class="font-extrabold tracking-tight" style="font-family:'Poppins',sans-serif;font-size:1.35rem">Jurnal Siswa monitoring</h2>
    <p class="text-sm" style="color:var(--muted)">Jurnal harian siswa yang Anda bimbing. <?= $total; ?> data.</p>
  </div>
  <div class="flex flex-wrap gap-2">
  <select id="filterSiswa" class="form-input !w-auto">
    <option value="">Semua siswa</option>
    <?php while ($fs = mysqli_fetch_assoc($filterSiswa)): ?>
    <option value="<?= htmlspecialchars($fs['nama_lengkap']); ?>"><?= htmlspecialchars($fs['nama_lengkap']); ?></option>
    <?php endwhile; ?>
  </select>
  </div>
</div>

<div class="card mt-4 !p-0">
  <div class="border-b p-4" style="border-color:var(--border)">
    <input id="searchJurnal" type="search" placeholder="Cari siswa / NISN / kegiatan / DUDI..." class="form-input">
  </div>
  <div class="table-wrap">
    <table id="tblJurnal" data-paginate="10" data-paginate-nav="#pgJurnal" class="table-medical">
      <thead>
        <tr>
          <th>No</th>
          <th>Tanggal</th>
          <th>Kelas</th>
          <th>Materi / Kegiatan</th>
          <th>Guru / Pembimbing</th>
          <th>Foto</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && mysqli_num_rows($result) > 0): $no=1; while ($j = mysqli_fetch_assoc($result)): ?>
        <tr data-siswa="<?= htmlspecialchars($j['nama_lengkap']); ?>">
          <td style="color:var(--muted)"><?= $no++; ?></td>
          <td class="whitespace-nowrap text-xs font-bold"><?= htmlspecialchars($j['tanggal_kegiatan']); ?></td>
          <td>
            <p class="font-semibold"><?= htmlspecialchars($j['nama_lengkap']); ?></p>
            <p class="text-xs" style="color:var(--muted)"><?= htmlspecialchars($j['nisn']); ?> &bull; <?= htmlspecialchars($j['kelas']); ?></p>
          </td>
          <td class="max-w-[260px]">
            <p class="line-clamp-2"><?= htmlspecialchars($j['kegiatan_magang']); ?></p>
            <?php if (!empty($j['keterangan'])): ?>
            <p class="mt-1 text-xs line-clamp-1" style="color:var(--muted)"><?= htmlspecialchars($j['keterangan']); ?></p>
            <?php endif; ?>
          </td>
          <td>
            <p class="font-semibold"><?= htmlspecialchars($j['nama_dudi']); ?></p>
            <p class="text-xs" style="color:var(--muted)"><?= htmlspecialchars($j['pembimbing']); ?></p>
          </td>
          <td>
            <?php if (!empty($j['foto'])): ?>
            <button onclick="openLightbox('../uploads/jurnal/<?= htmlspecialchars($j['foto']); ?>')">
              <img src="../uploads/jurnal/<?= htmlspecialchars($j['foto']); ?>"
                   class="h-12 w-12 rounded-xl border object-cover" style="border-color:var(--border)" alt="Bukti">
            </button>
            <?php else: ?><span class="text-xs" style="color:var(--muted)">-</span><?php endif; ?>
          </td>
          <td>
            <a href="jurnal_siswa.php?id=<?= $j['id_siswa']; ?>"
               class="btn-ghost !px-2.5 !py-1.5 !text-xs">
              Lihat Semua
            </a>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="7" class="px-4 py-10 text-center" style="color:var(--muted)">Belum ada jurnal dari siswa monitoring Anda.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="flex items-center justify-between gap-2 border-t p-4" style="border-color:var(--border)">
    <p class="text-xs" style="color:var(--muted)"><?= $total; ?> data</p>
    <div id="pgJurnal" class="flex flex-wrap gap-1.5"></div>
  </div>
</div>

<script>
(function(){
  function applyJurnalFilter(){
    var q=(document.getElementById('searchJurnal').value||'').toLowerCase();
    var sw=document.getElementById('filterSiswa').value;
    document.querySelectorAll('#tblJurnal tbody tr').forEach(function(tr){
      var okText=!q||tr.textContent.toLowerCase().includes(q);
      var okSiswa=!sw||tr.dataset.siswa===sw;
      var ok=okText&&okSiswa;
      tr.setAttribute('data-filtered', ok?'1':'0');
      if(!ok) tr.style.display='none';
    });
    var tbl=document.getElementById('tblJurnal'); tbl.setAttribute('data-page','1');
    if(window.__ejPaginate) window.__ejPaginate();
  }
  document.getElementById('searchJurnal').addEventListener('input',applyJurnalFilter);
  document.getElementById('filterSiswa').addEventListener('change',applyJurnalFilter);
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
