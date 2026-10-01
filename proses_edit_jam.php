<?php
include 'koneksi.php';
session_start();

$jam_lama = mysqli_real_escape_string($conn, $_POST['jam_lama']);
$jam_baru = mysqli_real_escape_string($conn, $_POST['jam']);
$mulai    = mysqli_real_escape_string($conn, $_POST['mulai']);
$akhir    = mysqli_real_escape_string($conn, $_POST['akhir']);

// Kalau Jam Ke- diubah ke angka lain yang sudah dipakai jam lain, tolak
if ($jam_baru != $jam_lama) {
    $cek = mysqli_query($conn, "SELECT COUNT(*) as c FROM jam WHERE jam='$jam_baru'");
    $row = mysqli_fetch_assoc($cek);
    if ($row['c'] > 0) {
        header("Location: jam.php?status=duplikat");
        exit;
    }
}

// Update semua baris (semua hari) yang punya nomor jam lama ini sekaligus
mysqli_query($conn, "UPDATE jam SET jam='$jam_baru', mulai='$mulai', akhir='$akhir' WHERE jam='$jam_lama'");

header("Location: jam.php?status=edit");
exit;