<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function sudahLogin()
{
    return isset($_SESSION['user_id']);
}

function wajibLogin()
{
    if (!sudahLogin()) {
        header("Location: ../auth/login.php");
        exit;
    }
}

function userLogin()
{
    return [
        'id'       => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'nama'     => $_SESSION['nama'] ?? null,
        'role'     => $_SESSION['role'] ?? null
    ];
}

function wajibAdmin()
{
    wajibLogin();

    if (
        !isset($_SESSION['role']) ||
        $_SESSION['role'] !== 'admin'
    ) {
        header("Location: ../dashboard/index.php?error=forbidden");
        exit;
    }
}