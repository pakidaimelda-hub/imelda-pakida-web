<?php
include 'koneksi.php';

if(isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $sql = "DELETE FROM guru WHERE id_guru = $id";

    if(mysqli_query($conn, $sql)) {
        header("Location: guru.php?status=hapus");
    } else {
        header("Location: guru.php?status=error");
    }
    exit;
} else {
    header("Location: guru.php");
    exit;
}
?>3