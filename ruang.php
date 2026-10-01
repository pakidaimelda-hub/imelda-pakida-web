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
$data_ruang = mysqli_query($conn, "SELECT * FROM ruang_kelas ORDER BY id_ruang ASC");
$total_ruang = mysqli_num_rows($data_ruang);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ruang Kelas - SMAN 2 Toraja Utara</title>
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
  <a href="ruang.php" class="nav-link active"><i class="bi bi-door-open"></i> Ruang Kelas</a>
  <a href="kurikulum.php" class="nav-link"><i class="bi bi-journal-bookmark"></i> Kurikulum</a>
  <div class="nav-label">Penjadwalan</div>
  <a href="generate.php" class="nav-link"><i class="bi bi-gear-wide-connected"></i> Generate Jadwal</a>
  <a href="jadwal.php" class="nav-link"><i class="bi bi-table"></i> Lihat Jadwal</a>
        <a href="arsip.php" class="nav-link"><i class="bi bi-archive"></i> Arsip Jadwal</a>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
  <div class="topbar">
    <h5><i class="bi bi-door-open me-2"></i>Ruang Kelas</h5>
    <a href="login.php?logout=1" class="admin-link text-muted text-decoration-none" style="font-size:13px;" title="Klik untuk logout" onclick="return confirm('Yakin ingin logout?');"><i class="bi bi-person-circle me-1"></i>Admin</a>
  </div>

  <div class="p-4">

    <!-- Alert sukses/error -->
    <?php if(isset($_GET['status'])): ?>
      <?php if($_GET['status'] == 'tambah'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle me-2"></i>Ruang kelas berhasil ditambahkan!
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php elseif($_GET['status'] == 'hapus'): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
          <i class="bi bi-trash me-2"></i>Ruang kelas berhasil dihapus!
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <!-- Card utama -->
    <div class="bg-white rounded-3 border p-4">

      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h6 class="fw-semibold mb-1" style="color:#1a3c5e;">Daftar Ruang Kelas</h6>
          <small class="text-muted">Total: <?= $total_ruang ?> ruang tersedia</small>
        </div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
          <i class="bi bi-plus-lg me-1"></i>Tambah Ruang
        </button>
      </div>

      <!-- Tabel -->
      <?php if($total_ruang > 0): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle" style="font-size:14px;">
          <thead style="background:#f8fafc;">
            <tr>
              <th style="width:50px;" class="text-center">No</th>
              <th>Nama Ruang</th>
              <th style="width:120px;" class="text-center">Kapasitas</th>
              <th style="width:120px;" class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; while($r = mysqli_fetch_assoc($data_ruang)): ?>
            <tr>
              <td class="text-center text-muted"><?= $no++ ?></td>
              <td>
                <i class="bi bi-door-closed me-2 text-primary"></i>
                <?= htmlspecialchars($r['nama_ruang']) ?>
              </td>
              <td class="text-center">
                <span class="badge bg-light text-dark border">
                  <?= $r['kapasitas'] ?> siswa
                </span>
              </td>
              <td class="text-center">
                <a href="hapus_ruang.php?id=<?= $r['id_ruang'] ?>"
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Hapus ruang <?= $r['nama_ruang'] ?>?')">
                  <i class="bi bi-trash"></i>
                </a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-door-open" style="font-size:48px;opacity:0.3;"></i>
        <p class="mt-3">Belum ada data ruang kelas.</p>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
          <i class="bi bi-plus-lg me-1"></i>Tambah Ruang Pertama
        </button>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<!-- MODAL TAMBAH RUANG -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:#1a3c5e;">
        <h6 class="modal-title text-white">
          <i class="bi bi-plus-circle me-2"></i>Tambah Ruang Kelas
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="tambah_ruang.php" method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Nama Ruang <span class="text-danger">*</span></label>
            <input type="text" name="nama_ruang" class="form-control"
                   placeholder="Contoh: Ruang 01, Lab IPA, Aula..." required>
            <div class="form-text">Masukkan nama ruang sesuai kondisi sekolah</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Kapasitas (siswa)</label>
            <input type="number" name="kapasitas" class="form-control" value="40" min="1" max="60">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-save me-1"></i>Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>