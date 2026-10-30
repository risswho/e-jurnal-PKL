<?php

session_start();

require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";

// =====================================================
// HANYA GURU YANG BOLEH AKSES
// =====================================================
require_login('guru');

$id_guru = (int) ($_SESSION['id_guru'] ?? 0);

if ($id_guru <= 0) {
    abort(
        "Akses tidak valid",
        "Session guru tidak ditemukan.",
        "bimbingan.php"
    );
}


// =====================================================
// AMBIL ID MONITORING
// =====================================================
$id_monitoring = (int) ($_GET['id'] ?? 0);

if ($id_monitoring <= 0) {
    abort(
        "ID tidak valid",
        "Parameter monitoring tidak ditemukan.",
        "bimbingan.php"
    );
}


// =====================================================
// AMBIL DATA MONITORING
// HANYA MILIK GURU YANG LOGIN
// =====================================================
$stmt = mysqli_prepare(
    $conn,
    "SELECT
        b.id_monitoring,
        b.tanggal_monitoring,
        b.nama_dudi,
        b.keterangan,
        b.foto,

        s.id_siswa,
        s.nama_lengkap,
        s.nisn,
        s.kelas,
        s.jurusan,
        s.dudi_magang,

        g.id_guru,
        g.nip,
        g.nama_guru

     FROM monitoring b

     INNER JOIN siswa s
        ON b.id_siswa = s.id_siswa

     INNER JOIN guru g
        ON b.id_guru = g.id_guru

     WHERE b.id_monitoring = ?
       AND b.id_guru = ?

     LIMIT 1"
);


if (!$stmt) {
    abort(
        "Terjadi kesalahan",
        "Query database gagal diproses.",
        "bimbingan.php"
    );
}


mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $id_monitoring,
    $id_guru
);


mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


// =====================================================
// PENTING:
// JANGAN PAKAI $m
// Supaya tidak bentrok dengan layout.php
// =====================================================
$monitoring = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


// =====================================================
// CEK DATA
// =====================================================
if (!$monitoring) {
    abort(
        "Monitoring tidak ditemukan",
        "Data monitoring tidak ada atau bukan milik Anda.",
        "bimbingan.php"
    );
}


// =====================================================
// DATA LAYOUT
// =====================================================
$title  = 'Detail Monitoring';
$base   = '../';
$role   = 'guru';
$nama   = $_SESSION['nama'] ?? 'Guru';
$active = 'monitoring';


require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/toast.php';

?>


<!-- =====================================================
     KEMBALI KE DAFTAR
===================================================== -->

<a
    href="bimbingan.php"
    class="text-sm font-semibold text-slate-500 hover:text-indigo-600 dark:text-slate-400"
>
    &larr; Kembali ke Daftar
</a>


<!-- =====================================================
     CONTENT
===================================================== -->

<div class="mt-3 grid gap-4 lg:grid-cols-2">


    <!-- =================================================
         DETAIL MONITORING
    ================================================== -->

    <div
        class="rounded-2xl border border-slate-200 bg-white p-6
               dark:border-slate-800 dark:bg-slate-900"
    >

        <div class="flex items-center justify-between">

            <h2 class="text-lg font-extrabold">
                Detail Monitoring
            </h2>

        </div>


        <dl class="mt-4 space-y-3 text-sm">


            <!-- TANGGAL -->

            <div>

                <dt
                    class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                >
                    Tanggal
                </dt>

                <dd class="font-semibold">

                    <?= htmlspecialchars(
                        $monitoring['tanggal_monitoring'] ?? '-'
                    ); ?>

                </dd>

            </div>


            <!-- SISWA -->

            <div>

                <dt
                    class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                >
                    Siswa
                </dt>

                <dd class="font-semibold">

                    <?= htmlspecialchars(
                        $monitoring['nama_lengkap'] ?? '-'
                    ); ?>

                </dd>

                <dd class="text-xs text-slate-500">

                    <?= htmlspecialchars(
                        $monitoring['nisn'] ?? '-'
                    ); ?>

                    &bull;

                    <?= htmlspecialchars(
                        $monitoring['kelas'] ?? '-'
                    ); ?>

                    &bull;

                    <?= htmlspecialchars(
                        $monitoring['jurusan'] ?? '-'
                    ); ?>

                </dd>

            </div>


            <!-- DUDI -->

            <div>

                <dt
                    class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                >
                    DUDI
                </dt>

                <dd>

                    <?= htmlspecialchars(
                        $monitoring['nama_dudi'] ?? '-'
                    ); ?>

                </dd>

            </div>


            <!-- GURU PEMBIMBING -->

            <div>

                <dt
                    class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                >
                    Guru Pembimbing
                </dt>

                <dd>

                    <?= htmlspecialchars(
                        $monitoring['nama_guru'] ?? '-'
                    ); ?>

                </dd>

                <dd class="text-xs text-slate-500">

                    <?= htmlspecialchars(
                        $monitoring['nip'] ?? '-'
                    ); ?>

                </dd>

            </div>


            <!-- KETERANGAN -->

            <div>

                <dt
                    class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                >
                    Keterangan / Hasil Monitoring
                </dt>

                <dd
                    class="rounded-xl bg-slate-50 p-3 leading-relaxed
                           dark:bg-slate-800"
                >

                    <?= nl2br(
                        htmlspecialchars(
                            $monitoring['keterangan'] ?? '-'
                        )
                    ); ?>

                </dd>

            </div>


        </dl>


        <!-- =================================================
             AKSI GURU
        ================================================== -->

        <div class="mt-5 flex gap-2">


            <!-- EDIT -->

            <a
                href="edit_monitoring.php?id=<?= (int) $monitoring['id_monitoring']; ?>"
                class="flex-1 rounded-xl bg-indigo-600 px-4 py-2.5
                       text-center text-sm font-bold text-white
                       hover:bg-indigo-700
                       dark:bg-indigo-500"
            >
                Edit Monitoring
            </a>


            <!-- HAPUS -->

            <form
                method="POST"
                action="proses_hapus_monitoring.php"
                class="flex-1"
                onsubmit="return confirm('Yakin menghapus monitoring ini?');"
            >

                <input
                    type="hidden"
                    name="id_monitoring"
                    value="<?= (int) $monitoring['id_monitoring']; ?>"
                >

                <button
                    type="submit"
                    class="w-full rounded-xl border border-rose-200
                           px-4 py-2.5 text-sm font-bold text-rose-600
                           hover:bg-rose-50
                           dark:border-rose-500/30
                           dark:text-rose-400"
                >
                    Hapus
                </button>

            </form>


        </div>

    </div>


    <!-- =================================================
         BUKTI DOKUMENTASI
    ================================================== -->

    <div
        class="rounded-2xl border border-slate-200 bg-white p-6
               dark:border-slate-800 dark:bg-slate-900"
    >

        <h3 class="font-bold">
            Bukti Dokumentasi
        </h3>


        <?php if (!empty($monitoring['foto'])): ?>

            <button
                type="button"
                onclick="openLightbox('../uploads/monitoring/<?= htmlspecialchars($monitoring['foto']); ?>')"
                class="mt-3 block w-full"
            >

                <img
                    src="../uploads/monitoring/<?= htmlspecialchars($monitoring['foto']); ?>"
                    alt="Dokumentasi monitoring"
                    class="max-h-[480px] w-full rounded-2xl
                           border border-slate-200 object-cover
                           dark:border-slate-700"
                >

            </button>


            <p
                class="mt-2 text-center text-xs text-slate-400"
            >
                Klik foto untuk perbesar &bull; PNG/JPG
            </p>


        <?php else: ?>

            <div
                class="mt-3 flex h-48 flex-col items-center
                       justify-center rounded-2xl border-2
                       border-dashed border-slate-300
                       text-sm text-slate-400
                       dark:border-slate-700"
            >
                Tidak ada foto dokumentasi
            </div>

        <?php endif; ?>


    </div>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>