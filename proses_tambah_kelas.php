<?php
include 'koneksi.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kelas       = mysqli_real_escape_string($conn, $_POST['kelas']);
    $id_semester = (int) $_POST['id_semester'];

    $sql = "INSERT INTO kelas (kelas, id_semester) VALUES ('$kelas', $id_semester)";

    if(mysqli_query($conn, $sql)) {

        // Auto-generate ruang kelas dengan nama sama seperti nama kelas,
        // supaya tidak perlu isi manual di menu Ruang Kelas.
        // Cek dulu supaya tidak dobel kalau nama kelas yang sama pernah dibuat ruangnya.
        $nama_ruang = "Ruang " . $kelas;
        $cek = mysqli_query($conn, "SELECT id_ruang FROM ruang_kelas WHERE nama_ruang = '$nama_ruang'");

        if (mysqli_num_rows($cek) == 0) {
            mysqli_query($conn, "INSERT INTO ruang_kelas (nama_ruang, kapasitas) VALUES ('$nama_ruang', 40)");
        }

        header("Location: kelas.php?status=tambah");
    } else {
        header("Location: kelas.php?status=error");
    }
    exit;
}
?>