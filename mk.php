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
$data_mk = mysqli_query($conn, "SELECT * FROM pelajaran ORDER BY id_pelajaran ASC");
$total_mk = mysqli_num_rows($data_mk);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mata Pelajaran - SMAN 2 Toraja Utara</title>
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
  <a href="mk.php" class="nav-link active"><i class="bi bi-book"></i> Mata Pelajaran</a>
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
    <h5><i class="bi bi-book me-2"></i>Mata Pelajaran</h5>
    <a href="login.php?logout=1" class="admin-link text-muted text-decoration-none" style="font-size:13px;" title="Klik untuk logout" onclick="return confirm('Yakin ingin logout?');"><i class="bi bi-person-circle me-1"></i>Admin</a>
  </div>

  <div class="p-4">

    <!-- Alert -->
    <?php if(isset($_GET['status'])): ?>
      <?php if($_GET['status'] == 'tambah'): ?>
        <div class="alert alert-success alert-dismissible fade show">
          <i class="bi bi-check-circle me-2"></i>Mata pelajaran berhasil ditambahkan!
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php elseif($_GET['status'] == 'edit'): ?>
        <div class="alert alert-info alert-dismissible fade show">
          <i class="bi bi-pencil me-2"></i>Mata pelajaran berhasil diubah!
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php elseif($_GET['status'] == 'hapus'): ?>
        <div class="alert alert-warning alert-dismissible fade show">
          <i class="bi bi-trash me-2"></i>Mata pelajaran berhasil dihapus!
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <div class="row g-3">
      <!-- Tabel -->
      <div class="col-md-12">
        <div class="bg-white rounded-3 border p-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
              <h6 class="fw-semibold mb-1" style="color:#1a3c5e;">Daftar Mata Pelajaran</h6>
              <small class="text-muted">Total: <?= $total_mk ?> mata pelajaran</small>
            </div>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
              <i class="bi bi-plus-lg me-1"></i>Tambah Mapel
            </button>
          </div>

          <!-- Search -->
          <div class="mb-3">
            <input type="text" id="searchMk" class="form-control form-control-sm"
                   placeholder="Cari mata pelajaran..." style="max-width:280px;">
          </div>

          <?php if($total_mk > 0): ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle" id="tabelMk" style="font-size:14px;">
              <thead style="background:#f8fafc;">
                <tr>
                  <th style="width:50px;" class="text-center">No</th>
                  <th>Nama Mata Pelajaran</th>
                  <th style="width:120px;" class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no = 1;
                $colors = ['#e8f4fd','#dcfce7','#fef9c3','#fce7f3','#f3e8ff','#fff7ed','#e0f2fe','#f0fdf4'];
                $text_colors = ['#1d4ed8','#16a34a','#ca8a04','#be185d','#7c3aed','#ea580c','#0369a1','#15803d'];
                mysqli_data_seek($data_mk, 0);
                $i = 0;
                while($mk = mysqli_fetch_assoc($data_mk)):
                  $ci = $i % count($colors);
                ?>
                <tr>
                  <td class="text-center text-muted"><?= $no++ ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span style="width:32px;height:32px;border-radius:8px;background:<?= $colors[$ci] ?>;color:<?= $text_colors[$ci] ?>;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;flex-shrink:0;">
                        <?= strtoupper(substr($mk['pelajaran'],0,2)) ?>
                      </span>
                      <span style="font-weight:500;"><?= htmlspecialchars($mk['pelajaran']) ?></span>
                    </div>
                  </td>
                  <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center">
                      <button class="btn btn-sm btn-outline-primary"
                              data-bs-toggle="modal"
                              data-bs-target="#modalEdit"
                              data-id="<?= $mk['id_pelajaran'] ?>"
                              data-nama="<?= htmlspecialchars($mk['pelajaran']) ?>"
                              title="Edit">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <a href="hapus_mk.php?id=<?= $mk['id_pelajaran'] ?>"
                         class="btn btn-sm btn-outline-danger"
                         onclick="return confirm('Hapus mata pelajaran <?= htmlspecialchars($mk['pelajaran']) ?>?')"
                         title="Hapus">
                        <i class="bi bi-trash"></i>
                      </a>
                    </div>
                  </td>
                </tr>
                <?php $i++; endwhile; ?>
              </tbody>
            </table>
          </div>
          <?php else: ?>
          <div class="text-center py-5 text-muted">
            <i class="bi bi-book" style="font-size:48px;opacity:0.3;"></i>
            <p class="mt-3">Belum ada mata pelajaran.</p>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
              <i class="bi bi-plus-lg me-1"></i>Tambah Sekarang
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
        <h6 class="modal-title text-white"><i class="bi bi-plus-circle me-2"></i>Tambah Mata Pelajaran</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="proses_tambah_pelajaran.php" method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Nama Mata Pelajaran <span class="text-danger">*</span></label>
            <input type="text" name="pelajaran" class="form-control"
                   placeholder="Contoh: Matematika, Fisika, Bahasa Indonesia..." required>
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

<!-- MODAL EDIT -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:#1a3c5e;">
        <h6 class="modal-title text-white"><i class="bi bi-pencil me-2"></i>Edit Mata Pelajaran</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="proses_edit_pelajaran.php" method="POST">
        <input type="hidden" name="id" id="editId">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Nama Mata Pelajaran <span class="text-danger">*</span></label>
            <input type="text" name="pelajaran" id="editNama" class="form-control" required>
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
// Isi data modal edit
document.getElementById('modalEdit').addEventListener('show.bs.modal', function(e) {
  const btn = e.relatedTarget;
  document.getElementById('editId').value = btn.dataset.id;
  document.getElementById('editNama').value = btn.dataset.nama;
});

// Pencarian
document.getElementById('searchMk').addEventListener('keyup', function() {
  const keyword = this.value.toLowerCase();
  document.querySelectorAll('#tabelMk tbody tr').forEach(row => {
    row.style.display = row.cells[1].textContent.toLowerCase().includes(keyword) ? '' : 'none';
  });
});
</script>
</body>
</html>