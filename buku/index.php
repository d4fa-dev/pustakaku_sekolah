<?php

$pageTitle = "Koleksi Buku";

require_once "../partials/header.php";
require_once "../partials/sidebar.php";
require_once "../config/database.php";

?>

<main class="flex-1 min-w-0">

    <?php require_once "../partials/navbar.php"; ?>

    <div class="p-5 md:p-8 overflow-y-auto">

        <div class="max-w-7xl mx-auto space-y-6">
            <?php if (isset($_GET['success'])): ?>

                <div
                    class="flex items-center gap-3
               p-4
               rounded-lg
               bg-green-50
               border border-green-200
               text-green-700">

                    <span class="material-symbols-outlined">
                        check_circle
                    </span>

                    <p class="text-sm font-medium">

                        <?php if ($_GET['success'] === 'added'): ?>

                            Buku berhasil ditambahkan.

                        <?php elseif ($_GET['success'] === 'updated'): ?>

                            Data buku berhasil diperbarui.

                        <?php elseif ($_GET['success'] === 'deleted'): ?>

                            Buku berhasil dihapus.

                        <?php endif; ?>

                    </p>

                </div>

            <?php endif; ?>


            <!-- HEADER -->

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                <div>

                    <h1 class="text-2xl font-semibold text-[#0b1c30]">
                        Koleksi Buku
                    </h1>

                    <p class="text-sm text-[#75777e] mt-1">
                        Kelola seluruh koleksi buku perpustakaan.
                    </p>

                </div>


                <a
                    href="tambah.php"
                    class="inline-flex items-center justify-center gap-2
                           px-4 py-2.5
                           rounded-lg
                           bg-[#182442]
                           text-white
                           text-sm font-medium
                           hover:bg-[#2e3a59]
                           transition
                           shadow-sm">

                    <span class="material-symbols-outlined text-[20px]">
                        add
                    </span>

                    Tambah Buku

                </a>

            </div>


            <!-- SEARCH & FILTER -->

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
                                   absolute left-3 top-1/2
                                   -translate-y-1/2
                                   text-[#75777e]">
                            search
                        </span>

                        <input
                            type="text"
                            name="search"
                            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                            placeholder="Cari berdasarkan kode, judul, atau penulis..."
                            class="w-full
                                   pl-11 pr-4 py-3
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


            <!-- TABLE -->

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
                                       border-b border-[#c6c6ce]/20
                                       text-xs
                                       text-[#75777e]">

                                <th class="px-5 py-4 font-medium">
                                    No
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Kode
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Buku
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Penerbit
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Tahun
                                </th>

                                <th class="px-6 py-4 font-medium">Kategori</th>

                                <th class="px-5 py-4 font-medium">
                                    Stok
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
                                         buku.*,
                                         kategori.nama_kategori
                                     FROM buku
                                     LEFT JOIN kategori
                                         ON buku.kategori_id = kategori.id
                                     WHERE buku.kode_buku LIKE ?
                                         OR buku.judul LIKE ?
                                         OR buku.penulis LIKE ?
                                     ORDER BY buku.id DESC"
                                );

                                mysqli_stmt_bind_param(
                                    $stmt,
                                    "sss",
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
                                        buku.*,
                                        kategori.nama_kategori
                                     FROM buku
                                     LEFT JOIN kategori
                                        ON buku.kategori_id = kategori.id
                                     ORDER BY buku.id DESC"
                                );
                            }

                            $no = 1;

                            if (mysqli_num_rows($result) > 0):

                                while ($buku = mysqli_fetch_assoc($result)):

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

                                                <?= htmlspecialchars($buku['kode_buku']) ?>

                                            </span>

                                        </td>


                                        <td class="px-5 py-4">

                                            <div class="font-medium text-[#0b1c30]">

                                                <?= htmlspecialchars($buku['judul']) ?>

                                            </div>

                                            <div class="text-xs text-[#75777e] mt-1">

                                                <?= htmlspecialchars($buku['penulis']) ?>

                                            </div>

                                        </td>


                                        <td class="px-5 py-4 text-[#45464e]">

                                            <?= htmlspecialchars($buku['penerbit'] ?: '-') ?>

                                        </td>


                                        <td class="px-5 py-4 text-[#45464e]">

                                            <?= htmlspecialchars($buku['tahun_terbit'] ?: '-') ?>

                                        </td>


                                        <td class="px-6 py-4">

                                            <?php if (!empty($buku['nama_kategori'])): ?>

                                                <span
                                                    class="inline-flex items-center
                                                   px-2.5 py-1
                                                   rounded-full
                                                   text-xs font-medium
                                                   bg-[#e5eeff]  
                                                   text-[#182442]">

                                                    <?= htmlspecialchars($buku['nama_kategori']) ?>

                                                </span>

                                            <?php else: ?>

                                                <span class="text-sm text-[#75777e]">
                                                    -
                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <td class="px-5 py-4">

                                            <?php if ($buku['stok'] > 5): ?>

                                                <span class="text-green-600 font-medium">

                                                    <?= $buku['stok'] ?>

                                                </span>

                                            <?php elseif ($buku['stok'] > 0): ?>

                                                <span class="text-yellow-600 font-medium">

                                                    <?= $buku['stok'] ?>

                                                </span>

                                            <?php else: ?>

                                                <span class="text-red-600 font-medium">

                                                    Habis

                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <td class="px-5 py-4">

                                            <div class="flex justify-end gap-2">


                                                <!-- EDIT -->

                                                <a
                                                    href="edit.php?id=<?= $buku['id'] ?>"
                                                    class="w-9 h-9
                                                   rounded-lg
                                                   bg-[#e5eeff]
                                                   text-[#182442]
                                                   flex items-center justify-center
                                                   hover:bg-[#d3e4fe]
                                                   transition"
                                                    title="Edit">

                                                    <span class="material-symbols-outlined text-[18px]">
                                                        edit
                                                    </span>

                                                </a>


                                                <!-- HAPUS -->

                                                <a
                                                    href="hapus.php?id=<?= $buku['id'] ?>"
                                                    onclick="return confirm('Yakin ingin menghapus buku ini?')"
                                                    class="w-9 h-9
                                                   rounded-lg
                                                   bg-red-50
                                                   text-red-600
                                                   flex items-center justify-center
                                                   hover:bg-red-100
                                                   transition"
                                                    title="Hapus">

                                                    <span class="material-symbols-outlined text-[18px]">
                                                        delete
                                                    </span>

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
                                        colspan="8"
                                        class="px-5 py-12 text-center">

                                        <div
                                            class="w-14 h-14
                                               mx-auto
                                               rounded-xl
                                               bg-[#e5eeff]
                                               text-[#182442]
                                               flex items-center justify-center">

                                            <span class="material-symbols-outlined text-2xl">
                                                menu_book
                                            </span>

                                        </div>

                                        <p class="font-medium mt-4">
                                            Buku tidak ditemukan
                                        </p>

                                        <p class="text-sm text-[#75777e] mt-1">
                                            Belum ada data buku yang tersedia.
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