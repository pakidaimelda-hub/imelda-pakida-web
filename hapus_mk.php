<?php
include 'koneksi.php';

if(isset($_GET['id'])) {
    $id  = (int) $_GET['id'];
    $sql = "DELETE FROM pelajaran WHERE id_pelajaran = $id";

    if(mysqli_query($conn, $sql)) {
        header("Location: mk.php?status=hapus");
    } else {
        header("Location: mk.php?status=error");
    }
    exit;
} else {
    header("Location: mk.php");
    exit;
}
?>