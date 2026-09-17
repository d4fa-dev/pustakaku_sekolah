<?php

require_once "../config/auth.php";
require_once "../config/database.php";

wajibAdmin();

$user = userLogin();

$pageTitle = "Edit Pengguna";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, username, nama, role
     FROM users
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if ($nama === '' || $username === '' || $role === '') {

        $error = "Nama, username, dan role wajib diisi.";

    } elseif (!in_array($role, ['admin', 'petugas'])) {

        $error = "Role pengguna tidak valid.";

    } else {

        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id
             FROM users
             WHERE username = ?
             AND id != ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $cek,
            "si",
            $username,
            $id
        );

        mysqli_stmt_execute($cek);

        $hasilCek = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasilCek) > 0) {

            $error = "Username sudah digunakan oleh pengguna lain.";

        } else {

            if ($password !== '') {

                $passwordMD5 = md5($password);

                $update = mysqli_prepare(
                    $koneksi,
                    "UPDATE users
                     SET nama = ?,
                         username = ?,
                         password = ?,
                         role = ?
                     WHERE id = ?"
                );

                mysqli_stmt_bind_param(
                    $update,
                    "ssssi",
                    $nama,
                    $username,
                    $passwordMD5,
                    $role,
                    $id
                );

            } else {

                $update = mysqli_prepare(
                    $koneksi,
                    "UPDATE users
                     SET nama = ?,
                         username = ?,
                         role = ?
                     WHERE id = ?"
                );

                mysqli_stmt_bind_param(
                    $update,
                    "sssi",
                    $nama,
                    $username,
                    $role,
                    $id
                );
            }

            if (mysqli_stmt_execute($update)) {

                if ($id === (int) $user['id']) {

                    $_SESSION['username'] = $username;
                    $_SESSION['nama'] = $nama;
                    $_SESSION['role'] = $role;
                }

                header("Location: index.php?success=updated");
                exit;

            } else {

                $error = "Gagal memperbarui data pengguna.";
            }
        }
    }

    $data['nama'] = $nama;
    $data['username'] = $username;
    $data['role'] = $role;
}

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
                    class="inline-flex
                           items-center
                           gap-2
                           text-sm
                           text-[#75777e]
                           hover:text-[#182442]
                           transition">

                    <span class="material-symbols-outlined text-[20px]">
                        arrow_back
                    </span>

                    Kembali ke Pengguna

                </a>

                <h1
                    class="text-2xl
                           font-semibold
                           text-[#0b1c30]
                           mt-4">

                    Edit Pengguna

                </h1>

                <p
                    class="text-sm
                           text-[#75777e]
                           mt-1">

                    Perbarui informasi akun pengguna.

                </p>

            </div>


            <?php if ($error !== ''): ?>

                <div
                    class="flex
                           items-start
                           gap-3
                           p-4
                           rounded-xl
                           bg-red-50
                           border border-red-200
                           text-red-700">

                    <span class="material-symbols-outlined">
                        error
                    </span>

                    <div>

                        <p class="text-sm font-semibold">
                            Gagal
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
                       p-5 md:p-7">

                <form method="POST">

                    <div class="space-y-5">

                        <div>

                            <label
                                class="block
                                       text-sm
                                       font-medium
                                       text-[#0b1c30]
                                       mb-2">

                                Nama Lengkap

                            </label>

                            <input
                                type="text"
                                name="nama"
                                value="<?= htmlspecialchars(
                                    $data['nama']
                                ) ?>"
                                placeholder="Masukkan nama lengkap"
                                required
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
                                       focus:border-[#182442]">

                        </div>


                        <div>

                            <label
                                class="block
                                       text-sm
                                       font-medium
                                       text-[#0b1c30]
                                       mb-2">

                                Username

                            </label>

                            <input
                                type="text"
                                name="username"
                                value="<?= htmlspecialchars(
                                    $data['username']
                                ) ?>"
                                placeholder="Masukkan username"
                                required
                                autocomplete="off"
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
                                       focus:border-[#182442]">

                        </div>


                        <div>

                            <label
                                class="block
                                       text-sm
                                       font-medium
                                       text-[#0b1c30]
                                       mb-2">

                                Password Baru

                            </label>

                            <div class="relative">

                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    placeholder="Kosongkan jika tidak ingin mengubah"
                                    autocomplete="new-password"
                                    class="w-full
                                           px-4
                                           py-3
                                           pr-12
                                           rounded-lg
                                           border border-[#c6c6ce]
                                           bg-[#f8f9ff]
                                           text-sm
                                           focus:outline-none
                                           focus:ring-2
                                           focus:ring-[#182442]/20
                                           focus:border-[#182442]">

                                <button
                                    type="button"
                                    onclick="togglePassword()"
                                    class="absolute
                                           right-3
                                           top-1/2
                                           -translate-y-1/2
                                           text-[#75777e]
                                           hover:text-[#182442]">

                                    <span
                                        id="passwordIcon"
                                        class="material-symbols-outlined">

                                        visibility

                                    </span>

                                </button>

                            </div>

                            <p
                                class="text-xs
                                       text-[#75777e]
                                       mt-2">

                                Biarkan kosong jika password tidak ingin
                                diubah.

                            </p>

                        </div>


                        <div>

                            <label
                                class="block
                                       text-sm
                                       font-medium
                                       text-[#0b1c30]
                                       mb-2">

                                Role

                            </label>

                            <select
                                name="role"
                                required
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
                                       focus:border-[#182442]">

                                <option
                                    value="admin"
                                    <?= $data['role'] === 'admin'
                                        ? 'selected'
                                        : '' ?>>

                                    Admin

                                </option>

                                <option
                                    value="petugas"
                                    <?= $data['role'] === 'petugas'
                                        ? 'selected'
                                        : '' ?>>

                                    Petugas

                                </option>

                            </select>

                        </div>


                        <div
                            class="flex
                                   flex-col-reverse
                                   sm:flex-row
                                   sm:justify-end
                                   gap-3
                                   pt-4
                                   border-t
                                   border-[#c6c6ce]/20">

                            <a
                                href="index.php"
                                class="inline-flex
                                       items-center
                                       justify-center
                                       px-5
                                       py-2.5
                                       rounded-lg
                                       border border-[#c6c6ce]
                                       text-[#45464e]
                                       text-sm
                                       font-medium
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
                                       px-5
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
                                           text-[19px]">

                                    save

                                </span>

                                Simpan Perubahan

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

</main>


<script>

function togglePassword()
{
    const password = document.getElementById('password');
    const icon = document.getElementById('passwordIcon');

    if (password.type === 'password') {

        password.type = 'text';
        icon.textContent = 'visibility_off';

    } else {

        password.type = 'password';
        icon.textContent = 'visibility';

    }
}

</script>

<?php require_once "../partials/footer.php"; ?>