<?php

require_once "../config/auth.php";
require_once "../config/database.php";

$pageTitle = "Peminjaman";

require_once "../partials/header.php";
require_once "../partials/sidebar.php";

?>

<main class="flex-1 min-w-0">

    <?php require_once "../partials/navbar.php"; ?>

    <div class="p-5 md:p-8 overflow-y-auto">

        <div class="max-w-7xl mx-auto space-y-6">

            <?php if (isset($_GET['success'])): ?>

                <div
                    class="flex items-center gap-3 p-4 rounded-lg
                           bg-green-50 border border-green-200 text-green-700">

                    <span class="material-symbols-outlined">
                        check_circle
                    </span>

                    <p class="text-sm font-medium">

                        <?php if ($_GET['success'] === 'added'): ?>

                            Peminjaman berhasil ditambahkan.

                        <?php elseif ($_GET['success'] === 'returned'): ?>

                            Buku berhasil dikembalikan.

                        <?php endif; ?>

                    </p>

                </div>

            <?php endif; ?>


            <?php if (isset($_GET['error'])): ?>

                <div
                    class="flex items-center gap-3 p-4 rounded-lg
                           bg-red-50 border border-red-200 text-red-700">

                    <span class="material-symbols-outlined">
                        error
                    </span>

                    <p class="text-sm font-medium">

                        <?php if ($_GET['error'] === 'not_found'): ?>

                            Data peminjaman tidak ditemukan.

                        <?php else: ?>

                            Terjadi kesalahan saat memproses data.

                        <?php endif; ?>

                    </p>

                </div>

            <?php endif; ?>


            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                <div>

                    <h1 class="text-2xl font-semibold text-[#0b1c30]">
                        Peminjaman
                    </h1>

                    <p class="text-sm text-[#75777e] mt-1">
                        Kelola transaksi peminjaman buku perpustakaan.
                    </p>

                </div>


                <a
                    href="tambah.php"
                    class="inline-flex items-center justify-center gap-2
                           px-4 py-2.5 rounded-lg
                           bg-[#182442] text-white
                           text-sm font-medium
                           hover:bg-[#2e3a59]
                           transition shadow-sm">

                    <span class="material-symbols-outlined text-[20px]">
                        add
                    </span>

                    Tambah Peminjaman

                </a>

            </div>


            <div
                class="bg-white rounded-xl
                       border border-[#c6c6ce]/20
                       shadow-sm p-4">

                <form method="GET">

                    <div class="relative">

                        <span
                            class="material-symbols-outlined
                                   absolute left-3 top-1/2
                                   -translate-y-1/2
                                   text-[#75777e]">
                            search
                        </span>

                        <input
                            type="text"
                            name="search"
                            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                            placeholder="Cari kode peminjaman, anggota, atau buku..."
                            class="w-full pl-11 pr-4 py-3
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
                class="bg-white rounded-xl
                       border border-[#c6c6ce]/20
                       shadow-sm overflow-hidden">

                <div class="overflow-x-auto">

                    <table class="w-full text-left">

                        <thead>

                            <tr
                                class="bg-[#f8f9ff]
                                       border-b border-[#c6c6ce]/20
                                       text-xs text-[#75777e]">

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
                                    Tanggal Pinjam
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Jatuh Tempo
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Status
                                </th>

                                <th class="px-5 py-4 font-medium text-right">
                                    Aksi
                                </th>

                            </tr>

                        </thead>


                        <tbody class="text-sm">

                            <?php

                            $search = trim($_GET['search'] ?? '');

                            if ($search !== '') {

                                $keyword = "%" . $search . "%";

                                $stmt = mysqli_prepare(
                                    $koneksi,
                                    "SELECT
                                        p.*,
                                        a.nisn,
                                        a.nama AS nama_anggota,
                                        b.kode_buku,
                                        b.judul
                                    FROM peminjaman p
                                    INNER JOIN anggota a
                                        ON p.anggota_id = a.id
                                    INNER JOIN buku b
                                        ON p.buku_id = b.id
                                    WHERE p.kode_peminjaman LIKE ?
                                        OR a.nisn LIKE ?
                                        OR a.nama LIKE ?
                                        OR b.kode_buku LIKE ?
                                        OR b.judul LIKE ?
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
                                        p.*,
                                        a.nisn,
                                        a.nama AS nama_anggota,
                                        b.kode_buku,
                                        b.judul
                                    FROM peminjaman p
                                    INNER JOIN anggota a
                                        ON p.anggota_id = a.id
                                    INNER JOIN buku b
                                        ON p.buku_id = b.id
                                    ORDER BY p.id DESC"
                                );
                            }

                            $no = 1;

                            if ($result && mysqli_num_rows($result) > 0):

                                while ($peminjaman = mysqli_fetch_assoc($result)):

                                    $hariIni = strtotime(date('Y-m-d'));

                                    $jatuhTempo = !empty($peminjaman['tanggal_jatuh_tempo'])
                                        ? strtotime($peminjaman['tanggal_jatuh_tempo'])
                                        : null;

                            ?>

                                    <tr
                                        class="border-b
                                               border-[#c6c6ce]/10
                                               hover:bg-[#f8f9ff]
                                               transition">

                                        <td class="px-5 py-4 text-[#75777e]">
                                            <?= $no++ ?>
                                        </td>


                                        <td class="px-5 py-4">

                                            <span
                                                class="px-2.5 py-1
                                                       rounded-md
                                                       bg-[#e5eeff]
                                                       text-[#182442]
                                                       text-xs
                                                       font-medium">

                                                <?= htmlspecialchars($peminjaman['kode_peminjaman']) ?>

                                            </span>

                                        </td>


                                        <td class="px-5 py-4">

                                            <div class="font-medium text-[#0b1c30]">

                                                <?= htmlspecialchars($peminjaman['nama_anggota']) ?>

                                            </div>

                                            <div class="text-xs text-[#75777e] mt-1">

                                                <?= htmlspecialchars($peminjaman['nisn']) ?>

                                            </div>

                                        </td>


                                        <td class="px-5 py-4">

                                            <div class="font-medium text-[#0b1c30]">

                                                <?= htmlspecialchars($peminjaman['judul']) ?>

                                            </div>

                                            <div class="text-xs text-[#75777e] mt-1">

                                                <?= htmlspecialchars($peminjaman['kode_buku']) ?>

                                            </div>

                                        </td>


                                        <td class="px-5 py-4 text-[#45464e]">

                                            <?php if (!empty($peminjaman['tanggal_pinjam'])): ?>

                                                <?= date(
                                                    'd M Y',
                                                    strtotime($peminjaman['tanggal_pinjam'])
                                                ) ?>

                                            <?php else: ?>

                                                -

                                            <?php endif; ?>

                                        </td>


                                        <td class="px-5 py-4">

                                            <?php if ($jatuhTempo): ?>

                                                <span
                                                    class="<?=
                                                            $peminjaman['status'] !== 'dikembalikan'
                                                                && $jatuhTempo < $hariIni
                                                                ? 'text-red-600 font-medium'
                                                                : 'text-[#45464e]'
                                                            ?>">

                                                    <?= date(
                                                        'd M Y',
                                                        $jatuhTempo
                                                    ) ?>

                                                </span>

                                            <?php else: ?>

                                                -

                                            <?php endif; ?>

                                        </td>


                                        <td class="px-5 py-4">

                                            <?php if ($peminjaman['status'] === 'dipinjam'): ?>

                                                <?php if ($jatuhTempo && $jatuhTempo < $hariIni): ?>

                                                    <span
                                                        class="inline-flex items-center gap-1.5
                                                               px-2.5 py-1
                                                               rounded-full
                                                               text-xs font-medium
                                                               bg-red-50
                                                               text-red-700
                                                               border border-red-200">

                                                        <span
                                                            class="w-1.5 h-1.5 rounded-full bg-red-500">
                                                        </span>

                                                        Terlambat

                                                    </span>

                                                <?php else: ?>

                                                    <span
                                                        class="inline-flex items-center gap-1.5
                                                               px-2.5 py-1
                                                               rounded-full
                                                               text-xs font-medium
                                                               bg-[#e5eeff]
                                                               text-[#182442]
                                                               border border-[#d3e4fe]">

                                                        <span
                                                            class="w-1.5 h-1.5 rounded-full bg-[#182442]">
                                                        </span>

                                                        Dipinjam

                                                    </span>

                                                <?php endif; ?>


                                            <?php elseif ($peminjaman['status'] === 'terlambat'): ?>

                                                <span
                                                    class="inline-flex items-center gap-1.5
                                                           px-2.5 py-1
                                                           rounded-full
                                                           text-xs font-medium
                                                           bg-red-50
                                                           text-red-700
                                                           border border-red-200">

                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full bg-red-500">
                                                    </span>

                                                    Terlambat

                                                </span>


                                            <?php elseif ($peminjaman['status'] === 'dikembalikan'): ?>

                                                <span
                                                    class="inline-flex items-center gap-1.5
                                                           px-2.5 py-1
                                                           rounded-full
                                                           text-xs font-medium
                                                           bg-green-50
                                                           text-green-700
                                                           border border-green-200">

                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full bg-green-500">
                                                    </span>

                                                    Dikembalikan

                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <td class="px-5 py-4">

                                            <div class="flex justify-end gap-2">

                                                <a
                                                    href="detail.php?id=<?= $peminjaman['id'] ?>"
                                                    class="w-9 h-9 rounded-lg
                                                           bg-[#e5eeff]
                                                           text-[#182442]
                                                           flex items-center justify-center
                                                           hover:bg-[#d3e4fe]
                                                           transition"
                                                    title="Detail">

                                                    <span
                                                        class="material-symbols-outlined text-[18px]">
                                                        visibility
                                                    </span>

                                                </a>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="8"
                                        class="px-5 py-12 text-center">

                                        <div
                                            class="w-14 h-14
                                                   mx-auto
                                                   rounded-xl
                                                   bg-[#e5eeff]
                                                   text-[#182442]
                                                   flex items-center justify-center">

                                            <span
                                                class="material-symbols-outlined text-2xl">
                                                compare_arrows
                                            </span>

                                        </div>

                                        <p class="font-medium mt-4 text-[#0b1c30]">
                                            Belum ada peminjaman
                                        </p>

                                        <p class="text-sm text-[#75777e] mt-1">
                                            Belum ada transaksi peminjaman yang tersedia.
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