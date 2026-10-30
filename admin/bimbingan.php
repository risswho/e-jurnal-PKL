<?php
session_start();

require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../includes/helpers.php";

require_login('admin');

/*
|--------------------------------------------------------------------------
| Ambil data monitoring
|--------------------------------------------------------------------------
*/
$query = "
    SELECT 
        b.id_monitoring,
        b.tanggal_monitoring,
        b.nama_dudi,
        b.keterangan,
        b.foto,
        s.nisn,
        s.nama_lengkap,
        s.kelas,
        guru.nip,
        guru.nama_guru
    FROM monitoring b
    INNER JOIN siswa s ON b.id_siswa = s.id_siswa
    INNER JOIN guru ON b.id_guru = guru.id_guru
    ORDER BY b.tanggal_monitoring DESC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Gagal mengambil data monitoring: " . mysqli_error($conn));
}

$title = 'Dokumentasi Monitoring';
$base = '../';
$role = 'admin';
$nama = $_SESSION['nama'] ?? 'Admin';
$active = 'monitoring';

require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/toast.php';

/*
|--------------------------------------------------------------------------
| Ambil daftar DUDI untuk filter
|--------------------------------------------------------------------------
*/
$dudi_list = mysqli_query(
    $conn,
    "SELECT DISTINCT nama_dudi 
     FROM monitoring 
     WHERE nama_dudi IS NOT NULL 
     AND nama_dudi != ''
     ORDER BY nama_dudi ASC"
);
?>

<!-- Header -->
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-xl font-extrabold tracking-tight">
            Dokumentasi Monitoring
        </h2>

        <p class="text-sm text-slate-500 dark:text-slate-400">
            Arsip monitoring guru ke siswa PKL.
        </p>
    </div>
</div>


<!-- Card Monitoring -->
<div class="mt-4 rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

    <!-- Filter -->
    <div class="border-b border-slate-200 p-4 dark:border-slate-800">

        <div class="flex flex-col gap-2 sm:flex-row">

            <!-- Filter DUDI -->
            <select
                id="filterDudi"
                class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-950 sm:w-64"
            >
                <option value="">Semua DUDI</option>

                <?php while ($row = mysqli_fetch_assoc($dudi_list)): ?>

                    <option value="<?= htmlspecialchars($row['nama_dudi']); ?>">
                        <?= htmlspecialchars($row['nama_dudi']); ?>
                    </option>

                <?php endwhile; ?>
            </select>


            <!-- Search -->
            <input
                id="searchBim"
                type="search"
                placeholder="Cari guru / siswa / DUDI..."
                class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-950"
            >

        </div>

    </div>


    <!-- Table -->
    <div class="overflow-x-auto">

        <table
            id="tblBim"
            data-paginate="8"
            data-paginate-nav="#pgBimA"
            class="w-full min-w-[860px] text-left text-sm"
        >

            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-400">

                    <th class="px-4 py-3">
                        Tanggal
                    </th>

                    <th class="px-4 py-3">
                        Guru
                    </th>

                    <th class="px-4 py-3">
                        Siswa
                    </th>

                    <th class="px-4 py-3">
                        DUDI
                    </th>

                    <th class="px-4 py-3">
                        Keterangan
                    </th>

                    <th class="px-4 py-3">
                        Foto
                    </th>

                    <!-- <th class="px-4 py-3">
                        Aksi
                    </th> -->

                </tr>
            </thead>


            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">

                <?php
                $tot_rows = mysqli_num_rows($result);

                if ($tot_rows > 0):

                    while ($b = mysqli_fetch_assoc($result)):
                ?>

                    <tr
                        class="hover:bg-slate-50 dark:hover:bg-slate-800/50"
                        data-dudi="<?= htmlspecialchars($b['nama_dudi']); ?>"
                    >

                        <!-- Tanggal -->
                        <td class="whitespace-nowrap px-4 py-3 text-xs font-semibold">
                            <?= htmlspecialchars($b['tanggal_monitoring']); ?>
                        </td>


                        <!-- Guru -->
                        <td class="px-4 py-3">

                            <p class="font-semibold">
                                <?= htmlspecialchars($b['nama_guru']); ?>
                            </p>

                            <p class="font-mono text-xs text-slate-500">
                                <?= htmlspecialchars($b['nip']); ?>
                            </p>

                        </td>


                        <!-- Siswa -->
                        <td class="px-4 py-3">

                            <p class="font-semibold">
                                <?= htmlspecialchars($b['nama_lengkap']); ?>
                            </p>

                            <p class="text-xs text-slate-500">
                                <?= htmlspecialchars($b['nisn']); ?>
                                •
                                <?= htmlspecialchars($b['kelas']); ?>
                            </p>

                        </td>


                        <!-- DUDI -->
                        <td class="px-4 py-3">
                            <?= htmlspecialchars($b['nama_dudi']); ?>
                        </td>


                        <!-- Keterangan -->
                        <td class="max-w-[240px] px-4 py-3">

                            <p class="line-clamp-2 text-slate-600 dark:text-slate-300">
                                <?= nl2br(htmlspecialchars($b['keterangan'])); ?>
                            </p>

                        </td>


                        <!-- Foto -->
                        <td class="px-4 py-3">

                            <?php if (!empty($b['foto'])): ?>

                                <button
                                    type="button"
                                    onclick="openLightbox('../uploads/monitoring/<?= htmlspecialchars($b['foto']); ?>')"
                                >
                                    <img
                                        src="../uploads/monitoring/<?= htmlspecialchars($b['foto']); ?>"
                                        class="h-12 w-12 rounded-lg border border-slate-200 object-cover dark:border-slate-700"
                                        alt="Foto monitoring"
                                    >
                                </button>

                            <?php else: ?>

                                <span class="text-xs text-slate-400">
                                    —
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- AKSI ADMIN -->
                        <!-- <td class="px-4 py-3">

                             Admin hanya boleh melihat detail
                            <a
                                href="../guru/detail_monitoring.php?id=<?= (int)$b['id_monitoring']; ?>"
                                class="inline-block rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                            >
                                Detail
                            </a>

                        </td> -->

                    </tr>

                <?php
                    endwhile;

                else:
                ?>

                    <tr>

                        <td
                            colspan="7"
                            class="px-4 py-10 text-center text-slate-500 dark:text-slate-400"
                        >
                            Belum ada dokumentasi monitoring.
                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>


    <!-- Pagination -->
    <div class="flex items-center justify-between gap-2 border-t border-slate-200 p-4 dark:border-slate-800">

        <p class="text-xs text-slate-500 dark:text-slate-400">
            <?= $tot_rows; ?> data
        </p>

        <div
            id="pgBimA"
            class="flex flex-wrap gap-1.5"
        ></div>

    </div>

</div>


<!-- Filter JavaScript -->
<script>
(function () {

    const searchInput = document.getElementById('searchBim');
    const filterDudi = document.getElementById('filterDudi');
    const table = document.getElementById('tblBim');

    if (!searchInput || !filterDudi || !table) {
        return;
    }

    function applyBimFilter() {

        const keyword = (
            searchInput.value || ''
        ).toLowerCase().trim();

        const selectedDudi = filterDudi.value;

        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(function (row) {

            const text = row.textContent.toLowerCase();

            const rowDudi = row.dataset.dudi || '';

            const matchKeyword =
                !keyword ||
                text.includes(keyword);

            const matchDudi =
                !selectedDudi ||
                rowDudi === selectedDudi;

            const visible =
                matchKeyword &&
                matchDudi;

            row.setAttribute(
                'data-filtered',
                visible ? '1' : '0'
            );

        });


        table.setAttribute('data-page', '1');

        if (window.__ejPaginate) {
            window.__ejPaginate();
        }

    }


    searchInput.addEventListener(
        'input',
        applyBimFilter
    );

    filterDudi.addEventListener(
        'change',
        applyBimFilter
    );

})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>