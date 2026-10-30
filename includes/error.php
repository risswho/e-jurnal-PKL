<?php
// includes/error.php — halaman error styled (pengganti die() polos)
// Cara pakai: $errorTitle, $errorMsg, $backHref opsional, lalu require file ini + exit.
if (!isset($errorTitle)) $errorTitle = 'Terjadi Kesalahan';
if (!isset($errorMsg)) $errorMsg = 'Sesuatu tidak beres. Silakan coba lagi.';
if (!isset($backHref)) $backHref = 'javascript:history.back()';
if (!isset($title)) $title = $errorTitle;
if (!isset($base)) $base = '../';
require_once __DIR__ . '/head.php';
?>
<body class="flex min-h-screen items-center justify-center bg-slate-100 p-4 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
<button id="themeToggle" class="fixed right-4 top-4 rounded-xl border border-slate-200 bg-white p-2.5 shadow hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900" aria-label="Toggle tema">
<svg class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36 6.36l-1.42-1.42M7.05 7.05L5.64 5.64m12.72 0l-1.41 1.41M7.05 16.95l-1.41 1.41M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
<svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A9 9 0 1111.2 3 7 7 0 0021 12.8z"/></svg>
</button>
<div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 text-center shadow dark:border-slate-800 dark:bg-slate-900">
<div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-rose-500 dark:bg-rose-500/10">
<svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.7 3.86a2 2 0 00-3.4 0z"/></svg>
</div>
<h1 class="mt-4 text-lg font-extrabold"><?= htmlspecialchars($errorTitle); ?></h1>
<p class="mt-1 text-sm text-slate-500 dark:text-slate-400"><?= htmlspecialchars($errorMsg); ?></p>
<div class="mt-6 flex gap-2">
<a href="<?= htmlspecialchars($backHref); ?>" class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">← Kembali</a>
<a href="<?= $base; ?>auth/login.php" class="flex-1 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 dark:bg-indigo-500">Ke Login</a>
</div></div>
<script src="<?= $base; ?>assets/js/theme.js"></script>
</body></html>
