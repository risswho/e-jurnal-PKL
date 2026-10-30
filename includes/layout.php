<?php
// includes/layout.php — Medical Dashboard shell: Sidebar + Topbar
// Variabel: $role ('admin'|'guru'|'siswa'), $nama (string), $active (string slug), $base ('../'), $title
function ej_avatar_initial($nama){ $p=explode(' ',trim($nama)); $i=''; foreach(array_slice($p,0,2) as $w){$i.=strtoupper(mb_substr($w,0,1));} return $i?:'EJ'; }
if(!isset($role)) $role='siswa';
if(!isset($nama)) $nama = $_SESSION['nama'] ?? 'Pengguna';
if(!isset($active)) $active='';
if(!isset($base)) $base='../';
if(!isset($title)) $title='Dashboard';

$menus = [
 'admin'=>[
   ['section'=>'Menu Utama'],
   ['slug'=>'dashboard','label'=>'Dashboard','href'=>'dashboard.php','icon'=>'M3 12l9-9 9 9M3 12h18'],
   ['slug'=>'jurnal','label'=>'Jurnal','href'=>'jurnal.php','icon'=>'M9 12h6m-6 4h6M9 5h11a1 1 0 011 1v13a1 1 0 01-1 1H9a1 1 0 01-1-1V6a1 1 0 011-1zM4 5H3a1 1 0 00-1 1v13a1 1 0 001 1h1'],
   ['section'=>'Data'],
   ['slug'=>'siswa','label'=>'Siswa','href'=>'siswa.php','icon'=>'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-8 0 4 4 0 008 0zm6-4a3 3 0 11-6 0 3 3 0 016 0z'],
   ['slug'=>'guru','label'=>'Guru','href'=>'guru.php','icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
   ['slug'=>'monitoring','label'=>'Monitoring','href'=>'bimbingan.php','icon'=>'M8 10h8m-8 4h8M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z'],
   ['slug'=>'laporan','label'=>'Laporan','href'=>'laporan.php','icon'=>'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2 2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z'],
   ['section'=>'Lainnya'],
   ['slug'=>'admin','label'=>'Kelola Admin','href'=>'admin.php','icon'=>'M12 11a3 3 0 100-6 3 3 0 000 6zM5 21a7 7 0 0114 0'],
   ['slug'=>'import','label'=>'Import Data','href'=>'import.php','icon'=>'M12 16V4m0 12l-4-4m4 4l4-4M4 20h16'],
   ['slug'=>'profil','label'=>'Profil','href'=>'profil.php','icon'=>'M5.121 17.804A9 9 0 1118.88 6.196M15 11a3 3 0 11-6 0 3 3 0 016 0z'],
 ],
 'guru'=>[
   ['section'=>'Menu Utama'],
   ['slug'=>'dashboard','label'=>'Dashboard','href'=>'dashboard.php','icon'=>'M3 12l9-9 9 9M3 12h18'],
['slug'=>'jurnal_semua','label'=>'Jurnal','href'=>'jurnal_semua.php','icon'=>'M9 12h6m-6 4h6M9 5h11a1 1 0 011 1v13a1 1 0 01-1 1H9a1 1 0 01-1-1V6a1 1 0 011-1zM4 5H3a1 1 0 00-1 1v13a1 1 0 001 1h1'],
    ['slug'=>'siswa','label'=>'Siswa Bimbingan','href'=>'siswa.php','icon'=>'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-8 0 4 4 0 008 0zm6-4a3 3 0 11-6 0 3 3 0 016 0z'],
    ['section'=>'Data'],
    ['slug'=>'monitoring','label'=>'Monitoring','href'=>'bimbingan.php','icon'=>'M8 10h8m-8 4h8M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z'],
   ['slug'=>'laporan','label'=>'Laporan','href'=>'laporan.php','icon'=>'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2 2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z'],
   ['section'=>'Lainnya'],
   ['slug'=>'cetak','label'=>'Cetak Laporan','href'=>'cetak_laporan.php','icon'=>'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2 2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z'],
   ['slug'=>'profil','label'=>'Profil','href'=>'profil.php','icon'=>'M5.121 17.804A9 9 0 1118.88 6.196M15 11a3 3 0 11-6 0 3 3 0 016 0z'],
 ],
 'siswa'=>[
   ['section'=>'Menu Utama'],
   ['slug'=>'dashboard','label'=>'Dashboard','href'=>'dashboard.php','icon'=>'M3 12l9-9 9 9M3 12h18'],
   ['slug'=>'tambah','label'=>'Jurnal','href'=>'tambah_jurnal.php','icon'=>'M12 4v16m8-8H4'],
   ['slug'=>'jurnal','label'=>'Data Jurnal','href'=>'dashboard.php#riwayat','icon'=>'M9 12h6m-6 4h6M9 5h11a1 1 0 011 1v13a1 1 0 01-1 1H9a1 1 0 01-1-1V6a1 1 0 011-1zM4 5H3a1 1 0 00-1 1v13a1 1 0 001 1h1'],
['section'=>'Data'],
    ['slug'=>'laporan','label'=>'Laporan','href'=>'laporan.php','icon'=>'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2 2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z'],
   ['section'=>'Lainnya'],
   ['slug'=>'profil','label'=>'Profil','href'=>'profil.php','icon'=>'M5.121 17.804A9 9 0 1118.88 6.196M15 11a3 3 0 11-6 0 3 3 0 016 0z'],
 ],
];
$nav = $menus[$role] ?? $menus['siswa'];
$roleLabel = ['admin'=>'Administrator','guru'=>'Guru Pembimbing','siswa'=>'Siswa PKL'][$role] ?? $role;
?>
<body class="min-h-screen text-slate-900 antialiased dark:text-slate-100">
<!-- MOBILE SIDEBAR OVERLAY -->
<div id="sidebarOverlay" class="fixed inset-0 z-40 hidden bg-slate-950/50 lg:hidden"></div>
<div class="flex min-h-screen">
<!-- SIDEBAR -->
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 dark:border-slate-800 dark:bg-slate-900 lg:static lg:translate-x-0">
  <div class="flex items-center gap-3 px-5 py-5">
    <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl bg-white ring-1 ring-slate-200 dark:bg-white dark:ring-slate-700"><img src="<?= $base; ?>assets/img/Logo-SMK-Negeri-Purwosari.png" alt="Logo SMK" class="h-full w-full object-cover" onerror="this.style.display='none'"></div>
    <div>
      <p class="text-sm font-extrabold tracking-tight">E-Jurnal SMK</p>
      <p class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($roleLabel); ?></p>
    </div>
  </div>
  <nav class="flex-1 space-y-1 overflow-y-auto px-3 pb-4">
    <?php foreach($nav as $m): if(isset($m['section'])): ?>
    <p class="px-3 pb-1 pt-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500"><?= htmlspecialchars($m['section']); ?></p>
    <?php continue; endif; $isActive = ($active===$m['slug']); ?>
    <a href="<?= $m['href']; ?>" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition <?= $isActive ? 'bg-indigo-600 text-white shadow dark:bg-indigo-500' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white'; ?>">
      <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $m['icon']; ?>"/></svg>
      <?= htmlspecialchars($m['label']); ?>
    </a>
    <?php endforeach; ?>
  </nav>
  <div class="border-t border-slate-200 p-4 dark:border-slate-800">
    <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3 dark:bg-slate-800/70">
      <div class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300"><?= ej_avatar_initial($nama); ?></div>
      <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-semibold"><?= htmlspecialchars($nama); ?></p>
        <p class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars(ucfirst($role)); ?></p>
      </div>
    </div>
    <a href="<?= $base; ?>auth/logout.php" class="mt-3 flex items-center justify-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:border-slate-700 dark:text-rose-400 dark:hover:bg-rose-500/10">
      <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/></svg>
      Logout
    </a>
  </div>
</aside>

<!-- MAIN COLUMN -->
<div class="flex min-w-0 flex-1 flex-col">
<!-- TOPBAR -->
<header class="sticky top-0 z-30 border-b border-slate-200 bg-white/85 backdrop-blur dark:border-slate-800 dark:bg-slate-900/85">
  <div class="flex items-center gap-3 px-4 py-3 sm:px-6">
    <button id="sidebarToggle" class="rounded-lg border border-slate-200 p-2 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800 lg:hidden" aria-label="Menu">
      <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <div class="min-w-0 flex-1">
      <h1 class="truncate text-base font-bold sm:text-lg"><?= htmlspecialchars($title); ?></h1>
      <p class="hidden text-xs sm:block" style="color:var(--muted)">Sistem pencatatan jurnal harian PKL / monitoring siswa SMK</p>
    </div>
    <div class="hidden min-w-0 items-center gap-2 md:flex">
      <span class="truncate rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-300 dark:ring-indigo-500/30"><?= htmlspecialchars($nama); ?></span>
    </div>
    <button id="themeToggle" class="rounded-lg border border-slate-200 p-2 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800" aria-label="Toggle tema">
      <svg id="iconSun" class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m9-9h2M5 12H3m15.36 6.36l-1.42-1.42M7.05 7.05L5.64 5.64m12.72 0l-1.41 1.41M7.05 16.95l-1.41 1.41M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
      <svg id="iconMoon" class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A9 9 0 1111.2 3 7 7 0 0021 12.8z"/></svg>
    </button>
  </div>
</header>
<main class="mx-auto w-full max-w-6xl flex-1 px-4 py-6 sm:px-6">
