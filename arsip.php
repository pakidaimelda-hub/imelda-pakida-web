<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
if ($_SESSION['role'] !== 'admin') { header('Location: jadwal_guru.php'); exit; }
include 'koneksi.php';

// Daftar semua semester
$semua_semester = mysqli_query($conn, "SELECT * FROM semester ORDER BY id_semester DESC");

// Daftar tahun ajaran yang sudah diarsipkan
$arsip_list = mysqli_query($conn, "
    SELECT tahun_ajaran, semester, id_semester,
           COUNT(*) as total_jadwal,
           MAX(tanggal_arsip) as tanggal_arsip
    FROM arsip_jadwal
    GROUP BY id_semester, tahun_ajaran, semester
    ORDER BY tanggal_arsip DESC
");
$arsip_data = [];
while ($a = mysqli_fetch_assoc($arsip_list)) $arsip_data[] = $a;
$arsip_semester_ids = array_column($arsip_data, 'id_semester');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Arsip Jadwal - SMAN 2 Toraja Utara</title>
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
    .arsip-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:20px; transition: box-shadow 0.2s; }
    .arsip-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
    .icon-box { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; }
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

  <div class="nav-label">Penjadwalan</div>
  <a href="generate.php" class="nav-link"><i class="bi bi-gear-wide-connected"></i> Generate Jadwal</a>
  <a href="jadwal.php" class="nav-link"><i class="bi bi-table"></i> Lihat Jadwal</a>
        <a href="arsip.php" class="nav-link active"><i class="bi bi-archive"></i> Arsip Jadwal</a>
  <div class="nav-label">Akun</div>
  <a href="manajemen_akun.php" class="nav-link"><i class="bi bi-people"></i> Manajemen Akun</a>
  <div class="nav-label">Keluar</div>
  <a href="login.php?logout=1" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
</div>

<div class="main-content">
  <div class="topbar">
    <h5><i class="bi bi-archive me-2"></i>Arsip Jadwal</h5>
    <a href="login.php?logout=1" class="admin-link text-muted text-decoration-none" style="font-size:13px;" title="Klik untuk logout" onclick="return confirm('Yakin ingin logout?');"><i class="bi bi-person-circle me-1"></i>Admin</a>
  </div>

  <div class="p-4">

    <?php if(isset($_GET['status'])): ?>
      <?php if($_GET['status'] == 'berhasil'): ?>
      <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>
        Berhasil mengarsipkan <strong><?= (int)$_GET['total'] ?></strong> data jadwal!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php elseif($_GET['status'] == 'sudah_ada'): ?>
      <div class="alert alert-warning alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Semester ini sudah pernah diarsipkan sebelumnya.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php elseif($_GET['status'] == 'kosong'): ?>
      <div class="alert alert-warning alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Tidak ada data jadwal yang bisa diarsipkan untuk semester ini.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php elseif($_GET['status'] == 'hapus'): ?>
      <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-trash me-2"></i>Arsip berhasil dihapus.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>
    <?php endif; ?>

    <div class="row g-4">

      <!-- KIRI: Simpan Arsip Baru -->
      <div class="col-md-5">
        <div class="bg-white rounded-3 border p-4">
          <h6 class="fw-semibold mb-1" style="color:#1a3c5e;">
            <i class="bi bi-archive me-2"></i>Simpan Arsip Baru
          </h6>
          <p class="text-muted mb-4" style="font-size:13px;">
            Pilih semester yang ingin diarsipkan. Data jadwal akan disimpan permanen.
          </p>

          <form action="proses_arsip.php" method="POST">
            <div class="mb-3">
              <label class="form-label fw-semibold" style="font-size:13px;">Pilih Semester</label>
              <select name="id_semester" class="form-select" required>
                <option value="">-- Pilih Semester --</option>
                <?php
                mysqli_data_seek($semua_semester, 0);
                while ($s = mysqli_fetch_assoc($semua_semester)):
                  $sudah = in_array($s['id_semester'], $arsip_semester_ids);
                ?>
                <option value="<?= $s['id_semester'] ?>" <?= $sudah ? 'disabled' : '' ?>>
                  <?= ucfirst($s['semester']) . ' - ' . $s['tahun'] ?>
                  <?= $s['status']=='1' ? '(Aktif)' : '' ?>
                  <?= $sudah ? '✓ Sudah diarsipkan' : '' ?>
                </option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="p-3 rounded-3 mb-4" style="background:#fef9c3;font-size:12px;color:#713f12;">
              <i class="bi bi-lightbulb me-1"></i>
              <strong>Info:</strong> Arsip menyimpan snapshot jadwal termasuk nama guru, kelas, ruang, dan jam pelajaran agar tetap tersimpan meski data master diubah.
            </div>
            <button type="submit" class="btn btn-primary w-100"
              onclick="return confirm('Simpan jadwal semester ini ke arsip?')">
              <i class="bi bi-archive me-2"></i>Simpan ke Arsip
            </button>
          </form>
        </div>
      </div>

      <!-- KANAN: Daftar Arsip -->
      <div class="col-md-7">
        <div class="bg-white rounded-3 border p-4">
          <h6 class="fw-semibold mb-4" style="color:#1a3c5e;">
            <i class="bi bi-clock-history me-2"></i>Riwayat Arsip
            <span class="badge bg-primary ms-2"><?= count($arsip_data) ?></span>
          </h6>

          <?php if (count($arsip_data) > 0): ?>
          <div class="d-flex flex-column gap-3">
            <?php foreach ($arsip_data as $arsip): ?>
            <div class="arsip-card">
              <div class="d-flex align-items-center gap-3">
                <div class="icon-box" style="background:#eff6ff;color:#1d4ed8;">
                  <i class="bi bi-folder2-open"></i>
                </div>
                <div class="flex-fill">
                  <div style="font-weight:600;font-size:14px;color:#1a3c5e;">
                    Semester <?= ucfirst($arsip['semester']) ?> &mdash; <?= $arsip['tahun_ajaran'] ?>
                  </div>
                  <div style="font-size:12px;color:#6b7280;">
                    <i class="bi bi-table me-1"></i><?= $arsip['total_jadwal'] ?> slot jadwal &nbsp;|&nbsp;
                    <i class="bi bi-clock me-1"></i>Diarsipkan: <?= date('d M Y', strtotime($arsip['tanggal_arsip'])) ?>
                  </div>
                </div>
                <div class="d-flex gap-2">
                  <a href="lihat_arsip.php?id_semester=<?= $arsip['id_semester'] ?>"
                     class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye me-1"></i>Lihat
                  </a>
                  <a href="hapus_arsip.php?id_semester=<?= $arsip['id_semester'] ?>"
                     class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('Hapus arsip semester <?= ucfirst($arsip['semester']).' '.$arsip['tahun_ajaran'] ?>?')">
                    <i class="bi bi-trash"></i>
                  </a>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <?php else: ?>
          <div class="text-center py-5 text-muted">
            <i class="bi bi-archive" style="font-size:48px;opacity:0.3;display:block;margin-bottom:12px;"></i>
            <p>Belum ada arsip jadwal.</p>
            <small>Gunakan form di sebelah kiri untuk menyimpan arsip pertama.</small>
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