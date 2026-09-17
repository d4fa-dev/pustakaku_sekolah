<?php

require_once "../config/auth.php";
require_once "../config/database.php";

wajibLogin();

$user = userLogin();

$pageTitle = "Laporan";

?>

<?php require_once "../partials/header.php"; ?>
<?php require_once "../partials/sidebar.php"; ?>

<main class="flex-1 min-w-0">

    <?php require_once "../partials/navbar.php"; ?>

    <div class="p-5 md:p-8 overflow-y-auto">

        <div class="max-w-7xl mx-auto space-y-6">

            <div>

                <h1
                    class="text-2xl
                           font-semibold
                           text-[#0b1c30]">

                    Laporan

                </h1>

                <p
                    class="text-sm
                           text-[#75777e]
                           mt-1">

                    Pilih jenis laporan yang ingin kamu lihat.

                </p>

            </div>


            <div
                class="grid
                       grid-cols-1
                       md:grid-cols-2
                       xl:grid-cols-3
                       gap-5">


                <a
                    href="peminjaman.php"
                    class="group
                           bg-white
                           rounded-xl
                           border border-[#c6c6ce]/20
                           shadow-sm
                           p-6
                           hover:shadow-md
                           hover:-translate-y-0.5
                           transition">

                    <div
                        class="w-12
                               h-12
                               rounded-xl
                               bg-[#e5eeff]
                               text-[#182442]
                               flex
                               items-center
                               justify-center
                               mb-5">

                        <span
                            class="material-symbols-outlined
                                   text-[25px]">

                            menu_book

                        </span>

                    </div>


                    <div
                        class="flex
                               items-center
                               justify-between
                               gap-4">

                        <div>

                            <h2
                                class="font-semibold
                                       text-[#0b1c30]">

                                Laporan Peminjaman

                            </h2>

                            <p
                                class="text-sm
                                       text-[#75777e]
                                       mt-1">

                                Lihat seluruh data transaksi
                                peminjaman buku.

                            </p>

                        </div>

                        <span
                            class="material-symbols-outlined
                                   text-[#75777e]
                                   group-hover:text-[#182442]
                                   group-hover:translate-x-1
                                   transition">

                            arrow_forward

                        </span>

                    </div>

                </a>


                <a
                    href="pengembalian.php"
                    class="group
                           bg-white
                           rounded-xl
                           border border-[#c6c6ce]/20
                           shadow-sm
                           p-6
                           hover:shadow-md
                           hover:-translate-y-0.5
                           transition">

                    <div
                        class="w-12
                               h-12
                               rounded-xl
                               bg-green-50
                               text-green-600
                               flex
                               items-center
                               justify-center
                               mb-5">

                        <span
                            class="material-symbols-outlined
                                   text-[25px]">

                            assignment_return

                        </span>

                    </div>


                    <div
                        class="flex
                               items-center
                               justify-between
                               gap-4">

                        <div>

                            <h2
                                class="font-semibold
                                       text-[#0b1c30]">

                                Laporan Pengembalian

                            </h2>

                            <p
                                class="text-sm
                                       text-[#75777e]
                                       mt-1">

                                Lihat riwayat buku yang telah
                                dikembalikan.

                            </p>

                        </div>

                        <span
                            class="material-symbols-outlined
                                   text-[#75777e]
                                   group-hover:text-green-600
                                   group-hover:translate-x-1
                                   transition">

                            arrow_forward

                        </span>

                    </div>

                </a>


                <a
                    href="buku.php"
                    class="group
                           bg-white
                           rounded-xl
                           border border-[#c6c6ce]/20
                           shadow-sm
                           p-6
                           hover:shadow-md
                           hover:-translate-y-0.5
                           transition">

                    <div
                        class="w-12
                               h-12
                               rounded-xl
                               bg-blue-50
                               text-blue-600
                               flex
                               items-center
                               justify-center
                               mb-5">

                        <span
                            class="material-symbols-outlined
                                   text-[25px]">

                            inventory_2

                        </span>

                    </div>


                    <div
                        class="flex
                               items-center
                               justify-between
                               gap-4">

                        <div>

                            <h2
                                class="font-semibold
                                       text-[#0b1c30]">

                                Laporan Buku

                            </h2>

                            <p
                                class="text-sm
                                       text-[#75777e]
                                       mt-1">

                                Lihat koleksi dan ketersediaan
                                seluruh buku.

                            </p>

                        </div>

                        <span
                            class="material-symbols-outlined
                                   text-[#75777e]
                                   group-hover:text-blue-600
                                   group-hover:translate-x-1
                                   transition">

                            arrow_forward

                        </span>

                    </div>

                </a>

            </div>

        </div>

    </div>

</main>

<?php require_once "../partials/footer.php"; ?>