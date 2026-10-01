<?php
include 'koneksi.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $jk   = mysqli_real_escape_string($conn, $_POST['jk']);
    $nip  = mysqli_real_escape_string($conn, $_POST['nip']);

    $sql = "INSERT INTO guru (nama, jk, nip) VALUES ('$nama', '$jk', '$nip')";

    if(mysqli_query($conn, $sql)) {
        header("Location: guru.php?status=tambah");
    } else {
        header("Location: guru.php?status=error");
    }
    exit;
}
?>