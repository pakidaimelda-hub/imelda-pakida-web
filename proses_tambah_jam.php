<?php
include 'koneksi.php';
session_start();

$jam   = mysqli_real_escape_string($conn, $_POST['jam']);
$mulai = mysqli_real_escape_string($conn, $_POST['mulai']);
$akhir = mysqli_real_escape_string($conn, $_POST['akhir']);

$hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

// Cegah duplikat: kalau Jam Ke- ini sudah ada, tolak
$cek = mysqli_query($conn, "SELECT COUNT(*) as c FROM jam WHERE jam='$jam'");
$row = mysqli_fetch_assoc($cek);

if ($row['c'] > 0) {
    header("Location: jam.php?status=duplikat");
    exit;
}

// Insert satu baris untuk setiap hari kerja (Senin-Jumat) sekaligus
foreach ($hari_list as $hari) {
    mysqli_query($conn, "INSERT INTO jam (hari, jam, mulai, akhir) VALUES ('$hari','$jam','$mulai','$akhir')");
}

header("Location: jam.php?status=tambah");
exit;