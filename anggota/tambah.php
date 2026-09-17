<?php

require_once "../config/auth.php";
require_once "../config/database.php";

if (isset($_POST['simpan'])) {

    $nisn           = trim($_POST['nisn'] ?? '');
    $nama           = trim($_POST['nama'] ?? '');
    $jenis_kelamin  = $_POST['jenis_kelamin'] ?? '';
    $kelas          = trim($_POST['kelas'] ?? '');
    $no_hp          = trim($_POST['no_hp'] ?? '');
    $tanggal_daftar = $_POST['tanggal_daftar'] ?? '';
    $status         = $_POST['status'] ?? 'Aktif';

    if (
        $nisn === '' ||
        $nama === '' ||
        $jenis_kelamin === '' ||
        $kelas === '' ||
        $tanggal_daftar === ''
    ) {

        $error = "Data wajib belum lengkap.";
    } elseif (!in_array($jenis_kelamin, ['L', 'P'])) {

        $error = "Jenis kelamin tidak valid.";
    } elseif (!in_array($status, ['Aktif', 'Tidak Aktif'])) {

        $error = "Status anggota tidak valid.";
    } else {

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM anggota WHERE nisn = ? LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $nisn
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {

            $error = "NISN sudah digunakan.";
        } else {

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO anggota
                (
                    nisn,
                    nama,
                    jenis_kelamin,
                    kelas,
                    no_hp,
                    created_at,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sssssss",
                $nisn,
                $nama,
                $jenis_kelamin,
                $kelas,
                $no_hp,
                $tanggal_daftar,
                $status
            );

            if (mysqli_stmt_execute($stmt)) {

                header("Location: index.php?success=added");
                exit;
            } else {

                $error = "Gagal menambahkan anggota.";
            }
        }
    }
}

$pageTitle = "Tambah Anggota";

require_once "../partials/header.php";
require_once "../partials/sidebar.php";

?>

<main class="flex-1 min-w-0">

    <?php require_once "../partials/navbar.php"; ?>

    <div class="p-5 md:p-8 overflow-y-auto">

        <div class="max-w-3xl mx-auto space-y-6">

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                <div>

                    <h1 class="text-2xl font-semibold text-[#0b1c30]">
                        Tambah Anggota
                    </h1>

                    <p class="text-sm text-[#75777e] mt-1">
                        Tambahkan anggota baru ke perpustakaan.
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

            <?php if (isset($error)): ?>

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
                            Gagal menyimpan data
                        </p>

                        <p class="text-sm mt-1">
                            <?= htmlspecialchars($error) ?>
                        </p>

                    </div>

                </div>

            <?php endif; ?>

            <div
                class="bg-white
                       rounded-xl
                       border border-[#c6c6ce]/20
                       shadow-sm
                       overflow-hidden">

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
                                person_add
                            </span>

                        </div>

                        <div>

                            <h2 class="font-semibold text-[#0b1c30]">
                                Informasi Anggota
                            </h2>

                            <p class="text-xs text-[#75777e] mt-0.5">
                                Isi data anggota dengan lengkap.
                            </p>

                        </div>

                    </div>

                </div>

                <form method="POST" class="p-6 space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>

                            <label
                                for="nisn"
                                class="block text-sm font-medium text-[#0b1c30] mb-2">

                                NISN
                                <span class="text-red-500">*</span>

                            </label>

                            <input
                                type="text"
                                id="nisn"
                                name="nisn"
                                required
                                maxlength="20"
                                value="<?= htmlspecialchars($_POST['nisn'] ?? '') ?>"
                                placeholder="Masukkan NISN"
                                class="w-full px-4 py-3 rounded-lg
                                       border border-[#c6c6ce]
                                       bg-[#f8f9ff]
                                       text-sm text-[#0b1c30]
                                       placeholder-[#75777e]
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-[#182442]/20
                                       focus:border-[#182442]
                                       transition">

                            <p class="text-xs text-[#75777e] mt-1.5">
                                NISN harus unik.
                            </p>

                        </div>

                        <div>

                            <label
                                for="nama"
                                class="block text-sm font-medium text-[#0b1c30] mb-2">

                                Nama Lengkap
                                <span class="text-red-500">*</span>

                            </label>

                            <input
                                type="text"
                                id="nama"
                                name="nama"
                                required
                                maxlength="100"
                                value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                                placeholder="Masukkan nama lengkap"
                                class="w-full px-4 py-3 rounded-lg
                                       border border-[#c6c6ce]
                                       bg-[#f8f9ff]
                                       text-sm text-[#0b1c30]
                                       placeholder-[#75777e]
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-[#182442]/20
                                       focus:border-[#182442]
                                       transition">

                        </div>

                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>

                            <label
                                for="jenis_kelamin"
                                class="block text-sm font-medium text-[#0b1c30] mb-2">

                                Jenis Kelamin
                                <span class="text-red-500">*</span>

                            </label>

                            <select
                                id="jenis_kelamin"
                                name="jenis_kelamin"
                                required
                                class="w-full px-4 py-3 rounded-lg
                                       border border-[#c6c6ce]
                                       bg-[#f8f9ff]
                                       text-sm text-[#0b1c30]
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-[#182442]/20
                                       focus:border-[#182442]
                                       transition">

                                <option value="">
                                    Pilih jenis kelamin
                                </option>

                                <option
                                    value="L"
                                    <?= ($_POST['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>
                                    Laki-laki
                                </option>

                                <option
                                    value="P"
                                    <?= ($_POST['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>
                                    Perempuan
                                </option>

                            </select>

                        </div>

                        <div>

                            <label
                                for="kelas"
                                class="block text-sm font-medium text-[#0b1c30] mb-2">

                                Kelas
                                <span class="text-red-500">*</span>

                            </label>

                            <input
                                type="text"
                                id="kelas"
                                name="kelas"
                                required
                                maxlength="50"
                                value="<?= htmlspecialchars($_POST['kelas'] ?? '') ?>"
                                placeholder="Contoh: 12 RPL 2"
                                class="w-full px-4 py-3 rounded-lg
                                       border border-[#c6c6ce]
                                       bg-[#f8f9ff]
                                       text-sm text-[#0b1c30]
                                       placeholder-[#75777e]
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-[#182442]/20
                                       focus:border-[#182442]
                                       transition">

                        </div>

                    </div>

                    <div>

                        <label
                            for="no_hp"
                            class="block text-sm font-medium text-[#0b1c30] mb-2">

                            Nomor HP

                        </label>

                        <input
                            type="text"
                            id="no_hp"
                            name="no_hp"
                            maxlength="20"
                            value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>"
                            placeholder="Contoh: 081234567890"
                            class="w-full px-4 py-3 rounded-lg
                                   border border-[#c6c6ce]
                                   bg-[#f8f9ff]
                                   text-sm text-[#0b1c30]
                                   placeholder-[#75777e]
                                   focus:outline-none
                                   focus:ring-2
                                   focus:ring-[#182442]/20
                                   focus:border-[#182442]
                                   transition">

                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>

                            <label
                                for="tanggal_daftar"
                                class="block text-sm font-medium text-[#0b1c30] mb-2">

                                Tanggal Daftar
                                <span class="text-red-500">*</span>

                            </label>

                            <input
                                type="date"
                                id="tanggal_daftar"
                                name="tanggal_daftar"
                                required
                                value="<?= htmlspecialchars($_POST['tanggal_daftar'] ?? date('Y-m-d')) ?>"
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
                                for="status"
                                class="block text-sm font-medium text-[#0b1c30] mb-2">

                                Status

                            </label>

                            <select
                                id="status"
                                name="status"
                                class="w-full px-4 py-3 rounded-lg
                                       border border-[#c6c6ce]
                                       bg-[#f8f9ff]
                                       text-sm text-[#0b1c30]
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-[#182442]/20
                                       focus:border-[#182442]
                                       transition">

                                <option
                                    value="Aktif"
                                    <?= ($_POST['status'] ?? 'Aktif') === 'Aktif' ? 'selected' : '' ?>>
                                    Aktif
                                </option>

                                <option
                                    value="Tidak Aktif"
                                    <?= ($_POST['status'] ?? '') === 'Tidak Aktif' ? 'selected' : '' ?>>
                                    Tidak Aktif
                                </option>

                            </select>

                        </div>

                    </div>

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
                            name="simpan"
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

                            Simpan Anggota

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</main>

<?php require_once "../partials/footer.php"; ?>