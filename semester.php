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
$data_semester = mysqli_query($conn, "SELECT * FROM semester ORDER BY id_semester DESC");
$total_semester = mysqli_num_rows($data_semester);
$semester_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM semester WHERE status='1' LIMIT 1"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Semester - SMAN 2 Toraja Utara</title>
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
    .semester-card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px 20px; transition: all 0.15s; }
    .semester-card.aktif { border-color: #22c55e; background: #f0fdf4; }
    .semester-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
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
  <a href="semester.php" class="nav-link active"><i class="bi bi-calendar-range"></i> Semester</a>
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
  <div class="nav-label">Akun</div>
  <a href="manajemen_akun.php" class="nav-link"><i class="bi bi-people"></i> Manajemen Akun</a>
  <div class="nav-label">Keluar</div>
  <a href="login.php?logout=1" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
  <div class="topbar">
    <h5><i class="bi bi-calendar-range me-2"></i>Semester</h5>
    <a href="login.php?logout=1" class="admin-link text-muted text-decoration-none" style="font-size:13px;" title="Klik untuk logout" onclick="return confirm('Yakin ingin logout?');"><i class="bi bi-person-circle me-1"></i>Admin</a>
  </div>

  <div class="p-4">

    <!-- Alert -->
    <?php if(isset($_GET['status'])): ?>
      <?php if($_GET['status'] == 'tambah'): ?>
        <div class="alert alert-success alert-dismissible fade show">
          <i class="bi bi-check-circle me-2"></i>Semester berhasil ditambahkan!
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php elseif($_GET['status'] == 'aktif'): ?>
        <div class="alert alert-success alert-dismissible fade show">
          <i class="bi bi-check-circle me-2"></i>Semester aktif berhasil diubah!
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php elseif($_GET['status'] == 'hapus'): ?>
        <div class="alert alert-warning alert-dismissible fade show">
          <i class="bi bi-trash me-2"></i>Semester berhasil dihapus!
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <div class="row g-3">
      <!-- Kiri: daftar semester -->
      <div class="col-md-8">
        <div class="bg-white rounded-3 border p-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
              <h6 class="fw-semibold mb-1" style="color:#1a3c5e;">Daftar Semester</h6>
              <small class="text-muted">Total: <?= $total_semester ?> semester</small>
            </div>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
              <i class="bi bi-plus-lg me-1"></i>Tambah Semester
            </button>
          </div>

          <?php if($total_semester > 0): ?>
          <div class="d-flex flex-column gap-3">
            <?php while($s = mysqli_fetch_assoc($data_semester)): ?>
            <div class="semester-card <?= $s['status']=='1' ? 'aktif' : '' ?>">
              <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                  <div style="width:44px;height:44px;border-radius:10px;background:<?= $s['status']=='1' ? '#dcfce7' : '#f3f4f6' ?>;color:<?= $s['status']=='1' ? '#16a34a' : '#6b7280' ?>;display:flex;align-items:center;justify-content:center;font-size:20px;">
                    <i class="bi bi-calendar<?= $s['status']=='1' ? '-check' : '' ?>"></i>
                  </div>
                  <div>
                    <div style="font-weight:600;font-size:14px;color:#1a3c5e;">
                      Semester <?= ucfirst($s['semester']) ?> - <?= $s['tahun'] ?>
                    </div>
                    <div style="font-size:12px;color:#6b7280;">
                      Tahun Ajaran <?= $s['tahun'] ?>
                    </div>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <?php if($s['status'] == '1'): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                  <?php else: ?>
                    <a href="proses_status_semester.php?id=<?= $s['id_semester'] ?>"
                       class="btn btn-sm btn-outline-success"
                       onclick="return confirm('Jadikan semester ini aktif?')">
                      <i class="bi bi-toggle-off me-1"></i>Aktifkan
                    </a>
                  <?php endif; ?>
                  <a href="hapus_semester.php?id=<?= $s['id_semester'] ?>"
                     class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('Hapus semester ini?')">
                    <i class="bi bi-trash"></i>
                  </a>
                </div>
              </div>
            </div>
            <?php endwhile; ?>
          </div>
          <?php else: ?>
          <div class="text-center py-5 text-muted">
            <i class="bi bi-calendar-range" style="font-size:48px;opacity:0.3;"></i>
            <p class="mt-3">Belum ada data semester.</p>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
              <i class="bi bi-plus-lg me-1"></i>Tambah Semester Pertama
            </button>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Kanan: info semester aktif -->
      <div class="col-md-4">
        <div class="bg-white rounded-3 border p-4">
          <h6 class="fw-semibold mb-3" style="color:#1a3c5e;"><i class="bi bi-star me-2"></i>Semester Aktif</h6>
          <?php if($semester_aktif): ?>
          <div class="p-3 rounded-3 mb-3" style="background:#f0fdf4;border:1px solid #86efac;">
            <div style="font-size:11px;color:#16a34a;font-weight:600;margin-bottom:4px;">SEDANG AKTIF</div>
            <div style="font-size:16px;font-weight:700;color:#1a3c5e;">
              <?= ucfirst($semester_aktif['semester']) ?>
            </div>
            <div style="font-size:13px;color:#6b7280;">Tahun Ajaran <?= $semester_aktif['tahun'] ?></div>
          </div>
          <?php else: ?>
          <div class="p-3 rounded-3 mb-3" style="background:#fef2f2;border:1px solid #fca5a5;">
            <div style="font-size:13px;color:#dc2626;">
              <i class="bi bi-exclamation-triangle me-1"></i>
              Belum ada semester aktif! Klik "Aktifkan" pada salah satu semester.
            </div>
          </div>
          <?php endif; ?>

          <div class="p-3 rounded-3" style="background:#fef9c3;font-size:12px;color:#713f12;line-height:1.8;">
            <i class="bi bi-lightbulb me-1"></i>
            <strong>Catatan:</strong><br>
            Hanya 1 semester yang bisa aktif dalam satu waktu. Mengaktifkan semester baru akan menonaktifkan yang lama.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:#1a3c5e;">
        <h6 class="modal-title text-white"><i class="bi bi-plus-circle me-2"></i>Tambah Semester</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="proses_tambah_semester.php" method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Tahun Ajaran <span class="text-danger">*</span></label>
            <input type="text" name="tahun" class="form-control"
                   placeholder="Contoh: 2024/2025" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Semester <span class="text-danger">*</span></label>
            <select name="semester" class="form-select" required>
              <option value="">-- Pilih --</option>
              <option value="ganjil">Ganjil</option>
              <option value="genap">Genap</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Status</label>
            <select name="status" class="form-select">
              <option value="0">Tidak Aktif</option>
              <option value="1">Aktif</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>