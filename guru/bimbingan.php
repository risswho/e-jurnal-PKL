<?php

session_start();

require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";

require_login('guru');

$id_guru = (int) ($_SESSION['id_guru'] ?? 0);

if ($id_guru <= 0) {
    header("Location: ../auth/login.php");
    exit;
}


// =====================================================
// AMBIL DATA MONITORING MILIK GURU
// =====================================================

$stmt = mysqli_prepare(
    $conn,
    "
    SELECT
        b.id_monitoring,
        b.tanggal_monitoring,
        b.nama_dudi,
        b.keterangan,
        b.foto,

        s.id_siswa,
        s.nisn,
        s.nama_lengkap,
        s.kelas

    FROM monitoring b

    INNER JOIN siswa s
        ON b.id_siswa = s.id_siswa

    WHERE b.id_guru = ?

    ORDER BY b.tanggal_monitoring DESC
    "
);


if (!$stmt) {
    die("Query monitoring gagal diproses.");
}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_guru
);


mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


// =====================================================
// DATA LAYOUT
// =====================================================

$title  = 'Monitoring Siswa';
$base   = '../';
$role   = 'guru';
$nama   = $_SESSION['nama'] ?? 'Guru';
$active = 'monitoring';


require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/toast.php';


// =====================================================
// AMBIL DAFTAR DUDI
// =====================================================

$dudi_q = mysqli_prepare(
    $conn,
    "
    SELECT DISTINCT dudi_magang
    FROM siswa
    WHERE id_guru = ?
    ORDER BY dudi_magang ASC
    "
);


if ($dudi_q) {

    mysqli_stmt_bind_param(
        $dudi_q,
        "i",
        $id_guru
    );

    mysqli_stmt_execute($dudi_q);

    $dudi_list = mysqli_stmt_get_result($dudi_q);

} else {

    $dudi_list = false;

}

?>


<div class="p-6">


    <!-- =================================================
         HEADER
    ================================================== -->

    <div
        class="mb-6 flex flex-col gap-4 md:flex-row
               md:items-center md:justify-between"
    >

        <div>

            <h1 class="text-2xl font-bold text-gray-800 dark:text-slate-100">
                Monitoring Siswa
            </h1>

            <p class="mt-1 text-gray-500 dark:text-slate-400">
                Kelola data monitoring siswa selama kegiatan PKL.
            </p>

        </div>


        <!-- TAMBAH MONITORING -->

        <a
            href="tambah_monitoring.php"
            class="inline-flex items-center justify-center
                   rounded-lg bg-blue-600 px-4 py-2
                   text-white transition hover:bg-blue-700"
        >
            + Tambah Monitoring
        </a>

    </div>


    <!-- =================================================
         FILTER
    ================================================== -->

    <div
        class="mb-6 rounded-xl border border-gray-200
               bg-white p-4 shadow-sm
               dark:border-slate-700 dark:bg-slate-900"
    >

        <div class="flex flex-col gap-4 md:flex-row">


            <!-- FILTER DUDI -->

            <div class="flex-1">

                <label
                    for="filterDudi"
                    class="mb-2 block text-sm font-medium
                           text-gray-700 dark:text-slate-300"
                >
                    Filter DUDI
                </label>


                <select
                    id="filterDudi"
                    class="w-full rounded-lg border border-gray-300
                           bg-white px-3 py-2
                           text-gray-800
                           focus:border-blue-500 focus:ring-2
                           focus:ring-blue-500
                           dark:border-slate-600
                           dark:bg-slate-800
                           dark:text-slate-100"
                >

                    <option value="">
                        Semua DUDI
                    </option>


                    <?php if ($dudi_list): ?>

                        <?php while ($dudi = mysqli_fetch_assoc($dudi_list)): ?>

                            <?php if (!empty($dudi['dudi_magang'])): ?>

                                <option
                                    value="<?= htmlspecialchars($dudi['dudi_magang']); ?>"
                                >
                                    <?= htmlspecialchars($dudi['dudi_magang']); ?>
                                </option>

                            <?php endif; ?>

                        <?php endwhile; ?>

                    <?php endif; ?>

                </select>

            </div>


            <!-- SEARCH SISWA -->

            <div class="flex-1">

                <label
                    for="searchSiswa"
                    class="mb-2 block text-sm font-medium
                           text-gray-700 dark:text-slate-300"
                >
                    Cari Siswa
                </label>


                <input
                    type="text"
                    id="searchSiswa"
                    placeholder="Cari nama atau NISN..."
                    class="w-full rounded-lg border border-gray-300
                           bg-white px-3 py-2
                           text-gray-800
                           placeholder-gray-400
                           focus:border-blue-500
                           focus:ring-2 focus:ring-blue-500
                           dark:border-slate-600
                           dark:bg-slate-800
                           dark:text-slate-100
                           dark:placeholder-slate-500"
                >

            </div>

        </div>

    </div>


    <!-- =================================================
         TABLE
    ================================================== -->

    <div
        class="overflow-hidden rounded-xl border
               border-gray-200 bg-white shadow-sm
               dark:border-slate-700 dark:bg-slate-900"
    >

        <div class="overflow-x-auto">

            <table class="w-full">

                <thead
                    class="border-b border-gray-200 bg-gray-50
                           dark:border-slate-700
                           dark:bg-slate-800"
                >

                    <tr>

                        <th
                            class="px-4 py-3 text-left text-sm
                                   font-semibold text-gray-700
                                   dark:text-slate-300"
                        >
                            No
                        </th>


                        <th
                            class="px-4 py-3 text-left text-sm
                                   font-semibold text-gray-700
                                   dark:text-slate-300"
                        >
                            Siswa
                        </th>


                        <th
                            class="px-4 py-3 text-left text-sm
                                   font-semibold text-gray-700
                                   dark:text-slate-300"
                        >
                            Kelas
                        </th>


                        <th
                            class="px-4 py-3 text-left text-sm
                                   font-semibold text-gray-700
                                   dark:text-slate-300"
                        >
                            DUDI
                        </th>


                        <th
                            class="px-4 py-3 text-left text-sm
                                   font-semibold text-gray-700
                                   dark:text-slate-300"
                        >
                            Tanggal
                        </th>


                        <th
                            class="px-4 py-3 text-left text-sm
                                   font-semibold text-gray-700
                                   dark:text-slate-300"
                        >
                            Keterangan
                        </th>


                        <th
                            class="px-4 py-3 text-left text-sm
                                   font-semibold text-gray-700
                                   dark:text-slate-300"
                        >
                            Foto
                        </th>


                        <th
                            class="px-4 py-3 text-left text-sm
                                   font-semibold text-gray-700
                                   dark:text-slate-300"
                        >
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody
                    id="monitoringTable"
                    class="divide-y divide-gray-200
                           dark:divide-slate-700"
                >

                    <?php

                    $no = 1;

                    if ($result && mysqli_num_rows($result) > 0):

                        while ($d = mysqli_fetch_assoc($result)):

                            $namaSiswa = $d['nama_lengkap'] ?? '';
                            $nisn      = $d['nisn'] ?? '';
                            $namaDudi  = $d['nama_dudi'] ?? '';

                            $searchData = strtolower(
                                $namaSiswa . ' ' . $nisn
                            );

                    ?>

                        <tr
                            class="monitoring-row transition
                                   hover:bg-gray-50
                                   dark:hover:bg-slate-800"
                            data-dudi="<?= htmlspecialchars($namaDudi); ?>"
                            data-search="<?= htmlspecialchars($searchData); ?>"
                        >


                            <!-- NO -->

                            <td
                                class="px-4 py-3 text-sm
                                       text-gray-700
                                       dark:text-slate-300"
                            >
                                <?= $no++; ?>
                            </td>


                            <!-- SISWA -->

                            <td class="px-4 py-3">

                                <div
                                    class="font-medium text-gray-800
                                           dark:text-slate-100"
                                >
                                    <?= htmlspecialchars($namaSiswa); ?>
                                </div>

                                <div
                                    class="text-xs text-gray-500
                                           dark:text-slate-400"
                                >
                                    NISN:
                                    <?= htmlspecialchars($nisn); ?>
                                </div>

                            </td>


                            <!-- KELAS -->

                            <td
                                class="px-4 py-3 text-sm
                                       text-gray-700
                                       dark:text-slate-300"
                            >
                                <?= htmlspecialchars(
                                    $d['kelas'] ?? '-'
                                ); ?>
                            </td>


                            <!-- DUDI -->

                            <td
                                class="px-4 py-3 text-sm
                                       text-gray-700
                                       dark:text-slate-300"
                            >
                                <?= htmlspecialchars(
                                    $namaDudi ?: '-'
                                ); ?>
                            </td>


                            <!-- TANGGAL -->

                            <td
                                class="px-4 py-3 text-sm
                                       text-gray-700
                                       dark:text-slate-300"
                            >
                                <?= htmlspecialchars(
                                    $d['tanggal_monitoring'] ?? '-'
                                ); ?>
                            </td>


                            <!-- KETERANGAN -->

                            <td
                                class="px-4 py-3 text-sm
                                       text-gray-700
                                       dark:text-slate-300"
                            >

                                <div class="max-w-xs">

                                    <?= htmlspecialchars(
                                        $d['keterangan'] ?? '-'
                                    ); ?>

                                </div>

                            </td>


                            <!-- FOTO -->

                            <td class="px-4 py-3">

                                <?php if (!empty($d['foto'])): ?>

                                    <img
                                        src="../uploads/monitoring/<?= htmlspecialchars($d['foto']); ?>"
                                        alt="Foto monitoring"
                                        class="h-16 w-16 rounded-lg
                                               border border-gray-200
                                               object-cover
                                               dark:border-slate-600"
                                    >

                                <?php else: ?>

                                    <span
                                        class="text-sm text-gray-400
                                               dark:text-slate-500"
                                    >
                                        Tidak ada foto
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- =================================================
                                 AKSI
                                 KHUSUS GURU
                            ================================================== -->

                            <td class="px-4 py-3">

                                <div
                                    class="flex items-center gap-2"
                                >


                                    <!-- DETAIL -->

                                    <a
                                        href="detail_monitoring.php?id=<?= (int) $d['id_monitoring']; ?>"
                                        class="rounded-lg bg-blue-100
                                               px-3 py-1.5 text-sm
                                               text-blue-700
                                               hover:bg-blue-200"
                                    >
                                        Detail
                                    </a>


                                    <!-- EDIT -->

                                    <a
                                        href="edit_monitoring.php?id=<?= (int) $d['id_monitoring']; ?>"
                                        class="rounded-lg bg-yellow-100
                                               px-3 py-1.5 text-sm
                                               text-yellow-700
                                               hover:bg-yellow-200"
                                    >
                                        Edit
                                    </a>


                                    <!-- HAPUS -->

                                    <form
                                        method="POST"
                                        action="proses_hapus_monitoring.php"
                                        class="inline"
                                        onsubmit="return confirm('Yakin ingin menghapus data monitoring ini?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id_monitoring"
                                            value="<?= (int) $d['id_monitoring']; ?>"
                                        >


                                        <button
                                            type="submit"
                                            class="rounded-lg bg-red-100
                                                   px-3 py-1.5 text-sm
                                                   text-red-700
                                                   hover:bg-red-200"
                                        >
                                            Hapus
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>


                    <?php

                        endwhile;

                    else:

                    ?>

                        <tr>

                            <td
                                colspan="8"
                                class="px-4 py-10 text-center
                                       text-gray-500
                                       dark:text-slate-400"
                            >
                                Belum ada data monitoring.
                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<!-- =====================================================
     JAVASCRIPT FILTER MONITORING
     
     Dibungkus dalam IIFE supaya tidak bentrok
     dengan theme.js / app.js
====================================================== -->

<script>
(function () {

    const filterDudiElement =
        document.getElementById('filterDudi');

    const searchSiswaElement =
        document.getElementById('searchSiswa');


    // Pastikan elemen tersedia
    if (
        !filterDudiElement ||
        !searchSiswaElement
    ) {
        return;
    }


    function filterMonitoringTable() {

        const selectedDudi =
            filterDudiElement.value
                .toLowerCase()
                .trim();


        const search =
            searchSiswaElement.value
                .toLowerCase()
                .trim();


        const rows =
            document.querySelectorAll(
                '.monitoring-row'
            );


        rows.forEach(function (row) {

            const dudi =
                (row.dataset.dudi || '')
                    .toLowerCase()
                    .trim();


            const text =
                (row.dataset.search || '')
                    .toLowerCase()
                    .trim();


            const cocokDudi =
                selectedDudi === '' ||
                dudi === selectedDudi;


            const cocokSearch =
                search === '' ||
                text.includes(search);


            if (
                cocokDudi &&
                cocokSearch
            ) {

                row.style.display = '';

            } else {

                row.style.display = 'none';

            }

        });

    }


    // Filter DUDI

    filterDudiElement.addEventListener(
        'change',
        filterMonitoringTable
    );


    // Search siswa

    searchSiswaElement.addEventListener(
        'input',
        filterMonitoringTable
    );

})();
</script>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>