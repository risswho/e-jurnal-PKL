<?php
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/import_helpers.php";
require_login('admin');

$tipeAktif = $_GET['tipe'] ?? 'siswa';
$allowed = ['siswa', 'guru', 'admin'];
if (!in_array($tipeAktif, $allowed, true)) $tipeAktif = 'siswa';
$cfgAktif = ej_import_config($tipeAktif);
$result = $_SESSION['import_result'] ?? null;
// Tampilkan hasil hanya jika baru (<= 5 menit) agar tidak basi
if ($result && isset($result['at']) && (time() - strtotime($result['at']) > 300)) {
    $result = null;
    unset($_SESSION['import_result']);
}
$title = 'Import Data';
$base = '../';
$role = 'admin';
$nama = $_SESSION['nama'] ?? 'Admin';
$active = 'import';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/toast.php';
$input = UI_INPUT;
$label = UI_LABEL;
$tabs = [
    'siswa' => ['label' => 'Siswa', 'desc' => 'Data pokok + akun login siswa', 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-8 0 4 4 0 008 0zm6-4a3 3 0 11-6 0 3 3 0 016 0z', 'kelola' => 'siswa.php', 'kelolaLabel' => 'Kelola Siswa'],
    'guru' => ['label' => 'Guru', 'desc' => 'Guru pembimbing PKL', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'kelola' => 'guru.php', 'kelolaLabel' => 'Kelola Guru'],
    'admin' => ['label' => 'Admin', 'desc' => 'Operator / administrator', 'icon' => 'M12 11a3 3 0 100-6 3 3 0 000 6zM5 21a7 7 0 0114 0', 'kelola' => 'admin.php', 'kelolaLabel' => 'Kelola Admin'],
];
$tabAktif = $tabs[$tipeAktif];
// Kolom opsional per tipe (tidak wajib diisi)
$optionalCols = ['siswa' => [], 'guru' => [], 'admin' => []];
$optAktif = $optionalCols[$tipeAktif] ?? [];
?>
<div>
  <h2 class="text-xl font-extrabold tracking-tight">Import Data Excel & CSV</h2>
  <p class="text-sm text-slate-500 dark:text-slate-400">Ikuti 3 langkah di bawah. Anda bisa langsung upload file <strong>.xlsx</strong> / <strong>.xls</strong> dari Excel atau file <strong>.csv</strong> (maks 5 MB, maks 2000 baris).</p>
</div>

<div class="mt-4 grid gap-3 sm:grid-cols-3">
  <?php foreach ($tabs as $key => $t): $on = ($key === $tipeAktif); ?>
  <a href="import.php?tipe=<?= $key; ?>" class="flex items-center gap-3 rounded-2xl border p-4 transition <?= $on ? 'border-indigo-600 bg-indigo-600 text-white shadow dark:border-indigo-500 dark:bg-indigo-500' : 'border-slate-200 bg-white hover:border-indigo-300 hover:shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:hover:border-indigo-500/50'; ?>">
    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl <?= $on ? 'bg-white/20 text-white' : 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300'; ?>">
      <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $t['icon']; ?>"/></svg>
    </span>
    <span class="min-w-0">
      <span class="block text-sm font-extrabold"><?= $t['label']; ?> <?= $on ? '✓' : ''; ?></span>
      <span class="block truncate text-xs <?= $on ? 'text-indigo-100' : 'text-slate-500 dark:text-slate-400'; ?>"><?= $t['desc']; ?> • <?= count(ej_import_config($key)['headers']); ?> kolom</span>
    </span>
  </a>
  <?php endforeach; ?>
</div>

<?php if ($result): $pct = ((int) ($result['total'] ?? 0)) > 0 ? round(100 * (int) ($result['ok'] ?? 0) / (int) $result['total']) : 0; $isOk = ((int) ($result['ok'] ?? 0)) > 0; ?>
<div class="mt-4 rounded-2xl border <?= $isOk ? 'border-emerald-200 bg-emerald-50/60 dark:border-emerald-500/30 dark:bg-emerald-500/5' : 'border-rose-200 bg-rose-50/60 dark:border-rose-500/30 dark:bg-rose-500/5'; ?> p-5">
  <div class="flex flex-wrap items-center gap-2">
    <span class="flex h-8 w-8 items-center justify-center rounded-full <?= $isOk ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'; ?> text-sm font-extrabold"><?= $isOk ? '✓' : '!'; ?></span>
    <h3 class="font-extrabold">Hasil import <?= htmlspecialchars($result['label'] ?? ''); ?></h3>
    <span class="text-xs text-slate-500"><?= htmlspecialchars($result['at'] ?? ''); ?></span>
  </div>
  <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800"><div class="h-full rounded-full <?= $isOk ? 'bg-emerald-500' : 'bg-rose-500'; ?>" style="width:<?= $pct; ?>%"></div></div>
  <div class="mt-2 flex flex-wrap gap-2 text-sm">
    <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-bold text-white dark:bg-white dark:text-slate-900">Total: <?= (int) ($result['total'] ?? 0); ?></span>
    <span class="rounded-full bg-emerald-600 px-3 py-1 text-xs font-bold text-white">Berhasil: <?= (int) ($result['ok'] ?? 0); ?> (<?= $pct; ?>%)</span>
    <span class="rounded-full bg-amber-500 px-3 py-1 text-xs font-bold text-white">Dilewati: <?= (int) ($result['skip'] ?? 0); ?></span>
  </div>
  <?php if (!empty($result['errors'])): ?>
  <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-950">
    <table class="w-full min-w-[480px] text-left text-sm">
      <thead><tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-700 dark:text-slate-400"><th class="px-4 py-2">Baris</th><th class="px-4 py-2">Keterangan</th></tr></thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        <?php foreach ($result['errors'] as $e): ?>
        <tr><td class="whitespace-nowrap px-4 py-2 font-mono text-xs font-bold"><?= htmlspecialchars((string) ($e['baris'] ?? '?')); ?></td><td class="px-4 py-2"><?= htmlspecialchars($e['pesan'] ?? ''); ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if (($result['skip'] ?? 0) > count($result['errors'])): ?>
  <p class="mt-2 text-xs text-slate-500">…dan <?= (int) ($result['skip'] - count($result['errors'])); ?> baris error lainnya (ditampilkan 50 pertama).</p>
  <?php endif; ?>
  <?php else: ?>
  <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Semua baris berhasil diimport tanpa error. 🎉</p>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="mt-4 grid items-start gap-4 lg:grid-cols-3">
  <div class="space-y-4 lg:col-span-2">
    <!-- LANGKAH 1 -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="flex items-center gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-extrabold text-white dark:bg-indigo-500">1</span>
        <div><h3 class="font-extrabold">Unduh template <?= htmlspecialchars($cfgAktif['label']); ?></h3><p class="text-xs text-slate-500 dark:text-slate-400">Header harus persis seperti template agar terbaca.</p></div>
      </div>
      <div class="mt-4 flex flex-wrap gap-1.5">
        <?php foreach ($cfgAktif['headers'] as $h): $isOpt = in_array($h, $optAktif, true); ?>
        <span class="rounded-lg px-2.5 py-1 font-mono text-xs font-semibold <?= $isOpt ? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' : 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-300 dark:ring-indigo-500/30'; ?>"><?= htmlspecialchars($h); ?><?= $isOpt ? ' (opsional)' : ''; ?></span>
        <?php endforeach; ?>
      </div>
      <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
        <table class="w-full min-w-[420px] text-left text-xs">
          <thead><tr class="bg-slate-50 dark:bg-slate-800">
            <?php foreach ($cfgAktif['headers'] as $h): ?><th class="whitespace-nowrap px-3 py-2 font-mono"><?= htmlspecialchars($h); ?></th><?php endforeach; ?>
          </tr></thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php foreach ($cfgAktif['sample'] as $row): ?><tr>
              <?php foreach ($row as $c): ?><td class="max-w-[160px] truncate px-3 py-2"><?= htmlspecialchars($c === '' ? '—' : $c); ?></td><?php endforeach; ?>
            </tr><?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <a href="download_template.php?tipe=<?= htmlspecialchars($tipeAktif); ?>" class="mt-4 block rounded-xl border-2 border-dashed border-indigo-300 bg-indigo-50/50 px-4 py-3 text-center text-sm font-extrabold text-indigo-700 hover:border-indigo-500 hover:bg-indigo-50 dark:border-indigo-500/40 dark:bg-indigo-500/10 dark:text-indigo-300 dark:hover:bg-indigo-500/20">⬇ Unduh Template Excel (.xlsx) — <?= htmlspecialchars($cfgAktif['label']); ?></a>
    </div>

    <!-- LANGKAH 2 -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="flex items-center gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-extrabold text-white dark:bg-indigo-500">2</span>
        <div><h3 class="font-extrabold">Isi data di Excel / Spreadsheet</h3><p class="text-xs text-slate-500 dark:text-slate-400">Jangan ubah baris header bawaan template.</p></div>
      </div>
      <ol class="mt-4 space-y-2.5 text-sm">
        <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">a</span><span class="text-slate-600 dark:text-slate-300">Buka template di <strong>Excel / WPS / Google Sheets</strong>, isi baris data di bawah header.</span></li>
        <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">b</span><span class="text-slate-600 dark:text-slate-300">Simpan file Anda berformat <strong>.xlsx</strong> atau <strong>.xls</strong> (atau CSV).</span></li>
        <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">c</span><span class="text-slate-600 dark:text-slate-300">Lanjut ke langkah 3 untuk upload.</span></li>
      </ol>
      <div class="mt-4 rounded-xl bg-amber-50 p-3 text-xs leading-relaxed text-amber-800 ring-1 ring-inset ring-amber-200 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/30">
        <strong>Catatan:</strong> Data duplikat (NISN / NIP / username yang sudah ada) otomatis <strong>dilewati</strong>, bukan ditimpa. Password otomatis di-hash (aman untuk login).
      </div>
    </div>

    <!-- LANGKAH 3 -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="flex items-center gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-sm font-extrabold text-white">3</span>
        <div><h3 class="font-extrabold">Upload & import <?= htmlspecialchars($cfgAktif['label']); ?></h3><p class="text-xs text-slate-500 dark:text-slate-400">File .xlsx, .xls, .csv • maks 5 MB • maks 2000 baris.</p></div>
      </div>
      <form action="proses_import.php" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
        <input type="hidden" name="tipe" value="<?= htmlspecialchars($tipeAktif); ?>">
        <input type="hidden" name="redirect" value="import.php?tipe=<?= htmlspecialchars($tipeAktif); ?>">
        <div>
          <label class="<?= $label; ?>">File Excel / CSV</label>
          <label id="dropZone" class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center transition hover:border-indigo-400 hover:bg-indigo-50/50 dark:border-slate-700 dark:bg-slate-950 dark:hover:border-indigo-500 dark:hover:bg-indigo-500/5">
            <svg class="h-10 w-10 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 12l-4-4m4 4l4-4M4 16v3a1 1 0 001 1h14a1 1 0 001-1v-3"/></svg>
            <span class="mt-2 text-sm font-bold">Klik untuk pilih file / seret file ke sini</span>
            <span class="text-xs text-slate-500">Mendukung format .xlsx, .xls, dan .csv</span>
            <span id="fileName" class="mt-2 hidden rounded-lg bg-indigo-50 px-3 py-1 font-mono text-xs font-bold text-indigo-700 ring-1 ring-inset ring-indigo-200 dark:bg-indigo-500/15 dark:text-indigo-300 dark:ring-indigo-500/30"></span>
            <input id="fileCsv" type="file" name="file_csv" accept=".xlsx, .xls, .csv" required class="hidden">
          </label>
        </div>
        <button class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-3 text-sm font-extrabold text-white shadow hover:from-indigo-700 hover:to-indigo-600 dark:from-indigo-500 dark:to-indigo-600">⬆ Import Sekarang</button>
      </form>
    </div>
  </div>

  <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:sticky lg:top-20">
    <h3 class="font-extrabold">Ringkasan — <?= htmlspecialchars($cfgAktif['label']); ?></h3>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400"><?= htmlspecialchars($cfgAktif['desc']); ?></p>
    <dl class="mt-4 space-y-2 text-sm">
      <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 dark:bg-slate-800/70"><dt class="text-slate-500 dark:text-slate-400">Kolom wajib</dt><dd class="font-extrabold"><?= count($cfgAktif['headers']) - count($optAktif); ?></dd></div>
      <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 dark:bg-slate-800/70"><dt class="text-slate-500 dark:text-slate-400">Kolom opsional</dt><dd class="font-extrabold"><?= count($optAktif); ?></dd></div>
      <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 dark:bg-slate-800/70"><dt class="text-slate-500 dark:text-slate-400">Batas file</dt><dd class="font-extrabold">2 MB</dd></div>
      <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 dark:bg-slate-800/70"><dt class="text-slate-500 dark:text-slate-400">Batas baris</dt><dd class="font-extrabold">2000</dd></div>
    </dl>
    <a href="<?= $tabAktif['kelola']; ?>" class="mt-4 block rounded-xl bg-indigo-600 px-4 py-2.5 text-center text-sm font-bold text-white hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-400"><?= htmlspecialchars($tabAktif['kelolaLabel']); ?> →</a>
    <p class="mt-3 text-center text-xs text-slate-400">Pindah tipe lewat kartu di atas —<br>langkah & template ikut berubah.</p>
  </div>
</div>
<script>
(function(){
  var input=document.getElementById('fileCsv'), name=document.getElementById('fileName'), zone=document.getElementById('dropZone');
  if(!input) return;
  input.addEventListener('change',function(){
    if(input.files&&input.files[0]){name.textContent='📄 '+input.files[0].name;name.classList.remove('hidden');}
    else{name.classList.add('hidden');}
  });
  ['dragover','dragenter'].forEach(function(ev){zone.addEventListener(ev,function(e){e.preventDefault();zone.classList.add('border-indigo-500','bg-indigo-50');});});
  ['dragleave','drop'].forEach(function(ev){zone.addEventListener(ev,function(e){e.preventDefault();zone.classList.remove('border-indigo-500','bg-indigo-50');});});
})();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
