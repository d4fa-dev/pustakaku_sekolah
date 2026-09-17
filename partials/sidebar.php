<?php

$currentPage = basename($_SERVER['PHP_SELF']);
$currentFolder = basename(dirname($_SERVER['PHP_SELF']));

function menuAktif($folder, $page = '')
{
    global $currentPage, $currentFolder;

    if ($page !== '') {
        return $currentFolder === $folder && $currentPage === $page;
    }

    return $currentFolder === $folder;
}

function classMenu($aktif)
{
    return $aktif
        ? 'bg-[#2e3a59] text-white shadow-sm'
        : 'text-[#45464e] hover:bg-[#eff4ff] hover:text-[#182442]';
}

function classSubMenu($aktif)
{
    return $aktif
        ? 'bg-[#e5eeff] text-[#182442] font-medium'
        : 'text-[#75777e] hover:bg-[#eff4ff] hover:text-[#182442]';
}

?>


<aside
    id="sidebar"
    class="fixed md:sticky top-0 left-0 z-50
           w-64 h-screen
           bg-white
           border-r border-[#c6c6ce]/30
           flex flex-col
           -translate-x-full md:translate-x-0
           transition-transform duration-300"
>

    <div class="h-20 px-6 flex items-center gap-3">

        <div
            class="w-10 h-10 rounded-xl
                   bg-[#2e3a59]
                   text-white
                   flex items-center justify-center
                   shadow-sm"
        >

            <span class="material-symbols-outlined">
                menu_book
            </span>

        </div>

        <div>

            <h1 class="font-bold text-xl text-[#182442]">
                PustakaKu
            </h1>

            <p class="text-[10px] text-[#75777e]">
                Library Management
            </p>

        </div>

    </div>


    <nav class="flex-1 px-4 py-5 space-y-1 overflow-y-auto">


        <!-- Dashboard -->

        <a
            href="../dashboard/index.php"
            class="flex items-center gap-3
                   px-4 py-3 rounded-lg
                   <?= classMenu(
                       menuAktif('dashboard', 'index.php')
                   ) ?>
                   text-sm font-medium
                   transition"
        >

            <span class="material-symbols-outlined text-[20px]">
                dashboard
            </span>

            Dashboard

        </a>


        <!-- Buku -->

        <a
            href="../buku/index.php"
            class="flex items-center gap-3
                   px-4 py-3 rounded-lg
                   <?= classMenu(
                       menuAktif('buku')
                   ) ?>
                   text-sm font-medium
                   transition"
        >

            <span class="material-symbols-outlined text-[20px]">
                library_books
            </span>

            Koleksi Buku

        </a>


        <!-- Kategori -->

        <a
            href="../kategori/index.php"
            class="flex items-center gap-3
                   px-4 py-3 rounded-lg
                   <?= classMenu(
                       menuAktif('kategori')
                   ) ?>
                   text-sm font-medium
                   transition"
        >

            <span class="material-symbols-outlined text-[20px]">
                category
            </span>

            Kategori

        </a>


        <!-- Peminjaman -->

        <a
            href="../peminjaman/index.php"
            class="flex items-center gap-3
                   px-4 py-3 rounded-lg
                   <?= classMenu(
                       menuAktif('peminjaman')
                   ) ?>
                   text-sm font-medium
                   transition"
        >

            <span class="material-symbols-outlined text-[20px]">
                compare_arrows
            </span>

            Peminjaman

        </a>


        <!-- Anggota -->

        <a
            href="../anggota/index.php"
            class="flex items-center gap-3
                   px-4 py-3 rounded-lg
                   <?= classMenu(
                       menuAktif('anggota')
                   ) ?>
                   text-sm font-medium
                   transition"
        >

            <span class="material-symbols-outlined text-[20px]">
                group
            </span>

            Anggota

        </a>


        <!-- Pengembalian -->

        <a
            href="../pengembalian/index.php"
            class="flex items-center gap-3
                   px-4 py-3 rounded-lg
                   <?= classMenu(
                       menuAktif('pengembalian')
                   ) ?>
                   text-sm font-medium
                   transition"
        >

            <span class="material-symbols-outlined text-[20px]">
                assignment_return
            </span>

            Pengembalian

        </a>


        <!-- Laporan -->

        <div>

            <?php

            $laporanAktif =
                $currentFolder === 'laporan';

            ?>

            <a
                href="../laporan/index.php"
                class="flex items-center justify-between
                       px-4 py-3 rounded-lg
                       <?= classMenu($laporanAktif) ?>
                       text-sm font-medium
                       transition"
            >

                <div class="flex items-center gap-3">

                    <span
                        class="material-symbols-outlined text-[20px]">

                        bar_chart

                    </span>

                    Laporan

                </div>

                <span
                    class="material-symbols-outlined text-[18px]">

                    <?= $laporanAktif
                        ? 'expand_more'
                        : 'chevron_right' ?>

                </span>

            </a>


            <?php if ($laporanAktif): ?>

                <div class="ml-4 mt-1 space-y-1">

                    <!-- Laporan Peminjaman -->

                    <a
                        href="../laporan/peminjaman.php"
                        class="flex items-center gap-3
                               px-4 py-2.5 rounded-lg
                               <?= classSubMenu(
                                   $currentPage === 'peminjaman.php'
                               ) ?>
                               text-sm
                               transition"
                    >

                        <span
                            class="material-symbols-outlined text-[18px]">

                            menu_book

                        </span>

                        Peminjaman

                    </a>


                    <!-- Laporan Pengembalian -->

                    <a
                        href="../laporan/pengembalian.php"
                        class="flex items-center gap-3
                               px-4 py-2.5 rounded-lg
                               <?= classSubMenu(
                                   $currentPage === 'pengembalian.php'
                               ) ?>
                               text-sm
                               transition"
                    >

                        <span
                            class="material-symbols-outlined text-[18px]">

                            assignment_return

                        </span>

                        Pengembalian

                    </a>


                    <!-- Laporan Buku -->

                    <a
                        href="../laporan/buku.php"
                        class="flex items-center gap-3
                               px-4 py-2.5 rounded-lg
                               <?= classSubMenu(
                                   $currentPage === 'buku.php'
                               ) ?>
                               text-sm
                               transition"
                    >

                        <span
                            class="material-symbols-outlined text-[18px]">

                            inventory_2

                        </span>

                        Buku

                    </a>

                </div>

            <?php endif; ?>

        </div>


        <!-- User -->

        <a
            href="../user/index.php"
            class="flex items-center gap-3
                   px-4 py-3 rounded-lg
                   <?= classMenu(
                       menuAktif('user')
                   ) ?>
                   text-sm font-medium
                   transition"
        >

            <span class="material-symbols-outlined text-[20px]">
                manage_accounts
            </span>

            Pengguna

        </a>

    </nav>


    <!-- Bottom -->

    <div class="p-4 border-t border-[#c6c6ce]/30">

        <a
            href="../auth/logout.php"
            class="flex items-center gap-3
                   px-4 py-3 rounded-lg
                   text-red-600
                   hover:bg-red-50
                   text-sm font-medium
                   transition"
        >

            <span class="material-symbols-outlined text-[20px]">
                logout
            </span>

            Keluar

        </a>

    </div>

</aside>


<!-- Overlay Mobile -->

<div
    id="sidebarOverlay"
    class="fixed inset-0 bg-black/30 z-40 hidden md:hidden"
></div>
