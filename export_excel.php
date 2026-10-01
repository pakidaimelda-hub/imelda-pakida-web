<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$koneksi = mysqli_connect("localhost", "root", "", "jadwal");
$id_kelas = (int)$_GET['id_kelas'];

$get_kelas_now = mysqli_query($koneksi, "SELECT * FROM kelas WHERE id_kelas = $id_kelas");
$kelas_now = mysqli_fetch_array($get_kelas_now);

$hari_list = ['Senin','Selasa','Rabu','Kamis','Jumat'];

// Header supaya browser download sebagai file Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Jadwal_Kelas_" . $kelas_now['kelas'] . ".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table border="1">
  <tr>
    <th colspan="6" style="font-size:16px; text-align:center;">
      JADWAL PELAJARAN - KELAS <?= strtoupper($kelas_now['kelas']) ?>
    </th>
  </tr>
  <tr>
    <th>Jam</th>
    <th>Senin</th>
    <th>Selasa</th>
    <th>Rabu</th>
    <th>Kamis</th>
    <th>Jumat</th>
  </tr>
  <?php
  $get_jam = mysqli_query($koneksi, "SELECT * FROM jam ORDER BY mulai ASC");
  while ($data_jam = mysqli_fetch_array($get_jam)):
  ?>
  <tr>
    <td><b><?= $data_jam['mulai'] ?> - <?= $data_jam['akhir'] ?></b></td>
    <?php foreach ($hari_list as $hari): ?>
    <?php
      $id_jam = (int)$data_jam['id_jam'];
      $get_jadwal = mysqli_query($koneksi,
        "SELECT j.*, p.pelajaran, g.nama as nama_guru
         FROM jadwal j
         LEFT JOIN pelajaran p ON p.id_pelajaran = j.id_pelajaran
         LEFT JOIN guru g ON g.id_guru = j.id_guru
         WHERE j.id_jam='$id_jam' AND j.id_kelas='$id_kelas' AND j.hari='$hari'
         LIMIT 1");
      $data_jadwal = mysqli_fetch_array($get_jadwal);
    ?>
    <td>
      <?php if ($data_jadwal): ?>
        <?= $data_jadwal['pelajaran'] ?><br>
        <small><?= $data_jadwal['nama_guru'] ?></small>
      <?php else: ?>
        -
      <?php endif; ?>
    </td>
    <?php endforeach; ?>
  </tr>
  <?php endwhile; ?>
</table>