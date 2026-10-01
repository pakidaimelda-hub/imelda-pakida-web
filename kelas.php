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
$data_kelas = mysqli_query($conn, "
    SELECT k.*, s.tahun, s.semester 
    FROM kelas k 
    LEFT JOIN semester s ON k.id_semester = s.id_semester 
    ORDER BY k.id_kelas ASC
");
$total_kelas = mysqli_num_rows($data_kelas);
$data_semester = mysqli_query($conn, "SELECT * FROM semester ORDER BY id_semester DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelas - SMAN 2 Toraja Utara</title>
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
  <a href="kelas.php" class="nav-link active"><i class="bi bi-mortarboard"></i> Kelas</a>
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
    <h5><i class="bi bi-mortarboard me-2"></i>Data Kelas</h5>
    <a href="login.php?logout=1" class="admin-link text-muted text-decoration-none" style="font-size:13px;" title="Klik untuk logout" onclick="return confirm('Yakin ingin logout?');"><i class="bi bi-person-circle me-1"></i>Admin</a>
  </div>

  <div class="p-4">

    <?php if(isset($_GET['status'])): ?>
      <?php if($_GET['status'] == 'tambah'): ?>
        <div class="alert alert-success alert-dismissible fade show">
          <i class="bi bi-check-circle me-2"></i>Kelas berhasil ditambahkan!
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php elseif($_GET['status'] == 'hapus'): ?>
        <div class="alert alert-warning alert-dismissible fade show">
          <i class="bi bi-trash me-2"></i>Kelas berhasil dihapus!
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <div class="row g-3">
      <div class="col-md-12">
        <div class="bg-white rounded-3 border p-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
              <h6 class="fw-semibold mb-1" style="color:#1a3c5e;">Daftar Kelas</h6>
              <small class="text-muted">Total: <?= $total_kelas ?> kelas</small>
            </div>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
              <i class="bi bi-plus-lg me-1"></i>Tambah Kelas
            </button>
          </div>

          <div class="d-flex gap-2 mb-3">
            <input type="text" id="searchKelas" class="form-control form-control-sm"
                   placeholder="Cari nama kelas..." style="max-width:220px;">
            <select id="filterTingkat" class="form-select form-select-sm" style="max-width:160px;">
              <option value="">Semua Tingkat</option>
              <option value="X">Kelas X</option>
              <option value="XI">Kelas XI</option>
              <option value="XII">Kelas XII</option>
            </select>
          </div>

          <?php if($total_kelas > 0): ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle" id="tabelKelas" style="font-size:14px;">
              <thead style="background:#f8fafc;">
                <tr>
                  <th style="width:50px;" class="text-center">No</th>
                  <th>Nama Kelas</th>
                  <th class="text-center">Tingkat</th>
                  <th>Semester</th>
                  <th style="width:80px;" class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1; while($k = mysqli_fetch_assoc($data_kelas)):
                  $tingkat = '';
                  if(stripos($k['kelas'], 'XII') !== false) $tingkat = 'XII';
                  elseif(stripos($k['kelas'], 'XI') !== false) $tingkat = 'XI';
                  elseif(stripos($k['kelas'], 'X') !== false) $tingkat = 'X';
                  $tc = ['X'=>['bg'=>'#dbeafe','color'=>'#1d4ed8'],'XI'=>['bg'=>'#dcfce7','color'=>'#16a34a'],'XII'=>['bg'=>'#fce7f3','color'=>'#be185d']];
                  $c = $tc[$tingkat] ?? ['bg'=>'#f3f4f6','color'=>'#6b7280'];
                ?>
                <tr data-tingkat="<?= $tingkat ?>">
                  <td class="text-center text-muted"><?= $no++ ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span style="width:32px;height:32px;border-radius:8px;background:<?= $c['bg'] ?>;color:<?= $c['color'] ?>;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;"><?= $tingkat ?: '?' ?></span>
                      <span style="font-weight:500;"><?= htmlspecialchars($k['kelas']) ?></span>
                    </div>
                  </td>
                  <td class="text-center">
                    <span class="badge" style="background:<?= $c['bg'] ?>;color:<?= $c['color'] ?>;">Kelas <?= $tingkat ?: '-' ?></span>
                  </td>
                  <td style="font-size:13px;color:#6b7280;">
                    <?= $k['semester'] ? ucfirst($k['semester']).' '.$k['tahun'] : '-' ?>
                  </td>
                  <td class="text-center">
                    <a href="hapus_kelas.php?id=<?= $k['id_kelas'] ?>"
                       class="btn btn-sm btn-outline-danger"
                       onclick="return confirm('Hapus kelas <?= htmlspecialchars($k['kelas']) ?>?')">
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
            <i class="bi bi-mortarboard" style="font-size:48px;opacity:0.3;"></i>
            <p class="mt-3">Belum ada data kelas.</p>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
              <i class="bi bi-plus-lg me-1"></i>Tambah Kelas Pertama
            </button>
          </div>
          <?php endif; ?>
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
        <h6 class="modal-title text-white"><i class="bi bi-plus-circle me-2"></i>Tambah Kelas</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="proses_tambah_kelas.php" method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Nama Kelas <span class="text-danger">*</span></label>
            <input type="text" name="kelas" class="form-control"
                   placeholder="Contoh: X MIPA 1, XI IPS 2, XII MIPA 3" required>
            <div class="form-text">Format: Tingkat + Jurusan + Nomor</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Semester <span class="text-danger">*</span></label>
            <select name="id_semester" class="form-select" required>
              <option value="">-- Pilih Semester --</option>
              <?php
              mysqli_data_seek($data_semester, 0);
              while($s = mysqli_fetch_assoc($data_semester)): ?>
                <option value="<?= $s['id_semester'] ?>" <?= $s['status']=='1' ? 'selected' : '' ?>>
                  <?= ucfirst($s['semester']) ?> <?= $s['tahun'] ?> <?= $s['status']=='1' ? '(Aktif)' : '' ?>
                </option>
              <?php endwhile; ?>
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
<script>
document.getElementById('searchKelas').addEventListener('keyup', function() {
  const keyword = this.value.toLowerCase();
  document.querySelectorAll('#tabelKelas tbody tr').forEach(row => {
    row.style.display = row.cells[1].textContent.toLowerCase().includes(keyword) ? '' : 'none';
  });
});
document.getElementById('filterTingkat').addEventListener('change', function() {
  const val = this.value;
  document.querySelectorAll('#tabelKelas tbody tr').forEach(row => {
    row.style.display = (!val || row.dataset.tingkat === val) ? '' : 'none';
  });
});
</script>
</body>
</html>