<?php

require_once "../config/auth.php";
require_once "../config/database.php";

$pageTitle = "Anggota";

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

                            Anggota berhasil ditambahkan.

                        <?php elseif ($_GET['success'] === 'updated'): ?>

                            Data anggota berhasil diperbarui.

                        <?php elseif ($_GET['success'] === 'deleted'): ?>

                            Anggota berhasil dihapus.

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

                        <?php if ($_GET['error'] === 'has_peminjaman'): ?>

                            Anggota tidak dapat dihapus karena masih memiliki
                            riwayat peminjaman.

                        <?php elseif ($_GET['error'] === 'delete'): ?>

                            Gagal menghapus anggota.

                        <?php endif; ?>

                    </p>

                </div>

            <?php endif; ?>


            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                <div>

                    <h1 class="text-2xl font-semibold text-[#0b1c30]">
                        Anggota
                    </h1>

                    <p class="text-sm text-[#75777e] mt-1">
                        Kelola seluruh anggota perpustakaan.
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
                        person_add
                    </span>

                    Tambah Anggota

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
                            placeholder="Cari berdasarkan NISN atau nama anggota..."
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
                                    NISN
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Anggota
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Jenis Kelamin
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Kelas
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    No. HP
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Tanggal Daftar
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
                                    "SELECT *
                                     FROM anggota
                                     WHERE nisn LIKE ?
                                        OR nama LIKE ?
                                     ORDER BY id DESC"
                                );

                                mysqli_stmt_bind_param(
                                    $stmt,
                                    "ss",
                                    $keyword,
                                    $keyword
                                );

                                mysqli_stmt_execute($stmt);

                                $result = mysqli_stmt_get_result($stmt);
                            } else {

                                $result = mysqli_query(
                                    $koneksi,
                                    "SELECT *
                                     FROM anggota
                                     ORDER BY id DESC"
                                );
                            }

                            $no = 1;

                            if ($result && mysqli_num_rows($result) > 0):

                                while ($anggota = mysqli_fetch_assoc($result)):

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

                                                <?= htmlspecialchars($anggota['nisn']) ?>

                                            </span>

                                        </td>


                                        <td class="px-5 py-4">

                                            <div class="flex items-center gap-3">

                                                <div
                                                    class="w-10 h-10
                                                           rounded-lg
                                                           bg-[#e5eeff]
                                                           text-[#182442]
                                                           flex items-center
                                                           justify-center
                                                           flex-shrink-0">

                                                    <span class="material-symbols-outlined">
                                                        person
                                                    </span>

                                                </div>

                                                <div>

                                                    <div
                                                        class="font-medium
                                                               text-[#0b1c30]">

                                                        <?= htmlspecialchars($anggota['nama']) ?>

                                                    </div>

                                                    <div
                                                        class="text-xs
                                                               text-[#75777e]
                                                               mt-0.5">

                                                        ID #<?= $anggota['id'] ?>

                                                    </div>

                                                </div>

                                            </div>

                                        </td>


                                        <td class="px-5 py-4">

                                            <?php if ($anggota['jenis_kelamin'] === 'L'): ?>

                                                <span
                                                    class="inline-flex items-center
                                                           px-2.5 py-1
                                                           rounded-full
                                                           text-xs font-medium
                                                           bg-[#e5eeff]
                                                           text-[#182442]">

                                                    Laki-laki

                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="inline-flex items-center
                                                           px-2.5 py-1
                                                           rounded-full
                                                           text-xs font-medium
                                                           bg-[#f3f4f6]
                                                           text-[#45464e]">

                                                    Perempuan

                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <td class="px-5 py-4 text-[#45464e]">

                                            <?= $anggota['kelas']
                                                ? htmlspecialchars($anggota['kelas'])
                                                : '-' ?>

                                        </td>


                                        <td class="px-5 py-4 text-[#45464e]">

                                            <?= $anggota['no_hp']
                                                ? htmlspecialchars($anggota['no_hp'])
                                                : '-' ?>

                                        </td>


                                        <td class="px-5 py-4 text-[#45464e]">

                                            <?= $anggota['created_at']
                                                ? date(
                                                    'd M Y',
                                                    strtotime($anggota['created_at'])
                                                )
                                                : '-' ?>

                                        </td>


                                        <td class="px-5 py-4">

                                            <?php if ($anggota['status'] === 'Aktif'): ?>

                                                <span
                                                    class="inline-flex items-center gap-1.5
                                                           px-2.5 py-1
                                                           rounded-full
                                                           text-xs font-medium
                                                           bg-green-50
                                                           text-green-700
                                                           border border-green-200">

                                                    <span
                                                        class="w-1.5 h-1.5
                                                               rounded-full
                                                               bg-green-500">
                                                    </span>

                                                    Aktif

                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="inline-flex items-center gap-1.5
                                                           px-2.5 py-1
                                                           rounded-full
                                                           text-xs font-medium
                                                           bg-gray-100
                                                           text-gray-600
                                                           border border-gray-200">

                                                    <span
                                                        class="w-1.5 h-1.5
                                                               rounded-full
                                                               bg-gray-400">
                                                    </span>

                                                    Tidak Aktif

                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <td class="px-5 py-4">

                                            <div class="flex justify-end gap-2">

                                                <a
                                                    href="edit.php?id=<?= $anggota['id'] ?>"
                                                    class="w-9 h-9 rounded-lg
                                                           bg-[#e5eeff]
                                                           text-[#182442]
                                                           flex items-center
                                                           justify-center
                                                           hover:bg-[#d3e4fe]
                                                           transition"
                                                    title="Edit">

                                                    <span
                                                        class="material-symbols-outlined
                                                               text-[18px]">

                                                        edit

                                                    </span>

                                                </a>


                                                <a
                                                    href="hapus.php?id=<?= $anggota['id'] ?>"
                                                    onclick="return confirm('Yakin ingin menghapus anggota ini?')"
                                                    class="w-9 h-9 rounded-lg
                                                           bg-red-50
                                                           text-red-600
                                                           flex items-center
                                                           justify-center
                                                           hover:bg-red-100
                                                           transition"
                                                    title="Hapus">

                                                    <span
                                                        class="material-symbols-outlined
                                                               text-[18px]">

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
                                        colspan="9"
                                        class="px-5 py-12 text-center">

                                        <div
                                            class="w-14 h-14
                                                   mx-auto
                                                   rounded-xl
                                                   bg-[#e5eeff]
                                                   text-[#182442]
                                                   flex items-center
                                                   justify-center">

                                            <span
                                                class="material-symbols-outlined
                                                       text-2xl">

                                                group

                                            </span>

                                        </div>

                                        <p class="font-medium mt-4 text-[#0b1c30]">
                                            Anggota tidak ditemukan
                                        </p>

                                        <p class="text-sm text-[#75777e] mt-1">
                                            Belum ada data anggota yang tersedia.
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