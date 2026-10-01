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
?>
<?php include 'koneksi.php'; ?>
<?php
// Hitung statistik dashboard
$total_guru = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM guru"));
$total_pelajaran = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM pelajaran"));
$total_kelas = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM kelas"));
$total_ruang = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM ruang_kelas"));

// Ambil semester aktif
$semester_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM semester WHERE status='1' LIMIT 1"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem Penjadwalan - SMAN 2 Toraja Utara</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root {
      --sidebar-bg: #1a3c5e;
      --sidebar-width: 240px;
    }

    body {
      background-color: #f0f2f5;
      font-family: 'Segoe UI', sans-serif;
    }

    /* ===== SIDEBAR ===== */
    .sidebar {
      width: var(--sidebar-width);
      background: var(--sidebar-bg);
      min-height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 100;
    }

    .sidebar-brand {
      padding: 20px 16px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .sidebar-brand h6 {
      color: #fff;
      font-size: 14px;
      font-weight: 600;
      margin: 0;
    }

    .sidebar-brand small {
      color: rgba(255,255,255,0.5);
      font-size: 11px;
    }

    .nav-label {
      padding: 16px 20px 4px;
      font-size: 10px;
      color: rgba(255,255,255,0.35);
      letter-spacing: 1px;
      text-transform: uppercase;
    }

    .sidebar .nav-link {
      color: rgba(255,255,255,0.7);
      padding: 10px 20px;
      font-size: 13.5px;
      display: flex;
      align-items: center;
      gap: 10px;
      border-left: 3px solid transparent;
      transition: all 0.15s;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      background: rgba(255,255,255,0.08);
      color: #fff;
      border-left-color: #4fc3f7;
    }

    /* ===== MAIN CONTENT ===== */
    .main-content {
      margin-left: var(--sidebar-width);
    }

    .topbar {
      background: #fff;
      padding: 14px 24px;
      border-bottom: 1px solid #e5e7eb;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .topbar h5 {
      font-size: 16px;
      font-weight: 600;
      color: #1a3c5e;
      margin: 0;
    }

    /* ===== STAT CARDS ===== */
    .stat-card {
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      border: 1px solid #e5e7eb;
    }

    .stat-card .number {
      font-size: 32px;
      font-weight: 700;
      line-height: 1;
    }

    .stat-card .label {
      font-size: 13px;
      color: #6b7280;
      margin-bottom: 8px;
    }

    .stat-card .icon-box {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
    }

    /* ===== SEMESTER BADGE ===== */
    .semester-badge {
      background: #dcfce7;
      color: #16a34a;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 500;
    }

    /* ===== ADMIN LOGOUT LINK ===== */
    .admin-link {
      transition: color 0.15s;
    }

    .admin-link:hover {
      color: #dc2626 !important;
    }
  </style>
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<div class="sidebar">
  <div class="sidebar-brand">
    <h6><i class="bi bi-calendar3me-2"></i>Sistem Jadwal</h6>
    <small>SMAN 2 Toraja Utara</small>
  </div>

  <div class="nav-label">Menu Utama</div>
  <a href="index.php" class="nav-link active">
    <i class="bi bi-grid-1x2"></i> Dashboard
  </a>
  <a href="semester.php" class="nav-link">
    <i class="bi bi-calendar-range"></i> Semester
  </a>

  <div class="nav-label">Data Master</div>
  <a href="guru.php" class="nav-link">
    <i class="bi bi-person-badge"></i> Guru
  </a>
  <a href="mk.php" class="nav-link">
    <i class="bi bi-book"></i> Mata Pelajaran
  </a>
  <a href="jam.php" class="nav-link">
    <i class="bi bi-clock"></i> Jam Pelajaran
  </a>
    <a href="kelas.php" class="nav-link">
    <i class="bi bi-mortarboard"></i> Kelas
  </a>
  <a href="ruang.php" class="nav-link">
    <i class="bi bi-door-open"></i> Ruang Kelas
  </a>
  <a href="kurikulum.php" class="nav-link">
    <i class="bi bi-journal-bookmark"></i> Kurikulum
  </a>

  <div class="nav-label">Penjadwalan</div>
  <a href="generate.php" class="nav-link"><i class="bi bi-gear-wide-connected"></i> Generate Jadwal</a>
  <a href="jadwal.php" class="nav-link"><i class="bi bi-table"></i> Lihat Jadwal</a>
        <a href="arsip.php" class="nav-link"><i class="bi bi-archive"></i> Arsip Jadwal</a>
  <div class="nav-label">Akun</div>
  <a href="manajemen_akun.php" class="nav-link"><i class="bi bi-people"></i> Manajemen Akun</a>
  <div class="nav-label">Keluar</div>
  <a href="login.php?logout=1" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
</div>

<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">

  <!-- Topbar -->
  <div class="topbar">
    <h5><i class="bi bi-grid-1x2 me-2"></i>Dashboard</h5>
    <div class="d-flex align-items-center gap-3">
      <?php if($semester_aktif): ?>
        <span class="semester-badge">
          <i class="bi bi-check-circle me-1"></i>
          Semester <?= $semester_aktif['semester'] ?> - <?= $semester_aktif['tahun'] ?> Aktif
        </span>
      <?php else: ?>
        <span class="badge bg-warning text-dark">Belum ada semester aktif</span>
      <?php endif; ?>
      <a href="login.php?logout=1" class="admin-link text-muted text-decoration-none" style="font-size:13px;" title="Klik untuk logout" onclick="return confirm('Yakin ingin logout?');">
        <i class="bi bi-person-circle me-1"></i>Admin
      </a>
    </div>
  </div>

  <!-- Content -->
  <div class="p-4">

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
          <div>
            <div class="label">Total Guru</div>
            <div class="number text-primary"><?= $total_guru ?></div>
          </div>
          <div class="icon-box" style="background:#e8f4fd; color:#1a3c5e;">
            <i class="bi bi-person-badge"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
          <div>
            <div class="label">Mata Pelajaran</div>
            <div class="number text-success"><?= $total_pelajaran ?></div>
          </div>
          <div class="icon-box" style="background:#dcfce7; color:#16a34a;">
            <i class="bi bi-book"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
          <div>
            <div class="label">Jumlah Kelas</div>
            <div class="number text-warning"><?= $total_kelas ?></div>
          </div>
          <div class="icon-box" style="background:#fef9c3; color:#ca8a04;">
            <i class="bi bi-mortarboard"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
          <div>
            <div class="label">Ruang Kelas</div>
            <div class="number text-danger"><?= $total_ruang ?></div>
          </div>
          <div class="icon-box" style="background:#fee2e2; color:#dc2626;">
            <i class="bi bi-door-open"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Row 2: Status + Info GA -->
    <div class="row g-3">

      <!-- Status Pengisian Data -->
      <div class="col-md-6">
        <div class="bg-white rounded-3 border p-4">
          <h6 class="fw-semibold mb-3" style="color:#1a3c5e;">
            <i class="bi bi-check2-square me-2"></i>Status Pengisian Data
          </h6>
          <ul class="list-unstyled mb-0" style="display:flex;flex-direction:column;gap:8px;">
            <li class="d-flex align-items-center justify-content-between p-2 rounded" style="background:#f9fafb;">
              <span style="font-size:13px;"><i class="bi bi-calendar-check text-success me-2"></i>Semester aktif</span>
              <?php if($semester_aktif): ?>
                <span class="badge bg-success">Aktif</span>
              <?php else: ?>
                <span class="badge bg-danger">Belum diset</span>
              <?php endif; ?>
            </li>
            <li class="d-flex align-items-center justify-content-between p-2 rounded" style="background:#f9fafb;">
              <span style="font-size:13px;"><i class="bi bi-person-badge text-primary me-2"></i>Data guru</span>
              <span class="badge <?= $total_guru > 0 ? 'bg-success' : 'bg-danger' ?>">
                <?= $total_guru > 0 ? $total_guru.' guru' : 'Belum ada' ?>
              </span>
            </li>
            <li class="d-flex align-items-center justify-content-between p-2 rounded" style="background:#f9fafb;">
              <span style="font-size:13px;"><i class="bi bi-book text-success me-2"></i>Mata pelajaran</span>
              <span class="badge <?= $total_pelajaran > 0 ? 'bg-success' : 'bg-danger' ?>">
                <?= $total_pelajaran > 0 ? $total_pelajaran.' mapel' : 'Belum ada' ?>
              </span>
            </li>
            <li class="d-flex align-items-center justify-content-between p-2 rounded" style="background:#f9fafb;">
              <span style="font-size:13px;"><i class="bi bi-door-open text-danger me-2"></i>Ruang kelas</span>
              <span class="badge <?= $total_ruang > 0 ? 'bg-success' : 'bg-warning text-dark' ?>">
                <?= $total_ruang > 0 ? $total_ruang.' ruang' : 'Belum diisi' ?>
              </span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Info Algoritma Genetika -->
      <div class="col-md-6">
        <div class="bg-white rounded-3 border p-4">
          <h6 class="fw-semibold mb-3" style="color:#1a3c5e;">
            <i class="bi bi-gear-wide-connected me-2"></i>Algoritma Genetika
          </h6>
          <p style="font-size:13px;color:#6b7280;">
            Jadwal dibuat otomatis dengan meminimalkan bentrok guru, ruang, dan kelas.
          </p>
          <div style="display:flex;flex-direction:column;gap:8px;margin-top:12px;">
            <div class="p-2 rounded" style="background:#f0f9ff;border-left:3px solid #0ea5e9;font-size:12px;">
              <i class="bi bi-arrow-repeat me-2 text-info"></i><strong>Seleksi:</strong> Roulette Wheel
            </div>
            <div class="p-2 rounded" style="background:#f0fdf4;border-left:3px solid #22c55e;font-size:12px;">
              <i class="bi bi-shuffle me-2 text-success"></i><strong>Crossover:</strong> Single Point
            </div>
            <div class="p-2 rounded" style="background:#fff7ed;border-left:3px solid #f97316;font-size:12px;">
              <i class="bi bi-lightning me-2 text-warning"></i><strong>Mutasi:</strong> Random Gene
            </div>
          </div>
          <a href="generate.php" class="btn btn-primary btn-sm mt-3 w-100">
            <i class="bi bi-play-fill me-1"></i>Generate Jadwal Sekarang
          </a>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>