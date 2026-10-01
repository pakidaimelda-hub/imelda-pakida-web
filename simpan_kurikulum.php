<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SESSION['role'] !== 'admin') {
    header('Location: jadwal_guru.php');
    exit;
}
include 'koneksi.php';

$id_kelas = (int)($_POST['id_kelas'] ?? 0);
$mapel    = $_POST['mapel'] ?? [];

if ($id_kelas <= 0) {
    header('Location: kurikulum.php');
    exit;
}

// Hapus dulu data lama untuk kelas ini, lalu masukkan ulang sesuai centang terbaru
mysqli_query($conn, "DELETE FROM kelas_pelajaran WHERE id_kelas=$id_kelas");

foreach ($mapel as $id_pelajaran) {
    $id_pelajaran = (int)$id_pelajaran;
    mysqli_query($conn, "INSERT INTO kelas_pelajaran (id_kelas, id_pelajaran) VALUES ($id_kelas, $id_pelajaran)");
}

header("Location: kurikulum.php?id_kelas=$id_kelas&status=sukses");
exit;
?>