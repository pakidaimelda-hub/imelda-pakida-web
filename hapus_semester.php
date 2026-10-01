<?php
include 'koneksi.php';

if(isset($_GET['id'])) {
    $id  = (int) $_GET['id'];
    $sql = "DELETE FROM semester WHERE id_semester=$id";

    if(mysqli_query($conn, $sql)) {
        header("Location: semester.php?status=hapus");
    } else {
        header("Location: semester.php?status=error");
    }
    exit;
} else {
    header("Location: semester.php");
    exit;
}
?>