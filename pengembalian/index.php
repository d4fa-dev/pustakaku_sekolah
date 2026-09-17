<?php

require_once "../config/auth.php";
require_once "../config/database.php";

wajibLogin();

$user = userLogin();

$pageTitle = "Pengembalian";

$search = trim($_GET['search'] ?? '');

if ($search !== '') {

    $keyword = "%" . $search . "%";

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT
            p.id,
            p.kode_peminjaman,
            p.tanggal_pinjam,
            p.tanggal_jatuh_tempo,
            p.status,
            a.nisn,
            a.nama AS nama_anggota,
            b.kode_buku,
            b.judul
         FROM peminjaman p
         LEFT JOIN anggota a
            ON p.anggota_id = a.id
         LEFT JOIN buku b
            ON p.buku_id = b.id
         WHERE
            p.status IN ('dipinjam', 'terlambat')
            AND (
                p.kode_peminjaman LIKE ?
                OR a.nisn LIKE ?
                OR a.nama LIKE ?
                OR b.kode_buku LIKE ?
                OR b.judul LIKE ?
            )
         ORDER BY p.id DESC"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sssss",
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
} else {

    $result = mysqli_query(
        $koneksi,
        "SELECT
            p.id,
            p.kode_peminjaman,
            p.tanggal_pinjam,
            p.tanggal_jatuh_tempo,
            p.status,
            a.nisn,
            a.nama AS nama_anggota,
            b.kode_buku,
            b.judul
         FROM peminjaman p
         LEFT JOIN anggota a
            ON p.anggota_id = a.id
         LEFT JOIN buku b
            ON p.buku_id = b.id
         WHERE p.status IN ('dipinjam', 'terlambat')
         ORDER BY p.id DESC"
    );
}

?>

<?php require_once "../partials/header.php"; ?>
<?php require_once "../partials/sidebar.php"; ?>

<main class="flex-1 min-w-0">

    <?php require_once "../partials/navbar.php"; ?>

    <div class="p-5 md:p-8 overflow-y-auto">

        <div class="max-w-7xl mx-auto space-y-6">

            <?php if (isset($_GET['success'])): ?>

                <div
                    class="flex items-start gap-3
                           p-4 rounded-xl
                           bg-green-50
                           border border-green-200
                           text-green-700">

                    <span class="material-symbols-outlined">
                        check_circle
                    </span>

                    <div>

                        <p class="text-sm font-semibold">
                            Pengembalian Berhasil
                        </p>

                        <p class="text-sm mt-1">

                            <?php if (
                                $_GET['success'] === 'returned'
                            ): ?>

                                Buku berhasil dikembalikan.

                            <?php endif; ?>

                        </p>

                    </div>

                </div>

            <?php endif; ?>


            <?php if (isset($_GET['error'])): ?>

                <div
                    class="flex items-start gap-3
                           p-4 rounded-xl
                           bg-red-50
                           border border-red-200
                           text-red-700">

                    <span class="material-symbols-outlined">
                        error
                    </span>

                    <div>

                        <p class="text-sm font-semibold">
                            Gagal
                        </p>

                        <p class="text-sm mt-1">

                            <?php

                            switch ($_GET['error']) {

                                case 'not_found':
                                    echo "Data peminjaman tidak ditemukan.";
                                    break;

                                case 'already_returned':
                                    echo "Buku sudah dikembalikan sebelumnya.";
                                    break;

                                case 'process_failed':
                                    echo "Pengembalian gagal diproses.";
                                    break;

                                default:
                                    echo "Terjadi kesalahan.";
                            }

                            ?>

                        </p>

                    </div>

                </div>

            <?php endif; ?>


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

                        Pengembalian

                    </h1>

                    <p
                        class="text-sm
                               text-[#75777e]
                               mt-1">

                        Kelola buku yang sedang dipinjam dan proses
                        pengembaliannya.

                    </p>

                </div>

            </div>


            <div
                class="bg-white
                       rounded-xl
                       border border-[#c6c6ce]/20
                       shadow-sm
                       p-4">

                <form method="GET">

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
                                        $_GET['search'] ?? ''
                                    ) ?>"
                            placeholder="Cari kode peminjaman, anggota, atau buku..."
                            class="w-full
                                   pl-11
                                   pr-4
                                   py-3
                                   rounded-lg
                                   border border-[#c6c6ce]
                                   bg-[#f8f9ff]
                                   text-sm
                                   focus:outline-none
                                   focus:ring-2
                                   focus:ring-[#182442]/20
                                   focus:border-[#182442]">

                    </div>

                </form>

            </div>


            <div
                class="bg-white
                       rounded-xl
                       border border-[#c6c6ce]/20
                       shadow-sm
                       overflow-hidden">

                <div class="overflow-x-auto">

                    <table class="w-full text-left">

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
                                    Peminjaman
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Anggota
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Buku
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Jatuh Tempo
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Status
                                </th>

                                <th
                                    class="px-5 py-4
                                           font-medium
                                           text-right">

                                    Aksi

                                </th>

                            </tr>

                        </thead>


                        <tbody class="text-sm">

                            <?php

                            $no = 1;

                            if (
                                $result &&
                                mysqli_num_rows($result) > 0
                            ):

                                while (
                                    $data =
                                    mysqli_fetch_assoc($result)
                                ):

                                    $jatuhTempo = $data['tanggal_jatuh_tempo'];

                                    $terlambat = (
                                        $jatuhTempo !== null &&
                                        $jatuhTempo !== '' &&
                                        strtotime($jatuhTempo) <
                                        strtotime(date('Y-m-d'))
                                    );

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

                                            <p
                                                class="text-xs
                                                   text-[#75777e]
                                                   mt-2">

                                                Pinjam:
                                                <?= htmlspecialchars(
                                                    $data['tanggal_pinjam'] ?? '-'
                                                ) ?>

                                            </p>

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
                                                $jatuhTempo ?? '-'
                                            ) ?>

                                        </td>


                                        <td class="px-5 py-4">

                                            <?php if (
                                                $terlambat ||
                                                $data['status'] ===
                                                'terlambat'
                                            ): ?>

                                                <span
                                                    class="inline-flex
                                                       items-center
                                                       gap-1.5
                                                       px-2.5
                                                       py-1
                                                       rounded-full
                                                       bg-red-50
                                                       text-red-600
                                                       text-xs
                                                       font-medium">

                                                    <span
                                                        class="material-symbols-outlined
                                                           text-[15px]">

                                                        warning

                                                    </span>

                                                    Terlambat

                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="inline-flex
                                                       items-center
                                                       gap-1.5
                                                       px-2.5
                                                       py-1
                                                       rounded-full
                                                       bg-yellow-50
                                                       text-yellow-600
                                                       text-xs
                                                       font-medium">

                                                    <span
                                                        class="material-symbols-outlined
                                                           text-[15px]">

                                                        schedule

                                                    </span>

                                                    Dipinjam

                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <td class="px-5 py-4">

                                            <div
                                                class="flex
                                                   justify-end">

                                                <a
                                                    href="proses.php?id=<?= $data['id'] ?>"
                                                    onclick="return confirm('Proses pengembalian buku ini?')"
                                                    class="inline-flex
                                                       items-center
                                                       gap-2
                                                       px-3
                                                       py-2
                                                       rounded-lg
                                                       bg-[#182442]
                                                       text-white
                                                       text-xs
                                                       font-medium
                                                       hover:bg-[#2e3a59]
                                                       transition">

                                                    <span
                                                        class="material-symbols-outlined
                                                           text-[17px]">

                                                        assignment_return

                                                    </span>

                                                    Kembalikan

                                                </a>

                                            </div>

                                        </td>

                                    </tr>

                                <?php

                                endwhile;

                            else:

                                ?>

                                <tr>

                                    <td
                                        colspan="7"
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

                                                assignment_return

                                            </span>

                                        </div>

                                        <p
                                            class="font-medium
                                                   text-[#0b1c30]
                                                   mt-4">

                                            Tidak ada buku yang perlu
                                            dikembalikan

                                        </p>

                                        <p
                                            class="text-sm
                                                   text-[#75777e]
                                                   mt-1">

                                            Semua buku sedang tidak memiliki
                                            transaksi pengembalian aktif.

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

<?php require_once "../partials/footer.php"; ?>