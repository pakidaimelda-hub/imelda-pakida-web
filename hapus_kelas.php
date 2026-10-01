<?php
include 'koneksi.php';

if(isset($_GET['id'])) {
    $id  = (int) $_GET['id'];
    $sql = "DELETE FROM kelas WHERE id_kelas = $id";

    if(mysqli_query($conn, $sql)) {
        header("Location: kelas.php?status=hapus");
    } else {
        header("Location: kelas.php?status=error");
    }
    exit;
} else {
    header("Location: kelas.php");
    exit;
}
?>