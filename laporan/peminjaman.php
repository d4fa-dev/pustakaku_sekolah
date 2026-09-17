<?php

require_once "../config/auth.php";
require_once "../config/database.php";

wajibLogin();

$user = userLogin();

$pageTitle = "Laporan Peminjaman";

$tanggalMulai = $_GET['tanggal_mulai'] ?? '';
$tanggalAkhir = $_GET['tanggal_akhir'] ?? '';
$status = $_GET['status'] ?? '';

$where = [];
$params = [];
$types = '';

/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/

if ($tanggalMulai !== '') {

    $where[] = "p.tanggal_pinjam >= ?";
    $params[] = $tanggalMulai;
    $types .= "s";
}

if ($tanggalAkhir !== '') {

    $where[] = "p.tanggal_pinjam <= ?";
    $params[] = $tanggalAkhir;
    $types .= "s";
}

if (
    $status !== '' &&
    in_array(
        $status,
        ['dipinjam', 'dikembalikan', 'terlambat']
    )
) {

    $where[] = "p.status = ?";
    $params[] = $status;
    $types .= "s";
}

$whereSQL = '';

if (!empty($where)) {

    $whereSQL =
        "WHERE " .
        implode(" AND ", $where);
}


/*
|--------------------------------------------------------------------------
| DATA LAPORAN
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        p.id,
        p.kode_peminjaman,
        p.tanggal_pinjam,
        p.tanggal_jatuh_tempo,
        p.tanggal_kembali,
        p.status,
        p.denda,

        a.nisn,
        a.nama AS nama_anggota,

        b.kode_buku,
        b.judul

    FROM peminjaman p

    LEFT JOIN anggota a
        ON p.anggota_id = a.id

    LEFT JOIN buku b
        ON p.buku_id = b.id

    $whereSQL

    ORDER BY p.id DESC
";

$stmt = mysqli_prepare(
    $koneksi,
    $sql
);

if (!empty($params)) {

    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );
}

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


/*
|--------------------------------------------------------------------------
| STATISTIK
|--------------------------------------------------------------------------
*/

$totalTransaksi = 0;
$totalDenda = 0;

$totalDipinjam = 0;
$totalDikembalikan = 0;
$totalTerlambat = 0;

$dataLaporan = [];

while (
    $row = mysqli_fetch_assoc($result)
) {

    $dataLaporan[] = $row;

    $totalTransaksi++;

    $totalDenda +=
        (float) ($row['denda'] ?? 0);

    if (
        $row['status'] === 'dipinjam'
    ) {

        $totalDipinjam++;
    } elseif (
        $row['status'] === 'dikembalikan'
    ) {

        $totalDikembalikan++;
    } elseif (
        $row['status'] === 'terlambat'
    ) {

        $totalTerlambat++;
    }
}


/*
|--------------------------------------------------------------------------
| DATA GRAFIK STATUS
|--------------------------------------------------------------------------
*/

$statusChart = [
    'Dipinjam' => $totalDipinjam,
    'Dikembalikan' => $totalDikembalikan,
    'Terlambat' => $totalTerlambat
];


/*
|--------------------------------------------------------------------------
| DATA GRAFIK BULANAN
|--------------------------------------------------------------------------
|
| Grafik menggunakan tanggal_pinjam.
|
*/

$monthlyData = [];

foreach (
    $dataLaporan
    as $data
) {

    if (
        empty($data['tanggal_pinjam'])
    ) {
        continue;
    }

    $bulan =
        date(
            'Y-m',
            strtotime(
                $data['tanggal_pinjam']
            )
        );

    if (
        !isset(
            $monthlyData[$bulan]
        )
    ) {

        $monthlyData[$bulan] = 0;
    }

    $monthlyData[$bulan]++;
}

ksort($monthlyData);


/*
|--------------------------------------------------------------------------
| LABEL BULAN
|--------------------------------------------------------------------------
*/

$namaBulan = [
    '01' => 'Januari',
    '02' => 'Februari',
    '03' => 'Maret',
    '04' => 'April',
    '05' => 'Mei',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'Agustus',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember'
];

$chartLabels = [];
$chartValues = [];

foreach (
    $monthlyData
    as $bulan => $jumlah
) {

    $parts = explode(
        '-',
        $bulan
    );

    $tahun = $parts[0];
    $bulanAngka = $parts[1];

    $chartLabels[] =
        $namaBulan[$bulanAngka] .
        ' ' .
        $tahun;

    $chartValues[] =
        $jumlah;
}

?>

<?php require_once "../partials/header.php"; ?>
<?php require_once "../partials/sidebar.php"; ?>

<main class="flex-1 min-w-0">

    <?php require_once "../partials/navbar.php"; ?>

    <div class="p-5 md:p-8 overflow-y-auto">

        <div class="max-w-7xl mx-auto space-y-6">


            <!-- HEADER -->

            <div
                class="flex flex-col
                       sm:flex-row
                       sm:items-center
                       sm:justify-between
                       gap-4">

                <div>

                    <h1
                        class="text-2xl
                               font-semibold
                               text-[#0b1c30]">

                        Laporan Peminjaman

                    </h1>

                    <p
                        class="text-sm
                               text-[#75777e]
                               mt-1">

                        Rekap seluruh transaksi peminjaman
                        perpustakaan.

                    </p>

                </div>


                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex
                           items-center
                           justify-center
                           gap-2
                           px-4
                           py-2.5
                           rounded-lg
                           bg-[#182442]
                           text-white
                           text-sm
                           font-medium
                           hover:bg-[#2e3a59]
                           transition
                           shadow-sm">

                    <span
                        class="material-symbols-outlined
                               text-[19px]">

                        print

                    </span>

                    Cetak Laporan

                </button>

            </div>


            <!-- FILTER -->

            <div
                class="bg-white
                       rounded-xl
                       border border-[#c6c6ce]/20
                       shadow-sm
                       p-5">

                <form
                    method="GET"
                    class="grid
                           grid-cols-1
                           md:grid-cols-2
                           lg:grid-cols-4
                           gap-4">

                    <div>

                        <label
                            class="block
                                   text-sm
                                   font-medium
                                   text-[#0b1c30]
                                   mb-2">

                            Tanggal Mulai

                        </label>

                        <input
                            type="date"
                            name="tanggal_mulai"
                            value="<?= htmlspecialchars(
                                        $tanggalMulai
                                    ) ?>"
                            class="w-full
                                   px-4
                                   py-2.5
                                   rounded-lg
                                   border border-[#c6c6ce]
                                   bg-[#f8f9ff]
                                   text-sm
                                   focus:outline-none
                                   focus:ring-2
                                   focus:ring-[#182442]/20
                                   focus:border-[#182442]">

                    </div>


                    <div>

                        <label
                            class="block
                                   text-sm
                                   font-medium
                                   text-[#0b1c30]
                                   mb-2">

                            Tanggal Akhir

                        </label>

                        <input
                            type="date"
                            name="tanggal_akhir"
                            value="<?= htmlspecialchars(
                                        $tanggalAkhir
                                    ) ?>"
                            class="w-full
                                   px-4
                                   py-2.5
                                   rounded-lg
                                   border border-[#c6c6ce]
                                   bg-[#f8f9ff]
                                   text-sm
                                   focus:outline-none
                                   focus:ring-2
                                   focus:ring-[#182442]/20
                                   focus:border-[#182442]">

                    </div>


                    <div>

                        <label
                            class="block
                                   text-sm
                                   font-medium
                                   text-[#0b1c30]
                                   mb-2">

                            Status

                        </label>

                        <select
                            name="status"
                            class="w-full
                                   px-4
                                   py-2.5
                                   rounded-lg
                                   border border-[#c6c6ce]
                                   bg-[#f8f9ff]
                                   text-sm
                                   focus:outline-none
                                   focus:ring-2
                                   focus:ring-[#182442]/20
                                   focus:border-[#182442]">

                            <option value="">
                                Semua Status
                            </option>

                            <option
                                value="dipinjam"
                                <?= $status === 'dipinjam'
                                    ? 'selected'
                                    : '' ?>>

                                Dipinjam

                            </option>

                            <option
                                value="dikembalikan"
                                <?= $status === 'dikembalikan'
                                    ? 'selected'
                                    : '' ?>>

                                Dikembalikan

                            </option>

                            <option
                                value="terlambat"
                                <?= $status === 'terlambat'
                                    ? 'selected'
                                    : '' ?>>

                                Terlambat

                            </option>

                        </select>

                    </div>


                    <div
                        class="flex
                               items-end
                               gap-2">

                        <button
                            type="submit"
                            class="flex-1
                                   inline-flex
                                   items-center
                                   justify-center
                                   gap-2
                                   px-4
                                   py-2.5
                                   rounded-lg
                                   bg-[#182442]
                                   text-white
                                   text-sm
                                   font-medium
                                   hover:bg-[#2e3a59]
                                   transition">

                            <span
                                class="material-symbols-outlined
                                       text-[19px]">

                                filter_alt

                            </span>

                            Filter

                        </button>


                        <a
                            href="peminjaman.php"
                            class="w-11
                                   h-11
                                   rounded-lg
                                   border border-[#c6c6ce]
                                   flex
                                   items-center
                                   justify-center
                                   text-[#75777e]
                                   hover:bg-[#f8f9ff]
                                   transition"
                            title="Reset">

                            <span
                                class="material-symbols-outlined">

                                refresh

                            </span>

                        </a>

                    </div>

                </form>

            </div>


            <!-- STATISTIK -->

            <div
                class="grid
                       grid-cols-1
                       sm:grid-cols-2
                       lg:grid-cols-5
                       gap-4">


                <!-- TOTAL -->

                <div
                    class="bg-white
                           rounded-xl
                           border border-[#c6c6ce]/20
                           shadow-sm
                           p-5">

                    <div
                        class="flex
                               items-center
                               justify-between">

                        <div>

                            <p
                                class="text-sm
                                       text-[#75777e]">

                                Total Transaksi

                            </p>

                            <p
                                class="text-2xl
                                       font-semibold
                                       text-[#0b1c30]
                                       mt-2">

                                <?= number_format(
                                    $totalTransaksi
                                ) ?>

                            </p>

                        </div>


                        <div
                            class="w-11 h-11
                                   rounded-xl
                                   bg-[#e5eeff]
                                   text-[#182442]
                                   flex
                                   items-center
                                   justify-center">

                            <span
                                class="material-symbols-outlined">

                                receipt_long

                            </span>

                        </div>

                    </div>

                </div>


                <!-- DIPINJAM -->

                <div
                    class="bg-white
                           rounded-xl
                           border border-[#c6c6ce]/20
                           shadow-sm
                           p-5">

                    <div
                        class="flex
                               items-center
                               justify-between">

                        <div>

                            <p
                                class="text-sm
                                       text-[#75777e]">

                                Dipinjam

                            </p>

                            <p
                                class="text-2xl
                                       font-semibold
                                       text-yellow-600
                                       mt-2">

                                <?= number_format(
                                    $totalDipinjam
                                ) ?>

                            </p>

                        </div>


                        <div
                            class="w-11 h-11
                                   rounded-xl
                                   bg-yellow-50
                                   text-yellow-600
                                   flex
                                   items-center
                                   justify-center">

                            <span
                                class="material-symbols-outlined">

                                schedule

                            </span>

                        </div>

                    </div>

                </div>


                <!-- DIKEMBALIKAN -->

                <div
                    class="bg-white
                           rounded-xl
                           border border-[#c6c6ce]/20
                           shadow-sm
                           p-5">

                    <div
                        class="flex
                               items-center
                               justify-between">

                        <div>

                            <p
                                class="text-sm
                                       text-[#75777e]">

                                Dikembalikan

                            </p>

                            <p
                                class="text-2xl
                                       font-semibold
                                       text-green-600
                                       mt-2">

                                <?= number_format(
                                    $totalDikembalikan
                                ) ?>

                            </p>

                        </div>


                        <div
                            class="w-11 h-11
                                   rounded-xl
                                   bg-green-50
                                   text-green-600
                                   flex
                                   items-center
                                   justify-center">

                            <span
                                class="material-symbols-outlined">

                                check_circle

                            </span>

                        </div>

                    </div>

                </div>


                <!-- TERLAMBAT -->

                <div
                    class="bg-white
                           rounded-xl
                           border border-[#c6c6ce]/20
                           shadow-sm
                           p-5">

                    <div
                        class="flex
                               items-center
                               justify-between">

                        <div>

                            <p
                                class="text-sm
                                       text-[#75777e]">

                                Terlambat

                            </p>

                            <p
                                class="text-2xl
                                       font-semibold
                                       text-red-600
                                       mt-2">

                                <?= number_format(
                                    $totalTerlambat
                                ) ?>

                            </p>

                        </div>


                        <div
                            class="w-11 h-11
                                   rounded-xl
                                   bg-red-50
                                   text-red-600
                                   flex
                                   items-center
                                   justify-center">

                            <span
                                class="material-symbols-outlined">

                                warning

                            </span>

                        </div>

                    </div>

                </div>


                <!-- DENDA -->

                <div
                    class="bg-white
                           rounded-xl
                           border border-[#c6c6ce]/20
                           shadow-sm
                           p-5">

                    <div
                        class="flex
                               items-center
                               justify-between">

                        <div>

                            <p
                                class="text-sm
                                       text-[#75777e]">

                                Total Denda

                            </p>

                            <p
                                class="text-xl
                                       font-semibold
                                       text-[#0b1c30]
                                       mt-2">

                                Rp <?= number_format(
                                        $totalDenda,
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                            </p>

                        </div>


                        <div
                            class="w-11 h-11
                                   rounded-xl
                                   bg-red-50
                                   text-red-600
                                   flex
                                   items-center
                                   justify-center">

                            <span
                                class="material-symbols-outlined">

                                payments

                            </span>

                        </div>

                    </div>

                </div>

            </div>


            <!-- GRAFIK -->

            <div
                class="grid
                       grid-cols-1
                       lg:grid-cols-2
                       gap-6">


                <!-- GRAFIK BULANAN -->

                <div
                    class="bg-white
                           rounded-xl
                           border border-[#c6c6ce]/20
                           shadow-sm
                           p-5">

                    <div class="mb-5">

                        <h2
                            class="font-semibold
                                   text-[#0b1c30]">

                            Peminjaman per Bulan

                        </h2>

                        <p
                            class="text-xs
                                   text-[#75777e]
                                   mt-1">

                            Jumlah transaksi berdasarkan
                            tanggal peminjaman.

                        </p>

                    </div>

                    <div class="h-72">

                        <canvas
                            id="grafikBulanan">
                        </canvas>

                    </div>

                </div>


                <!-- GRAFIK STATUS -->

                <div
                    class="bg-white
                           rounded-xl
                           border border-[#c6c6ce]/20
                           shadow-sm
                           p-5">

                    <div class="mb-5">

                        <h2
                            class="font-semibold
                                   text-[#0b1c30]">

                            Status Peminjaman

                        </h2>

                        <p
                            class="text-xs
                                   text-[#75777e]
                                   mt-1">

                            Perbandingan status transaksi
                            peminjaman.

                        </p>

                    </div>

                    <div
                        class="h-72
                               flex
                               justify-center">

                        <canvas
                            id="grafikStatus">
                        </canvas>

                    </div>

                </div>

            </div>


            <!-- DATA PEMINJAMAN -->

            <div
                class="bg-white
                       rounded-xl
                       border border-[#c6c6ce]/20
                       shadow-sm
                       overflow-hidden">

                <div
                    class="px-5
                           py-4
                           border-b
                           border-[#c6c6ce]/20">

                    <h2
                        class="font-semibold
                               text-[#0b1c30]">

                        Data Peminjaman

                    </h2>

                </div>


                <div class="overflow-x-auto">

                    <table
                        class="w-full
                               text-left">

                        <thead>

                            <tr
                                class="bg-[#f8f9ff]
                                       border-b
                                       border-[#c6c6ce]/20
                                       text-xs
                                       text-[#75777e]">

                                <th class="px-5 py-4 font-medium">
                                    No
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Kode
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Anggota
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Buku
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Tgl. Pinjam
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Jatuh Tempo
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Tgl. Kembali
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Status
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Denda
                                </th>

                            </tr>

                        </thead>


                        <tbody class="text-sm">

                            <?php if (
                                count($dataLaporan) > 0
                            ): ?>

                                <?php

                                $no = 1;

                                foreach (
                                    $dataLaporan
                                    as $data
                                ):

                                ?>

                                    <tr
                                        class="border-b
                                               border-[#c6c6ce]/10
                                               hover:bg-[#f8f9ff]
                                               transition">

                                        <td
                                            class="px-5 py-4
                                                   text-[#75777e]">

                                            <?= $no++ ?>

                                        </td>


                                        <td class="px-5 py-4">

                                            <span
                                                class="px-2.5
                                                       py-1
                                                       rounded-md
                                                       bg-[#e5eeff]
                                                       text-[#182442]
                                                       text-xs
                                                       font-medium">

                                                <?= htmlspecialchars(
                                                    $data['kode_peminjaman']
                                                ) ?>

                                            </span>

                                        </td>


                                        <td class="px-5 py-4">

                                            <p
                                                class="font-medium
                                                       text-[#0b1c30]">

                                                <?= htmlspecialchars(
                                                    $data['nama_anggota'] ?? '-'
                                                ) ?>

                                            </p>

                                            <p
                                                class="text-xs
                                                       text-[#75777e]
                                                       mt-1">

                                                <?= htmlspecialchars(
                                                    $data['kode_anggota'] ?? '-'
                                                ) ?>

                                            </p>

                                        </td>


                                        <td class="px-5 py-4">

                                            <p
                                                class="font-medium
                                                       text-[#0b1c30]">

                                                <?= htmlspecialchars(
                                                    $data['judul'] ?? '-'
                                                ) ?>

                                            </p>

                                            <p
                                                class="text-xs
                                                       text-[#75777e]
                                                       mt-1">

                                                <?= htmlspecialchars(
                                                    $data['kode_buku'] ?? '-'
                                                ) ?>

                                            </p>

                                        </td>


                                        <td
                                            class="px-5 py-4
                                                   text-[#45464e]">

                                            <?= htmlspecialchars(
                                                $data['tanggal_pinjam'] ?? '-'
                                            ) ?>

                                        </td>


                                        <td
                                            class="px-5 py-4
                                                   text-[#45464e]">

                                            <?= htmlspecialchars(
                                                $data['tanggal_jatuh_tempo'] ?? '-'
                                            ) ?>

                                        </td>


                                        <td
                                            class="px-5 py-4
                                                   text-[#45464e]">

                                            <?= htmlspecialchars(
                                                $data['tanggal_kembali'] ?? '-'
                                            ) ?>

                                        </td>


                                        <td class="px-5 py-4">

                                            <?php

                                            $statusClass =
                                                'bg-gray-50 text-gray-600';

                                            $statusLabel =
                                                ucfirst(
                                                    $data['status']
                                                );

                                            if (
                                                $data['status'] ===
                                                'dipinjam'
                                            ) {

                                                $statusClass =
                                                    'bg-yellow-50 text-yellow-600';

                                                $statusLabel =
                                                    'Dipinjam';
                                            } elseif (
                                                $data['status'] ===
                                                'dikembalikan'
                                            ) {

                                                $statusClass =
                                                    'bg-green-50 text-green-600';

                                                $statusLabel =
                                                    'Dikembalikan';
                                            } elseif (
                                                $data['status'] ===
                                                'terlambat'
                                            ) {

                                                $statusClass =
                                                    'bg-red-50 text-red-600';

                                                $statusLabel =
                                                    'Terlambat';
                                            }

                                            ?>

                                            <span
                                                class="inline-flex
                                                       px-2.5
                                                       py-1
                                                       rounded-full
                                                       text-xs
                                                       font-medium
                                                       <?= $statusClass ?>">

                                                <?= $statusLabel ?>

                                            </span>

                                        </td>


                                        <td
                                            class="px-5 py-4
                                                   font-medium">

                                            <?php if (
                                                (float)
                                                $data['denda'] > 0
                                            ): ?>

                                                <span
                                                    class="text-red-600">

                                                    Rp <?= number_format(
                                                            (float)
                                                            $data['denda'],
                                                            0,
                                                            ',',
                                                            '.'
                                                        ) ?>

                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="text-[#75777e]">

                                                    -

                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="9"
                                        class="px-5 py-12
                                               text-center">

                                        <div
                                            class="w-14 h-14
                                                   mx-auto
                                                   rounded-xl
                                                   bg-[#e5eeff]
                                                   text-[#182442]
                                                   flex
                                                   items-center
                                                   justify-center">

                                            <span
                                                class="material-symbols-outlined
                                                       text-2xl">

                                                assessment

                                            </span>

                                        </div>

                                        <p
                                            class="font-medium
                                                   text-[#0b1c30]
                                                   mt-4">

                                            Data tidak ditemukan

                                        </p>

                                        <p
                                            class="text-sm
                                                   text-[#75777e]
                                                   mt-1">

                                            Tidak ada transaksi yang sesuai
                                            dengan filter.

                                        </p>

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</main>


<!-- CHART.JS -->

<script
    src="https://cdn.jsdelivr.net/npm/chart.js">
</script>


<script>
    const chartLabels =
        <?= json_encode($chartLabels) ?>;

    const chartValues =
        <?= json_encode($chartValues) ?>;


    /*
    |--------------------------------------------------------------------------
    | GRAFIK PEMINJAMAN PER BULAN
    |--------------------------------------------------------------------------
    */

    const grafikBulanan =
        document.getElementById(
            'grafikBulanan'
        );

    if (grafikBulanan) {

        new Chart(
            grafikBulanan, {
                type: 'line',

                data: {

                    labels: chartLabels,

                    datasets: [

                        {
                            label: 'Jumlah Peminjaman',

                            data: chartValues,

                            borderColor: '#182442',

                            backgroundColor: 'rgba(24, 36, 66, 0.08)',

                            borderWidth: 2,

                            fill: true,

                            tension: 0.35,

                            pointRadius: 4,

                            pointHoverRadius: 6
                        }

                    ]

                },

                options: {

                    responsive: true,

                    maintainAspectRatio: false,

                    plugins: {

                        legend: {
                            display: false
                        }

                    },

                    scales: {

                        y: {

                            beginAtZero: true,

                            ticks: {

                                precision: 0

                            },

                            grid: {

                                color: 'rgba(198, 198, 206, 0.25)'
                            }

                        },

                        x: {

                            grid: {

                                display: false
                            }

                        }

                    }

                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | GRAFIK STATUS
    |--------------------------------------------------------------------------
    */

    const grafikStatus =
        document.getElementById(
            'grafikStatus'
        );

    if (grafikStatus) {

        new Chart(
            grafikStatus, {
                type: 'doughnut',

                data: {

                    labels: [
                        'Dipinjam',
                        'Dikembalikan',
                        'Terlambat'
                    ],

                    datasets: [

                        {

                            data: [

                                <?= $totalDipinjam ?>,

                                <?= $totalDikembalikan ?>,

                                <?= $totalTerlambat ?>

                            ],

                            backgroundColor: [

                                '#eab308',

                                '#22c55e',

                                '#ef4444'

                            ],

                            borderWidth: 0,

                            hoverOffset: 5

                        }

                    ]

                },

                options: {

                    responsive: true,

                    maintainAspectRatio: false,

                    cutout: '68%',

                    plugins: {

                        legend: {

                            position: 'bottom',

                            labels: {

                                usePointStyle: true,

                                padding: 18,

                                font: {

                                    size: 12

                                }

                            }

                        }

                    }

                }

            }

        );

    }
</script>


<style>
    @media print {

        body {
            background: white !important;
        }

        aside,
        nav,
        header,
        button,
        form,
        .no-print {
            display: none !important;
        }

        main {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        main>div {
            padding: 0 !important;
        }

        table {
            font-size: 11px !important;
        }

        table th,
        table td {
            padding: 6px !important;
        }

        canvas {
            max-height: 250px !important;
        }

    }
</style>


<?php require_once "../partials/footer.php"; ?>