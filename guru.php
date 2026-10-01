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
$data_guru = mysqli_query($conn, "SELECT * FROM guru ORDER BY id_guru ASC");
$total_guru = mysqli_num_rows($data_guru);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Guru - SMAN 2 Toraja Utara</title>
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
    .avatar { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; flex-shrink: 0; }
    .avatar-laki { background: #dbeafe; color: #1d4ed8; }
    .avatar-perempuan { background: #fce7f3; color: #be185d; }
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
  <a href="guru.php" class="nav-link active"><i class="bi bi-person-badge"></i> Guru</a>
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
    <h5><i class="bi bi-person-badge me-2"></i>Data Guru</h5>
    <a href="login.php?logout=1" class="admin-link text-muted text-decoration-none" style="font-size:13px;" title="Klik untuk logout" onclick="return confirm('Yakin ingin logout?');"><i class="bi bi-person-circle me-1"></i>Admin</a>
  </div>

  <div class="p-4">

    <!-- Alert -->
    <?php if(isset($_GET['status'])): ?>
      <?php if($_GET['status'] == 'tambah'): ?>
        <div class="alert alert-success alert-dismissible fade show">
          <i class="bi bi-check-circle me-2"></i>Data guru berhasil ditambahkan!
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php elseif($_GET['status'] == 'edit'): ?>
        <div class="alert alert-info alert-dismissible fade show">
          <i class="bi bi-pencil me-2"></i>Data guru berhasil diubah!
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php elseif($_GET['status'] == 'hapus'): ?>
        <div class="alert alert-warning alert-dismissible fade show">
          <i class="bi bi-trash me-2"></i>Data guru berhasil dihapus!
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <!-- Card utama -->
    <div class="bg-white rounded-3 border p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h6 class="fw-semibold mb-1" style="color:#1a3c5e;">Daftar Guru</h6>
          <small class="text-muted">Total: <?= $total_guru ?> guru terdaftar</small>
        </div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
          <i class="bi bi-plus-lg me-1"></i>Tambah Guru
        </button>
      </div>

      <?php if($total_guru > 0): ?>
      <!-- Search -->
      <div class="mb-3">
        <input type="text" id="searchGuru" class="form-control form-control-sm"
               placeholder="Cari nama guru..." style="max-width:280px;">
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle" id="tabelGuru" style="font-size:14px;">
          <thead style="background:#f8fafc;">
            <tr>
              <th style="width:50px;" class="text-center">No</th>
              <th>Nama Guru</th>
              <th style="width:120px;" class="text-center">Jenis Kelamin</th>
              <th>NIP</th>
              <th style="width:120px;" class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; while($g = mysqli_fetch_assoc($data_guru)): ?>
            <?php
              $inisial = strtoupper(substr($g['nama'], 0, 1));
              $avatar_class = ($g['jk'] == 'laki-laki') ? 'avatar-laki' : 'avatar-perempuan';
            ?>
            <tr>
              <td class="text-center text-muted"><?= $no++ ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar <?= $avatar_class ?>"><?= $inisial ?></div>
                  <div>
                    <div style="font-weight:500;"><?= htmlspecialchars($g['nama']) ?></div>
                    <div style="font-size:11px;color:#9ca3af;">ID: <?= $g['id_guru'] ?></div>
                  </div>
                </div>
              </td>
              <td class="text-center">
                <?php if($g['jk'] == 'laki-laki'): ?>
                  <span class="badge" style="background:#dbeafe;color:#1d4ed8;">
                    <i class="bi bi-gender-male me-1"></i>Laki-laki
                  </span>
                <?php else: ?>
                  <span class="badge" style="background:#fce7f3;color:#be185d;">
                    <i class="bi bi-gender-female me-1"></i>Perempuan
                  </span>
                <?php endif; ?>
              </td>
              <td style="color:#6b7280;font-size:13px;"><?= htmlspecialchars($g['nip']) ?></td>
              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <a href="edit_guru.php?id=<?= $g['id_guru'] ?>"
                     class="btn btn-sm btn-outline-primary" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="hapus_guru.php?id=<?= $g['id_guru'] ?>"
                     class="btn btn-sm btn-outline-danger" title="Hapus"
                     onclick="return confirm('Hapus guru <?= htmlspecialchars($g['nama']) ?>?')">
                    <i class="bi bi-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-person-badge" style="font-size:48px;opacity:0.3;"></i>
        <p class="mt-3">Belum ada data guru.</p>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
          <i class="bi bi-plus-lg me-1"></i>Tambah Guru Pertama
        </button>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- MODAL TAMBAH GURU -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:#1a3c5e;">
        <h6 class="modal-title text-white"><i class="bi bi-plus-circle me-2"></i>Tambah Guru</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="proses_tambah_guru.php" method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control" placeholder="Nama lengkap guru" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Jenis Kelamin <span class="text-danger">*</span></label>
            <select name="jk" class="form-select" required>
              <option value="">-- Pilih --</option>
              <option value="laki-laki">Laki-laki</option>
              <option value="perempuan">Perempuan</option>
            </select>
          </div>
        <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">NIP</label>
            <input type="text" name="nip" class="form-control" placeholder="Nomor Induk Pegawai">
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
// Fitur pencarian
document.getElementById('searchGuru').addEventListener('keyup', function() {
  const keyword = this.value.toLowerCase();
  const rows = document.querySelectorAll('#tabelGuru tbody tr');
  rows.forEach(row => {
    const nama = row.cells[1].textContent.toLowerCase();
    row.style.display = nama.includes(keyword) ? '' : 'none';
  });
});
</script>
</body>
</html>