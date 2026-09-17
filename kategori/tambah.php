<?php

$pageTitle = "Tambah Kategori";

require_once "../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST["nama_kategori"] ?? "");


    if ($nama === "") {

        $error = "Nama kategori wajib diisi.";

    } else {

        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM kategori WHERE nama_kategori = ?"
        );

        mysqli_stmt_bind_param(
            $cek,
            "s",
            $nama
        );

        mysqli_stmt_execute($cek);

        $hasil = mysqli_stmt_get_result($cek);


        if (mysqli_num_rows($hasil) > 0) {

            $error = "Kategori tersebut sudah ada.";

        } else {

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO kategori
                (nama_kategori)
                VALUES (?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $nama
            );


            if (mysqli_stmt_execute($stmt)) {

                header("Location: index.php?success=added");
                exit;

            } else {

                $error = "Gagal menambahkan kategori.";

            }
        }
    }
}


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
                        class="text-[#75777e] hover:text-[#182442]"
                    >
                        Kategori
                    </a>

                    <span class="text-[#75777e]">
                        /
                    </span>

                    <span class="text-[#182442]">
                        Tambah
                    </span>

                </div>


                <h1 class="text-2xl font-semibold text-[#0b1c30]">
                    Tambah Kategori
                </h1>

                <p class="text-sm text-[#75777e] mt-1">
                    Tambahkan kategori buku baru.
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
                            class="block text-sm font-medium
                                   text-[#45464e] mb-2"
                        >

                            Nama Kategori

                            <span class="text-red-500">
                                *
                            </span>

                        </label>


                        <input
                            type="text"
                            name="nama_kategori"
                            value="<?= htmlspecialchars($_POST['nama_kategori'] ?? '') ?>"
                            placeholder="Contoh: Teknologi"
                            required
                            autofocus
                            class="w-full
                                   px-4 py-3
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
                        class="px-5 py-2.5
                               rounded-lg
                               border border-[#c6c6ce]
                               bg-white
                               text-[#45464e]
                               text-sm font-medium
                               text-center
                               hover:bg-gray-50
                               transition"
                    >

                        Batal

                    </a>


                    <!-- SIMPAN -->

                    <button
                        type="submit"
                        class="px-5 py-2.5
                               rounded-lg
                               bg-[#182442]
                               text-white
                               text-sm font-medium
                               hover:bg-[#2e3a59]
                               transition"
                    >

                        Simpan Kategori

                    </button>


                </div>

            </form>

        </div>

    </div>

</main>


<?php require_once "../partials/footer.php"; ?>