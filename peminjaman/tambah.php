<?php

require_once "../config/auth.php";
require_once "../config/database.php";

$pageTitle = "Tambah Peminjaman";

$error = "";

$anggotaTerpilih = null;
$bukuTerpilih = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $anggota_id = intval($_POST['anggota_id'] ?? 0);

    $buku_ids = $_POST['buku_id'] ?? [];

    if (!is_array($buku_ids)) {
        $buku_ids = [$buku_ids];
    }

    $buku_ids = array_values(
        array_unique(
            array_filter(
                array_map('intval', $buku_ids),
                function ($id) {
                    return $id > 0;
                }
            )
        )
    );

    $tanggal_pinjam = $_POST['tanggal_pinjam'] ?? '';
    $tanggal_jatuh_tempo = $_POST['tanggal_jatuh_tempo'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | Validasi dasar
    |--------------------------------------------------------------------------
    */

    if ($anggota_id <= 0) {

        $error = "Silakan pilih anggota.";
    } elseif (count($buku_ids) < 1) {

        $error = "Silakan pilih minimal satu buku.";
    } elseif (count($buku_ids) > 5) {

        $error = "Maksimal 5 buku dalam satu transaksi.";
    } elseif (
        empty($tanggal_pinjam) ||
        empty($tanggal_jatuh_tempo)
    ) {

        $error = "Tanggal peminjaman wajib diisi.";
    } elseif ($tanggal_jatuh_tempo < $tanggal_pinjam) {

        $error = "Tanggal jatuh tempo tidak boleh sebelum tanggal pinjam.";
    } else {

        mysqli_begin_transaction($koneksi);

        try {

            /*
            |--------------------------------------------------------------------------
            | Cek anggota
            |--------------------------------------------------------------------------
            */

            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id, nisn, nama, kelas, status
                 FROM anggota
                 WHERE id = ?
                 LIMIT 1"
            );

            if (!$stmt) {
                throw new Exception("Gagal memeriksa data anggota.");
            }

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $anggota_id
            );

            mysqli_stmt_execute($stmt);

            $resultAnggota = mysqli_stmt_get_result($stmt);

            $anggota = mysqli_fetch_assoc($resultAnggota);

            if (!$anggota) {

                throw new Exception(
                    "Anggota tidak ditemukan."
                );
            }

            if ($anggota['status'] !== 'Aktif') {

                throw new Exception(
                    "Anggota tidak aktif dan tidak dapat melakukan peminjaman."
                );
            }

            $anggotaTerpilih = $anggota;

            /*
            |--------------------------------------------------------------------------
            | User yang melakukan transaksi
            |--------------------------------------------------------------------------
            */

            $user_id = $_SESSION['user_id'] ?? null;

            /*
            |--------------------------------------------------------------------------
            | Mulai proses setiap buku
            |--------------------------------------------------------------------------
            */

            foreach ($buku_ids as $buku_id) {

                /*
                |--------------------------------------------------------------------------
                | Kunci dan cek stok buku
                |--------------------------------------------------------------------------
                */

                $stmt = mysqli_prepare(
                    $koneksi,
                    "SELECT id, kode_buku, judul, penulis, stok
                     FROM buku
                     WHERE id = ?
                     FOR UPDATE"
                );

                if (!$stmt) {
                    throw new Exception("Gagal memeriksa data buku.");
                }

                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $buku_id
                );

                mysqli_stmt_execute($stmt);

                $resultBuku = mysqli_stmt_get_result($stmt);

                $buku = mysqli_fetch_assoc($resultBuku);

                if (!$buku) {

                    throw new Exception(
                        "Salah satu buku tidak ditemukan."
                    );
                }

                if ((int) $buku['stok'] <= 0) {

                    throw new Exception(
                        'Stok buku "' .
                            $buku['judul'] .
                            '" sedang habis.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Generate kode peminjaman
                |--------------------------------------------------------------------------
                */

                $kode_peminjaman =
                    "PJM-" .
                    date('YmdHis') .
                    "-" .
                    random_int(100, 999);

                /*
                |--------------------------------------------------------------------------
                | Simpan transaksi
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

                if (!$stmt) {
                    throw new Exception(
                        "Gagal menyiapkan data peminjaman."
                    );
                }

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
                | Kurangi stok
                |--------------------------------------------------------------------------
                */

                $stmt = mysqli_prepare(
                    $koneksi,
                    "UPDATE buku
                     SET stok = stok - 1
                     WHERE id = ?
                     AND stok > 0"
                );

                if (!$stmt) {
                    throw new Exception(
                        "Gagal menyiapkan pembaruan stok."
                    );
                }

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
                        'Stok buku "' .
                            $buku['judul'] .
                            '" tidak berhasil diperbarui.'
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Commit
            |--------------------------------------------------------------------------
            */

            mysqli_commit($koneksi);

            header(
                "Location: index.php?success=added"
            );

            exit;
        } catch (Throwable $e) {

            mysqli_rollback($koneksi);

            $error = $e->getMessage();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Ambil kembali data pilihan ketika validasi gagal
    |--------------------------------------------------------------------------
    */

    if ($anggota_id > 0) {

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id, nisn, nama, kelas, status
             FROM anggota
             WHERE id = ?
             LIMIT 1"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $anggota_id
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            $anggotaTerpilih = mysqli_fetch_assoc($result);
        }
    }

    if (!empty($buku_ids)) {

        $placeholders = implode(
            ',',
            array_fill(0, count($buku_ids), '?')
        );

        $types = str_repeat('i', count($buku_ids));

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id, kode_buku, judul, penulis, stok
             FROM buku
             WHERE id IN ($placeholders)
             ORDER BY FIELD(id, $placeholders)"
        );

        if ($stmt) {

            $params = array_merge(
                $buku_ids,
                $buku_ids
            );

            $bindTypes = $types . $types;

            $bindValues = [$bindTypes];

            foreach ($params as $key => $value) {
                $bindValues[] = &$params[$key];
            }

            call_user_func_array(
                [$stmt, 'bind_param'],
                $bindValues
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            while ($row = mysqli_fetch_assoc($result)) {
                $bukuTerpilih[] = $row;
            }
        }
    }
}

?>

<?php require_once "../partials/header.php"; ?>
<?php require_once "../partials/sidebar.php"; ?>

<main class="flex-1 min-w-0">

    <?php require_once "../partials/navbar.php"; ?>

    <div class="p-5 md:p-8 overflow-y-auto">

        <div class="max-w-3xl mx-auto space-y-6">

            <!-- HEADER -->

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                <div>

                    <h1 class="text-2xl font-semibold text-[#0b1c30]">
                        Tambah Peminjaman
                    </h1>

                    <p class="text-sm text-[#75777e] mt-1">
                        Buat transaksi peminjaman buku baru.
                    </p>

                </div>

                <a
                    href="index.php"
                    class="inline-flex items-center justify-center gap-2
                           px-4 py-2.5
                           rounded-lg
                           border border-[#c6c6ce]
                           bg-white
                           text-[#45464e]
                           text-sm font-medium
                           hover:bg-[#f8f9ff]
                           transition">

                    <span class="material-symbols-outlined text-[20px]">
                        arrow_back
                    </span>

                    Kembali

                </a>

            </div>


            <!-- ERROR -->

            <?php if ($error): ?>

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

                        <p class="text-sm font-medium">
                            Gagal membuat peminjaman
                        </p>

                        <p class="text-sm mt-1">
                            <?= htmlspecialchars($error) ?>
                        </p>

                    </div>

                </div>

            <?php endif; ?>


            <!-- CARD -->

            <div
                class="bg-white
                       rounded-xl
                       border border-[#c6c6ce]/20
                       shadow-sm
                       overflow-hidden">

                <!-- CARD HEADER -->

                <div
                    class="px-6 py-5
                           border-b border-[#c6c6ce]/20
                           bg-[#f8f9ff]">

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
                                Informasi Peminjaman
                            </h2>

                            <p class="text-xs text-[#75777e] mt-0.5">
                                Pilih anggota dan buku yang akan dipinjam.
                            </p>

                        </div>

                    </div>

                </div>


                <form
                    method="POST"
                    id="formPeminjaman"
                    class="p-6 space-y-6">


                    <!-- ANGGOTA -->

                    <div>

                        <label
                            class="block text-sm font-medium text-[#0b1c30] mb-2">

                            Anggota
                            <span class="text-red-500">*</span>

                        </label>

                        <input
                            type="hidden"
                            name="anggota_id"
                            id="anggota_id"
                            value="<?= (int)($anggotaTerpilih['id'] ?? 0) ?>">

                        <div
                            id="anggota-selected"
                            class="<?= $anggotaTerpilih ? '' : 'hidden' ?>">

                            <?php if ($anggotaTerpilih): ?>

                                <div
                                    class="flex flex-col sm:flex-row
                                           sm:items-center
                                           sm:justify-between
                                           gap-4
                                           p-4
                                           rounded-lg
                                           border border-[#c6c6ce]
                                           bg-[#f8f9ff]">

                                    <div class="flex items-start gap-3">

                                        <div
                                            class="w-10 h-10 shrink-0
                                                   rounded-lg
                                                   bg-[#e5eeff]
                                                   text-[#182442]
                                                   flex items-center justify-center">

                                            <span class="material-symbols-outlined">
                                                person
                                            </span>

                                        </div>

                                        <div>

                                            <p
                                                id="anggota-selected-nama"
                                                class="text-sm font-semibold text-[#0b1c30]">

                                                <?= htmlspecialchars($anggotaTerpilih['nama']) ?>

                                            </p>

                                            <p
                                                id="anggota-selected-detail"
                                                class="text-xs text-[#75777e] mt-1">

                                                NISN:
                                                <?= htmlspecialchars($anggotaTerpilih['nisn']) ?>

                                                <?php if (!empty($anggotaTerpilih['kelas'])): ?>

                                                    <span class="mx-1">•</span>

                                                    Kelas:
                                                    <?= htmlspecialchars($anggotaTerpilih['kelas']) ?>

                                                <?php endif; ?>

                                            </p>

                                        </div>

                                    </div>

                                    <button
                                        type="button"
                                        onclick="bukaPilihAnggota()"
                                        class="inline-flex items-center justify-center gap-2
                                               px-4 py-2
                                               rounded-lg
                                               border border-[#c6c6ce]
                                               bg-white
                                               text-[#45464e]
                                               text-sm font-medium
                                               hover:bg-[#f8f9ff]
                                               transition">

                                        <span class="material-symbols-outlined text-[18px]">
                                            edit
                                        </span>

                                        Ganti

                                    </button>

                                </div>

                            <?php endif; ?>

                        </div>


                        <div
                            id="anggota-empty"
                            class="<?= $anggotaTerpilih ? 'hidden' : '' ?>">

                            <button
                                type="button"
                                onclick="bukaPilihAnggota()"
                                class="w-full
                                       inline-flex items-center justify-center gap-2
                                       px-4 py-3
                                       rounded-lg
                                       border border-[#c6c6ce]
                                       bg-[#f8f9ff]
                                       text-[#45464e]
                                       text-sm font-medium
                                       hover:bg-white
                                       hover:border-[#182442]
                                       transition">

                                <span class="material-symbols-outlined text-[20px]">
                                    person_add
                                </span>

                                Pilih Anggota

                            </button>

                        </div>


                        <!-- PANEL ANGGOTA -->

                        <div
                            id="anggota-panel"
                            class="hidden mt-3
                                   rounded-lg
                                   border border-[#c6c6ce]/50
                                   bg-white
                                   shadow-sm
                                   overflow-hidden">

                            <div class="p-4 bg-[#f8f9ff] border-b border-[#c6c6ce]/20">

                                <div class="relative">

                                    <span
                                        class="material-symbols-outlined
                                               absolute left-3 top-1/2
                                               -translate-y-1/2
                                               text-[#75777e] text-[20px]">

                                        search

                                    </span>

                                    <input
                                        type="text"
                                        id="searchAnggota"
                                        placeholder="Cari nama atau NISN..."
                                        autocomplete="off"
                                        class="w-full
                                               pl-10 pr-4 py-3
                                               rounded-lg
                                               border border-[#c6c6ce]
                                               bg-white
                                               text-sm text-[#0b1c30]
                                               placeholder-[#75777e]
                                               focus:outline-none
                                               focus:ring-2
                                               focus:ring-[#182442]/20
                                               focus:border-[#182442]
                                               transition">

                                </div>

                            </div>

                            <div
                                id="anggota-list"
                                class="max-h-[320px] overflow-y-auto">

                                <div class="p-6 text-center">

                                    <span
                                        class="material-symbols-outlined
                                               text-[#75777e]
                                               text-[28px]
                                               animate-spin">

                                        progress_activity

                                    </span>

                                    <p class="text-xs text-[#75777e] mt-2">
                                        Memuat anggota...
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- BUKU -->

                    <div>

                        <div class="flex items-center justify-between gap-3 mb-2">

                            <label
                                class="block text-sm font-medium text-[#0b1c30]">

                                Buku
                                <span class="text-red-500">*</span>

                            </label>

                            <span
                                id="jumlahBuku"
                                class="inline-flex items-center
                                       px-2.5 py-1
                                       rounded-full
                                       bg-[#e5eeff]
                                       text-[#182442]
                                       text-xs font-medium">

                                0 / 5

                            </span>

                        </div>


                        <div
                            id="buku-container"
                            class="space-y-3">

                        </div>


                        <button
                            type="button"
                            id="tambahBukuBtn"
                            onclick="tambahBuku()"
                            class="mt-3
                                   inline-flex items-center justify-center gap-2
                                   px-4 py-2.5
                                   rounded-lg
                                   border border-[#c6c6ce]
                                   bg-white
                                   text-[#45464e]
                                   text-sm font-medium
                                   hover:bg-[#f8f9ff]
                                   hover:border-[#182442]
                                   transition">

                            <span class="material-symbols-outlined text-[20px]">
                                add
                            </span>

                            Tambah Buku

                        </button>

                        <p class="text-xs text-[#75777e] mt-2">
                            Buku yang ditampilkan hanya buku yang masih memiliki stok.
                            Maksimal 5 buku dalam satu transaksi.
                        </p>

                    </div>


                    <!-- TANGGAL -->

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>

                            <label
                                for="tanggal_pinjam"
                                class="block text-sm font-medium text-[#0b1c30] mb-2">

                                Tanggal Pinjam
                                <span class="text-red-500">*</span>

                            </label>

                            <input
                                type="date"
                                name="tanggal_pinjam"
                                id="tanggal_pinjam"
                                required
                                value="<?= htmlspecialchars(
                                            $_POST['tanggal_pinjam']
                                                ?? date('Y-m-d')
                                        ) ?>"
                                class="w-full px-4 py-3 rounded-lg
                                       border border-[#c6c6ce]
                                       bg-[#f8f9ff]
                                       text-sm text-[#0b1c30]
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-[#182442]/20
                                       focus:border-[#182442]
                                       transition">

                        </div>


                        <div>

                            <label
                                for="tanggal_jatuh_tempo"
                                class="block text-sm font-medium text-[#0b1c30] mb-2">

                                Tanggal Jatuh Tempo
                                <span class="text-red-500">*</span>

                            </label>

                            <input
                                type="date"
                                name="tanggal_jatuh_tempo"
                                id="tanggal_jatuh_tempo"
                                required
                                value="<?= htmlspecialchars(
                                            $_POST['tanggal_jatuh_tempo']
                                                ?? date(
                                                    'Y-m-d',
                                                    strtotime('+7 days')
                                                )
                                        ) ?>"
                                class="w-full px-4 py-3 rounded-lg
                                       border border-[#c6c6ce]
                                       bg-[#f8f9ff]
                                       text-sm text-[#0b1c30]
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-[#182442]/20
                                       focus:border-[#182442]
                                       transition">

                            <p class="text-xs text-[#75777e] mt-1.5">
                                Default batas peminjaman adalah 7 hari.
                            </p>

                        </div>

                    </div>


                    <!-- INFO -->

                    <div
                        class="flex items-start gap-3
                               p-4 rounded-lg
                               bg-[#f8f9ff]
                               border border-[#c6c6ce]/30">

                        <span class="material-symbols-outlined text-[#182442]">
                            info
                        </span>

                        <div>

                            <p class="text-sm font-medium text-[#0b1c30]">
                                Informasi
                            </p>

                            <p class="text-xs text-[#75777e] mt-1">
                                Field yang bertanda
                                <span class="text-red-500">*</span>
                                wajib diisi.
                            </p>

                        </div>

                    </div>


                    <!-- ACTION -->

                    <div
                        class="flex flex-col-reverse sm:flex-row
                               sm:justify-end gap-3
                               pt-5
                               border-t border-[#c6c6ce]/20">

                        <a
                            href="index.php"
                            class="inline-flex items-center justify-center
                                   px-5 py-2.5 rounded-lg
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
                                   px-5 py-2.5 rounded-lg
                                   bg-[#182442]
                                   text-white
                                   text-sm font-medium
                                   hover:bg-[#2e3a59]
                                   transition shadow-sm">

                            <span class="material-symbols-outlined text-[20px]">
                                save
                            </span>

                            Simpan Peminjaman

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</main>


<script>
    let bukuCounter = 0;
    let bukuTerpilih = <?= json_encode($bukuTerpilih) ?>;

    let anggotaPanelOpen = false;

    const initialBuku = bukuTerpilih.length > 0 ?
        bukuTerpilih :
        [];

    document.addEventListener('DOMContentLoaded', function() {

        if (initialBuku.length > 0) {

            initialBuku.forEach(function(buku) {
                tambahBuku(buku);
            });

        } else {

            tambahBuku();

        }

        updateJumlahBuku();

        document
            .getElementById('searchAnggota')
            .addEventListener('input', debounce(function() {

                cariAnggota(
                    this.value.trim()
                );

            }, 250));

    });


    function debounce(callback, delay) {

        let timeout;

        return function() {

            clearTimeout(timeout);

            timeout = setTimeout(
                callback,
                delay
            );

        };

    }


    /*
    |--------------------------------------------------------------------------
    | Anggota
    |--------------------------------------------------------------------------
    */

    function bukaPilihAnggota() {

        const panel = document.getElementById('anggota-panel');

        if (panel.classList.contains('hidden')) {

            panel.classList.remove('hidden');

            anggotaPanelOpen = true;

            const search =
                document.getElementById('searchAnggota');

            search.value = '';

            cariAnggota('');

            setTimeout(function() {

                search.focus();

            }, 50);

        } else {

            tutupPilihAnggota();

        }

    }


    function tutupPilihAnggota() {

        document
            .getElementById('anggota-panel')
            .classList.add('hidden');

        anggotaPanelOpen = false;

    }


    async function cariAnggota(query = '') {

        const list =
            document.getElementById('anggota-list');

        list.innerHTML = `
        <div class="p-6 text-center">
            <span class="material-symbols-outlined
                         text-[#75777e]
                         text-[28px]
                         animate-spin">
                progress_activity
            </span>

            <p class="text-xs text-[#75777e] mt-2">
                Mencari anggota...
            </p>
        </div>
    `;

        try {

            const response =
                await fetch(
                    'cari_anggota.php?q=' +
                    encodeURIComponent(query)
                );

            const result =
                await response.json();

            if (
                !result.success ||
                !Array.isArray(result.data)
            ) {

                throw new Error(
                    result.message ||
                    'Gagal mengambil data anggota.'
                );

            }

            if (result.data.length === 0) {

                list.innerHTML = `
                <div class="p-8 text-center">

                    <span class="material-symbols-outlined
                                 text-[#75777e]
                                 text-[32px]">
                        person_off
                    </span>

                    <p class="text-sm
                              font-medium
                              text-[#45464e]
                              mt-2">

                        Anggota tidak ditemukan

                    </p>

                    <p class="text-xs text-[#75777e] mt-1">
                        Coba gunakan nama atau NISN lain.
                    </p>

                </div>
            `;

                return;

            }

            list.innerHTML =
                result.data
                .map(function(anggota) {

                    return `
                        <button
                            type="button"
                            onclick="pilihAnggota(
                                ${Number(anggota.id)},
                                '${escapeJs(anggota.nisn)}',
                                '${escapeJs(anggota.nama)}',
                                '${escapeJs(anggota.kelas || '')}'
                            )"
                            class="w-full
                                   text-left
                                   flex items-center gap-3
                                   px-4 py-3.5
                                   border-b border-[#c6c6ce]/20
                                   hover:bg-[#f8f9ff]
                                   transition">

                            <div
                                class="w-9 h-9 shrink-0
                                       rounded-lg
                                       bg-[#e5eeff]
                                       text-[#182442]
                                       flex items-center justify-center">

                                <span class="material-symbols-outlined text-[19px]">
                                    person
                                </span>

                            </div>

                            <div class="min-w-0 flex-1">

                                <p class="text-sm
                                          font-medium
                                          text-[#0b1c30]
                                          truncate">

                                    ${escapeHtml(anggota.nama)}

                                </p>

                                <p class="text-xs
                                          text-[#75777e]
                                          mt-0.5">

                                    NISN:
                                    ${escapeHtml(anggota.nisn)}

                                    ${
                                        anggota.kelas
                                            ? ' • Kelas ' +
                                              escapeHtml(anggota.kelas)
                                            : ''
                                    }

                                </p>

                            </div>

                            <span
                                class="material-symbols-outlined
                                       text-[#75777e]
                                       text-[20px]">

                                chevron_right

                            </span>

                        </button>
                    `;

                })
                .join('');

        } catch (error) {

            list.innerHTML = `
            <div class="p-6 text-center">

                <span
                    class="material-symbols-outlined
                           text-red-500
                           text-[30px]">

                    error

                </span>

                <p class="text-sm
                          font-medium
                          text-[#45464e]
                          mt-2">

                    Gagal memuat anggota

                </p>

                <p class="text-xs text-[#75777e] mt-1">
                    ${escapeHtml(error.message)}
                </p>

            </div>
        `;

        }

    }


    function pilihAnggota(
        id,
        nisn,
        nama,
        kelas
    ) {

        document
            .getElementById('anggota_id')
            .value = id;

        document
            .getElementById('anggota-selected')
            .innerHTML = `
            <div
                class="flex flex-col sm:flex-row
                       sm:items-center
                       sm:justify-between
                       gap-4
                       p-4
                       rounded-lg
                       border border-[#c6c6ce]
                       bg-[#f8f9ff]">

                <div class="flex items-start gap-3">

                    <div
                        class="w-10 h-10 shrink-0
                               rounded-lg
                               bg-[#e5eeff]
                               text-[#182442]
                               flex items-center justify-center">

                        <span class="material-symbols-outlined">
                            person
                        </span>

                    </div>

                    <div>

                        <p
                            class="text-sm
                                   font-semibold
                                   text-[#0b1c30]">

                            ${escapeHtml(nama)}

                        </p>

                        <p
                            class="text-xs
                                   text-[#75777e]
                                   mt-1">

                            NISN:
                            ${escapeHtml(nisn)}

                            ${
                                kelas
                                    ? '<span class="mx-1">•</span>Kelas ' +
                                      escapeHtml(kelas)
                                    : ''
                            }

                        </p>

                    </div>

                </div>

                <button
                    type="button"
                    onclick="bukaPilihAnggota()"
                    class="inline-flex items-center justify-center gap-2
                           px-4 py-2
                           rounded-lg
                           border border-[#c6c6ce]
                           bg-white
                           text-[#45464e]
                           text-sm font-medium
                           hover:bg-[#f8f9ff]
                           transition">

                    <span class="material-symbols-outlined text-[18px]">
                        edit
                    </span>

                    Ganti

                </button>

            </div>
        `;

        document
            .getElementById('anggota-selected')
            .classList.remove('hidden');

        document
            .getElementById('anggota-empty')
            .classList.add('hidden');

        tutupPilihAnggota();

    }


    /*
    |--------------------------------------------------------------------------
    | Buku
    |--------------------------------------------------------------------------
    */

    function tambahBuku(data = null) {

        const container =
            document.getElementById('buku-container');

        const jumlah =
            container.querySelectorAll('.buku-item').length;

        if (jumlah >= 5) {

            alert('Maksimal 5 buku dalam satu transaksi.');

            return;

        }

        bukuCounter++;

        const id =
            'buku-' + bukuCounter;

        const item =
            document.createElement('div');

        item.className =
            'buku-item rounded-lg border border-[#c6c6ce] bg-[#f8f9ff] p-4';

        item.dataset.row = id;

        item.innerHTML = `
        <input
            type="hidden"
            name="buku_id[]"
            class="buku-input"
            value="${data ? Number(data.id) : ''}">

        <div class="buku-selected
                    ${data ? '' : 'hidden'}">

            ${
                data
                    ? bukuCardHtml(data, id)
                    : ''
            }

        </div>

        <div class="buku-empty
                    ${data ? 'hidden' : ''}">

            <button
                type="button"
                onclick="bukaPilihBuku('${id}')"
                class="w-full
                       inline-flex
                       items-center
                       justify-center
                       gap-2
                       px-4 py-3
                       rounded-lg
                       border border-[#c6c6ce]
                       bg-white
                       text-[#45464e]
                       text-sm
                       font-medium
                       hover:bg-[#f8f9ff]
                       hover:border-[#182442]
                       transition">

                <span class="material-symbols-outlined text-[20px]">
                    library_add
                </span>

                Pilih Buku

            </button>

        </div>

        <div
            id="panel-${id}"
            class="buku-panel hidden mt-3
                   rounded-lg
                   border border-[#c6c6ce]/50
                   bg-white
                   shadow-sm
                   overflow-hidden">

            <div
                class="p-4
                       bg-[#f8f9ff]
                       border-b border-[#c6c6ce]/20">

                <div class="relative">

                    <span
                        class="material-symbols-outlined
                               absolute left-3 top-1/2
                               -translate-y-1/2
                               text-[#75777e]
                               text-[20px]">

                        search

                    </span>

                    <input
                        type="text"
                        class="buku-search w-full
                               pl-10 pr-4 py-3
                               rounded-lg
                               border border-[#c6c6ce]
                               bg-white
                               text-sm text-[#0b1c30]
                               placeholder-[#75777e]
                               focus:outline-none
                               focus:ring-2
                               focus:ring-[#182442]/20
                               focus:border-[#182442]
                               transition"
                        placeholder="Cari judul, kode, atau penulis..."
                        autocomplete="off"
                        oninput="cariBuku(
                            '${id}',
                            this.value
                        )">

                </div>

            </div>

            <div
                id="list-${id}"
                class="max-h-[320px] overflow-y-auto">

            </div>

        </div>
    `;

        container.appendChild(item);

        updateJumlahBuku();

        if (!data) {

            bukaPilihBuku(id);

        }

    }


    function bukuCardHtml(data, rowId) {

        const total =
            document
            .querySelectorAll('.buku-item')
            .length;

        return `
        <div
            class="flex flex-col sm:flex-row
                   sm:items-center
                   sm:justify-between
                   gap-4">

            <div class="flex items-start gap-3 min-w-0">

                <div
                    class="w-10 h-10 shrink-0
                           rounded-lg
                           bg-[#e5eeff]
                           text-[#182442]
                           flex items-center justify-center">

                    <span class="material-symbols-outlined">
                        menu_book
                    </span>

                </div>

                <div class="min-w-0">

                    <p
                        class="text-sm
                               font-semibold
                               text-[#0b1c30]">

                        ${escapeHtml(data.judul)}

                    </p>

                    <p
                        class="text-xs
                               text-[#75777e]
                               mt-1">

                        ${escapeHtml(data.kode_buku)}

                        ${
                            data.penulis
                                ? ' • ' +
                                  escapeHtml(data.penulis)
                                : ''
                        }

                    </p>

                    <p
                        class="text-xs
                               text-[#75777e]
                               mt-1">

                        Stok tersedia:
                        ${Number(data.stok)}

                    </p>

                </div>

            </div>

            <div class="flex items-center gap-2 shrink-0">

                <button
                    type="button"
                    onclick="bukaPilihBuku('${rowId}')"
                    class="inline-flex
                           items-center
                           justify-center
                           gap-2
                           px-3 py-2
                           rounded-lg
                           border border-[#c6c6ce]
                           bg-white
                           text-[#45464e]
                           text-sm
                           font-medium
                           hover:bg-[#f8f9ff]
                           transition">

                    <span class="material-symbols-outlined text-[18px]">
                        edit
                    </span>

                    Ganti

                </button>

                ${
                    total > 1
                        ? `
                            <button
                                type="button"
                                onclick="hapusBuku('${rowId}')"
                                class="inline-flex
                                       items-center
                                       justify-center
                                       w-9 h-9
                                       rounded-lg
                                       border border-red-200
                                       bg-white
                                       text-red-600
                                       hover:bg-red-50
                                       transition">

                                <span class="material-symbols-outlined text-[19px]">
                                    delete
                                </span>

                            </button>
                        `
                        : ''
                }

            </div>

        </div>
    `;

    }


    function bukaPilihBuku(rowId) {

        const item =
            document.querySelector(
                `[data-row="${rowId}"]`
            );

        if (!item) {
            return;
        }

        const panel =
            item.querySelector('.buku-panel');

        const input =
            item.querySelector('.buku-search');

        const isHidden =
            panel.classList.contains('hidden');

        document
            .querySelectorAll('.buku-panel')
            .forEach(function(otherPanel) {

                otherPanel.classList.add('hidden');

            });

        if (isHidden) {

            panel.classList.remove('hidden');

            input.value = '';

            cariBuku(
                rowId,
                ''
            );

            setTimeout(function() {

                input.focus();

            }, 50);

        }

    }


    async function cariBuku(
        rowId,
        query = ''
    ) {

        const item =
            document.querySelector(
                `[data-row="${rowId}"]`
            );

        if (!item) {
            return;
        }

        const list =
            item.querySelector('.buku-panel > div:last-child');

        list.innerHTML = `
        <div class="p-6 text-center">

            <span
                class="material-symbols-outlined
                       text-[#75777e]
                       text-[28px]
                       animate-spin">

                progress_activity

            </span>

            <p class="text-xs text-[#75777e] mt-2">
                Mencari buku...
            </p>

        </div>
    `;

        try {

            const selectedIds =
                Array.from(
                    document.querySelectorAll('.buku-input')
                )
                .map(function(input) {

                    return Number(input.value);

                })
                .filter(function(id) {

                    return id > 0;

                });

            const response =
                await fetch(
                    'cari_buku.php?q=' +
                    encodeURIComponent(query) +
                    '&exclude=' +
                    encodeURIComponent(
                        selectedIds.join(',')
                    )
                );

            const result =
                await response.json();

            if (
                !result.success ||
                !Array.isArray(result.data)
            ) {

                throw new Error(
                    result.message ||
                    'Gagal mengambil data buku.'
                );

            }

            if (result.data.length === 0) {

                list.innerHTML = `
                <div class="p-8 text-center">

                    <span
                        class="material-symbols-outlined
                               text-[#75777e]
                               text-[32px]">

                        menu_book

                    </span>

                    <p
                        class="text-sm
                               font-medium
                               text-[#45464e]
                               mt-2">

                        Buku tidak ditemukan

                    </p>

                    <p class="text-xs text-[#75777e] mt-1">
                        Tidak ada buku tersedia yang cocok.
                    </p>

                </div>
            `;

                return;

            }

            list.innerHTML =
                result.data
                .map(function(buku) {

                    return `
                        <button
                            type="button"
                            onclick="pilihBuku(
                                '${rowId}',
                                ${Number(buku.id)},
                                '${escapeJs(buku.kode_buku)}',
                                '${escapeJs(buku.judul)}',
                                '${escapeJs(buku.penulis || '')}',
                                ${Number(buku.stok)}
                            )"
                            class="w-full
                                   text-left
                                   flex items-center gap-3
                                   px-4 py-3.5
                                   border-b border-[#c6c6ce]/20
                                   hover:bg-[#f8f9ff]
                                   transition">

                            <div
                                class="w-9 h-9 shrink-0
                                       rounded-lg
                                       bg-[#e5eeff]
                                       text-[#182442]
                                       flex items-center justify-center">

                                <span class="material-symbols-outlined text-[19px]">
                                    menu_book
                                </span>

                            </div>

                            <div class="min-w-0 flex-1">

                                <p
                                    class="text-sm
                                           font-medium
                                           text-[#0b1c30]
                                           truncate">

                                    ${escapeHtml(buku.judul)}

                                </p>

                                <p
                                    class="text-xs
                                           text-[#75777e]
                                           mt-0.5">

                                    ${escapeHtml(buku.kode_buku)}

                                    ${
                                        buku.penulis
                                            ? ' • ' +
                                              escapeHtml(buku.penulis)
                                            : ''
                                    }

                                </p>

                                <p
                                    class="text-xs
                                           text-[#75777e]
                                           mt-0.5">

                                    Stok:
                                    ${Number(buku.stok)}

                                </p>

                            </div>

                            <span
                                class="material-symbols-outlined
                                       text-[#75777e]
                                       text-[20px]">

                                chevron_right

                            </span>

                        </button>
                    `;

                })
                .join('');

        } catch (error) {

            list.innerHTML = `
            <div class="p-6 text-center">

                <span
                    class="material-symbols-outlined
                           text-red-500
                           text-[30px]">

                    error

                </span>

                <p
                    class="text-sm
                           font-medium
                           text-[#45464e]
                           mt-2">

                    Gagal memuat buku

                </p>

                <p class="text-xs text-[#75777e] mt-1">
                    ${escapeHtml(error.message)}
                </p>

            </div>
        `;

        }

    }


    function pilihBuku(
        rowId,
        id,
        kode,
        judul,
        penulis,
        stok
    ) {

        const item =
            document.querySelector(
                `[data-row="${rowId}"]`
            );

        if (!item) {
            return;
        }

        const duplicate =
            Array.from(
                document.querySelectorAll('.buku-input')
            )
            .some(function(input) {

                return (
                    input.closest('.buku-item') !== item &&
                    Number(input.value) === Number(id)
                );

            });

        if (duplicate) {

            alert('Buku tersebut sudah dipilih.');

            return;

        }

        item.querySelector('.buku-input').value = id;

        item.querySelector('.buku-selected').innerHTML =
            bukuCardHtml({
                    id: id,
                    kode_buku: kode,
                    judul: judul,
                    penulis: penulis,
                    stok: stok
                },
                rowId
            );

        item
            .querySelector('.buku-selected')
            .classList.remove('hidden');

        item
            .querySelector('.buku-empty')
            .classList.add('hidden');

        item
            .querySelector('.buku-panel')
            .classList.add('hidden');

        updateJumlahBuku();

    }


    function hapusBuku(rowId) {

        const item =
            document.querySelector(
                `[data-row="${rowId}"]`
            );

        if (!item) {
            return;
        }

        const total =
            document.querySelectorAll('.buku-item').length;

        if (total <= 1) {

            item.querySelector('.buku-input').value = '';

            item.querySelector('.buku-selected')
                .classList.add('hidden');

            item.querySelector('.buku-empty')
                .classList.remove('hidden');

            updateJumlahBuku();

            return;

        }

        item.remove();

        refreshBukuDeleteButtons();

        updateJumlahBuku();

    }


    function refreshBukuDeleteButtons() {

        const items =
            document.querySelectorAll('.buku-item');

        items.forEach(function(item) {

            const selected =
                item.querySelector('.buku-selected');

            if (!selected) {
                return;
            }

            const rowId =
                item.dataset.row;

            const input =
                item.querySelector('.buku-input');

            if (!input.value) {
                return;
            }

            /*
            | Render ulang card supaya tombol hapus
            | mengikuti jumlah item terbaru.
            */

            const currentCard =
                selected.querySelector('p');

            if (!currentCard) {
                return;
            }

        });

    }


    function updateJumlahBuku() {

        const inputs =
            document.querySelectorAll('.buku-input');

        let count = 0;

        inputs.forEach(function(input) {

            if (Number(input.value) > 0) {
                count++;
            }

        });

        document
            .getElementById('jumlahBuku')
            .textContent = count + ' / 5';

        const button =
            document.getElementById('tambahBukuBtn');

        if (inputs.length >= 5) {

            button.classList.add(
                'opacity-50',
                'cursor-not-allowed'
            );

            button.disabled = true;

        } else {

            button.classList.remove(
                'opacity-50',
                'cursor-not-allowed'
            );

            button.disabled = false;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Submit validation
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('formPeminjaman')
        .addEventListener('submit', function(event) {

            const anggotaId =
                Number(
                    document.getElementById('anggota_id').value
                );

            if (anggotaId <= 0) {

                event.preventDefault();

                alert('Silakan pilih anggota terlebih dahulu.');

                bukaPilihAnggota();

                return;

            }

            const bukuInputs =
                Array.from(
                    document.querySelectorAll('.buku-input')
                );

            const bukuIds =
                bukuInputs
                .map(function(input) {
                    return Number(input.value);
                })
                .filter(function(id) {
                    return id > 0;
                });

            if (bukuIds.length === 0) {

                event.preventDefault();

                alert('Silakan pilih minimal satu buku.');

                return;

            }

            if (bukuIds.length !== bukuInputs.length) {

                event.preventDefault();

                alert('Semua pilihan buku harus diisi.');

                return;

            }

            if (
                new Set(bukuIds).size !==
                bukuIds.length
            ) {

                event.preventDefault();

                alert('Tidak boleh memilih buku yang sama.');

                return;

            }

            const tanggalPinjam =
                document.getElementById(
                    'tanggal_pinjam'
                ).value;

            const tanggalJatuhTempo =
                document.getElementById(
                    'tanggal_jatuh_tempo'
                ).value;

            if (
                tanggalPinjam &&
                tanggalJatuhTempo &&
                tanggalJatuhTempo < tanggalPinjam
            ) {

                event.preventDefault();

                alert(
                    'Tanggal jatuh tempo tidak boleh sebelum tanggal pinjam.'
                );

            }

        });


    /*
    |--------------------------------------------------------------------------
    | Utility
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

    }


    function escapeJs(value) {

        return String(value ?? '')
            .replace(/\\/g, '\\\\')
            .replace(/'/g, "\\'")
            .replace(/"/g, '\\"')
            .replace(/\r/g, '\\r')
            .replace(/\n/g, '\\n');

    }
</script>


<?php require_once "../partials/footer.php"; ?>