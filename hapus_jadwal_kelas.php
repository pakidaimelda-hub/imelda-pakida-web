<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
include 'koneksi.php';

if (isset($_GET['id_kelas'])) {
    $id_kelas = (int)$_GET['id_kelas'];
    $sql = "DELETE FROM jadwal WHERE id_kelas = $id_kelas";

    if (mysqli_query($conn, $sql)) {
        header("Location: jadwal.php?status=hapus");
    } else {
        header("Location: jadwal.php?status=error");
    }
    exit;
} else {
    header("Location: jadwal.php");
    exit;
}
?>