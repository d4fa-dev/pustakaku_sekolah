<?php

require_once "../config/auth.php";
require_once "../config/database.php";

wajibAdmin();

$user = userLogin();

$pageTitle = "Pengguna";

$search = trim($_GET['search'] ?? '');

if ($search !== '') {

    $keyword = "%" . $search . "%";

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id, username, nama, role
         FROM users
         WHERE username LIKE ?
            OR nama LIKE ?
            OR role LIKE ?
         ORDER BY id DESC"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $keyword,
        $keyword,
        $keyword
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
} else {

    $result = mysqli_query(
        $koneksi,
        "SELECT id, username, nama, role
         FROM users
         ORDER BY id DESC"
    );
}

$totalUser = 0;

$countQuery = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM users"
);

if ($countQuery) {

    $countData = mysqli_fetch_assoc($countQuery);

    $totalUser = (int) ($countData['total'] ?? 0);
}

?>

<?php require_once "../partials/header.php"; ?>
<?php require_once "../partials/sidebar.php"; ?>

<main class="flex-1 min-w-0">

    <?php require_once "../partials/navbar.php"; ?>

    <div class="p-5 md:p-8 overflow-y-auto">

        <div class="max-w-7xl mx-auto space-y-6">

            <?php if (isset($_GET['success'])): ?>

                <div
                    class="flex items-start gap-3
                           p-4 rounded-xl
                           bg-green-50
                           border border-green-200
                           text-green-700">

                    <span class="material-symbols-outlined">
                        check_circle
                    </span>

                    <div>

                        <p class="text-sm font-semibold">
                            Berhasil
                        </p>

                        <p class="text-sm mt-1">

                            <?php if ($_GET['success'] === 'added'): ?>

                                Pengguna berhasil ditambahkan.

                            <?php elseif ($_GET['success'] === 'updated'): ?>

                                Data pengguna berhasil diperbarui.

                            <?php elseif ($_GET['success'] === 'deleted'): ?>

                                Pengguna berhasil dihapus.

                            <?php endif; ?>

                        </p>

                    </div>

                </div>

            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>

                <?php if ($_GET['error'] === 'self_delete'): ?>

                    <div
                        class="flex items-start gap-3
                   p-4 rounded-xl
                   bg-red-50
                   border border-red-200
                   text-red-700">

                        <span class="material-symbols-outlined">
                            block
                        </span>

                        <div>

                            <p class="text-sm font-semibold">
                                Tidak dapat menghapus akun
                            </p>

                            <p class="text-sm mt-1">
                                Kamu tidak dapat menghapus akun yang sedang digunakan.
                            </p>

                        </div>

                    </div>

                <?php elseif ($_GET['error'] === 'not_found'): ?>

                    <div
                        class="flex items-start gap-3
                   p-4 rounded-xl
                   bg-red-50
                   border border-red-200
                   text-red-700">

                        <span class="material-symbols-outlined">
                            error
                        </span>

                        <div>

                            <p class="text-sm font-semibold">
                                Pengguna tidak ditemukan
                            </p>

                            <p class="text-sm mt-1">
                                Data pengguna yang ingin dihapus tidak ditemukan.
                            </p>

                        </div>

                    </div>

                <?php elseif ($_GET['error'] === 'delete_failed'): ?>

                    <div
                        class="flex items-start gap-3
                   p-4 rounded-xl
                   bg-red-50
                   border border-red-200
                   text-red-700">

                        <span class="material-symbols-outlined">
                            error
                        </span>

                        <div>

                            <p class="text-sm font-semibold">
                                Gagal menghapus pengguna
                            </p>

                            <p class="text-sm mt-1">
                                Terjadi kesalahan saat menghapus data pengguna.
                            </p>

                        </div>

                    </div>

                <?php endif; ?>

            <?php endif; ?>


            <div
                class="flex flex-col
                       sm:flex-row
                       sm:items-center
                       sm:justify-between
                       gap-4">

                <div>

                    <h1
                        class="text-2xl
                               font-semibold
                               text-[#0b1c30]">

                        Pengguna

                    </h1>

                    <p
                        class="text-sm
                               text-[#75777e]
                               mt-1">

                        Kelola akun pengguna sistem PustakaKu.

                    </p>

                </div>


                <a
                    href="tambah.php"
                    class="inline-flex
                           items-center
                           justify-center
                           gap-2
                           px-4
                           py-2.5
                           rounded-lg
                           bg-[#182442]
                           text-white
                           text-sm
                           font-medium
                           hover:bg-[#2e3a59]
                           transition
                           shadow-sm">

                    <span
                        class="material-symbols-outlined
                               text-[20px]">

                        person_add

                    </span>

                    Tambah Pengguna

                </a>

            </div>


            <div
                class="grid
                       grid-cols-1
                       sm:grid-cols-2
                       gap-4">

                <div
                    class="bg-white
                           rounded-xl
                           border border-[#c6c6ce]/20
                           shadow-sm
                           p-5">

                    <div
                        class="flex
                               items-center
                               justify-between">

                        <div>

                            <p
                                class="text-sm
                                       text-[#75777e]">

                                Total Pengguna

                            </p>

                            <p
                                class="text-2xl
                                       font-semibold
                                       text-[#0b1c30]
                                       mt-2">

                                <?= number_format($totalUser) ?>

                            </p>

                        </div>

                        <div
                            class="w-11 h-11
                                   rounded-xl
                                   bg-[#e5eeff]
                                   text-[#182442]
                                   flex
                                   items-center
                                   justify-center">

                            <span class="material-symbols-outlined">
                                manage_accounts
                            </span>

                        </div>

                    </div>

                </div>


                <div
                    class="bg-white
                           rounded-xl
                           border border-[#c6c6ce]/20
                           shadow-sm
                           p-5">

                    <div
                        class="flex
                               items-center
                               justify-between">

                        <div>

                            <p
                                class="text-sm
                                       text-[#75777e]">

                                Administrator

                            </p>

                            <?php

                            $adminQuery = mysqli_query(
                                $koneksi,
                                "SELECT COUNT(*) AS total
                                 FROM users
                                 WHERE role = 'admin'"
                            );

                            $adminData = mysqli_fetch_assoc(
                                $adminQuery
                            );

                            $totalAdmin =
                                (int) (
                                    $adminData['total'] ?? 0
                                );

                            ?>

                            <p
                                class="text-2xl
                                       font-semibold
                                       text-[#0b1c30]
                                       mt-2">

                                <?= number_format($totalAdmin) ?>

                            </p>

                        </div>

                        <div
                            class="w-11 h-11
                                   rounded-xl
                                   bg-green-50
                                   text-green-600
                                   flex
                                   items-center
                                   justify-center">

                            <span class="material-symbols-outlined">
                                admin_panel_settings
                            </span>

                        </div>

                    </div>

                </div>

            </div>


            <div
                class="bg-white
                       rounded-xl
                       border border-[#c6c6ce]/20
                       shadow-sm
                       p-4">

                <form method="GET">

                    <div class="relative">

                        <span
                            class="material-symbols-outlined
                                   absolute
                                   left-3
                                   top-1/2
                                   -translate-y-1/2
                                   text-[#75777e]">

                            search

                        </span>

                        <input
                            type="text"
                            name="search"
                            value="<?= htmlspecialchars(
                                        $_GET['search'] ?? ''
                                    ) ?>"
                            placeholder="Cari username, nama, atau role..."
                            class="w-full
                                   pl-11
                                   pr-4
                                   py-3
                                   rounded-lg
                                   border border-[#c6c6ce]
                                   bg-[#f8f9ff]
                                   text-sm
                                   focus:outline-none
                                   focus:ring-2
                                   focus:ring-[#182442]/20
                                   focus:border-[#182442]">

                    </div>

                </form>

            </div>


            <div
                class="bg-white
                       rounded-xl
                       border border-[#c6c6ce]/20
                       shadow-sm
                       overflow-hidden">

                <div class="overflow-x-auto">

                    <table class="w-full text-left">

                        <thead>

                            <tr
                                class="bg-[#f8f9ff]
                                       border-b
                                       border-[#c6c6ce]/20
                                       text-xs
                                       text-[#75777e]">

                                <th class="px-5 py-4 font-medium">
                                    No
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Pengguna
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Username
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    Role
                                </th>

                                <th
                                    class="px-5 py-4
                                           font-medium
                                           text-right">

                                    Aksi

                                </th>

                            </tr>

                        </thead>


                        <tbody class="text-sm">

                            <?php

                            $no = 1;

                            if (
                                $result &&
                                mysqli_num_rows($result) > 0
                            ):

                                while (
                                    $data =
                                    mysqli_fetch_assoc($result)
                                ):

                            ?>

                                    <tr
                                        class="border-b
                                           border-[#c6c6ce]/10
                                           hover:bg-[#f8f9ff]
                                           transition">

                                        <td
                                            class="px-5 py-4
                                               text-[#75777e]">

                                            <?= $no++ ?>

                                        </td>


                                        <td class="px-5 py-4">

                                            <div
                                                class="flex
                                                   items-center
                                                   gap-3">

                                                <div
                                                    class="w-10 h-10
                                                       rounded-full
                                                       bg-[#e5eeff]
                                                       text-[#182442]
                                                       flex
                                                       items-center
                                                       justify-center
                                                       font-semibold">

                                                    <?= strtoupper(
                                                        substr(
                                                            $data['nama'] ??
                                                                'U',
                                                            0,
                                                            1
                                                        )
                                                    ) ?>

                                                </div>

                                                <div>

                                                    <p
                                                        class="font-medium
                                                           text-[#0b1c30]">

                                                        <?= htmlspecialchars(
                                                            $data['nama']
                                                        ) ?>

                                                    </p>

                                                    <p
                                                        class="text-xs
                                                           text-[#75777e]
                                                           mt-1">

                                                        ID #<?= $data['id'] ?>

                                                    </p>

                                                </div>

                                            </div>

                                        </td>


                                        <td
                                            class="px-5 py-4
                                               text-[#45464e]">

                                            <?= htmlspecialchars(
                                                $data['username']
                                            ) ?>

                                        </td>


                                        <td class="px-5 py-4">

                                            <?php if (
                                                $data['role'] === 'admin'
                                            ): ?>

                                                <span
                                                    class="inline-flex
                                                       items-center
                                                       gap-1.5
                                                       px-2.5
                                                       py-1
                                                       rounded-full
                                                       bg-purple-50
                                                       text-purple-600
                                                       text-xs
                                                       font-medium">

                                                    <span
                                                        class="material-symbols-outlined
                                                           text-[15px]">

                                                        admin_panel_settings

                                                    </span>

                                                    Admin

                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="inline-flex
                                                       items-center
                                                       gap-1.5
                                                       px-2.5
                                                       py-1
                                                       rounded-full
                                                       bg-blue-50
                                                       text-blue-600
                                                       text-xs
                                                       font-medium">

                                                    <span
                                                        class="material-symbols-outlined
                                                           text-[15px]">

                                                        person

                                                    </span>

                                                    Petugas

                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <td class="px-5 py-4">

                                            <div
                                                class="flex
                                                   justify-end
                                                   gap-2">

                                                <a
                                                    href="edit.php?id=<?= $data['id'] ?>"
                                                    class="w-9 h-9
                                                       rounded-lg
                                                       bg-[#e5eeff]
                                                       text-[#182442]
                                                       flex
                                                       items-center
                                                       justify-center
                                                       hover:bg-[#d3e4fe]
                                                       transition"
                                                    title="Edit">

                                                    <span
                                                        class="material-symbols-outlined
                                                           text-[18px]">

                                                        edit

                                                    </span>

                                                </a>


                                                <?php if (
                                                    $data['id'] !=
                                                    ($user['id'] ?? null)
                                                ): ?>

                                                    <a
                                                        href="hapus.php?id=<?= $data['id'] ?>"
                                                        onclick="return confirm('Yakin ingin menghapus pengguna ini?')"
                                                        class="w-9 h-9
                                                           rounded-lg
                                                           bg-red-50
                                                           text-red-600
                                                           flex
                                                           items-center
                                                           justify-center
                                                           hover:bg-red-100
                                                           transition"
                                                        title="Hapus">

                                                        <span
                                                            class="material-symbols-outlined
                                                               text-[18px]">

                                                            delete

                                                        </span>

                                                    </a>

                                                <?php endif; ?>

                                            </div>

                                        </td>

                                    </tr>

                                <?php

                                endwhile;

                            else:

                                ?>

                                <tr>

                                    <td
                                        colspan="5"
                                        class="px-5 py-12
                                               text-center">

                                        <div
                                            class="w-14 h-14
                                                   mx-auto
                                                   rounded-xl
                                                   bg-[#e5eeff]
                                                   text-[#182442]
                                                   flex
                                                   items-center
                                                   justify-center">

                                            <span
                                                class="material-symbols-outlined
                                                       text-2xl">

                                                manage_accounts

                                            </span>

                                        </div>

                                        <p
                                            class="font-medium
                                                   text-[#0b1c30]
                                                   mt-4">

                                            Pengguna tidak ditemukan

                                        </p>

                                        <p
                                            class="text-sm
                                                   text-[#75777e]
                                                   mt-1">

                                            Belum ada data pengguna
                                            yang tersedia.

                                        </p>

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</main>

<?php require_once "../partials/footer.php"; ?>