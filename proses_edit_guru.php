<?php
include 'koneksi.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id   = (int) $_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $jk   = mysqli_real_escape_string($conn, $_POST['jk']);
    $nip  = mysqli_real_escape_string($conn, $_POST['nip']);

    // Checkbox mata pelajaran yang dicentang (array id_pelajaran)
    $pelajaran_dipilih = isset($_POST['pelajaran']) && is_array($_POST['pelajaran'])
        ? $_POST['pelajaran']
        : [];

    $sql = "UPDATE guru SET nama='$nama', jk='$jk', nip='$nip' WHERE id_guru=$id";

    if(mysqli_query($conn, $sql)) {
        // Sinkronkan relasi guru <-> mata pelajaran
        mysqli_query($conn, "DELETE FROM guru_pelajaran WHERE id_guru=$id");

        foreach ($pelajaran_dipilih as $id_pelajaran) {
            $id_pelajaran = (int) $id_pelajaran;
            if ($id_pelajaran <= 0) continue;

            mysqli_query($conn,
                "INSERT INTO guru_pelajaran (id_guru, id_pelajaran) VALUES ($id, $id_pelajaran)"
            );
        }

        header("Location: guru.php?status=edit");
    } else {
        header("Location: guru.php?status=error");
    }
    exit;
}
?>