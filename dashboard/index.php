<?php

require_once "../config/auth.php";
require_once "../config/database.php";

wajibLogin();

$user = userLogin();

$pageTitle = "Ringkasan Dashboard";

/*
|--------------------------------------------------------------------------
| STATISTIK DASHBOARD
|--------------------------------------------------------------------------
*/

/* Total buku */
$queryTotalBuku = mysqli_query(
    $koneksi,
    "SELECT COALESCE(SUM(stok), 0) AS total
     FROM buku"
);

$totalBuku = (int) mysqli_fetch_assoc($queryTotalBuku)['total'];


/* Peminjaman aktif */
$queryPeminjamanAktif = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM peminjaman
     WHERE status IN ('dipinjam', 'terlambat')"
);

$peminjamanAktif = (int) mysqli_fetch_assoc(
    $queryPeminjamanAktif
)['total'];


/* Terlambat */
$queryTerlambat = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM peminjaman
     WHERE status = 'terlambat'
        OR (
            status = 'dipinjam'
            AND tanggal_jatuh_tempo IS NOT NULL
            AND tanggal_jatuh_tempo < CURDATE()
        )"
);

$totalTerlambat = (int) mysqli_fetch_assoc(
    $queryTerlambat
)['total'];


/* Anggota bulan ini */
$queryAnggotaBaru = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM anggota
     WHERE MONTH(created_at) = MONTH(CURDATE())
       AND YEAR(created_at) = YEAR(CURDATE())"
);

$rowAnggotaBaru = mysqli_fetch_assoc($queryAnggotaBaru);

$anggotaBaru = (int) ($rowAnggotaBaru['total'] ?? 0);


/*
|--------------------------------------------------------------------------
| PEMINJAMAN TERBARU
|--------------------------------------------------------------------------
*/

$queryPeminjamanTerbaru = mysqli_query(
    $koneksi,
    "SELECT
        p.id,
        p.tanggal_jatuh_tempo,
        p.status,
        a.nama AS nama_anggota,
        b.judul,
        b.penulis
     FROM peminjaman p
     LEFT JOIN anggota a
        ON p.anggota_id = a.id
     LEFT JOIN buku b
        ON p.buku_id = b.id
     ORDER BY p.id DESC
     LIMIT 5"
);


/*
|--------------------------------------------------------------------------
| BUKU TERPOPULER
|--------------------------------------------------------------------------
*/

$queryBukuPopuler = mysqli_query(
    $koneksi,
    "SELECT
        b.id,
        b.judul,
        b.penulis,
        COUNT(p.id) AS total_pinjaman
     FROM buku b
     LEFT JOIN peminjaman p
        ON p.buku_id = b.id
     GROUP BY
        b.id,
        b.judul,
        b.penulis
     HAVING total_pinjaman > 0
     ORDER BY total_pinjaman DESC
     LIMIT 5"
);


/*
|--------------------------------------------------------------------------
| HITUNG TANGGAL ANGGOTA
|--------------------------------------------------------------------------
*/

$bulanSekarang = date('m');
$tahunSekarang = date('Y');


require_once "../partials/header.php";
require_once "../partials/sidebar.php";

?>

<main class="flex-1 min-w-0">

    <?php require_once "../partials/navbar.php"; ?>


    <!-- CONTENT -->
    <div class="p-5 md:p-8 overflow-y-auto">

        <div class="max-w-7xl mx-auto space-y-6">


            <?php if (
                isset($_GET['error']) &&
                $_GET['error'] === 'forbidden'
            ): ?>

                <div
                    id="accessAlert"
                    class="flex items-start gap-3
                           p-4 rounded-xl
                           bg-red-50
                           border border-red-200
                           text-red-700">

                    <span class="material-symbols-outlined">
                        lock
                    </span>

                    <div class="flex-1">

                        <p class="text-sm font-semibold">
                            Akses Ditolak
                        </p>

                        <p class="text-sm mt-1 text-red-600">
                            Kamu tidak memiliki izin untuk mengakses halaman tersebut.
                            Fitur ini hanya dapat digunakan oleh Admin.
                        </p>

                    </div>

                    <button
                        type="button"
                        onclick="document.getElementById('accessAlert').remove()"
                        class="text-red-400 hover:text-red-600">

                        <span class="material-symbols-outlined">
                            close
                        </span>

                    </button>

                </div>

            <?php endif; ?>


            <!-- ===================================== -->
            <!-- WELCOME -->
            <!-- ===================================== -->

            <div>

                <h1 class="text-2xl font-semibold text-[#0b1c30]">

                    Selamat datang,
                    <?= htmlspecialchars(
                        $user['nama'] ?? 'Administrator'
                    ) ?>

                </h1>

                <p class="text-sm text-[#75777e] mt-1">

                    Berikut ringkasan aktivitas perpustakaan hari ini.

                </p>

            </div>


            <!-- ===================================== -->
            <!-- STATISTICS -->
            <!-- ===================================== -->

            <div
                class="grid
                       grid-cols-1
                       sm:grid-cols-2
                       xl:grid-cols-4
                       gap-5">


                <!-- TOTAL BUKU -->

                <div
                    class="bg-white
                           rounded-xl
                           p-5
                           border border-[#c6c6ce]/20
                           shadow-sm
                           hover:shadow-md
                           transition">

                    <div class="flex items-start justify-between">

                        <div>

                            <p class="text-sm text-[#45464e]">
                                Total Buku
                            </p>

                            <h3
                                class="text-2xl
                                       font-bold
                                       text-[#0b1c30]
                                       mt-2">

                                <?= number_format(
                                    $totalBuku
                                ) ?>

                            </h3>

                        </div>

                        <div
                            class="w-11 h-11
                                   rounded-lg
                                   bg-[#e5eeff]
                                   text-[#182442]
                                   flex
                                   items-center
                                   justify-center">

                            <span class="material-symbols-outlined">
                                auto_stories
                            </span>

                        </div>

                    </div>

                    <div class="flex items-center gap-2 mt-4">

                        <span
                            class="text-xs
                                   font-medium
                                   text-[#75777e]">

                            Koleksi tersedia

                        </span>

                    </div>

                </div>


                <!-- PEMINJAMAN -->

                <div
                    class="bg-white
                           rounded-xl
                           p-5
                           border border-[#c6c6ce]/20
                           shadow-sm
                           hover:shadow-md
                           transition">

                    <div class="flex items-start justify-between">

                        <div>

                            <p class="text-sm text-[#45464e]">
                                Peminjaman Aktif
                            </p>

                            <h3
                                class="text-2xl
                                       font-bold
                                       text-[#0b1c30]
                                       mt-2">

                                <?= number_format(
                                    $peminjamanAktif
                                ) ?>

                            </h3>

                        </div>

                        <div
                            class="w-11 h-11
                                   rounded-lg
                                   bg-[#e5eeff]
                                   text-[#182442]
                                   flex
                                   items-center
                                   justify-center">

                            <span class="material-symbols-outlined">
                                assignment_return
                            </span>

                        </div>

                    </div>

                    <div class="flex items-center gap-2 mt-4">

                        <span
                            class="text-xs
                                   font-medium
                                   text-[#75777e]">

                            Sedang dipinjam

                        </span>

                    </div>

                </div>


                <!-- TERLAMBAT -->

                <div
                    class="bg-white
                           rounded-xl
                           p-5
                           border border-[#c6c6ce]/20
                           shadow-sm
                           hover:shadow-md
                           transition">

                    <div class="flex items-start justify-between">

                        <div>

                            <p class="text-sm text-[#45464e]">
                                Terlambat Kembali
                            </p>

                            <h3
                                class="text-2xl
                                       font-bold
                                       text-red-600
                                       mt-2">

                                <?= number_format(
                                    $totalTerlambat
                                ) ?>

                            </h3>

                        </div>

                        <div
                            class="w-11 h-11
                                   rounded-lg
                                   bg-red-50
                                   text-red-600
                                   flex
                                   items-center
                                   justify-center">

                            <span class="material-symbols-outlined">
                                warning
                            </span>

                        </div>

                    </div>

                    <div class="flex items-center gap-2 mt-4">

                        <span
                            class="text-xs
                                   font-medium
                                   text-red-600">

                            Perlu perhatian

                        </span>

                    </div>

                </div>


                <!-- ANGGOTA -->

                <div
                    class="bg-white
                           rounded-xl
                           p-5
                           border border-[#c6c6ce]/20
                           shadow-sm
                           hover:shadow-md
                           transition">

                    <div class="flex items-start justify-between">

                        <div>

                            <p class="text-sm text-[#45464e]">
                                Anggota Baru
                            </p>

                            <h3
                                class="text-2xl
                                       font-bold
                                       text-[#0b1c30]
                                       mt-2">

                                <?= number_format(
                                    $anggotaBaru
                                ) ?>

                            </h3>

                        </div>

                        <div
                            class="w-11 h-11
                                   rounded-lg
                                   bg-[#fddfa4]
                                   text-[#312300]
                                   flex
                                   items-center
                                   justify-center">

                            <span class="material-symbols-outlined">
                                person_add
                            </span>

                        </div>

                    </div>

                    <div class="flex items-center gap-2 mt-4">

                        <span
                            class="text-xs
                                   font-medium
                                   text-[#75777e]">

                            Bulan ini

                        </span>

                    </div>

                </div>

            </div>


            <!-- ===================================== -->
            <!-- MAIN GRID -->
            <!-- ===================================== -->

            <div
                class="grid
                       grid-cols-1
                       xl:grid-cols-3
                       gap-6">


                <!-- ================================= -->
                <!-- PEMINJAMAN TERBARU -->
                <!-- ================================= -->

                <div
                    class="xl:col-span-2
                           bg-white
                           rounded-xl
                           border border-[#c6c6ce]/20
                           shadow-sm
                           overflow-hidden">

                    <div
                        class="p-5
                               border-b
                               border-[#c6c6ce]/20
                               flex
                               items-center
                               justify-between">

                        <div>

                            <h2
                                class="text-lg
                                       font-semibold
                                       text-[#0b1c30]">

                                Peminjaman Terbaru

                            </h2>

                            <p
                                class="text-xs
                                       text-[#75777e]
                                       mt-1">

                                Aktivitas peminjaman terbaru

                            </p>

                        </div>

                        <a
                            href="../peminjaman/index.php"
                            class="text-sm
                                   font-medium
                                   text-[#182442]
                                   hover:underline">

                            Lihat Semua →

                        </a>

                    </div>


                    <div class="overflow-x-auto">

                        <table class="w-full text-left">

                            <thead>

                                <tr
                                    class="bg-[#f8f9ff]
                                           text-xs
                                           text-[#75777e]
                                           border-b
                                           border-[#c6c6ce]/20">

                                    <th class="px-5 py-4 font-medium">
                                        Judul Buku
                                    </th>

                                    <th class="px-5 py-4 font-medium">
                                        Anggota
                                    </th>

                                    <th class="px-5 py-4 font-medium">
                                        Tenggat
                                    </th>

                                    <th class="px-5 py-4 font-medium">
                                        Status
                                    </th>

                                </tr>

                            </thead>


                            <tbody class="text-sm">

                                <?php if (
                                    $queryPeminjamanTerbaru &&
                                    mysqli_num_rows(
                                        $queryPeminjamanTerbaru
                                    ) > 0
                                ): ?>

                                    <?php while (
                                        $data = mysqli_fetch_assoc(
                                            $queryPeminjamanTerbaru
                                        )
                                    ): ?>

                                        <?php

                                        $statusData =
                                            $data['status'];

                                        $jatuhTempo =
                                            $data[
                                                'tanggal_jatuh_tempo'
                                            ];

                                        /*
                                         * Jika masih dipinjam tetapi
                                         * tanggal jatuh tempo sudah lewat,
                                         * tampilkan sebagai terlambat.
                                         */
                                        if (
                                            $statusData === 'dipinjam' &&
                                            !empty($jatuhTempo) &&
                                            strtotime(
                                                $jatuhTempo
                                            ) < strtotime(
                                                date('Y-m-d')
                                            )
                                        ) {
                                            $statusData =
                                                'terlambat';
                                        }

                                        ?>

                                        <tr
                                            class="border-b
                                                   border-[#c6c6ce]/10
                                                   hover:bg-[#f8f9ff]
                                                   transition">

                                            <td class="px-5 py-4">

                                                <div
                                                    class="font-medium
                                                           text-[#0b1c30]">

                                                    <?= htmlspecialchars(
                                                        $data[
                                                            'judul'
                                                        ] ?? '-'
                                                    ) ?>

                                                </div>

                                                <div
                                                    class="text-xs
                                                           text-[#75777e]
                                                           mt-1">

                                                    <?= htmlspecialchars(
                                                        $data[
                                                            'penulis'
                                                        ] ?? '-'
                                                    ) ?>

                                                </div>

                                            </td>


                                            <td
                                                class="px-5 py-4
                                                       text-[#45464e]">

                                                <?= htmlspecialchars(
                                                    $data[
                                                        'nama_anggota'
                                                    ] ?? '-'
                                                ) ?>

                                            </td>


                                            <td
                                                class="px-5 py-4
                                                       <?= $statusData === 'terlambat'
                                                           ? 'text-red-600 font-medium'
                                                           : 'text-[#45464e]' ?>">

                                                <?= !empty(
                                                    $jatuhTempo
                                                )
                                                    ? date(
                                                        'd M Y',
                                                        strtotime(
                                                            $jatuhTempo
                                                        )
                                                    )
                                                    : '-'
                                                ?>

                                            </td>


                                            <td class="px-5 py-4">

                                                <?php if (
                                                    $statusData ===
                                                    'terlambat'
                                                ): ?>

                                                    <span
                                                        class="inline-flex
                                                               px-2.5 py-1
                                                               rounded-full
                                                               text-xs
                                                               font-medium
                                                               bg-red-50
                                                               text-red-600">

                                                        Terlambat

                                                    </span>

                                                <?php elseif (
                                                    $statusData ===
                                                    'dikembalikan'
                                                ): ?>

                                                    <span
                                                        class="inline-flex
                                                               px-2.5 py-1
                                                               rounded-full
                                                               text-xs
                                                               font-medium
                                                               bg-green-50
                                                               text-green-700">

                                                        Dikembalikan

                                                    </span>

                                                <?php else: ?>

                                                    <span
                                                        class="inline-flex
                                                               px-2.5 py-1
                                                               rounded-full
                                                               text-xs
                                                               font-medium
                                                               bg-[#e5eeff]
                                                               text-[#182442]">

                                                        Dipinjam

                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                <?php else: ?>

                                    <tr>

                                        <td
                                            colspan="4"
                                            class="px-5 py-12
                                                   text-center">

                                            <div
                                                class="w-12 h-12
                                                       mx-auto
                                                       rounded-xl
                                                       bg-[#e5eeff]
                                                       text-[#182442]
                                                       flex
                                                       items-center
                                                       justify-center">

                                                <span
                                                    class="material-symbols-outlined">

                                                    library_books

                                                </span>

                                            </div>

                                            <p
                                                class="font-medium
                                                       text-[#0b1c30]
                                                       mt-3">

                                                Belum ada peminjaman

                                            </p>

                                            <p
                                                class="text-xs
                                                       text-[#75777e]
                                                       mt-1">

                                                Data peminjaman akan muncul
                                                setelah ada transaksi.

                                            </p>

                                        </td>

                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>


                <!-- ================================= -->
                <!-- BUKU TERPOPULER -->
                <!-- ================================= -->

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
                               mb-5">

                        <div>

                            <h2
                                class="text-lg
                                       font-semibold
                                       text-[#0b1c30]">

                                Buku Terpopuler

                            </h2>

                            <p
                                class="text-xs
                                       text-[#75777e]
                                       mt-1">

                                Buku yang paling sering dipinjam

                            </p>

                        </div>

                        <span
                            class="material-symbols-outlined
                                   text-[#312300]">

                            local_fire_department

                        </span>

                    </div>


                    <div class="space-y-4">

                        <?php if (
                            $queryBukuPopuler &&
                            mysqli_num_rows(
                                $queryBukuPopuler
                            ) > 0
                        ): ?>

                            <?php

                            $warnaBuku = [
                                'bg-[#e5eeff] text-[#182442]',
                                'bg-[#fddfa4] text-[#312300]'
                            ];

                            $indexBuku = 0;

                            ?>

                            <?php while (
                                $buku =
                                mysqli_fetch_assoc(
                                    $queryBukuPopuler
                                )
                            ): ?>

                                <div
                                    class="flex
                                           items-center
                                           gap-4
                                           p-2
                                           rounded-lg
                                           hover:bg-[#f8f9ff]
                                           transition">

                                    <div
                                        class="w-12 h-16
                                               rounded-md
                                               flex
                                               items-center
                                               justify-center
                                               flex-shrink-0
                                               <?= $warnaBuku[
                                                   $indexBuku % 2
                                               ] ?>">

                                        <span
                                            class="material-symbols-outlined">

                                            menu_book

                                        </span>

                                    </div>


                                    <div
                                        class="flex-1
                                               min-w-0">

                                        <h3
                                            class="text-sm
                                                   font-medium
                                                   text-[#0b1c30]
                                                   truncate">

                                            <?= htmlspecialchars(
                                                $buku['judul']
                                            ) ?>

                                        </h3>

                                        <p
                                            class="text-xs
                                                   text-[#75777e]
                                                   mt-1">

                                            <?= htmlspecialchars(
                                                $buku['penulis']
                                                    ?? '-'
                                            ) ?>

                                        </p>

                                    </div>


                                    <div class="text-right">

                                        <p
                                            class="text-sm
                                                   font-semibold
                                                   text-[#182442]">

                                            <?= number_format(
                                                (int)
                                                $buku[
                                                    'total_pinjaman'
                                                ]
                                            ) ?>

                                        </p>

                                        <p
                                            class="text-[10px]
                                                   text-[#75777e]">

                                            Pinjaman

                                        </p>

                                    </div>

                                </div>

                                <?php
                                $indexBuku++;
                                ?>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <div class="text-center py-8">

                                <div
                                    class="w-12 h-12
                                           mx-auto
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

                                <p
                                    class="text-sm
                                           font-medium
                                           text-[#0b1c30]
                                           mt-3">

                                    Belum ada data

                                </p>

                                <p
                                    class="text-xs
                                           text-[#75777e]
                                           mt-1">

                                    Buku populer akan muncul
                                    setelah ada peminjaman.

                                </p>

                            </div>

                        <?php endif; ?>

                    </div>


                    <a
                        href="../buku/index.php"
                        class="mt-5
                               pt-4
                               border-t
                               border-[#c6c6ce]/20
                               flex
                               items-center
                               justify-center
                               text-sm
                               font-medium
                               text-[#182442]
                               hover:underline">

                        Lihat Koleksi Buku

                        <span
                            class="material-symbols-outlined
                                   text-[18px]
                                   ml-1">

                            arrow_forward

                        </span>

                    </a>

                </div>

            </div>


            <!-- ===================================== -->
            <!-- QUICK ACTION -->
            <!-- ===================================== -->

            <div>

                <h2
                    class="text-lg
                           font-semibold
                           text-[#0b1c30]
                           mb-4">

                    Aksi Cepat

                </h2>


                <div
                    class="grid
                           grid-cols-2
                           md:grid-cols-4
                           gap-4">


                    <a
                        href="../buku/tambah.php"
                        class="bg-white
                               border border-[#c6c6ce]/20
                               rounded-xl
                               p-5
                               hover:shadow-md
                               hover:border-[#182442]/20
                               transition">

                        <div
                            class="w-10 h-10
                                   rounded-lg
                                   bg-[#e5eeff]
                                   text-[#182442]
                                   flex
                                   items-center
                                   justify-center
                                   mb-3">

                            <span class="material-symbols-outlined">
                                add
                            </span>

                        </div>

                        <p class="text-sm font-semibold">
                            Tambah Buku
                        </p>

                        <p class="text-xs text-[#75777e] mt-1">
                            Tambahkan koleksi baru
                        </p>

                    </a>


                    <a
                        href="../anggota/tambah.php"
                        class="bg-white
                               border border-[#c6c6ce]/20
                               rounded-xl
                               p-5
                               hover:shadow-md
                               hover:border-[#182442]/20
                               transition">

                        <div
                            class="w-10 h-10
                                   rounded-lg
                                   bg-[#fddfa4]
                                   text-[#312300]
                                   flex
                                   items-center
                                   justify-center
                                   mb-3">

                            <span class="material-symbols-outlined">
                                person_add
                            </span>

                        </div>

                        <p class="text-sm font-semibold">
                            Tambah Anggota
                        </p>

                        <p class="text-xs text-[#75777e] mt-1">
                            Daftarkan anggota baru
                        </p>

                    </a>


                    <a
                        href="../peminjaman/tambah.php"
                        class="bg-white
                               border border-[#c6c6ce]/20
                               rounded-xl
                               p-5
                               hover:shadow-md
                               hover:border-[#182442]/20
                               transition">

                        <div
                            class="w-10 h-10
                                   rounded-lg
                                   bg-[#e5eeff]
                                   text-[#182442]
                                   flex
                                   items-center
                                   justify-center
                                   mb-3">

                            <span class="material-symbols-outlined">
                                library_add
                            </span>

                        </div>

                        <p class="text-sm font-semibold">
                            Peminjaman
                        </p>

                        <p class="text-xs text-[#75777e] mt-1">
                            Catat peminjaman buku
                        </p>

                    </a>


                    <a
                        href="../laporan/peminjaman.php"
                        class="bg-white
                               border border-[#c6c6ce]/20
                               rounded-xl
                               p-5
                               hover:shadow-md
                               hover:border-[#182442]/20
                               transition">

                        <div
                            class="w-10 h-10
                                   rounded-lg
                                   bg-[#e5eeff]
                                   text-[#182442]
                                   flex
                                   items-center
                                   justify-center
                                   mb-3">

                            <span class="material-symbols-outlined">
                                assessment
                            </span>

                        </div>

                        <p class="text-sm font-semibold">
                            Laporan
                        </p>

                        <p class="text-xs text-[#75777e] mt-1">
                            Lihat laporan perpustakaan
                        </p>

                    </a>

                </div>

            </div>


        </div>

    </div>

</main>


<?php require_once "../partials/footer.php"; ?>
