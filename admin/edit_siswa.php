<?php
session_start(); require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('admin');
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) abort("ID tidak valid", "ID siswa tidak ditemukan.", "siswa.php");
$stmt = mysqli_prepare($conn, "SELECT * FROM siswa WHERE id_siswa = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$siswa) abort("Siswa tidak ditemukan", "Data siswa tidak ada.", "siswa.php");
$listGuru = mysqli_query($conn, "SELECT id_guru, nip, nama_guru FROM guru ORDER BY nama_guru ASC");
$title='Edit Siswa'; $base='../'; $role='admin'; $nama=$_SESSION['nama']??'Admin'; $active='siswa';
require_once __DIR__.'/../includes/head.php'; require_once __DIR__.'/../includes/layout.php';
require_once __DIR__.'/../includes/toast.php';
$input = UI_INPUT;
$label = UI_LABEL;
?>
<a href="siswa.php" class="inline-flex items-center gap-1 text-sm font-semibold text-slate-500 hover:text-indigo-600 dark:text-slate-400">← Kembali ke Kelola Siswa</a>
<div class="mx-auto mt-4 max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
<h2 class="text-lg font-extrabold">Edit Siswa</h2><p class="text-sm text-slate-500 dark:text-slate-400">Perbarui identitas dan tempat magang. NISN dapat dikoreksi bila salah.</p>
<form action="proses_edit_siswa.php" method="POST" class="mt-5 grid gap-4 sm:grid-cols-2">
<input type="hidden" name="aksi" value="update">
<input type="hidden" name="id_siswa" value="<?= $siswa['id_siswa']; ?>">
<div><label class="<?= $label; ?>">NISN</label><input type="text" name="nisn" required value="<?= htmlspecialchars($siswa['nisn']); ?>" placeholder="Contoh: 0071234567" class="<?= $input; ?>"></div>
<div><label class="<?= $label; ?>">Nama Lengkap</label><input type="text" name="nama_lengkap" required value="<?= htmlspecialchars($siswa['nama_lengkap']); ?>" placeholder="Nama siswa" class="<?= $input; ?>"></div>
<div><label class="<?= $label; ?>">Kelas</label><input type="text" name="kelas" required value="<?= htmlspecialchars($siswa['kelas']); ?>" placeholder="Contoh: XII RPL 1" class="<?= $input; ?>"></div>
<div><label class="<?= $label; ?>">Jurusan</label><input type="text" name="jurusan" required value="<?= htmlspecialchars($siswa['jurusan']); ?>" placeholder="Contoh: Rekayasa Perangkat Lunak" class="<?= $input; ?>"></div>
<div class="sm:col-span-2"><label class="<?= $label; ?>">DUDI Magang</label><input type="text" name="dudi_magang" required value="<?= htmlspecialchars($siswa['dudi_magang']); ?>" placeholder="Nama perusahaan / instansi" class="<?= $input; ?>"></div>
<div class="sm:col-span-2"><label class="<?= $label; ?>">Guru Pembimbing <span class="font-normal text-slate-400">(wajib — seperti walikelas)</span></label><select name="id_guru" required class="<?= $input; ?>"><option value="">-- Pilih Guru Pembimbing --</option><?php if ($listGuru): while ($g = mysqli_fetch_assoc($listGuru)): ?><option value="<?= $g['id_guru']; ?>" <?= ((int)($siswa['id_guru'] ?? 0) === (int)$g['id_guru']) ? 'selected' : ''; ?>><?= htmlspecialchars($g['nama_guru']); ?> • <?= htmlspecialchars($g['nip']); ?></option><?php endwhile; endif; ?></select><?php if (empty($siswa['id_guru'])): ?><p class="mt-1 text-xs font-semibold text-rose-600">Siswa ini belum punya pembimbing — wajib pilih agar muncul di akun gurunya.</p><?php endif; ?></div>
<div class="sm:col-span-2"><label class="<?= $label; ?>">Password <span class="font-normal text-slate-400">(kosongkan jika tidak ingin mengubah)</span></label><input type="text" name="password" placeholder="Isi password baru bila ingin mengganti" class="<?= $input; ?>"><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Password lama tidak ditampilkan karena ter-hash. Isi field ini untuk mengganti, misalnya dengan NISN bila siswa lupa password.</p></div>
<div class="flex gap-2 sm:col-span-2"><a href="siswa.php" class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-center text-sm font-bold hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">Batal</a><button class="flex-1 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-400">Simpan Perubahan</button></div>
</form></div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
