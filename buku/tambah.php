<?php

$pageTitle = "Tambah Buku";

require_once "../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $kode_buku    = trim($_POST["kode_buku"] ?? "");
    $judul        = trim($_POST["judul"] ?? "");
    $penulis      = trim($_POST["penulis"] ?? "");
    $penerbit     = trim($_POST["penerbit"] ?? "");
    $tahun_terbit = trim($_POST["tahun_terbit"] ?? "");
    $kategori_id  = (int) ($_POST["kategori_id"] ?? 0);
    $stok         = (int) ($_POST["stok"] ?? 0);


    if ($kode_buku === "" || $judul === "" || $penulis === "") {

        $error = "Kode buku, judul, dan penulis wajib diisi.";
    } elseif ($stok < 0) {

        $error = "Stok tidak boleh kurang dari 0.";
    } else {

        // Cek kode buku
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM buku WHERE kode_buku = ?"
        );

        mysqli_stmt_bind_param(
            $cek,
            "s",
            $kode_buku
        );

        mysqli_stmt_execute($cek);

        $hasilCek = mysqli_stmt_get_result($cek);


        if (mysqli_num_rows($hasilCek) > 0) {

            $error = "Kode buku sudah digunakan.";
        } else {

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO buku
    (kode_buku, judul, penulis, penerbit, tahun_terbit, kategori_id, stok)
    VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sssssii",
                $kode_buku,
                $judul,
                $penulis,
                $penerbit,
                $tahun_terbit,
                $kategori_id,
                $stok
            );


            if (mysqli_stmt_execute($stmt)) {

                // REDIRECT HARUS TERJADI SEBELUM HTML DIKIRIM
                header("Location: index.php?success=added");
                exit;
            } else {

                $error = "Gagal menambahkan buku.";
            }
        }
    }
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
                        Tambah Buku
                    </span>

                </div>


                <h1 class="text-2xl font-semibold text-[#0b1c30]">

                    Tambah Buku

                </h1>

                <p class="text-sm text-[#75777e] mt-1">

                    Tambahkan buku baru ke dalam koleksi perpustakaan.

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


                <!-- FORM HEADER -->

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
                                menu_book
                            </span>

                        </div>


                        <div>

                            <h2 class="font-semibold text-[#0b1c30]">

                                Informasi Buku

                            </h2>

                            <p class="text-xs text-[#75777e] mt-1">

                                Isi informasi buku dengan lengkap.

                            </p>

                        </div>

                    </div>

                </div>



                <!-- FORM BODY -->

                <div class="p-5 md:p-6 space-y-5">


                    <!-- KODE + JUDUL -->

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


                        <!-- KODE -->

                        <div>

                            <label
                                for="kode_buku"
                                class="block text-sm font-medium text-[#45464e] mb-2">

                                Kode Buku
                                <span class="text-red-500">*</span>

                            </label>

                            <input
                                type="text"
                                id="kode_buku"
                                name="kode_buku"
                                value="<?= htmlspecialchars($_POST['kode_buku'] ?? '') ?>"
                                placeholder="Contoh: BK005"
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

                        </div>



                        <!-- JUDUL -->

                        <div>

                            <label
                                for="judul"
                                class="block text-sm font-medium text-[#45464e] mb-2">

                                Judul Buku
                                <span class="text-red-500">*</span>

                            </label>

                            <input
                                type="text"
                                id="judul"
                                name="judul"
                                value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>"
                                placeholder="Contoh: Negeri 5 Menara"
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

                        </div>

                    </div>



                    <!-- PENULIS + PENERBIT -->

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


                        <!-- PENULIS -->

                        <div>

                            <label
                                for="penulis"
                                class="block text-sm font-medium text-[#45464e] mb-2">

                                Penulis
                                <span class="text-red-500">*</span>

                            </label>

                            <input
                                type="text"
                                id="penulis"
                                name="penulis"
                                value="<?= htmlspecialchars($_POST['penulis'] ?? '') ?>"
                                placeholder="Nama penulis"
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

                        </div>



                        <!-- PENERBIT -->

                        <div>

                            <label
                                for="penerbit"
                                class="block text-sm font-medium text-[#45464e] mb-2">

                                Penerbit

                            </label>

                            <input
                                type="text"
                                id="penerbit"
                                name="penerbit"
                                value="<?= htmlspecialchars($_POST['penerbit'] ?? '') ?>"
                                placeholder="Nama penerbit"
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


                    <!-- KATEGORI -->

                    <div>

                        <label
                            for="kategori_id"
                            class="block text-sm font-medium text-[#45464e] mb-2">

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
                                        ($_POST['kategori_id'] ?? '') == $kategori['id']
                                    ) ? 'selected' : '' ?>>

                                    <?= htmlspecialchars($kategori['nama_kategori']) ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <!-- TAHUN + STOK -->

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


                        <!-- TAHUN -->

                        <div>

                            <label
                                for="tahun_terbit"
                                class="block text-sm font-medium text-[#45464e] mb-2">

                                Tahun Terbit

                            </label>

                            <input
                                type="number"
                                id="tahun_terbit"
                                name="tahun_terbit"
                                value="<?= htmlspecialchars($_POST['tahun_terbit'] ?? '') ?>"
                                placeholder="Contoh: 2025"
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



                        <!-- STOK -->

                        <div>

                            <label
                                for="stok"
                                class="block text-sm font-medium text-[#45464e] mb-2">

                                Stok
                                <span class="text-red-500">*</span>

                            </label>

                            <input
                                type="number"
                                id="stok"
                                name="stok"
                                value="<?= htmlspecialchars($_POST['stok'] ?? '0') ?>"
                                min="0"
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

                        </div>

                    </div>

                </div>



                <!-- FORM FOOTER -->

                <div
                    class="p-5 md:p-6
                           bg-[#f8f9ff]
                           border-t border-[#c6c6ce]/20
                           flex flex-col-reverse sm:flex-row
                           justify-end
                           gap-3">

                    <a
                        href="index.php"
                        class="inline-flex items-center justify-center
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
                        class="inline-flex items-center justify-center gap-2
                               px-5 py-2.5
                               rounded-lg
                               bg-[#182442]
                               text-white
                               text-sm font-medium
                               hover:bg-[#2e3a59]
                               transition
                               shadow-sm">

                        <span class="material-symbols-outlined text-[19px]">
                            save
                        </span>

                        Simpan Buku

                    </button>

                </div>

            </form>

        </div>

    </div>

</main>


<?php require_once "../partials/footer.php"; ?>