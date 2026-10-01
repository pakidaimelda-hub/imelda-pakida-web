<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
if ($_SESSION['role'] !== 'admin') { header('Location: jadwal_guru.php'); exit; }
include 'koneksi.php';

$id_semester = (int)($_GET['id_semester'] ?? 0);
if (!$id_semester) { header('Location: arsip.php'); exit; }

// Info arsip
$info = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT DISTINCT tahun_ajaran, semester FROM arsip_jadwal WHERE id_semester=$id_semester LIMIT 1"));
if (!$info) { header('Location: arsip.php'); exit; }

// Daftar kelas di arsip ini
$kelas_arsip = mysqli_query($conn,
    "SELECT DISTINCT id_kelas, nama_kelas FROM arsip_jadwal WHERE id_semester=$id_semester ORDER BY nama_kelas");

$hari_list = ['Senin','Selasa','Rabu','Kamis','Jumat'];
$kelas_dipilih = (int)($_GET['id_kelas'] ?? 0);

// Ambil kelas pertama jika belum dipilih
if (!$kelas_dipilih) {
    $first = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT id_kelas FROM arsip_jadwal WHERE id_semester=$id_semester LIMIT 1"));
    $kelas_dipilih = $first ? (int)$first['id_kelas'] : 0;
}

// Ambil jadwal arsip kelas terpilih
$jadwal_arsip = [];
if ($kelas_dipilih) {
    $q = mysqli_query($conn, "
        SELECT * FROM arsip_jadwal
        WHERE id_semester=$id_semester AND id_kelas=$kelas_dipilih
        ORDER BY FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat'), jam_mulai
    ");
    while ($r = mysqli_fetch_assoc($q)) {
        $jadwal_arsip[$r['hari']][] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lihat Arsip - SMAN 2 Toraja Utara</title>
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
    .hari-header { background: #1a3c5e; color: white; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-bottom: 8px; }
    .slot-jadwal { background: #eff6ff; border-left: 3px solid #3b82f6; border-radius: 8px; padding: 10px 14px; margin-bottom: 8px; font-size: 13px; }
    .slot-jadwal .jam { color: #6b7280; font-size: 11px; }
    .slot-jadwal .mapel { font-weight: 600; color: #1a3c5e; }
    .slot-jadwal .guru { color: #374151; }
    .slot-jadwal .ruang { color: #9ca3af; font-size: 11px; }
    @media print {
      .sidebar, .topbar, .no-print { display: none !important; }
      .main-content { margin-left: 0 !important; }
    }
  </style>
</head>
<body>

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
  <a href="jadwal.php" class="nav-link"><i class="bi bi-table"></i> Lihat Jadwal</a>
        <a href="arsip.php" class="nav-link"><i class="bi bi-archive"></i> Arsip Jadwal</a>
  <a href="arsip.php" class="nav-link active"><i class="bi bi-archive"></i> Arsip Jadwal</a>
  <div class="nav-label">Akun</div>
  <a href="manajemen_akun.php" class="nav-link"><i class="bi bi-people"></i> Manajemen Akun</a>
  <div class="nav-label">Keluar</div>
  <a href="login.php?logout=1" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
</div>

<div class="main-content">
  <div class="topbar">
    <h5>
      <i class="bi bi-folder2-open me-2"></i>
      Arsip: Semester <?= ucfirst($info['semester']) ?> &mdash; <?= $info['tahun_ajaran'] ?>
    </h5>
    <div class="d-flex gap-2 no-print">
      <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-printer me-1"></i>Cetak
      </button>
      <a href="arsip.php" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
      </a>
    </div>
  </div>

  <div class="p-4">
    <div class="row g-3">

      <!-- Pilih Kelas -->
      <div class="col-md-3 no-print">
        <div class="bg-white rounded-3 border p-3">
          <h6 class="fw-semibold mb-3" style="color:#1a3c5e;font-size:13px;">
            <i class="bi bi-mortarboard me-2"></i>Pilih Kelas
          </h6>
          <div class="d-flex flex-column gap-2">
            <?php
            mysqli_data_seek($kelas_arsip, 0);
            while ($k = mysqli_fetch_assoc($kelas_arsip)):
              $active = ($k['id_kelas'] == $kelas_dipilih) ? 'btn-primary' : 'btn-outline-secondary';
            ?>
            <a href="?id_semester=<?= $id_semester ?>&id_kelas=<?= $k['id_kelas'] ?>"
               class="btn btn-sm <?= $active ?> text-start">
              <i class="bi bi-mortarboard me-1"></i>Kelas <?= htmlspecialchars($k['nama_kelas']) ?>
            </a>
            <?php endwhile; ?>
          </div>
        </div>
      </div>

      <!-- Tabel Jadwal Arsip -->
      <div class="col-md-9">
        <div class="bg-white rounded-3 border p-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="fw-semibold mb-0" style="color:#1a3c5e;">
              Jadwal Kelas <?= htmlspecialchars($jadwal_arsip ? array_values($jadwal_arsip)[0][0]['nama_kelas'] ?? '-' : '-') ?>
            </h6>
            <span class="badge" style="background:#eff6ff;color:#1d4ed8;">
              <i class="bi bi-archive me-1"></i>Arsip <?= ucfirst($info['semester']) . ' ' . $info['tahun_ajaran'] ?>
            </span>
          </div>

          <?php if (!empty($jadwal_arsip)): ?>
          <div class="row g-3">
            <?php foreach ($hari_list as $hari): ?>
            <?php if (isset($jadwal_arsip[$hari])): ?>
            <div class="col-md-6">
              <div class="hari-header"><i class="bi bi-calendar-day me-2"></i><?= $hari ?></div>
              <?php foreach ($jadwal_arsip[$hari] as $slot): ?>
              <div class="slot-jadwal">
                <div class="jam"><i class="bi bi-clock me-1"></i><?= $slot['jam_mulai'] ?> &ndash; <?= $slot['jam_selesai'] ?></div>
                <div class="mapel"><?= htmlspecialchars($slot['nama_pelajaran']) ?></div>
                <div class="guru"><i class="bi bi-person me-1"></i><?= htmlspecialchars($slot['nama_guru']) ?></div>
                <div class="ruang"><i class="bi bi-door-open me-1"></i><?= htmlspecialchars($slot['nama_ruang']) ?></div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <div class="text-center py-5 text-muted">
            <i class="bi bi-calendar-x" style="font-size:48px;opacity:0.3;display:block;margin-bottom:12px;"></i>
            <p>Tidak ada data jadwal untuk kelas ini.</p>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>