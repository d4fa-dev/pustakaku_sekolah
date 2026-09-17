<?php

$pageTitle = "Edit Buku";

require_once "../config/database.php";

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Ambil data buku
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT buku.*, kategori.nama_kategori
     FROM buku
     LEFT JOIN kategori
     ON buku.kategori_id = kategori.id
     WHERE buku.id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$buku = mysqli_fetch_assoc($result);


if (!$buku) {
    header("Location: index.php");
    exit;
}


$error = "";


/*
|--------------------------------------------------------------------------
| Proses UPDATE
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $kode_buku = trim($_POST["kode_buku"] ?? "");
    $judul = trim($_POST["judul"] ?? "");
    $penulis = trim($_POST["penulis"] ?? "");
    $penerbit = trim($_POST["penerbit"] ?? "");
    $tahun_terbit = trim($_POST["tahun_terbit"] ?? "");
    $kategori_id = (int) ($_POST["kategori_id"] ?? 0);
    $stok = (int) ($_POST["stok"] ?? 0);


    if ($kode_buku === "" || $judul === "" || $penulis === "") {

        $error = "Kode buku, judul, dan penulis wajib diisi.";
    } elseif ($stok < 0) {

        $error = "Stok tidak boleh kurang dari 0.";
    } else {

        /*
        |--------------------------------------------------------------------------
        | Cek kode buku
        |--------------------------------------------------------------------------
        */

        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id
             FROM buku
             WHERE kode_buku = ?
             AND id != ?"
        );

        mysqli_stmt_bind_param(
            $cek,
            "si",
            $kode_buku,
            $id
        );

        mysqli_stmt_execute($cek);

        $hasilCek = mysqli_stmt_get_result($cek);


        if (mysqli_num_rows($hasilCek) > 0) {

            $error = "Kode buku sudah digunakan oleh buku lain.";
        } else {

            /*
            |--------------------------------------------------------------------------
            | UPDATE
            |--------------------------------------------------------------------------
            */

            $update = mysqli_prepare(
                $koneksi,
                "UPDATE buku SET
        kode_buku = ?,
        judul = ?,
        penulis = ?,
        penerbit = ?,
        tahun_terbit = ?,
        kategori_id = ?,
        stok = ?
     WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $update,
                "ssssiiii",
                $kode_buku,
                $judul,
                $penulis,
                $penerbit,
                $tahun_terbit,
                $kategori_id,
                $stok,
                $id
            );


            if (mysqli_stmt_execute($update)) {

                header("Location: index.php?success=updated");
                exit;
            } else {

                $error = "Gagal memperbarui data buku.";
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Agar form tetap menampilkan data yang dikirim
    |--------------------------------------------------------------------------
    */

    $buku['kode_buku'] = $kode_buku;
    $buku['judul'] = $judul;
    $buku['penulis'] = $penulis;
    $buku['penerbit'] = $penerbit;
    $buku['tahun_terbit'] = $tahun_terbit;
    $buku['stok'] = $stok;
}

$kategoriQuery = mysqli_query(
    $koneksi,
    "SELECT * FROM kategori ORDER BY nama_kategori ASC"
);

require_once "../partials/header.php";
require_once "../partials/sidebar.php";

?>

<main class="flex-1 min-w-0">

    <?php require_once "../partials/navbar.php"; ?>


    <div class="p-5 md:p-8 overflow-y-auto">

        <div class="max-w-4xl mx-auto space-y-6">


            <!-- HEADER -->

            <div>

                <div class="flex items-center gap-2 text-sm mb-3">

                    <a
                        href="index.php"
                        class="text-[#75777e] hover:text-[#182442]">
                        Koleksi Buku
                    </a>

                    <span class="text-[#75777e]">
                        /
                    </span>

                    <span class="text-[#182442]">
                        Edit Buku
                    </span>

                </div>


                <h1 class="text-2xl font-semibold text-[#0b1c30]">

                    Edit Buku

                </h1>

                <p class="text-sm text-[#75777e] mt-1">

                    Perbarui informasi buku yang dipilih.

                </p>

            </div>


            <!-- ERROR -->

            <?php if ($error !== ""): ?>

                <div
                    class="flex items-start gap-3
                           p-4
                           rounded-lg
                           bg-red-50
                           border border-red-200
                           text-red-700">

                    <span class="material-symbols-outlined">
                        error
                    </span>

                    <div>

                        <p class="font-medium">
                            Terjadi kesalahan
                        </p>

                        <p class="text-sm mt-1">
                            <?= htmlspecialchars($error) ?>
                        </p>

                    </div>

                </div>

            <?php endif; ?>


            <!-- FORM -->

            <form
                method="POST"
                class="bg-white
                       rounded-xl
                       border border-[#c6c6ce]/20
                       shadow-sm
                       overflow-hidden">

                <!-- HEADER FORM -->

                <div
                    class="p-5 md:p-6
                           border-b border-[#c6c6ce]/20">

                    <div class="flex items-center gap-3">

                        <div
                            class="w-10 h-10
                                   rounded-lg
                                   bg-[#e5eeff]
                                   text-[#182442]
                                   flex items-center justify-center">

                            <span class="material-symbols-outlined">
                                edit
                            </span>

                        </div>

                        <div>

                            <h2 class="font-semibold text-[#0b1c30]">
                                Informasi Buku
                            </h2>

                            <p class="text-xs text-[#75777e] mt-1">
                                Perbarui data buku.
                            </p>

                        </div>

                    </div>

                </div>


                <!-- BODY -->

                <div class="p-5 md:p-6 space-y-5">


                    <!-- KODE + JUDUL -->

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>

                            <label
                                class="block text-sm font-medium
                                       text-[#45464e] mb-2">
                                Kode Buku
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="text"
                                name="kode_buku"
                                required
                                value="<?= htmlspecialchars($buku['kode_buku']) ?>"
                                class="w-full
                                       px-4 py-3
                                       rounded-lg
                                       border border-[#c6c6ce]
                                       bg-[#f8f9ff]
                                       text-sm
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-[#182442]/20
                                       focus:border-[#182442]">

                        </div>


                        <div>

                            <label
                                class="block text-sm font-medium
                                       text-[#45464e] mb-2">
                                Judul Buku
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="text"
                                name="judul"
                                required
                                value="<?= htmlspecialchars($buku['judul']) ?>"
                                class="w-full
                                       px-4 py-3
                                       rounded-lg
                                       border border-[#c6c6ce]
                                       bg-[#f8f9ff]
                                       text-sm
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-[#182442]/20
                                       focus:border-[#182442]">

                        </div>

                    </div>


                    <!-- PENULIS + PENERBIT -->

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>

                            <label
                                class="block text-sm font-medium
                                       text-[#45464e] mb-2">
                                Penulis
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="text"
                                name="penulis"
                                required
                                value="<?= htmlspecialchars($buku['penulis']) ?>"
                                class="w-full
                                       px-4 py-3
                                       rounded-lg
                                       border border-[#c6c6ce]
                                       bg-[#f8f9ff]
                                       text-sm
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-[#182442]/20
                                       focus:border-[#182442]">

                        </div>


                        <!-- KATEGORI -->


                        <div>

                            <label
                                class="block text-sm font-medium
                                       text-[#45464e] mb-2">
                                Penerbit
                            </label>

                            <input
                                type="text"
                                name="penerbit"
                                value="<?= htmlspecialchars($buku['penerbit']) ?>"
                                class="w-full
                                       px-4 py-3
                                       rounded-lg
                                       border border-[#c6c6ce]
                                       bg-[#f8f9ff]
                                       text-sm
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-[#182442]/20
                                       focus:border-[#182442]">

                        </div>

                    </div>

                    <div>

                        <label
                            for="kategori_id"
                            class="block text-sm font-medium
               text-[#45464e] mb-2">

                            Kategori
                            <span class="text-red-500">*</span>

                        </label>


                        <select
                            id="kategori_id"
                            name="kategori_id"
                            required
                            class="w-full
               px-4 py-3
               rounded-lg
               border border-[#c6c6ce]
               bg-[#f8f9ff]
               text-sm
               focus:outline-none
               focus:ring-2
               focus:ring-[#182442]/20
               focus:border-[#182442]">

                            <option value="">
                                Pilih kategori
                            </option>


                            <?php while ($kategori = mysqli_fetch_assoc($kategoriQuery)): ?>

                                <option
                                    value="<?= $kategori['id'] ?>"
                                    <?= (
                                        $buku['kategori_id'] == $kategori['id']
                                    ) ? 'selected' : '' ?>>

                                    <?= htmlspecialchars(
                                        $kategori['nama_kategori']
                                    ) ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <!-- TAHUN + STOK -->

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>

                            <label
                                class="block text-sm font-medium
                                       text-[#45464e] mb-2">
                                Tahun Terbit
                            </label>

                            <input
                                type="number"
                                name="tahun_terbit"
                                value="<?= htmlspecialchars($buku['tahun_terbit']) ?>"
                                min="1900"
                                max="2100"
                                class="w-full
                                       px-4 py-3
                                       rounded-lg
                                       border border-[#c6c6ce]
                                       bg-[#f8f9ff]
                                       text-sm
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-[#182442]/20
                                       focus:border-[#182442]">

                        </div>


                        <div>

                            <label
                                class="block text-sm font-medium
                                       text-[#45464e] mb-2">
                                Stok
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="number"
                                name="stok"
                                required
                                min="0"
                                value="<?= htmlspecialchars($buku['stok']) ?>"
                                class="w-full
                                       px-4 py-3
                                       rounded-lg
                                       border border-[#c6c6ce]
                                       bg-[#f8f9ff]
                                       text-sm
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-[#182442]/20
                                       focus:border-[#182442]">

                        </div>

                    </div>

                </div>


                <!-- FOOTER -->

                <div
                    class="p-5 md:p-6
                           bg-[#f8f9ff]
                           border-t border-[#c6c6ce]/20
                           flex flex-col-reverse
                           sm:flex-row
                           justify-end
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
                               hover:bg-[#f8f9ff]">
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
                               hover:bg-[#2e3a59]">

                        <span class="material-symbols-outlined text-[19px]">
                            save
                        </span>

                        Simpan Perubahan

                    </button>

                </div>

            </form>

        </div>

    </div>

</main>


<?php require_once "../partials/footer.php"; ?>