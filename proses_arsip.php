<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
if ($_SESSION['role'] !== 'admin') { header('Location: jadwal_guru.php'); exit; }

include 'koneksi.php';

$id_semester = (int)($_POST['id_semester'] ?? 0);
if (!$id_semester) { header('Location: arsip.php?status=error'); exit; }

// Ambil info semester
$sem = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM semester WHERE id_semester=$id_semester"));
if (!$sem) { header('Location: arsip.php?status=error'); exit; }

// Cek apakah sudah pernah diarsipkan
$cek = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM arsip_jadwal WHERE id_semester=$id_semester"));
if ($cek['total'] > 0) {
    header('Location: arsip.php?status=sudah_ada');
    exit;
}

// Ambil semua jadwal semester ini dengan JOIN lengkap
$query = mysqli_query($conn, "
    SELECT j.*,
           g.nama AS nama_guru,
           p.pelajaran AS nama_pelajaran,
           k.kelas AS nama_kelas,
           jm.mulai AS jam_mulai, jm.akhir AS jam_selesai
    FROM jadwal j
    JOIN guru g ON g.id_guru = j.id_guru
    JOIN pelajaran p ON p.id_pelajaran = j.id_pelajaran
    JOIN kelas k ON k.id_kelas = j.id_kelas
    JOIN jam jm ON jm.id_jam = j.id_jam
    WHERE k.id_semester = $id_semester
");

$total = 0;
while ($row = mysqli_fetch_assoc($query)) {
    $hari           = mysqli_real_escape_string($conn, $row['hari']);
    $tahun          = mysqli_real_escape_string($conn, $sem['tahun']);
    $semester_nama  = mysqli_real_escape_string($conn, $sem['semester']);
    $nama_guru      = mysqli_real_escape_string($conn, $row['nama_guru']);
    $nama_pelajaran = mysqli_real_escape_string($conn, $row['nama_pelajaran']);
    $nama_kelas     = mysqli_real_escape_string($conn, $row['nama_kelas']);
    $jam_mulai      = mysqli_real_escape_string($conn, $row['jam_mulai']);
    $jam_selesai    = mysqli_real_escape_string($conn, $row['jam_selesai']);
    $id_kelas       = (int)$row['id_kelas'];
    $id_guru        = (int)$row['id_guru'];
    $id_pelajaran   = (int)$row['id_pelajaran'];
    $id_jam         = (int)$row['id_jam'];

    mysqli_query($conn, "
        INSERT INTO arsip_jadwal
            (id_semester, id_kelas, id_guru, id_pelajaran, id_jam, id_ruang, hari,
             tahun_ajaran, semester,
             nama_guru, nama_pelajaran, nama_kelas, jam_mulai, jam_selesai, nama_ruang)
        VALUES
            ($id_semester, $id_kelas, $id_guru, $id_pelajaran, $id_jam, 0, '$hari',
             '$tahun', '$semester_nama',
             '$nama_guru', '$nama_pelajaran', '$nama_kelas', '$jam_mulai', '$jam_selesai', '-')
    ");
    $total++;
}

if ($total > 0) {
    header('Location: arsip.php?status=berhasil&total=' . $total);
} else {
    header('Location: arsip.php?status=kosong');
}
exit;