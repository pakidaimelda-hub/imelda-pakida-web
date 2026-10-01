<?php
include 'koneksi.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_ruang = mysqli_real_escape_string($conn, $_POST['nama_ruang']);
    $kapasitas  = (int) $_POST['kapasitas'];

    $sql = "INSERT INTO ruang_kelas (nama_ruang, kapasitas) VALUES ('$nama_ruang', $kapasitas)";

    if(mysqli_query($conn, $sql)) {
        header("Location: ruang.php?status=tambah");
    } else {
        header("Location: ruang.php?status=error");
    }
    exit;
}
?>