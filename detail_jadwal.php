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

$koneksi = mysqli_connect("localhost", "root", "", "jadwal");
$get_kelas_now = mysqli_query($koneksi, "SELECT * FROM kelas WHERE id_kelas = ".$_GET['id_kelas']);
$kelas_now = mysqli_fetch_array($get_kelas_now);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pelajaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --sidebar-bg: #1a3c5e; --sidebar-width: 240px; }
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .sidebar { width: var(--sidebar-width); background: var(--sidebar-bg); min-height: 100vh; position: fixed; top: 0; left: 0; z-index: 100; }
        .sidebar-brand { padding: 20px 16px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-brand h6 { color: #fff; font-size: 14px; font-weight: 600; margin: 0; }
        .sidebar-brand small { color: rgba(255,255,255,0.5); font-size: 11px; }
        .nav-label { padding: 16px 20px 4px; font-size: 10px; color: rgba(255,255,255,0.35); letter-spacing: 1px; text-transform: uppercase; }
        .sidebar .nav-link { color: rgba(255,255,255,0.7); padding: 10px 20px; font-size: 13.5px; display: flex; align-items: center; gap: 10px; border-left: 3px solid transparent; transition: all 0.15s; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,0.08); color: #fff; border-left-color: #4fc3f7; }
        .main-content { margin-left: var(--sidebar-width); }
        .topbar { background: #fff; padding: 14px 24px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; }
        .topbar h5 { font-size: 16px; font-weight: 600; color: #1a3c5e; margin: 0; }
        table th { background: #1a3c5e; color: white; text-align: center; }
        table td { text-align: center; vertical-align: middle; }
        .mapel-text { font-weight: 600; font-size: 13px; color: #1a3c5e; }
        .guru-text { font-size: 12px; color: #6b7280; }
        .jam-label { font-size: 12px; color: #374151; }
        .empty-slot { color: #d1d5db; font-size: 12px; }

        @media print {
          .sidebar, .topbar a, .topbar button { display: none !important; }
          .main-content { margin-left: 0 !important; }
          body { background: #fff !important; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <div class="sidebar-brand">
    <h6><i class="bi bi-calendar3 me-2"></i>Sistem Jadwal</h6>
    <small>SMAN 2 Toraja Utara</small>
  </div>
  <div class="nav-label">Menu Utama</div>
  <a href="index.php" class="nav-link"><i class="bi bi-grid-1x2"></i> Dashboard</a>
  <a href="semester.php" class="nav-link"><i class="bi bi-calendar-range"></i> Semester</a>
  <div class="nav-label">Data Master</div>
  <a href="guru.php" class="nav-link"><i class="bi bi-person-badge"></i> Guru</a>
  <a href="mk.php" class="nav-link"><i class="bi bi-book"></i> Mata Pelajaran</a>
  <a href="jam.php" class="nav-link"><i class="bi bi-clock"></i> Jam Pelajaran</a>
  <a href="kelas.php" class="nav-link"><i class="bi bi-mortarboard"></i> Kelas</a>
  <a href="ruang.php" class="nav-link"><i class="bi bi-door-open"></i> Ruang Kelas</a>
<a href="kurikulum.php" class="nav-link"><i class="bi bi-journal-bookmark"></i> Kurikulum</a>

  <div class="nav-label">Penjadwalan</div>
  <a href="generate.php" class="nav-link"><i class="bi bi-gear-wide-connected"></i> Generate Jadwal</a>
  <a href="jadwal.php" class="nav-link active"><i class="bi bi-table"></i> Lihat Jadwal</a>
  <div class="nav-label">Akun</div>
  <a href="manajemen_akun.php" class="nav-link"><i class="bi bi-people"></i> Manajemen Akun</a>
  <div class="nav-label">Keluar</div>
  <a href="login.php?logout=1" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
</div>

<!-- MAIN -->
<div class="main-content">
  <div class="topbar">
    <h5><i class="bi bi-table me-2"></i>Jadwal Pelajaran - Kelas <?= $kelas_now['kelas'] ?></h5>
   <div class="d-flex gap-2">
      <a href="export_excel.php?id_kelas=<?= $_GET['id_kelas'] ?>" class="btn btn-success btn-sm">
        <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
      </a>
      <button onclick="window.print()" class="btn btn-danger btn-sm">
        <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
      </button>
      <a href="jadwal.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
      </a>
    </div>
  </div>

  <div class="p-4">
    <?php if(isset($_SESSION['gagal'])): ?>
    <div class="alert alert-warning alert-dismissible fade show">
      <?= $_SESSION['gagal'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['gagal']); endif; ?>

    <div class="bg-white rounded-3 border p-3">
      <div class="table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Jam ke-</th>
              <th>Senin</th>
              <th>Selasa</th>
              <th>Rabu</th>
              <th>Kamis</th>
              <th>Jumat</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $hari_list = ['Senin','Selasa','Rabu','Kamis','Jumat'];
            $id_kelas  = (int)$_GET['id_kelas'];

            // PENTING: kolom `jam` (jam ke-) di tabel `jam` ternyata TIDAK unik
            // lintas hari -- setiap hari punya baris sendiri, jadi kalau kita
            // group by kolom `jam`, baris "jam ke-1" hari Senin dan "jam ke-1"
            // hari Rabu dianggap dua baris berbeda meski jam-nya sama persis.
            // Solusinya: group berdasarkan waktu ASLI (mulai + akhir), lalu
            // urutkan berdasarkan jam mulai secara kronologis.
            $get_slot_waktu = mysqli_query($koneksi,
                "SELECT DISTINCT mulai, akhir FROM jam ORDER BY mulai ASC");
            $slot_list = [];
            while ($r = mysqli_fetch_assoc($get_slot_waktu)) $slot_list[] = $r;

            foreach ($slot_list as $slot):
                $mulai = mysqli_real_escape_string($koneksi, $slot['mulai']);
                $akhir = mysqli_real_escape_string($koneksi, $slot['akhir']);
            ?>
            <tr>
              <td>
                <strong><?= substr($slot['mulai'], 0, 5) . " - " . substr($slot['akhir'], 0, 5) ?></strong>
              </td>
              <?php foreach ($hari_list as $hari):
                // Cari baris jam untuk kombinasi hari + waktu ini
                $get_jam_slot = mysqli_query($koneksi,
                  "SELECT * FROM jam WHERE hari='$hari' AND mulai='$mulai' AND akhir='$akhir' LIMIT 1");
                $jam_slot = mysqli_fetch_array($get_jam_slot);
              ?>
              <td>
                <?php if ($jam_slot):
                  $id_jam = (int)$jam_slot['id_jam'];
                  $get_jadwal = mysqli_query($koneksi,
                    "SELECT j.*, p.pelajaran, g.nama as nama_guru
                     FROM jadwal j
                     LEFT JOIN pelajaran p ON p.id_pelajaran = j.id_pelajaran
                     LEFT JOIN guru g ON g.id_guru = j.id_guru
                     WHERE j.id_jam='$id_jam' AND j.id_kelas='$id_kelas' AND j.hari='$hari'
                     LIMIT 1");
                  $data_jadwal = mysqli_fetch_array($get_jadwal);
                ?>
                  <?php if ($data_jadwal): ?>
                    <div class="mapel-text"><?= htmlspecialchars($data_jadwal['pelajaran']) ?></div>
                    <div class="guru-text"><i class="bi bi-person me-1"></i><?= htmlspecialchars($data_jadwal['nama_guru']) ?></div>
                  <?php else: ?>
                    <span class="empty-slot">-</span>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="empty-slot">-</span>
                <?php endif; ?>
              </td>
              <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>