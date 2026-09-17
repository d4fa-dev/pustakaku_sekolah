<?php
require_once "../config/auth.php";
require_once "../config/database.php";

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');

try {
    if ($q === '') {
        $sql = "SELECT id, nisn, nama, kelas
                FROM anggota
                WHERE status = 'Aktif'
                ORDER BY nama ASC
                LIMIT 20";

        $result = mysqli_query($koneksi, $sql);
    } else {
        $sql = "SELECT id, nisn, nama, kelas
                FROM anggota
                WHERE status = 'Aktif'
                AND (nama LIKE ? OR nisn LIKE ?)
                ORDER BY nama ASC
                LIMIT 20";

        $stmt = mysqli_prepare($koneksi, $sql);
        $like = "%{$q}%";

        mysqli_stmt_bind_param($stmt, "ss", $like, $like);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
    }

    $data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil data anggota.',
        'data' => []
    ]);
}
