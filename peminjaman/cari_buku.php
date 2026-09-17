<?php
require_once "../config/auth.php";
require_once "../config/database.php";

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
$exclude = array_values(
    array_filter(
        array_map('intval', explode(',', $_GET['exclude'] ?? ''))
    )
);

try {
    $where = "stok > 0";
    $params = [];
    $types = '';

    if ($q !== '') {
        $where .= " AND (judul LIKE ? OR kode_buku LIKE ? OR penulis LIKE ?)";
        $like = "%{$q}%";

        $params = [$like, $like, $like];
        $types = 'sss';
    }

    if ($exclude) {
        $where .= " AND id NOT IN (" . implode(',', $exclude) . ")";
    }

    $sql = "SELECT id, kode_buku, judul, penulis, stok
            FROM buku
            WHERE {$where}
            ORDER BY judul ASC
            LIMIT 20";

    if ($params) {
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($koneksi, $sql);
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
        'message' => 'Gagal mengambil data buku.',
        'data' => []
    ]);
}
