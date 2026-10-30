<?php
session_start(); require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('admin');
$title='Tambah Guru'; $base='../'; $role='admin'; $nama=$_SESSION['nama']??'Admin'; $active='guru';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
$input = UI_INPUT;
$label = UI_LABEL;
?>
<a href="guru.php" class="inline-flex items-center gap-1 text-sm font-semibold text-slate-500 hover:text-indigo-600 dark:text-slate-400">← Kembali ke Kelola Guru</a>
<div class="mx-auto mt-4 max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
<h2 class="text-lg font-extrabold">Tambah Guru</h2><p class="text-sm text-slate-500 dark:text-slate-400">Akun guru pembimbing PKL.</p>
<form action="proses_tambah_guru.php" method="POST" class="mt-5 space-y-4">
<div><label class="<?= $label; ?>">NIP</label><input type="text" name="nip" required placeholder="Masukkan NIP" class="<?= $input; ?>"></div>
<div><label class="<?= $label; ?>">Nama Guru</label><input type="text" name="nama_guru" required placeholder="Masukkan nama guru" class="<?= $input; ?>"></div>
<div><label class="<?= $label; ?>">Password</label><input type="password" name="password" required placeholder="Masukkan password" class="<?= $input; ?>"></div>
<div class="flex gap-2"><a href="guru.php" class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-center text-sm font-bold hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">Batal</a><button class="flex-1 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-400">Simpan Guru</button></div>
</form></div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
