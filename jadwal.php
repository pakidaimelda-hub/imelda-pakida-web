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
include 'koneksi.php';

$semester_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM semester WHERE status='1' LIMIT 1"));

$kelas_list = [];
if ($semester_aktif) {
    $q = mysqli_query($conn, "SELECT * FROM kelas WHERE id_semester=" . (int)$semester_aktif['id_semester'] . " ORDER BY kelas ASC");
    while ($r = mysqli_fetch_assoc($q)) $kelas_list[] = $r;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jadwal Kelas - SMAN 2 Toraja Utara</title>
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
    .kelas-card { background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; transition: box-shadow 0.2s, transform 0.2s; }
    .kelas-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.10); transform: translateY(-2px); }
    .kelas-icon { width: 48px; height: 48px; border-radius: 12px; background: #dbeafe; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #1a3c5e; flex-shrink: 0; }
    .empty-state { text-align: center; padding: 4rem 2rem; color: #9ca3af; }
    .empty-state i { font-size: 56px; margin-bottom: 1rem; display: block; color: #d1d5db; }
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
  <a href="jadwal.php" class="nav-link active"><i class="bi bi-table"></i> Lihat Jadwal</a>
  <div class="nav-label">Akun</div>
  <a href="manajemen_akun.php" class="nav-link"><i class="bi bi-people"></i> Manajemen Akun</a>
  <div class="nav-label">Keluar</div>
  <a href="login.php?logout=1" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
</div>

<div class="main-content">
  <div class="topbar">
    <h5><i class="bi bi-table me-2"></i>Daftar Jadwal Kelas</h5>
    <a href="login.php?logout=1" class="admin-link text-muted text-decoration-none" style="font-size:13px;" title="Klik untuk logout" onclick="return confirm('Yakin ingin logout?');"><i class="bi bi-person-circle me-1"></i>Admin</a>
  </div>

  <div class="p-4">

    <?php if (isset($_GET['status'])): ?>
      <?php if ($_GET['status'] === 'hapus'): ?>
      <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>Jadwal kelas berhasil dihapus.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php elseif ($_GET['status'] === 'error'): ?>
      <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-x-circle me-2"></i>Gagal menghapus jadwal.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>
    <?php endif; ?>

    <?php if ($semester_aktif): ?>
    <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
      <div style="background:#e8f4fd;color:#1a3c5e;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;">
        <i class="bi bi-calendar-check me-2"></i>Semester Aktif:
        <?= ucfirst($semester_aktif['semester']) . ' ' . $semester_aktif['tahun'] ?>
      </div>
      <div style="background:#f0fdf4;color:#166534;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;">
        <i class="bi bi-mortarboard me-2"></i><?= count($kelas_list) ?> Kelas
      </div>
      <a href="generate.php" class="btn btn-primary btn-sm ms-auto">
        <i class="bi bi-gear-wide-connected me-2"></i>Generate Ulang Jadwal
      </a>
    </div>
    <?php endif; ?>

    <?php if (empty($kelas_list)): ?>
    <div class="bg-white rounded-3 border">
      <div class="empty-state">
        <i class="bi bi-calendar-x"></i>
        <h6 class="text-secondary mb-1">Belum Ada Data Kelas</h6>
        <p class="mb-3" style="font-size:13px;">Tambahkan kelas terlebih dahulu atau aktifkan semester.</p>
        <a href="kelas.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-2"></i>Tambah Kelas</a>
      </div>
    </div>

    <?php else: ?>
    <div class="row g-3">
      <?php foreach ($kelas_list as $kelas): ?>
      <?php
        $id_k = (int)$kelas['id_kelas'];
        $cnt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM jadwal WHERE id_kelas=$id_k"));
        $total_jadwal = (int)$cnt['total'];
      ?>
      <div class="col-md-4 col-lg-3">
        <div class="kelas-card p-3">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="kelas-icon">
              <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div>
              <div style="font-size:16px;font-weight:700;color:#1a3c5e;">Kelas <?= htmlspecialchars($kelas['kelas']) ?></div>
              <div style="font-size:12px;color:#6b7280;">
                <?= $total_jadwal > 0 ? $total_jadwal . ' slot jadwal' : '<span class="text-warning">Belum ada jadwal</span>' ?>
              </div>
            </div>
          </div>
          <div class="d-flex gap-2">
            <a href="detail_jadwal.php?id_kelas=<?= $kelas['id_kelas'] ?>" class="btn btn-primary btn-sm flex-fill">
              <i class="bi bi-eye me-1"></i>Detail
            </a>
            <button type="button"
              class="btn btn-outline-danger btn-sm"
              onclick="konfirmasiHapus(<?= $kelas['id_kelas'] ?>, '<?= htmlspecialchars($kelas['kelas']) ?>')"
              title="Hapus jadwal kelas ini">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h6 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Konfirmasi Hapus</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-1" style="font-size:14px;">Hapus semua jadwal untuk <strong id="namaKelas"></strong>?</p>
        <p class="text-muted mb-0" style="font-size:12px;">Tindakan ini tidak bisa dibatalkan.</p>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        <a href="#" id="btnHapusConfirm" class="btn btn-danger btn-sm">
          <i class="bi bi-trash me-1"></i>Ya, Hapus
        </a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function konfirmasiHapus(id_kelas, namaKelas) {
  document.getElementById('namaKelas').textContent = 'Kelas ' + namaKelas;
  document.getElementById('btnHapusConfirm').href = 'hapus_jadwal_kelas.php?id_kelas=' + id_kelas;
  new bootstrap.Modal(document.getElementById('modalHapus')).show();
}
</script>
</body>
</html>