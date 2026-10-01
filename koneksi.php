<?php
$host   = "localhost";
$user   = "root";
$pass   = "";           // XAMPP default: kosong
$db     = "jadwal";     // nama database Anda

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("<div style='font-family:sans-serif;padding:20px;color:red;'>
        <b>Koneksi database gagal!</b><br>
        Error: " . mysqli_connect_error() . "
    </div>");
}

mysqli_set_charset($conn, "utf8");
?>