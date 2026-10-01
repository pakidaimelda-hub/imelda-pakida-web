<?php
include 'koneksi.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tahun    = mysqli_real_escape_string($conn, $_POST['tahun']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $status   = (int) $_POST['status'];

    // Kalau status aktif, nonaktifkan semua dulu
    if($status == 1) {
        mysqli_query($conn, "UPDATE semester SET status='0'");
    }

    $sql = "INSERT INTO semester (tahun, semester, status) VALUES ('$tahun', '$semester', '$status')";

    if(mysqli_query($conn, $sql)) {
        header("Location: semester.php?status=tambah");
    } else {
        header("Location: semester.php?status=error");
    }
    exit;
}
?>