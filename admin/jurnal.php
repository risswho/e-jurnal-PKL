<?php
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('admin');
    $query = "SELECT j.id_jurnal, j.tanggal_kegiatan, j.nama_dudi, j.pembimbing, j.kegiatan_magang, j.keterangan, j.foto, s.nisn, s.nama_lengkap, s.kelas, s.jurusan FROM jurnal j INNER JOIN siswa s ON j.id_siswa = s.id_siswa ORDER BY j.tanggal_kegiatan DESC";
    $result = mysqli_query($conn, $query);
$title='Jurnal'; $base='../'; $role='admin'; $nama=$_SESSION['nama']??'Admin'; $active='jurnal';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
$filterSiswa = mysqli_query($conn, "SELECT DISTINCT s.nama_lengkap FROM jurnal j INNER JOIN siswa s ON j.id_siswa = s.id_siswa ORDER BY s.nama_lengkap ASC");
$total = $result ? mysqli_num_rows($result) : 0;
?>
<div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
  <div><h2 class="font-extrabold tracking-tight" style="font-family:'Poppins',sans-serif;font-size:1.35rem">Data Jurnal Siswa</h2><p class="text-sm" style="color:var(--muted)">Monitoring seluruh jurnal PKL yang masuk. <?= $total; ?> data.</p></div>
  <div class="flex flex-wrap gap-2">
    <select id="filterSiswa" class="form-input !w-auto"><option value="">Semua siswa</option><?php if($filterSiswa): while($fs=mysqli_fetch_assoc($filterSiswa)): ?><option value="<?= htmlspecialchars($fs['nama_lengkap']); ?>"><?= htmlspecialchars($fs['nama_lengkap']); ?></option><?php endwhile; endif; ?></select>
  </div>
</div>
<div class="card mt-4 !p-0">
  <div class="border-b p-4" style="border-color:var(--border)"><input id="searchJurnal" type="search" placeholder="Cari siswa / NISN / kelas / DUDI / kegiatan..." class="form-input"></div>
  <div class="table-wrap"><table id="tblJurnal" data-paginate="10" data-paginate-nav="#pgJurnal" class="table-medical">
  <thead><tr><th>No</th><th>Tanggal</th><th>Kelas</th><th>Materi / Kegiatan</th><th>Guru / Pembimbing</th><th>Foto</th></tr></thead>
  <tbody>
  <?php if($result && mysqli_num_rows($result)>0): $no=1; while($j=mysqli_fetch_assoc($result)): ?>
  <tr data-siswa="<?= htmlspecialchars($j['nama_lengkap']); ?>">
    <td style="color:var(--muted)"><?= $no++; ?></td>
    <td class="whitespace-nowrap text-xs font-semibold"><?= htmlspecialchars($j['tanggal_kegiatan']); ?></td>
    <td><p class="font-semibold"><?= htmlspecialchars($j['nama_lengkap']); ?></p><p class="text-xs" style="color:var(--muted)"><?= htmlspecialchars($j['nisn']); ?> • <?= htmlspecialchars($j['kelas']); ?></p></td>
    <td class="max-w-[260px]"><p class="line-clamp-2"><?= htmlspecialchars($j['kegiatan_magang']); ?></p><p class="truncate text-xs" style="color:var(--muted)"><?= htmlspecialchars($j['nama_dudi']); ?></p></td>
    <td class="max-w-[160px] truncate"><?= htmlspecialchars($j['pembimbing']); ?></td>
    <td><?php if(!empty($j['foto'])): ?><button onclick="openLightbox('../uploads/jurnal/<?= htmlspecialchars($j['foto']); ?>')"><img src="../uploads/jurnal/<?= htmlspecialchars($j['foto']); ?>" alt="Bukti" class="h-12 w-12 rounded-xl border object-cover" style="border-color:var(--border)"></button><?php else: ?><span class="text-xs" style="color:var(--muted)">—</span><?php endif; ?></td>
  </tr>
  <?php endwhile; else: ?><tr><td colspan="6" class="px-4 py-10 text-center" style="color:var(--muted)">Belum ada jurnal siswa.</td></tr><?php endif; ?>
  </tbody></table></div>
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
<?php require_once __DIR__.'/../includes/footer.php'; ?>
