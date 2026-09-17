<?php

require_once "../config/auth.php";
require_once "../config/database.php";

$pageTitle = "Tambah Peminjaman";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $anggota_id = intval($_POST['anggota_id'] ?? 0);
    $buku_id = intval($_POST['buku_id'] ?? 0);
    $tanggal_pinjam = $_POST['tanggal_pinjam'] ?? '';
    $tanggal_jatuh_tempo = $_POST['tanggal_jatuh_tempo'] ?? '';

    if (
        $anggota_id <= 0 ||
        $buku_id <= 0 ||
        empty($tanggal_pinjam) ||
        empty($tanggal_jatuh_tempo)
    ) {

        $error = "Semua data wajib diisi.";
    } elseif ($tanggal_jatuh_tempo < $tanggal_pinjam) {

        $error = "Tanggal jatuh tempo tidak boleh sebelum tanggal pinjam.";
    } else {

        mysqli_begin_transaction($koneksi);

        try {

            /*
            |--------------------------------------------------------------------------
            | Cek buku dan stok
            |--------------------------------------------------------------------------
            */

            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id, stok, judul
                 FROM buku
                 WHERE id = ?
                 FOR UPDATE"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $buku_id
            );

            mysqli_stmt_execute($stmt);

            $resultBuku = mysqli_stmt_get_result($stmt);

            $buku = mysqli_fetch_assoc($resultBuku);

            if (!$buku) {
                throw new Exception("Buku tidak ditemukan.");
            }

            if ((int) $buku['stok'] <= 0) {

                throw new Exception(
                    "Stok buku \"" .
                        $buku['judul'] .
                        "\" sedang habis."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Cek anggota
            |--------------------------------------------------------------------------
            */

            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id, nisn, nama, status
                 FROM anggota
                 WHERE id = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $anggota_id
            );

            mysqli_stmt_execute($stmt);

            $resultAnggota = mysqli_stmt_get_result($stmt);

            $anggota = mysqli_fetch_assoc($resultAnggota);

            if (!$anggota) {
                throw new Exception("Anggota tidak ditemukan.");
            }

            if ($anggota['status'] !== 'Aktif') {
                throw new Exception(
                    "Anggota tidak aktif dan tidak dapat melakukan peminjaman."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Buat kode peminjaman
            |--------------------------------------------------------------------------
            */

            $kode_peminjaman =
                "PJM-" .
                date('YmdHis') .
                "-" .
                rand(100, 999);


            /*
            |--------------------------------------------------------------------------
            | User yang melakukan transaksi
            |--------------------------------------------------------------------------
            */

            $user_id = $_SESSION['user_id'] ?? null;


            /*
            |--------------------------------------------------------------------------
            | Simpan peminjaman
            |--------------------------------------------------------------------------
            */

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO peminjaman
                (
                    kode_peminjaman,
                    anggota_id,
                    buku_id,
                    user_id,
                    tanggal_pinjam,
                    tanggal_jatuh_tempo,
                    status,
                    denda
                )
                VALUES (?, ?, ?, ?, ?, ?, 'dipinjam', 0)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "siiiss",
                $kode_peminjaman,
                $anggota_id,
                $buku_id,
                $user_id,
                $tanggal_pinjam,
                $tanggal_jatuh_tempo
            );

            if (!mysqli_stmt_execute($stmt)) {

                throw new Exception(
                    "Gagal menyimpan peminjaman."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Kurangi stok buku
            |--------------------------------------------------------------------------
            */

            $stmt = mysqli_prepare(
                $koneksi,
                "UPDATE buku
                 SET stok = stok - 1
                 WHERE id = ?
                 AND stok > 0"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $buku_id
            );

            if (!mysqli_stmt_execute($stmt)) {

                throw new Exception(
                    "Gagal memperbarui stok buku."
                );
            }


            if (mysqli_stmt_affected_rows($stmt) !== 1) {

                throw new Exception(
                    "Stok buku tidak berhasil diperbarui."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Commit transaksi
            |--------------------------------------------------------------------------
            */

            mysqli_commit($koneksi);

            header(
                "Location: index.php?success=added"
            );

            exit;
        } catch (Exception $e) {

            mysqli_rollback($koneksi);

            $error = $e->getMessage();
        }
    }
}


/*
|--------------------------------------------------------------------------
| Ambil anggota aktif
|--------------------------------------------------------------------------
*/

$anggotaResult = mysqli_query(
    $koneksi,
    "SELECT id, nisn, nama, kelas
     FROM anggota
     WHERE status = 'Aktif'
     ORDER BY nama ASC"
);


/*
|--------------------------------------------------------------------------
| Ambil buku yang masih tersedia
|--------------------------------------------------------------------------
*/

$bukuResult = mysqli_query(
    $koneksi,
    "SELECT id, kode_buku, judul, penulis, stok
     FROM buku
     WHERE stok > 0
     ORDER BY judul ASC"
);

?>

<?php require_once "../partials/header.php"; ?>
<?php require_once "../partials/sidebar.php"; ?>

<main class="flex-1 min-w-0">

    <?php require_once "../partials/navbar.php"; ?>

    <div class="p-5 md:p-8 overflow-y-auto">

        <div class="max-w-3xl mx-auto space-y-6">


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


                <h1
                    class="text-2xl font-semibold
                           text-[#0b1c30] mt-4">

                    Tambah Peminjaman

                </h1>

                <p
                    class="text-sm text-[#75777e] mt-1">

                    Buat transaksi peminjaman buku baru.

                </p>

            </div>


            <?php if ($error): ?>

                <div
                    class="flex items-start gap-3
                           p-4 rounded-lg
                           bg-red-50
                           border border-red-200
                           text-red-700">

                    <span class="material-symbols-outlined">
                        error
                    </span>

                    <div>

                        <p class="text-sm font-medium">
                            Gagal membuat peminjaman
                        </p>

                        <p class="text-sm mt-1">
                            <?= htmlspecialchars($error) ?>
                        </p>

                    </div>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                class="bg-white
                       rounded-xl
                       border border-[#c6c6ce]/20
                       shadow-sm
                       overflow-hidden">


                <div class="p-6 md:p-8 space-y-6">


                    <div>

                        <label
                            for="anggota_id"
                            class="block text-sm
                                   font-medium
                                   text-[#0b1c30]
                                   mb-2">

                            Anggota

                        </label>


                        <select
                            name="anggota_id"
                            id="anggota_id"
                            required
                            class="w-full
                                   px-4 py-3
                                   rounded-lg
                                   border border-[#c6c6ce]
                                   bg-[#f8f9ff]
                                   text-sm
                                   text-[#0b1c30]
                                   focus:outline-none
                                   focus:ring-2
                                   focus:ring-[#182442]/20
                                   focus:border-[#182442]">

                            <option value="">
                                Pilih anggota
                            </option>

                            <?php while (
                                $anggota = mysqli_fetch_assoc(
                                    $anggotaResult
                                )
                            ): ?>

                                <option
                                    value="<?= $anggota['id'] ?>"
                                    <?= (
                                        ($_POST['anggota_id'] ?? '') ==
                                        $anggota['id']
                                    ) ? 'selected' : '' ?>>

                                    <?= htmlspecialchars(
                                        $anggota['nisn']
                                    ) ?>

                                    -

                                    <?= htmlspecialchars(
                                        $anggota['nama']
                                    ) ?>

                                    <?php if (!empty($anggota['kelas'])): ?>

                                        -
                                        <?= htmlspecialchars(
                                            $anggota['kelas']
                                        ) ?>

                                    <?php endif; ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <div>

                        <label
                            for="buku_id"
                            class="block text-sm
                                   font-medium
                                   text-[#0b1c30]
                                   mb-2">

                            Buku

                        </label>


                        <select
                            name="buku_id"
                            id="buku_id"
                            required
                            class="w-full
                                   px-4 py-3
                                   rounded-lg
                                   border border-[#c6c6ce]
                                   bg-[#f8f9ff]
                                   text-sm
                                   text-[#0b1c30]
                                   focus:outline-none
                                   focus:ring-2
                                   focus:ring-[#182442]/20
                                   focus:border-[#182442]">

                            <option value="">
                                Pilih buku
                            </option>

                            <?php while (
                                $buku = mysqli_fetch_assoc(
                                    $bukuResult
                                )
                            ): ?>

                                <option
                                    value="<?= $buku['id'] ?>"
                                    <?= (
                                        ($_POST['buku_id'] ?? '') ==
                                        $buku['id']
                                    ) ? 'selected' : '' ?>>

                                    <?= htmlspecialchars(
                                        $buku['kode_buku']
                                    ) ?>

                                    -

                                    <?= htmlspecialchars(
                                        $buku['judul']
                                    ) ?>

                                    | Stok:
                                    <?= $buku['stok'] ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                        <p class="text-xs text-[#75777e] mt-2">
                            Buku yang ditampilkan hanya buku yang masih memiliki stok.
                        </p>

                    </div>


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


                        <div>

                            <label
                                for="tanggal_pinjam"
                                class="block text-sm
                                       font-medium
                                       text-[#0b1c30]
                                       mb-2">

                                Tanggal Pinjam

                            </label>


                            <input
                                type="date"
                                name="tanggal_pinjam"
                                id="tanggal_pinjam"
                                value="<?= htmlspecialchars(
                                            $_POST['tanggal_pinjam']
                                                ?? date('Y-m-d')
                                        ) ?>"
                                required
                                class="w-full
                                       px-4 py-3
                                       rounded-lg
                                       border border-[#c6c6ce]
                                       bg-[#f8f9ff]
                                       text-sm
                                       text-[#0b1c30]
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-[#182442]/20
                                       focus:border-[#182442]">

                        </div>


                        <div>

                            <label
                                for="tanggal_jatuh_tempo"
                                class="block text-sm
                                       font-medium
                                       text-[#0b1c30]
                                       mb-2">

                                Tanggal Jatuh Tempo

                            </label>


                            <input
                                type="date"
                                name="tanggal_jatuh_tempo"
                                id="tanggal_jatuh_tempo"
                                value="<?= htmlspecialchars(
                                            $_POST['tanggal_jatuh_tempo']
                                                ?? date(
                                                    'Y-m-d',
                                                    strtotime('+7 days')
                                                )
                                        ) ?>"
                                required
                                class="w-full
                                       px-4 py-3
                                       rounded-lg
                                       border border-[#c6c6ce]
                                       bg-[#f8f9ff]
                                       text-sm
                                       text-[#0b1c30]
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-[#182442]/20
                                       focus:border-[#182442]">

                            <p class="text-xs text-[#75777e] mt-2">
                                Default batas peminjaman adalah 7 hari.
                            </p>

                        </div>

                    </div>


                </div>


                <div
                    class="px-6 md:px-8 py-5
                           bg-[#f8f9ff]
                           border-t border-[#c6c6ce]/20
                           flex flex-col-reverse
                           sm:flex-row
                           sm:justify-end
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

                        Batal

                    </a>


                    <button
                        type="submit"
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

                        <span
                            class="material-symbols-outlined text-[20px]">

                            save

                        </span>

                        Simpan Peminjaman

                    </button>

                </div>

            </form>

        </div>

    </div>

</main>

<?php require_once "../partials/footer.php"; ?>