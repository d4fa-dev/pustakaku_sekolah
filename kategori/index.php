<?php

$pageTitle = "Kategori";

require_once "../config/database.php";

$query = mysqli_query(
    $koneksi,
    "SELECT *
     FROM kategori
     ORDER BY id DESC"
);

if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}

require_once "../partials/header.php";
require_once "../partials/sidebar.php";

?>

<main class="flex-1 min-w-0">

    <?php require_once "../partials/navbar.php"; ?>

    <div class="p-5 md:p-8 overflow-y-auto">

        <div class="max-w-7xl mx-auto space-y-6">

            <!-- HEADER -->

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                <div>

                    <h1 class="text-2xl font-semibold text-[#0b1c30]">
                        Kategori
                    </h1>

                    <p class="text-sm text-[#75777e] mt-1">
                        Kelola kategori buku perpustakaan.
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
                           transition"
                >

                    <span class="material-symbols-outlined text-[20px]">
                        add
                    </span>

                    Tambah Kategori

                </a>

            </div>


            <!-- NOTIFICATION -->

            <?php if (isset($_GET['success'])): ?>

                <div
                    class="flex items-center gap-3
                           p-4 rounded-lg
                           bg-green-50
                           border border-green-200
                           text-green-700"
                >

                    <span class="material-symbols-outlined">
                        check_circle
                    </span>

                    <p class="text-sm font-medium">

                        <?php if ($_GET['success'] === 'added'): ?>

                            Kategori berhasil ditambahkan.

                        <?php elseif ($_GET['success'] === 'updated'): ?>

                            Kategori berhasil diperbarui.

                        <?php elseif ($_GET['success'] === 'deleted'): ?>

                            Kategori berhasil dihapus.

                        <?php endif; ?>

                    </p>

                </div>

            <?php endif; ?>


            <!-- TABLE -->

            <div
                class="bg-white
                       rounded-xl
                       border border-[#c6c6ce]/20
                       shadow-sm
                       overflow-hidden"
            >

                <div class="overflow-x-auto">

                    <table class="w-full text-left">

                        <thead>

                            <tr
                                class="bg-[#f8f9ff]
                                       border-b border-[#c6c6ce]/20
                                       text-xs
                                       text-[#75777e]"
                            >

                                <th class="px-6 py-4 font-medium">
                                    #
                                </th>

                                <th class="px-6 py-4 font-medium">
                                    Nama Kategori
                                </th>

                                <th class="px-6 py-4 font-medium text-center">
                                    Aksi
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-[#c6c6ce]/10">

                            <?php if (mysqli_num_rows($query) > 0): ?>

                                <?php $no = 1; ?>

                                <?php while ($kategori = mysqli_fetch_assoc($query)): ?>

                                    <tr class="hover:bg-[#eff4ff]/50 transition">

                                        <td class="px-6 py-4 text-sm text-[#75777e]">
                                            <?= $no++ ?>
                                        </td>


                                        <td class="px-6 py-4">

                                            <div class="flex items-center gap-3">

                                                <div
                                                    class="w-10 h-10
                                                           rounded-lg
                                                           bg-[#e5eeff]
                                                           text-[#182442]
                                                           flex items-center justify-center"
                                                >

                                                    <span class="material-symbols-outlined">
                                                        category
                                                    </span>

                                                </div>

                                                <span
                                                    class="text-sm
                                                           font-medium
                                                           text-[#0b1c30]"
                                                >

                                                    <?= htmlspecialchars(
                                                        $kategori['nama_kategori']
                                                    ) ?>

                                                </span>

                                            </div>

                                        </td>


                                        <td class="px-6 py-4">

                                            <div class="flex items-center justify-center gap-2">

                                                <a
                                                    href="edit.php?id=<?= $kategori['id'] ?>"
                                                    class="w-9 h-9
                                                           rounded-lg
                                                           flex items-center justify-center
                                                           text-[#182442]
                                                           bg-[#e5eeff]
                                                           hover:bg-[#d3e4fe]
                                                           transition"
                                                    title="Edit"
                                                >

                                                    <span class="material-symbols-outlined text-[19px]">
                                                        edit
                                                    </span>

                                                </a>


                                                <a
                                                    href="hapus.php?id=<?= $kategori['id'] ?>"
                                                    onclick="return confirm('Yakin ingin menghapus kategori ini?')"
                                                    class="w-9 h-9
                                                           rounded-lg
                                                           flex items-center justify-center
                                                           text-red-600
                                                           bg-red-50
                                                           hover:bg-red-100
                                                           transition"
                                                    title="Hapus"
                                                >

                                                    <span class="material-symbols-outlined text-[19px]">
                                                        delete
                                                    </span>

                                                </a>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="4"
                                        class="px-6 py-12 text-center"
                                    >

                                        <span
                                            class="material-symbols-outlined
                                                   text-5xl
                                                   text-[#c6c6ce]"
                                        >
                                            category
                                        </span>

                                        <p class="mt-3 text-sm text-[#75777e]">
                                            Belum ada kategori.
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