<?php
// admin/admin.php — Kelola Admin (sebelumnya belum ada halamannya).
// Menampilkan daftar admin + form tambah manual + tombol import CSV.
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";
require_login('admin');

// Tambah admin manual (inline, agar tidak perlu file proses baru)
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        header('Location: admin.php?error=' . urlencode('Username dan password wajib diisi.'));
        exit;
    }
    $cek = mysqli_prepare($conn, "SELECT id_admin FROM admin WHERE username = ?");
    mysqli_stmt_bind_param($cek, "s", $username);
    mysqli_stmt_execute($cek);
    if (mysqli_fetch_assoc(mysqli_stmt_get_result($cek))) {
        mysqli_stmt_close($cek);
        header('Location: admin.php?error=' . urlencode('Username sudah terdaftar.'));
        exit;
    }
    mysqli_stmt_close($cek);
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $st = mysqli_prepare($conn, "INSERT INTO admin (username, kode_unik) VALUES (?, ?)");
    mysqli_stmt_bind_param($st, "ss", $username, $hash);
    if (mysqli_stmt_execute($st)) {
        mysqli_stmt_close($st);
        header('Location: admin.php?success=' . urlencode('Admin berhasil ditambahkan.'));
        exit;
    }
    mysqli_stmt_close($st);
    header('Location: admin.php?error=' . urlencode('Gagal menambahkan admin.'));
    exit;
}

// Hapus admin (tidak boleh hapus akun sendiri)
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    if ($id === (int) ($_SESSION['id_admin'] ?? 0)) {
        header('Location: admin.php?error=' . urlencode('Tidak bisa menghapus akun sendiri.'));
        exit;
    }
    $jml = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM admin"))['c'] ?? 1;
    if ($jml <= 1) {
        header('Location: admin.php?error=' . urlencode('Minimal harus ada 1 akun admin.'));
        exit;
    }
    $st = mysqli_prepare($conn, "DELETE FROM admin WHERE id_admin = ?");
    mysqli_stmt_bind_param($st, "i", $id);
    mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
    header('Location: admin.php?success=' . urlencode('Admin dihapus.'));
    exit;
}

$result = mysqli_query($conn, "SELECT id_admin, username, created_at FROM admin ORDER BY username ASC");
$title = 'Kelola Admin';
$base = '../';
$role = 'admin';
$nama = $_SESSION['nama'] ?? 'Admin';
$active = 'admin';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/toast.php';
$input = UI_INPUT;
$label = UI_LABEL;
?>
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
  <div>
    <h2 class="text-xl font-extrabold tracking-tight">Kelola Admin</h2>
    <p class="text-sm text-slate-500 dark:text-slate-400"><?= mysqli_num_rows($result); ?> admin terdaftar</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="download_template.php?tipe=admin" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800">⬇ Template</a>
    <a href="import.php?tipe=admin" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow hover:bg-emerald-700">⬆ Import CSV</a>
  </div>
</div>

<div class="mt-4 grid gap-4 lg:grid-cols-3">
  <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <h3 class="font-extrabold">Tambah Admin</h3>
    <p class="text-sm text-slate-500 dark:text-slate-400">Akun operator / admin baru.</p>
    <form action="admin.php" method="POST" class="mt-4 space-y-3">
      <input type="hidden" name="aksi" value="tambah">
      <div><label class="<?= $label; ?>">Username</label><input type="text" name="username" required placeholder="cth: operator1" class="<?= $input; ?>"></div>
      <div><label class="<?= $label; ?>">Password</label><input type="password" name="password" required placeholder="Password login" class="<?= $input; ?>"></div>
      <button class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-400">Simpan Admin</button>
    </form>
    <div class="mt-4 rounded-xl bg-slate-50 p-3 text-xs leading-relaxed text-slate-600 dark:bg-slate-800 dark:text-slate-300">
      Untuk tambah banyak sekaligus, pakai <a href="import.php?tipe=admin" class="font-bold text-indigo-600 hover:underline">Import CSV</a> dengan header <code class="font-mono">username,password</code>.
    </div>
  </div>
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:col-span-2">
    <div class="border-b border-slate-200 p-4 dark:border-slate-800"><input data-table-filter="#tblAdmin" type="search" placeholder="Cari username..." class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-950"></div>
    <div class="overflow-x-auto"><table id="tblAdmin" class="w-full min-w-[480px] text-left text-sm">
      <thead><tr class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-400"><th class="px-4 py-3">No</th><th class="px-4 py-3">Username</th><th class="px-4 py-3">Dibuat</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        <?php if (mysqli_num_rows($result) > 0): $no = 1;
        while ($a = mysqli_fetch_assoc($result)): $isSelf = ((int) $a['id_admin'] === (int) ($_SESSION['id_admin'] ?? 0)); ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
          <td class="px-4 py-3 text-slate-400"><?= $no++; ?></td>
          <td class="px-4 py-3 font-semibold"><?= htmlspecialchars($a['username']); ?><?php if ($isSelf): ?> <span class="ml-1 rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-bold text-indigo-700 ring-1 ring-inset ring-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-300 dark:ring-indigo-500/30">Anda</span><?php endif; ?></td>
          <td class="px-4 py-3 text-xs text-slate-500"><?= htmlspecialchars($a['created_at'] ?? '-'); ?></td>
          <td class="px-4 py-3 text-right"><?php if (!$isSelf): ?><button onclick="return askDelete('admin.php?hapus=<?= $a['id_admin']; ?>','Hapus admin <?= htmlspecialchars(addslashes($a['username'])); ?>?')" class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-400 dark:hover:bg-rose-500/10">Hapus</button><?php else: ?><span class="text-xs text-slate-400">—</span><?php endif; ?></td>
        </tr>
        <?php endwhile;
        else: ?><tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">Belum ada data admin.</td></tr><?php endif; ?>
      </tbody>
    </table></div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
