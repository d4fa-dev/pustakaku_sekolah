<?php

require_once "../config/database.php";
require_once "../config/auth.php";

if (sudahLogin()) {
    header("Location: ../dashboard/index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {

        $error = "Username dan password wajib diisi.";

    } else {

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id, username, password, nama, role
             FROM users
             WHERE username = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && md5($password) === $user["password"]) {

            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["nama"] = $user["nama"];
            $_SESSION["role"] = $user["role"];

            header("Location: ../dashboard/index.php");
            exit;

        } else {

            $error = "Username atau password salah.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login - PustakaKu</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>

        body {
            font-family: 'Inter', sans-serif;
        }

    </style>

</head>

<body class="min-h-screen bg-[#f8f9ff] flex items-center justify-center p-5">

    <div class="w-full max-w-md">

        

        <div class="bg-white border border-[#c6c6ce]/30 rounded-2xl shadow-xl p-8">

            

            <div class="text-center mb-8">

                <h1 class="text-2xl font-bold text-[#182442]">
                    PustakaKu
                </h1>

                <p class="text-sm text-[#45464e] mt-1">
                    Sistem Manajemen Perpustakaan
                </p>

            </div>


            

            <?php if ($error): ?>

                <div class="mb-5 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>


            

            <form method="POST" class="space-y-5">

                

                <div>

                    <label class="block text-sm font-medium text-[#0b1c30] mb-2">

                        Username

                    </label>

                    <input
                        type="text"
                        name="username"
                        autocomplete="username"
                        placeholder="Masukkan username"
                        value="<?= htmlspecialchars($_POST["username"] ?? "") ?>"
                        class="w-full px-4 py-3 rounded-lg border border-[#c6c6ce] bg-[#f8f9ff] focus:outline-none focus:ring-2 focus:ring-[#182442]/20 focus:border-[#182442] transition"
                    >

                </div>


                

                <div>

                    <label class="block text-sm font-medium text-[#0b1c30] mb-2">

                        Password

                    </label>

                    <input
                        type="password"
                        name="password"
                        autocomplete="current-password"
                        placeholder="Masukkan password"
                        class="w-full px-4 py-3 rounded-lg border border-[#c6c6ce] bg-[#f8f9ff] focus:outline-none focus:ring-2 focus:ring-[#182442]/20 focus:border-[#182442] transition"
                    >

                </div>


                

                <div class="flex items-center justify-between text-sm">

                    <label class="flex items-center gap-2 text-[#45464e]">

                        <input
                            type="checkbox"
                            class="rounded border-[#c6c6ce] text-[#182442] focus:ring-[#182442]"
                        >

                        Ingat saya

                    </label>

                    <span class="text-[#182442]">
                        PustakaKu
                    </span>

                </div>


                

                <button
                    type="submit"
                    class="w-full py-3 rounded-lg bg-[#182442] hover:bg-[#2e3a59] text-white font-semibold transition duration-200 shadow-lg"
                >

                    Masuk ke Dashboard

                </button>

            </form>


            

            <div class="text-center mt-8">

                <p class="text-xs text-[#75777e]">

                    PustakaKu © <?= date("Y") ?>

                </p>

            </div>

        </div>

    </div>

</body>

</html>