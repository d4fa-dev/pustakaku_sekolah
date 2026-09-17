<?php

require_once "../config/auth.php";
require_once "../config/database.php";

wajibLogin();

$user = userLogin();

$pageTitle = "Laporan Buku";

$search = trim($_GET['search'] ?? '');
$stok = $_GET['stok'] ?? '';

$where = [];
$params = [];
$types = '';

if ($search !== '') {

    $where[] = "(
        b.kode_buku LIKE ?
        OR b.judul LIKE ?
        OR b.penulis LIKE ?
        OR b.penerbit LIKE ?
    )";

    $keyword = "%" . $search . "%";

    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;

    $types .= "ssss";
}

if ($stok === 'habis') {

    $where[] = "b.stok <= 0";

} elseif ($stok === 'menipis') {

    $where[] = "b.stok > 0 AND b.stok <= 3";

} elseif ($stok === 'tersedia') {

    $where[] = "b.stok > 3";
}

$whereSQL = '';

if (!empty($where)) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}


/*
|--------------------------------------------------------------------------
| DATA BUKU
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        b.id,
        b.kode_buku,
        b.judul,
        b.penulis,
        b.penerbit,
        b.tahun_terbit,
        b.stok,
        COUNT(p.id) AS total_dipinjam
    FROM buku b
    LEFT JOIN peminjaman p
        ON b.id = p.buku_id
    $whereSQL
    GROUP BY
        b.id,
        b.kode_buku,
        b.judul,
        b.penulis,
        b.penerbit,
        b.tahun_terbit,
        b.stok
    ORDER BY b.id DESC
";

$stmt = mysqli_prepare($koneksi, $sql);

if (!empty($params)) {

    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );
}

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$dataBuku = [];

while ($row = mysqli_fetch_assoc($result)) {

    $dataBuku[] = $row;
}


/*
|--------------------------------------------------------------------------
| STATISTIK
|--------------------------------------------------------------------------
*/

$statistikResult = mysqli_query(
    $koneksi,
    "SELECT
        COUNT(*) AS total_judul,
        COALESCE(SUM(stok), 0) AS total_stok,
        COALESCE(SUM(
            CASE
                WHEN stok <= 3 THEN 1
                ELSE 0
            END
        ), 0) AS stok_menipis,
        COALESCE(SUM(
            CASE
                WHEN stok <= 0 THEN 1
                ELSE 0
            END
        ), 0) AS stok_habis
     FROM buku"
);

$statistik = mysqli_fetch_assoc($statistikResult);

$totalJudul = (int) ($statistik['total_judul'] ?? 0);
$totalStok = (int) ($statistik['total_stok'] ?? 0);
$stokMenipis = (int) ($statistik['stok_menipis'] ?? 0);
$stokHabis = (int) ($statistik['stok_habis'] ?? 0);


/*
|--------------------------------------------------------------------------
| TOTAL PEMINJAMAN BUKU
|--------------------------------------------------------------------------
*/

$totalPeminjamanResult = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM peminjaman"
);

$totalPeminjamanData =
    mysqli_fetch_assoc(
        $totalPeminjamanResult
    );

$totalPeminjaman =
    (int) (
        $totalPeminjamanData['total']
        ?? 0
    );


/*
|--------------------------------------------------------------------------
| GRAFIK 1
| 10 BUKU PALING SERING DIPINJAM
|--------------------------------------------------------------------------
*/

$grafikPopulerResult = mysqli_query(
    $koneksi,
    "SELECT
        b.judul,
        COUNT(p.id) AS total
     FROM buku b
     LEFT JOIN peminjaman p
        ON b.id = p.buku_id
     GROUP BY b.id, b.judul
     ORDER BY total DESC
     LIMIT 10"
);

$grafikPopuler = [];

while (
    $row =
    mysqli_fetch_assoc(
        $grafikPopulerResult
    )
) {

    $grafikPopuler[] = [
        'judul' => $row['judul'],
        'total' => (int) $row['total']
    ];
}


/*
|--------------------------------------------------------------------------
| GRAFIK 2
| STATUS STOK
|--------------------------------------------------------------------------
*/

$grafikStok = [
    [
        'label' => 'Habis',
        'total' => $stokHabis
    ],
    [
        'label' => 'Menipis',
        'total' => max(
            0,
            $stokMenipis - $stokHabis
        )
    ],
    [
        'label' => 'Aman',
        'total' => max(
            0,
            $totalJudul - $stokMenipis
        )
    ]
];

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

                        Laporan Buku

                    </h1>

                    <p
                        class="text-sm
                               text-[#75777e]
                               mt-1">

                        Rekap koleksi buku dan kondisi stok
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
                           lg:grid-cols-3
                           gap-4">


                    <div>

                        <label
                            class="block
                                   text-sm
                                   font-medium
                                   text-[#0b1c30]
                                   mb-2">

                            Cari Buku

                        </label>

                        <div class="relative">

                            <span
                                class="material-symbols-outlined
                                       absolute
                                       left-3
                                       top-1/2
                                       -translate-y-1/2
                                       text-[#75777e]">

                                search

                            </span>

                            <input
                                type="text"
                                name="search"
                                value="<?= htmlspecialchars(
                                    $search
                                ) ?>"
                                placeholder="Kode, judul, penulis..."
                                class="w-full
                                       pl-11
                                       pr-4
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

                    </div>


                    <div>

                        <label
                            class="block
                                   text-sm
                                   font-medium
                                   text-[#0b1c30]
                                   mb-2">

                            Kondisi Stok

                        </label>

                        <select
                            name="stok"
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
                                Semua Kondisi
                            </option>

                            <option
                                value="tersedia"
                                <?= $stok === 'tersedia'
                                    ? 'selected'
                                    : '' ?>>

                                Stok Aman

                            </option>

                            <option
                                value="menipis"
                                <?= $stok === 'menipis'
                                    ? 'selected'
                                    : '' ?>>

                                Stok Menipis

                            </option>

                            <option
                                value="habis"
                                <?= $stok === 'habis'
                                    ? 'selected'
                                    : '' ?>>

                                Stok Habis

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
                            href="buku.php"
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
                       xl:grid-cols-4
                       gap-4">


                <!-- TOTAL JUDUL -->

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

                                Total Judul Buku

                            </p>

                            <p
                                class="text-2xl
                                       font-semibold
                                       text-[#0b1c30]
                                       mt-2">

                                <?= number_format(
                                    $totalJudul
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

                                menu_book

                            </span>

                        </div>

                    </div>

                </div>


                <!-- TOTAL STOK -->

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

                                Total Stok

                            </p>

                            <p
                                class="text-2xl
                                       font-semibold
                                       text-[#0b1c30]
                                       mt-2">

                                <?= number_format(
                                    $totalStok
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

                                inventory_2

                            </span>

                        </div>

                    </div>

                </div>


                <!-- STOK MENIPIS -->

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

                                Stok Menipis

                            </p>

                            <p
                                class="text-2xl
                                       font-semibold
                                       text-[#0b1c30]
                                       mt-2">

                                <?= number_format(
                                    $stokMenipis
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

                                warning

                            </span>

                        </div>

                    </div>

                </div>


                <!-- STOK HABIS -->

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

                                Stok Habis

                            </p>

                            <p
                                class="text-2xl
                                       font-semibold
                                       text-[#0b1c30]
                                       mt-2">

                                <?= number_format(
                                    $stokHabis
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

                                inventory

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


                <!-- GRAFIK BUKU POPULER -->

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

                            Buku Paling Sering Dipinjam

                        </h2>

                        <p
                            class="text-xs
                                   text-[#75777e]
                                   mt-1">

                            10 buku dengan jumlah peminjaman
                            terbanyak.

                        </p>

                    </div>


                    <div
                        class="space-y-4">

                        <?php if (
                            count($grafikPopuler) > 0
                        ): ?>

                            <?php

                            $maxPeminjaman =
                                max(
                                    array_column(
                                        $grafikPopuler,
                                        'total'
                                    )
                                );

                            foreach (
                                $grafikPopuler
                                as $item
                            ):

                                $persentase =
                                    $maxPeminjaman > 0
                                        ? (
                                            $item['total'] /
                                            $maxPeminjaman
                                        ) * 100
                                        : 0;

                            ?>

                                <div>

                                    <div
                                        class="flex
                                               items-center
                                               justify-between
                                               gap-3
                                               mb-1.5">

                                        <p
                                            class="text-sm
                                                   font-medium
                                                   text-[#0b1c30]
                                                   truncate">

                                            <?= htmlspecialchars(
                                                $item['judul']
                                            ) ?>

                                        </p>

                                        <span
                                            class="text-xs
                                                   font-medium
                                                   text-[#75777e]
                                                   shrink-0">

                                            <?= $item['total'] ?>
                                            kali

                                        </span>

                                    </div>

                                    <div
                                        class="h-2
                                               rounded-full
                                               bg-[#edf0f7]
                                               overflow-hidden">

                                        <div
                                            class="h-full
                                                   rounded-full
                                                   bg-[#182442]"
                                            style="width: <?= $persentase ?>%">

                                        </div>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <div
                                class="py-8
                                       text-center
                                       text-sm
                                       text-[#75777e]">

                                Belum ada data peminjaman.

                            </div>

                        <?php endif; ?>

                    </div>

                </div>


                <!-- GRAFIK STATUS STOK -->

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

                            Kondisi Stok Buku

                        </h2>

                        <p
                            class="text-xs
                                   text-[#75777e]
                                   mt-1">

                            Distribusi kondisi stok koleksi buku.

                        </p>

                    </div>


                    <div
                        class="space-y-5">


                        <?php foreach (
                            $grafikStok
                            as $item
                        ): ?>

                            <?php

                            $persentaseStok =
                                $totalJudul > 0
                                    ? (
                                        $item['total'] /
                                        $totalJudul
                                    ) * 100
                                    : 0;

                            if (
                                $item['label'] ===
                                'Habis'
                            ) {

                                $barClass =
                                    'bg-red-500';

                            } elseif (
                                $item['label'] ===
                                'Menipis'
                            ) {

                                $barClass =
                                    'bg-yellow-500';

                            } else {

                                $barClass =
                                    'bg-green-500';
                            }

                            ?>

                            <div>

                                <div
                                    class="flex
                                           items-center
                                           justify-between
                                           mb-2">

                                    <div
                                        class="flex
                                               items-center
                                               gap-2">

                                        <span
                                            class="w-2.5
                                                   h-2.5
                                                   rounded-full
                                                   <?= $barClass ?>">
                                        </span>

                                        <span
                                            class="text-sm
                                                   font-medium
                                                   text-[#0b1c30]">

                                            <?= $item['label'] ?>

                                        </span>

                                    </div>

                                    <span
                                        class="text-sm
                                               font-semibold
                                               text-[#0b1c30]">

                                        <?= $item['total'] ?>

                                    </span>

                                </div>

                                <div
                                    class="h-2
                                           rounded-full
                                           bg-[#edf0f7]
                                           overflow-hidden">

                                    <div
                                        class="h-full
                                               rounded-full
                                               <?= $barClass ?>"
                                        style="width: <?= $persentaseStok ?>%">

                                    </div>

                                </div>

                                <p
                                    class="text-xs
                                           text-[#75777e]
                                           mt-1">

                                    <?= number_format(
                                        $persentaseStok,
                                        1
                                    ) ?>%

                                </p>

                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>

            </div>


            <!-- INFORMASI PEMINJAMAN -->

            <div
                class="bg-white
                       rounded-xl
                       border border-[#c6c6ce]/20
                       shadow-sm
                       p-5">

                <div
                    class="flex
                           items-center
                           justify-between
                           gap-4">

                    <div>

                        <p
                            class="text-sm
                                   text-[#75777e]">

                            Total Peminjaman

                        </p>

                        <p
                            class="text-2xl
                                   font-semibold
                                   text-[#0b1c30]
                                   mt-1">

                            <?= number_format(
                                $totalPeminjaman
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

                            trending_up

                        </span>

                    </div>

                </div>

            </div>


            <!-- TABEL -->

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

                        Data Buku

                    </h2>

                    <p
                        class="text-xs
                               text-[#75777e]
                               mt-1">

                        Daftar koleksi buku berdasarkan filter
                        yang dipilih.

                    </p>

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
                                    Kode Buku
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Judul
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Penulis
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Penerbit
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Tahun
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Stok
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Dipinjam
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Kondisi
                                </th>

                            </tr>

                        </thead>


                        <tbody class="text-sm">

                            <?php if (
                                count($dataBuku) > 0
                            ): ?>

                                <?php

                                $no = 1;

                                foreach (
                                    $dataBuku
                                    as $data
                                ):

                                    $stokBuku =
                                        (int) $data['stok'];

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


                                        <td
                                            class="px-5 py-4">

                                            <span
                                                class="px-2.5
                                                       py-1
                                                       rounded-md
                                                       bg-[#e5eeff]
                                                       text-[#182442]
                                                       text-xs
                                                       font-medium">

                                                <?= htmlspecialchars(
                                                    $data[
                                                        'kode_buku'
                                                    ]
                                                ) ?>

                                            </span>

                                        </td>


                                        <td
                                            class="px-5 py-4">

                                            <p
                                                class="font-medium
                                                       text-[#0b1c30]">

                                                <?= htmlspecialchars(
                                                    $data[
                                                        'judul'
                                                    ]
                                                ) ?>

                                            </p>

                                        </td>


                                        <td
                                            class="px-5 py-4
                                                   text-[#45464e]">

                                            <?= htmlspecialchars(
                                                $data[
                                                    'penulis'
                                                ] ?? '-'
                                            ) ?>

                                        </td>


                                        <td
                                            class="px-5 py-4
                                                   text-[#45464e]">

                                            <?= htmlspecialchars(
                                                $data[
                                                    'penerbit'
                                                ] ?? '-'
                                            ) ?>

                                        </td>


                                        <td
                                            class="px-5 py-4
                                                   text-[#45464e]">

                                            <?= htmlspecialchars(
                                                $data[
                                                    'tahun_terbit'
                                                ] ?? '-'
                                            ) ?>

                                        </td>


                                        <td
                                            class="px-5 py-4">

                                            <span
                                                class="font-semibold
                                                       <?= $stokBuku <= 0
                                                           ? 'text-red-600'
                                                           : (
                                                               $stokBuku <= 3
                                                                   ? 'text-yellow-600'
                                                                   : 'text-green-600'
                                                           ) ?>">

                                                <?= $stokBuku ?>

                                            </span>

                                        </td>


                                        <td
                                            class="px-5 py-4
                                                   text-[#45464e]">

                                            <?= number_format(
                                                (int)
                                                $data[
                                                    'total_dipinjam'
                                                ]
                                            ) ?>

                                        </td>


                                        <td
                                            class="px-5 py-4">

                                            <?php if (
                                                $stokBuku <= 0
                                            ): ?>

                                                <span
                                                    class="inline-flex
                                                           px-2.5
                                                           py-1
                                                           rounded-full
                                                           bg-red-50
                                                           text-red-600
                                                           text-xs
                                                           font-medium">

                                                    Habis

                                                </span>

                                            <?php elseif (
                                                $stokBuku <= 3
                                            ): ?>

                                                <span
                                                    class="inline-flex
                                                           px-2.5
                                                           py-1
                                                           rounded-full
                                                           bg-yellow-50
                                                           text-yellow-600
                                                           text-xs
                                                           font-medium">

                                                    Menipis

                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="inline-flex
                                                           px-2.5
                                                           py-1
                                                           rounded-full
                                                           bg-green-50
                                                           text-green-600
                                                           text-xs
                                                           font-medium">

                                                    Aman

                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="9"
                                        class="px-5
                                               py-12
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

                                                menu_book

                                            </span>

                                        </div>

                                        <p
                                            class="font-medium
                                                   text-[#0b1c30]
                                                   mt-4">

                                            Data buku tidak ditemukan

                                        </p>

                                        <p
                                            class="text-sm
                                                   text-[#75777e]
                                                   mt-1">

                                            Tidak ada buku yang sesuai
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

    main > div {
        padding: 0 !important;
    }

    table {
        font-size: 11px !important;
    }

    table th,
    table td {
        padding: 6px !important;
    }

}

</style>


<?php require_once "../partials/footer.php"; ?>
