<?php
session_start();

require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";

require_login('guru');

$id_guru = (int)($_SESSION['id_guru'] ?? 0);
$id_monitoring = (int)($_GET['id'] ?? 0);

if ($id_guru <= 0 || $id_monitoring <= 0) {
    header("Location: bimbingan.php?error=" . urlencode("ID monitoring tidak valid."));
    exit;
}

/*
 * IMPORTANT:
 * Jangan memakai variabel $m untuk data monitoring.
 * includes/layout.php juga memakai $m untuk loop menu.
 * Kita gunakan $monitoring agar datanya tidak tertimpa oleh layout.
 */
$stmt = mysqli_prepare($conn, "
    SELECT
        b.id_monitoring,
        b.id_guru,
        b.id_siswa,
        b.tanggal_monitoring,
        b.nama_dudi,
        b.keterangan,
        b.foto,
        s.nama_lengkap,
        s.nisn,
        s.kelas,
        s.jurusan,
        s.dudi_magang,
        g.nip,
        g.nama_guru
    FROM monitoring b
    INNER JOIN siswa s ON b.id_siswa = s.id_siswa
    INNER JOIN guru g ON b.id_guru = g.id_guru
    WHERE b.id_monitoring = ?
      AND b.id_guru = ?
    LIMIT 1
");

if (!$stmt) {
    header("Location: bimbingan.php?error=" . urlencode("Gagal menyiapkan data monitoring."));
    exit;
}

mysqli_stmt_bind_param($stmt, "ii", $id_monitoring, $id_guru);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$monitoring = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$monitoring) {
    header("Location: bimbingan.php?error=" . urlencode("Data monitoring tidak ditemukan atau bukan milik Anda."));
    exit;
}

/*
 * Ambil daftar DUDI yang dimiliki siswa bimbingan guru ini.
 * Prepared statement digunakan agar aman.
 */
$dudi_list = [];

$dudi_stmt = mysqli_prepare($conn, "
    SELECT DISTINCT dudi_magang
    FROM siswa
    WHERE id_guru = ?
      AND dudi_magang IS NOT NULL
      AND dudi_magang <> ''
    ORDER BY dudi_magang ASC
");

if ($dudi_stmt) {
    mysqli_stmt_bind_param($dudi_stmt, "i", $id_guru);
    mysqli_stmt_execute($dudi_stmt);
    $dudi_result = mysqli_stmt_get_result($dudi_stmt);

    while ($row = mysqli_fetch_assoc($dudi_result)) {
        $dudi_list[] = $row['dudi_magang'];
    }

    mysqli_stmt_close($dudi_stmt);
}

$title = 'Edit Monitoring';
$base = '../';
$role = 'guru';
$nama = $_SESSION['nama'] ?? 'Guru';
$active = 'monitoring';

require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/toast.php';

$err = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

$foto_url = '';
if (!empty($monitoring['foto'])) {
    $foto_url = '../uploads/monitoring/' . rawurlencode(basename($monitoring['foto']));
}
?>

<div class="mx-auto max-w-2xl py-8">
    <div class="card shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="card-header flex flex-col gap-2 border-b border-slate-200 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-extrabold tracking-tight">Edit Monitoring</h2>

            <a
                href="bimbingan.php"
                class="text-sm text-slate-500 hover:underline dark:text-slate-400"
            >
                Kembali ke Daftar
            </a>
        </div>

        <div class="p-6">
            <?php if ($err): ?>
                <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-sm font-medium text-rose-700">
                    <?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm font-medium text-emerald-700">
                    <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form
                action="proses_edit_monitoring.php"
                method="POST"
                enctype="multipart/form-data"
                class="mt-5 space-y-4"
            >
                <input
                    type="hidden"
                    name="id_monitoring"
                    value="<?= (int)$monitoring['id_monitoring']; ?>"
                >

                <input
                    type="hidden"
                    name="id_siswa"
                    value="<?= (int)$monitoring['id_siswa']; ?>"
                >

                <!--
                    id_guru tidak dipercaya dari POST.
                    proses_edit_monitoring.php menggunakan $_SESSION['id_guru'].
                -->

                <div>
                    <label class="form-label">Siswa</label>
                    <input
                        type="text"
                        value="<?= htmlspecialchars(
                            $monitoring['nama_lengkap'] . ' (' . $monitoring['nisn'] . ')',
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>"
                        class="form-input"
                        readonly
                    >
                </div>

                <div>
                    <label class="form-label">DUDI</label>
                    <select name="dudi" required class="form-input">
                        <?php if (!$dudi_list): ?>
                            <option value="<?= htmlspecialchars($monitoring['nama_dudi'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($monitoring['nama_dudi'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php else: ?>
                            <?php foreach ($dudi_list as $dudi): ?>
                                <option
                                    value="<?= htmlspecialchars($dudi, ENT_QUOTES, 'UTF-8'); ?>"
                                    <?= $dudi === $monitoring['nama_dudi'] ? 'selected' : ''; ?>
                                >
                                    <?= htmlspecialchars($dudi, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div>
                    <label class="form-label">Tanggal Monitoring</label>
                    <input
                        type="date"
                        name="tanggal_monitoring"
                        value="<?= htmlspecialchars($monitoring['tanggal_monitoring'], ENT_QUOTES, 'UTF-8'); ?>"
                        required
                        class="form-input"
                    >
                </div>

                <div>
                    <label class="form-label">Keterangan / Hasil Monitoring</label>
                    <textarea
                        name="keterangan"
                        rows="4"
                        required
                        placeholder="Contoh: monitoring progres minggu ke-3, cek logbook, arahan K3..."
                        class="form-input"
                    ><?= htmlspecialchars($monitoring['keterangan'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>

                <div>
                    <label class="form-label">Foto Dokumentasi (opsional)</label>

                    <?php if ($foto_url): ?>
                        <div class="mb-3">
                            <p class="mb-2 text-xs font-medium text-slate-500 dark:text-slate-400">
                                Foto saat ini
                            </p>
                            <img
                                src="<?= htmlspecialchars($foto_url, ENT_QUOTES, 'UTF-8'); ?>"
                                alt="Foto monitoring"
                                class="h-32 w-32 rounded-xl object-cover"
                            >
                        </div>
                    <?php endif; ?>

                    <label
                        class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-4 py-6 text-center"
                        style="border-color:var(--border);background:var(--primary-soft)"
                    >
                        <img
                            id="prevEdit"
                            src=""
                            alt="Preview foto baru"
                            class="mb-2 hidden h-24 w-24 rounded-xl object-cover"
                        >

                        <span class="text-sm font-semibold">Ganti foto</span>
                        <span class="text-xs" style="color:var(--muted)">
                            JPG / JPEG / PNG • maks 20 MB
                        </span>

                        <input
                            type="file"
                            name="foto"
                            accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                            data-img-preview="#prevEdit"
                            class="hidden"
                        >
                    </label>
                </div>

                <div class="flex gap-2">
                    <a href="bimbingan.php" class="btn-ghost flex-1 text-center">
                        Batal
                    </a>

                    <button type="submit" class="btn-primary flex-1">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.querySelector('input[name="foto"]');
    const preview = document.getElementById('prevEdit');

    if (!input || !preview) return;

    input.addEventListener('change', function () {
        const file = this.files && this.files[0];

        if (!file) {
            preview.src = '';
            preview.classList.add('hidden');
            return;
        }

        if (!file.type.startsWith('image/')) {
            preview.src = '';
            preview.classList.add('hidden');
            return;
        }

        const objectUrl = URL.createObjectURL(file);
        preview.src = objectUrl;
        preview.classList.remove('hidden');

        preview.onload = function () {
            URL.revokeObjectURL(objectUrl);
        };
    });
});
</script>
