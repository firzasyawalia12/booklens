<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$id_buku = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_buku > 0) {
    $stmt = mysqli_prepare($koneksi, "DELETE FROM books WHERE id_buku = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id_buku);
    mysqli_stmt_execute($stmt);
}

header('Location: books.php?status=deleted');
exit();
?>
