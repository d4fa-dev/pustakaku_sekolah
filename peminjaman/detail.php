<?php

require_once "../config/auth.php";
require_once "../config/database.php";

$pageTitle = "Detail Peminjaman";

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php?error=not_found");
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        p.*,
        a.nisn,
        a.nama AS nama_anggota,
        a.jenis_kelamin,
        a.kelas,
        a.no_hp,
        b.kode_buku,
        b.judul,
        b.penulis,
        b.penerbit,
        b.tahun_terbit,
        b.stok,
        u.nama AS nama_user
    FROM peminjaman p
    INNER JOIN anggota a
        ON p.anggota_id = a.id
    INNER JOIN buku b
        ON p.buku_id = b.id
    LEFT JOIN users u
        ON p.user_id = u.id
    WHERE p.id = ?
    LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$peminjaman = mysqli_fetch_assoc($result);

if (!$peminjaman) {
    header("Location: index.php?error=not_found");
    exit;
}

$hariIni = strtotime(date('Y-m-d'));

$jatuhTempo = !empty($peminjaman['tanggal_jatuh_tempo'])
    ? strtotime($peminjaman['tanggal_jatuh_tempo'])
    : null;

$tanggalKembali = !empty($peminjaman['tanggal_kembali'])
    ? strtotime($peminjaman['tanggal_kembali'])
    : null;

$terlambat = false;
$jumlahHariTerlambat = 0;

if (
    $peminjaman['status'] !== 'dikembalikan' &&
    $jatuhTempo &&
    $hariIni > $jatuhTempo
) {

    $terlambat = true;

    $jumlahHariTerlambat = floor(
        ($hariIni - $jatuhTempo) / 86400
    );
}

?>

<?php require_once "../partials/header.php"; ?>
<?php require_once "../partials/sidebar.php"; ?>

<main class="flex-1 min-w-0">

    <?php require_once "../partials/navbar.php"; ?>

    <div class="p-5 md:p-8 overflow-y-auto">

        <div class="max-w-4xl mx-auto space-y-6">

            <div>

                <a
                    href="index.php"
                    class="inline-flex items-center gap-1
                           text-sm text-[#75777e]
                           hover:text-[#182442]
                           transition">

                    <span class="material-symbols-outlined text-[18px]">
                        arrow_back
                    </span>

                    Kembali ke Peminjaman

                </a>

                <div
                    class="flex flex-col sm:flex-row
                           sm:items-center
                           sm:justify-between
                           gap-4 mt-4">

                    <div>

                        <h1
                            class="text-2xl font-semibold
                                   text-[#0b1c30]">

                            Detail Peminjaman

                        </h1>

                        <p
                            class="text-sm text-[#75777e] mt-1">

                            Informasi lengkap transaksi peminjaman.

                        </p>

                    </div>

                    <div>

                        <?php if ($peminjaman['status'] === 'dikembalikan'): ?>

                            <span
                                class="inline-flex items-center gap-2
                                       px-3 py-1.5
                                       rounded-full
                                       bg-green-50
                                       border border-green-200
                                       text-green-700
                                       text-sm font-medium">

                                <span
                                    class="w-2 h-2 rounded-full
                                           bg-green-500">
                                </span>

                                Dikembalikan

                            </span>

                        <?php elseif ($terlambat): ?>

                            <span
                                class="inline-flex items-center gap-2
                                       px-3 py-1.5
                                       rounded-full
                                       bg-red-50
                                       border border-red-200
                                       text-red-700
                                       text-sm font-medium">

                                <span
                                    class="w-2 h-2 rounded-full
                                           bg-red-500">
                                </span>

                                Terlambat

                            </span>

                        <?php else: ?>

                            <span
                                class="inline-flex items-center gap-2
                                       px-3 py-1.5
                                       rounded-full
                                       bg-[#e5eeff]
                                       border border-[#d3e4fe]
                                       text-[#182442]
                                       text-sm font-medium">

                                <span
                                    class="w-2 h-2 rounded-full
                                           bg-[#182442]">
                                </span>

                                Dipinjam

                            </span>

                        <?php endif; ?>

                    </div>

                </div>

            </div>


            <?php if ($terlambat): ?>

                <div
                    class="flex items-start gap-3
                           p-4 rounded-xl
                           bg-red-50
                           border border-red-200
                           text-red-700">

                    <span class="material-symbols-outlined">
                        warning
                    </span>

                    <div>

                        <p class="font-medium text-sm">
                            Peminjaman terlambat
                        </p>

                        <p class="text-sm mt-1">

                            Buku terlambat dikembalikan
                            <?= $jumlahHariTerlambat ?>
                            hari.

                        </p>

                    </div>

                </div>

            <?php endif; ?>


            <div
                class="bg-white rounded-xl
                       border border-[#c6c6ce]/20
                       shadow-sm overflow-hidden">

                <div
                    class="px-6 py-5
                           border-b border-[#c6c6ce]/20
                           flex items-center gap-3">

                    <div
                        class="w-10 h-10
                               rounded-lg
                               bg-[#e5eeff]
                               text-[#182442]
                               flex items-center justify-center">

                        <span class="material-symbols-outlined">
                            receipt_long
                        </span>

                    </div>

                    <div>

                        <h2
                            class="font-semibold
                                   text-[#0b1c30]">

                            Informasi Transaksi

                        </h2>

                        <p
                            class="text-xs
                                   text-[#75777e] mt-0.5">

                            <?= htmlspecialchars(
                                $peminjaman['kode_peminjaman']
                            ) ?>

                        </p>

                    </div>

                </div>


                <div class="p-6">

                    <div
                        class="grid grid-cols-1
                               sm:grid-cols-2
                               gap-6">

                        <div>

                            <p
                                class="text-xs
                                       text-[#75777e]
                                       mb-1">

                                Kode Peminjaman

                            </p>

                            <p
                                class="font-medium
                                       text-[#0b1c30]">

                                <?= htmlspecialchars(
                                    $peminjaman['kode_peminjaman']
                                ) ?>

                            </p>

                        </div>


                        <div>

                            <p
                                class="text-xs
                                       text-[#75777e]
                                       mb-1">

                                Petugas

                            </p>

                            <p
                                class="font-medium
                                       text-[#0b1c30]">

                                <?= htmlspecialchars(
                                    $peminjaman['nama_user'] ?? '-'
                                ) ?>

                            </p>

                        </div>


                        <div>

                            <p
                                class="text-xs
                                       text-[#75777e]
                                       mb-1">

                                Tanggal Pinjam

                            </p>

                            <p
                                class="font-medium
                                       text-[#0b1c30]">

                                <?= !empty($peminjaman['tanggal_pinjam'])
                                    ? date(
                                        'd F Y',
                                        strtotime(
                                            $peminjaman['tanggal_pinjam']
                                        )
                                    )
                                    : '-'
                                ?>

                            </p>

                        </div>


                        <div>

                            <p
                                class="text-xs
                                       text-[#75777e]
                                       mb-1">

                                Jatuh Tempo

                            </p>

                            <p
                                class="font-medium
                                    <?= $terlambat
                                        ? 'text-red-600'
                                        : 'text-[#0b1c30]'
                                    ?>">

                                <?= $jatuhTempo
                                    ? date(
                                        'd F Y',
                                        $jatuhTempo
                                    )
                                    : '-'
                                ?>

                            </p>

                        </div>


                        <div>

                            <p
                                class="text-xs
                                       text-[#75777e]
                                       mb-1">

                                Tanggal Kembali

                            </p>

                            <p
                                class="font-medium
                                       text-[#0b1c30]">

                                <?= $tanggalKembali
                                    ? date(
                                        'd F Y',
                                        $tanggalKembali
                                    )
                                    : '-'
                                ?>

                            </p>

                        </div>


                        <div>

                            <p
                                class="text-xs
                                       text-[#75777e]
                                       mb-1">

                                Denda

                            </p>

                            <p
                                class="font-medium
                                       <?= $peminjaman['denda'] > 0
                                            ? 'text-red-600'
                                            : 'text-green-600'
                                        ?>">

                                Rp <?= number_format(
                                        $peminjaman['denda'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                            </p>

                        </div>

                    </div>

                </div>

            </div>


            <div
                class="bg-white rounded-xl
                       border border-[#c6c6ce]/20
                       shadow-sm overflow-hidden">

                <div
                    class="px-6 py-5
                           border-b border-[#c6c6ce]/20
                           flex items-center gap-3">

                    <div
                        class="w-10 h-10
                               rounded-lg
                               bg-[#e5eeff]
                               text-[#182442]
                               flex items-center justify-center">

                        <span class="material-symbols-outlined">
                            person
                        </span>

                    </div>

                    <div>

                        <h2
                            class="font-semibold
                                   text-[#0b1c30]">

                            Data Anggota

                        </h2>

                        <p
                            class="text-xs
                                   text-[#75777e] mt-0.5">

                            Informasi anggota yang meminjam buku.

                        </p>

                    </div>

                </div>


                <div class="p-6">

                    <div
                        class="grid grid-cols-1
                               sm:grid-cols-2
                               gap-6">

                        <div>

                            <p class="text-xs text-[#75777e] mb-1">
                                NISN
                            </p>

                            <p class="font-medium text-[#0b1c30]">

                                <?= htmlspecialchars(
                                    $peminjaman['nisn']
                                ) ?>

                            </p>

                        </div>


                        <div>

                            <p class="text-xs text-[#75777e] mb-1">
                                Nama
                            </p>

                            <p class="font-medium text-[#0b1c30]">

                                <?= htmlspecialchars(
                                    $peminjaman['nama_anggota']
                                ) ?>

                            </p>

                        </div>


                        <div>

                            <p class="text-xs text-[#75777e] mb-1">
                                Jenis Kelamin
                            </p>

                            <p class="font-medium text-[#0b1c30]">

                                <?php if ($peminjaman['jenis_kelamin'] === 'L'): ?>

                                    Laki-laki

                                <?php elseif ($peminjaman['jenis_kelamin'] === 'P'): ?>

                                    Perempuan

                                <?php else: ?>

                                    -

                                <?php endif; ?>

                            </p>

                        </div>


                        <div>

                            <p class="text-xs text-[#75777e] mb-1">
                                Kelas
                            </p>

                            <p class="font-medium text-[#0b1c30]">

                                <?= htmlspecialchars(
                                    $peminjaman['kelas'] ?? '-'
                                ) ?>

                            </p>

                        </div>


                        <div>

                            <p class="text-xs text-[#75777e] mb-1">
                                No. HP
                            </p>

                            <p class="font-medium text-[#0b1c30]">

                                <?= htmlspecialchars(
                                    $peminjaman['no_hp'] ?? '-'
                                ) ?>

                            </p>

                        </div>

                    </div>

                </div>

            </div>


            <div
                class="bg-white rounded-xl
                       border border-[#c6c6ce]/20
                       shadow-sm overflow-hidden">

                <div
                    class="px-6 py-5
                           border-b border-[#c6c6ce]/20
                           flex items-center gap-3">

                    <div
                        class="w-10 h-10
                               rounded-lg
                               bg-[#e5eeff]
                               text-[#182442]
                               flex items-center justify-center">

                        <span class="material-symbols-outlined">
                            menu_book
                        </span>

                    </div>

                    <div>

                        <h2
                            class="font-semibold
                                   text-[#0b1c30]">

                            Data Buku

                        </h2>

                        <p
                            class="text-xs
                                   text-[#75777e] mt-0.5">

                            Informasi buku yang dipinjam.

                        </p>

                    </div>

                </div>


                <div class="p-6">

                    <div
                        class="grid grid-cols-1
                               sm:grid-cols-2
                               gap-6">

                        <div>

                            <p class="text-xs text-[#75777e] mb-1">
                                Kode Buku
                            </p>

                            <p class="font-medium text-[#0b1c30]">

                                <?= htmlspecialchars(
                                    $peminjaman['kode_buku']
                                ) ?>

                            </p>

                        </div>


                        <div>

                            <p class="text-xs text-[#75777e] mb-1">
                                Judul
                            </p>

                            <p class="font-medium text-[#0b1c30]">

                                <?= htmlspecialchars(
                                    $peminjaman['judul']
                                ) ?>

                            </p>

                        </div>


                        <div>

                            <p class="text-xs text-[#75777e] mb-1">
                                Penulis
                            </p>

                            <p class="font-medium text-[#0b1c30]">

                                <?= htmlspecialchars(
                                    $peminjaman['penulis'] ?? '-'
                                ) ?>

                            </p>

                        </div>


                        <div>

                            <p class="text-xs text-[#75777e] mb-1">
                                Penerbit
                            </p>

                            <p class="font-medium text-[#0b1c30]">

                                <?= htmlspecialchars(
                                    $peminjaman['penerbit'] ?? '-'
                                ) ?>

                            </p>

                        </div>


                        <div>

                            <p class="text-xs text-[#75777e] mb-1">
                                Tahun Terbit
                            </p>

                            <p class="font-medium text-[#0b1c30]">

                                <?= htmlspecialchars(
                                    $peminjaman['tahun_terbit'] ?? '-'
                                ) ?>

                            </p>

                        </div>


                        <div>

                            <p class="text-xs text-[#75777e] mb-1">
                                Stok Saat Ini
                            </p>

                            <p class="font-medium text-[#0b1c30]">

                                <?= htmlspecialchars(
                                    $peminjaman['stok']
                                ) ?>

                            </p>

                        </div>

                    </div>

                </div>

            </div>


            <div
                class="flex flex-col-reverse
                       sm:flex-row
                       sm:justify-between
                       gap-3">

                <a
                    href="index.php"
                    class="inline-flex
                           items-center
                           justify-center
                           px-5 py-2.5
                           rounded-lg
                           border border-[#c6c6ce]
                           bg-white
                           text-[#45464e]
                           text-sm font-medium
                           hover:bg-[#f8f9ff]
                           transition">

                    Kembali

                </a>


                <?php if ($peminjaman['status'] !== 'dikembalikan'): ?>

                    <a
                        href="../pengembalian/proses.php?id=<?= $peminjaman['id'] ?>"
                        onclick="return confirm('Proses pengembalian buku ini?')"
                        class="inline-flex
                               items-center
                               justify-center
                               gap-2
                               px-5 py-2.5
                               rounded-lg
                               bg-[#182442]
                               text-white
                               text-sm font-medium
                               hover:bg-[#2e3a59]
                               transition
                               shadow-sm">

                        <span class="material-symbols-outlined text-[20px]">
                            assignment_return
                        </span>

                        Proses Pengembalian

                    </a>

                <?php endif; ?>

            </div>

        </div>

    </div>

</main>

<?php require_once "../partials/footer.php"; ?>