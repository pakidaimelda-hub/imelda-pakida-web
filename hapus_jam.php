<?php
include 'koneksi.php';
session_start();

$jam = mysqli_real_escape_string($conn, $_GET['jam']);

// Hapus semua baris (semua hari) untuk nomor jam ini sekaligus
mysqli_query($conn, "DELETE FROM jam WHERE jam='$jam'");

header("Location: jam.php?status=hapus");
exit;