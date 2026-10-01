<?php
include 'koneksi.php';

if(isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Nonaktifkan semua dulu
    mysqli_query($conn, "UPDATE semester SET status='0'");

    // Aktifkan yang dipilih
    $sql = "UPDATE semester SET status='1' WHERE id_semester=$id";

    if(mysqli_query($conn, $sql)) {
        header("Location: semester.php?status=aktif");
    } else {
        header("Location: semester.php?status=error");
    }
    exit;
} else {
    header("Location: semester.php");
    exit;
}
?>