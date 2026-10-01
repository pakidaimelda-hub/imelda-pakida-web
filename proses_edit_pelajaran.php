<?php
include 'koneksi.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id        = (int) $_POST['id'];
    $pelajaran = mysqli_real_escape_string($conn, $_POST['pelajaran']);
    $sql = "UPDATE pelajaran SET pelajaran='$pelajaran' WHERE id_pelajaran=$id";

    if(mysqli_query($conn, $sql)) {
        header("Location: mk.php?status=edit");
    } else {
        header("Location: mk.php?status=error");
    }
    exit;
}
?>