<?php

$hari = [
    'Sunday'    => 'Minggu',
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu'
];

$bulan = [
    1  => 'Januari',
    2  => 'Februari',
    3  => 'Maret',
    4  => 'April',
    5  => 'Mei',
    6  => 'Juni',
    7  => 'Juli',
    8  => 'Agustus',
    9  => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

$hariIni = $hari[date('l')];
$tanggalHariIni = date('d');
$bulanIni = $bulan[(int) date('m')];
$tahunIni = date('Y');

?>

<header
    class="h-20
           bg-[#f8f9ff]
           border-b border-[#c6c6ce]/20
           flex items-center
           justify-between
           px-5 md:px-8
           sticky top-0 z-30
           relative"
>

    <!-- Accent Line -->

    <div
        class="absolute
               bottom-0 left-0 right-0
               h-px
               bg-gradient-to-r
               from-transparent
               via-[#2e3a59]/20
               to-transparent"
    ></div>


    <!-- LEFT -->

    <div class="flex items-center gap-4">

        <!-- Mobile Menu -->

        <button
            id="menuButton"
            type="button"
            class="md:hidden
                   w-10 h-10
                   rounded-lg
                   flex items-center justify-center
                   text-[#45464e]
                   hover:bg-[#e5eeff]
                   hover:text-[#182442]
                   transition"
        >

            <span class="material-symbols-outlined">
                menu
            </span>

        </button>


        <!-- Breadcrumb -->

        <div class="hidden sm:flex items-center gap-2">

            <span
                class="text-xs
                       font-medium
                       text-[#75777e]"
            >

                PustakaKu

            </span>

            <span
                class="material-symbols-outlined
                       text-[16px]
                       text-[#b0b1b7]"
            >

                chevron_right

            </span>

            <span
                class="text-xs
                       font-semibold
                       text-[#182442]"
            >

                <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?>

            </span>

        </div>

    </div>


    <!-- RIGHT -->

    <div class="flex items-center">

        <!-- Date -->

        <div
            class="flex items-center gap-2
                   text-[#75777e]"
        >

            <span
                class="material-symbols-outlined
                       text-[19px]"
            >

                calendar_today

            </span>

            <span
                class="text-xs
                       md:text-sm
                       font-medium
                       text-[#45464e]
                       whitespace-nowrap"
            >

                <?= $hariIni . ', ' . $tanggalHariIni . ' ' . $bulanIni . ' ' . $tahunIni ?>

            </span>

        </div>

    </div>

</header>
