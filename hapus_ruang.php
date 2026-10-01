<?php
include 'koneksi.php';

if(isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $sql = "DELETE FROM ruang_kelas WHERE id_ruang = $id";

    if(mysqli_query($conn, $sql)) {
        header("Location: ruang.php?status=hapus");
    } else {
        header("Location: ruang.php?status=error");
    }
    exit;
} else {
    header("Location: ruang.php");
    exit;
}
?>