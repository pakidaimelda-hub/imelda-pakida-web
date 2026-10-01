<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
if ($_SESSION['role'] !== 'admin') { header('Location: jadwal_guru.php'); exit; }
include 'koneksi.php';

$id_semester = (int)($_GET['id_semester'] ?? 0);
if ($id_semester) {
    mysqli_query($conn, "DELETE FROM arsip_jadwal WHERE id_semester=$id_semester");
}
header('Location: arsip.php?status=hapus');
exit;