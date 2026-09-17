```php
<?php

$pageTitle = "Edit Kategori";

require_once "../config/database.php";

$error = "";

$id = (int) ($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}


/* =========================================
   AMBIL DATA KATEGORI
========================================= */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nama_kategori
     FROM kategori
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);

$kategori = mysqli_fetch_assoc($hasil);

if (!$kategori) {
    header("Location: index.php");
    exit;
}


/* =========================================
   PROSES UPDATE
========================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST["nama_kategori"] ?? "");


    if ($nama === "") {

        $error = "Nama kategori wajib diisi.";

    } else {

        /* Cek nama kategori agar tidak duplikat */

        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id
             FROM kategori
             WHERE nama_kategori = ?
             AND id != ?"
        );

        mysqli_stmt_bind_param(
            $cek,
            "si",
            $nama,
            $id
        );

        mysqli_stmt_execute($cek);

        $hasilCek = mysqli_stmt_get_result($cek);


        if (mysqli_num_rows($hasilCek) > 0) {

            $error = "Kategori tersebut sudah ada.";

        } else {

            /* Update kategori */

            $update = mysqli_prepare(
                $koneksi,
                "UPDATE kategori
                 SET nama_kategori = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $update,
                "si",
                $nama,
                $id
            );


            if (mysqli_stmt_execute($update)) {

                header("Location: index.php?success=updated");
                exit;

            } else {

                $error = "Gagal mengubah kategori.";

            }
        }
    }
}


/* =========================================
   HEADER
========================================= */

require_once "../partials/header.php";
require_once "../partials/sidebar.php";

?>

<main class="flex-1 min-w-0">

    <?php require_once "../partials/navbar.php"; ?>


    <div class="p-5 md:p-8">

        <div class="max-w-3xl mx-auto">


            <!-- HEADER -->

            <div class="mb-6">

                <div class="flex items-center gap-2 text-sm mb-3">

                    <a
                        href="index.php"
                        class="text-[#75777e]
                               hover:text-[#182442]
                               transition"
                    >
                        Kategori
                    </a>

                    <span class="text-[#75777e]">
                        /
                    </span>

                    <span class="text-[#182442]">
                        Edit
                    </span>

                </div>


                <h1 class="text-2xl font-semibold text-[#0b1c30]">
                    Edit Kategori
                </h1>

                <p class="text-sm text-[#75777e] mt-1">
                    Ubah nama kategori buku.
                </p>

            </div>


            <!-- ERROR -->

            <?php if ($error): ?>

                <div
                    class="mb-5
                           p-4
                           rounded-lg
                           bg-red-50
                           border border-red-200
                           text-red-700
                           text-sm"
                >

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>


            <!-- FORM -->

            <form
                method="POST"
                class="bg-white
                       rounded-xl
                       border border-[#c6c6ce]/20
                       shadow-sm
                       overflow-hidden"
            >

                <div class="p-6">


                    <!-- NAMA KATEGORI -->

                    <div>

                        <label
                            class="block
                                   text-sm
                                   font-medium
                                   text-[#45464e]
                                   mb-2"
                        >

                            Nama Kategori

                            <span class="text-red-500">
                                *
                            </span>

                        </label>


                        <input
                            type="text"
                            name="nama_kategori"
                            value="<?= htmlspecialchars(
                                $_POST["nama_kategori"]
                                ?? $kategori["nama_kategori"]
                            ) ?>"
                            placeholder="Contoh: Teknologi"
                            required
                            autofocus
                            class="w-full
                                   px-4
                                   py-3
                                   rounded-lg
                                   border border-[#c6c6ce]
                                   bg-[#f8f9ff]
                                   text-sm
                                   focus:outline-none
                                   focus:ring-2
                                   focus:ring-[#182442]/20
                                   focus:border-[#182442]"
                        >

                    </div>

                </div>


                <!-- FOOTER -->

                <div
                    class="p-5
                           bg-[#f8f9ff]
                           border-t border-[#c6c6ce]/20
                           flex flex-col-reverse
                           sm:flex-row
                           justify-end
                           gap-3"
                >


                    <!-- BATAL -->

                    <a
                        href="index.php"
                        class="px-5
                               py-2.5
                               rounded-lg
                               border border-[#c6c6ce]
                               bg-white
                               text-[#45464e]
                               text-sm
                               font-medium
                               text-center
                               hover:bg-gray-50
                               transition"
                    >

                        Batal

                    </a>


                    <!-- SIMPAN -->

                    <button
                        type="submit"
                        class="px-5
                               py-2.5
                               rounded-lg
                               bg-[#182442]
                               text-white
                               text-sm
                               font-medium
                               hover:bg-[#2e3a59]
                               transition"
                    >

                        Simpan Perubahan

                    </button>

                </div>

            </form>

        </div>

    </div>

</main>


<?php require_once "../partials/footer.php"; ?>
```
