<?php
session_start(); require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('siswa');
$id_siswa = $_SESSION['id_siswa'];
$stmt = mysqli_prepare($conn,"SELECT s.*, g.nama_guru, g.nip FROM siswa s LEFT JOIN guru g ON s.id_guru = g.id_guru WHERE s.id_siswa = ?"); mysqli_stmt_bind_param($stmt,"i",$id_siswa); mysqli_stmt_execute($stmt); $siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$stmt_j = mysqli_prepare($conn,"SELECT * FROM jurnal WHERE id_siswa = ? ORDER BY tanggal_kegiatan DESC"); mysqli_stmt_bind_param($stmt_j,"i",$id_siswa); mysqli_stmt_execute($stmt_j); $result_jurnal = mysqli_stmt_get_result($stmt_j);
$rows=[]; $tot=0; $today=0; $bulan=0; $bln=(int)date('m'); $thn=(int)date('Y');
while($r=mysqli_fetch_assoc($result_jurnal)){ $rows[]=$r; $tot++; if(($r['tanggal_kegiatan']??'')===date('Y-m-d')) $today++; $ts=strtotime($r['tanggal_kegiatan']??''); if($ts&&(int)date('m',$ts)===$bln&&(int)date('Y',$ts)===$thn) $bulan++; }
$title='Dashboard'; $base='../'; $role='siswa'; $nama=$siswa['nama_lengkap']??'Siswa'; $active='dashboard';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
?>
<div class="hero-medical">
  <h2 class="text-xl font-extrabold sm:text-2xl" style="font-family:'Poppins',sans-serif">Selamat Datang, <?= htmlspecialchars($siswa['nama_lengkap']); ?></h2>
  <p class="sub mt-1 text-sm">Kelola jurnal dan aktivitas pembelajaran dengan mudah.</p>
  <div class="no-print mt-4"><a href="tambah_jurnal.php" class="rounded-xl bg-white px-4 py-2.5 text-sm font-bold hover:bg-teal-50" style="color:var(--primary-dark)">Isi Jurnal</a></div>
</div>
<div class="card mt-4">
  <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
    <div class="flex h-14 w-14 items-center justify-center rounded-2xl text-xl font-extrabold text-white" style="background:var(--primary);font-family:'Poppins',sans-serif"><?= strtoupper(mb_substr($siswa['nama_lengkap']??'S',0,1)); ?></div>
    <div class="min-w-0 flex-1">
      <h2 class="truncate font-extrabold" style="font-family:'Poppins',sans-serif"><?= htmlspecialchars($siswa['nama_lengkap']); ?></h2>
      <p class="text-sm" style="color:var(--muted)"><?= htmlspecialchars($siswa['kelas']); ?> • <?= htmlspecialchars($siswa['jurusan']); ?> • <?= htmlspecialchars($siswa['dudi_magang']); ?></p>
      <p class="mt-1 text-xs" style="color:var(--muted)">Guru pembimbing: <strong><?= htmlspecialchars($siswa['nama_guru'] ?? 'Belum ditentukan'); ?></strong><?php if (!empty($siswa['nip'])): ?> (<?= htmlspecialchars($siswa['nip']); ?>)<?php endif; ?></p>
    </div>
  </div>
</div>
<div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-2">
  <div class="card stat-card"><p class="stat-label">TOTAL JURNAL</p><p class="stat-number"><?= $tot; ?></p><p class="stat-sub">+<?= $bulan; ?> bulan ini</p></div>
  <div class="card stat-card"><p class="stat-label">JURNAL HARI INI</p><p class="stat-number"><?= $today; ?></p><p class="stat-sub"><?= date('d M Y'); ?></p></div>
</div>
<div id="riwayat" class="card mt-5 !p-0">
  <div class="flex flex-col gap-2 border-b p-4 sm:flex-row sm:items-center" style="border-color:var(--border)">
    <h3 class="flex-1 font-bold" style="font-family:'Poppins',sans-serif">Data Jurnal Terbaru</h3>
    <div class="flex flex-wrap gap-2">
      <input id="searchJ" type="search" placeholder="Cari tanggal / kegiatan..." class="form-input !w-auto">
    </div>
  </div>
  <div class="table-wrap"><table id="tblJ" data-paginate="8" data-paginate-nav="#pgJ" class="table-medical">
  <thead><tr><th>No</th><th>Tanggal</th><th>Materi / Kegiatan</th><th>Foto</th><th class="no-print">Aksi</th></tr></thead>
  <tbody>
  <?php if(count($rows)>0): $no=1; foreach($rows as $j): ?>
  <tr data-siswa="<?= htmlspecialchars($siswa['nama_lengkap']); ?>">
    <td style="color:var(--muted)"><?= $no++; ?></td>
    <td class="whitespace-nowrap"><p class="text-xs font-bold"><?= htmlspecialchars($j['tanggal_kegiatan']); ?></p><p class="max-w-[140px] truncate text-xs" style="color:var(--muted)"><?= htmlspecialchars($j['nama_dudi']); ?></p></td>
    <td class="max-w-[280px]"><p class="line-clamp-2 font-medium"><?= htmlspecialchars($j['kegiatan_magang']); ?></p><?php if(!empty($j['keterangan'])): ?><p class="line-clamp-1 text-xs" style="color:var(--muted)"><?= htmlspecialchars($j['keterangan']); ?></p><?php endif; ?></td>
    <td><?php if(!empty($j['foto'])): ?><button onclick="openLightbox('../uploads/jurnal/<?= htmlspecialchars($j['foto']); ?>')"><img src="../uploads/jurnal/<?= htmlspecialchars($j['foto']); ?>" class="h-11 w-11 rounded-xl border object-cover" style="border-color:var(--border)" alt="Bukti"></button><?php else: ?><span class="text-xs" style="color:var(--muted)">—</span><?php endif; ?></td>
    <td class="no-print whitespace-nowrap">
      <a href="detail_jurnal.php?id=<?= $j['id_jurnal']; ?>" class="btn-ghost !px-2.5 !py-1.5 !text-xs">Detail</a>
      <a href="edit_jurnal.php?id=<?= $j['id_jurnal']; ?>" class="btn-ghost !px-2.5 !py-1.5 !text-xs" style="color:var(--primary-dark)">Edit</a>
      <button onclick="return askDelete('hapus_jurnal.php?id=<?= $j['id_jurnal']; ?>','Hapus jurnal tanggal <?= htmlspecialchars($j['tanggal_kegiatan']); ?>?')" class="btn-danger">Hapus</button>
    </td>
  </tr>
  <?php endforeach; else: ?><tr><td colspan="6" class="px-4 py-10 text-center"><p class="font-bold">Belum ada jurnal</p><p class="text-sm" style="color:var(--muted)">Klik Isi Jurnal untuk mencatat kegiatan PKL pertama Anda.</p><a href="tambah_jurnal.php" class="btn-primary mt-3">Isi Jurnal Sekarang</a></td></tr><?php endif; ?>
  </tbody></table></div>
  <div class="flex items-center justify-between gap-2 border-t p-4" style="border-color:var(--border)">
    <p class="text-xs" style="color:var(--muted)"><?= $tot; ?> data</p>
    <div id="pgJ" class="flex flex-wrap gap-1.5"></div>
  </div>
</div>
<script>
(function(){
  function applyRiwayatFilter(){
    var q=(document.getElementById('searchJ').value||'').toLowerCase();
    document.querySelectorAll('#tblJ tbody tr').forEach(function(tr){
      var ok=!q||tr.textContent.toLowerCase().includes(q);
      tr.setAttribute('data-filtered', ok?'1':'0');
      if(!ok) tr.style.display='none';
    });
    var tbl=document.getElementById('tblJ'); tbl.setAttribute('data-page','1');
    if(window.__ejPaginate) window.__ejPaginate();
  }
  document.getElementById('searchJ').addEventListener('input',applyRiwayatFilter);
})();
</script>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
