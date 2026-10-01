<?php
include 'koneksi.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pelajaran = mysqli_real_escape_string($conn, $_POST['pelajaran']);
    $sql = "INSERT INTO pelajaran (pelajaran) VALUES ('$pelajaran')";

    if(mysqli_query($conn, $sql)) {
        header("Location: mk.php?status=tambah");
    } else {
        header("Location: mk.php?status=error");
    }
    exit;
}
?>