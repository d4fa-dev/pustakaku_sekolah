<?php

require_once __DIR__ . '/../config/auth.php';

wajibLogin();

$user = userLogin();

$pageTitle = $pageTitle ?? 'PustakaKu';

?>

<!DOCTYPE html>
<html lang="id">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($pageTitle) ?> - PustakaKu
    </title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"
        rel="stylesheet"
    >

    <style>

        body {
            font-family: 'Inter', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c6c6ce;
            border-radius: 999px;
        }

    </style>

</head>

<body class="bg-[#f8f9ff] text-[#0b1c30] antialiased">

<div class="min-h-screen flex">