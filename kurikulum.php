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

$id_kelas_terpilih = isset($_GET['id_kelas']) ? (int)$_GET['id_kelas'] : (count($kelas_list) ? $kelas_list[0]['id_kelas'] : 0);

$semua_mapel = [];
$q = mysqli_query($conn, "SELECT * FROM pelajaran ORDER BY pelajaran ASC");
while ($r = mysqli_fetch_assoc($q)) $semua_mapel[] = $r;

// Mapel yang sudah dicentang untuk kelas terpilih
$mapel_terpilih = [];
if ($id_kelas_terpilih) {
    $q = mysqli_query($conn, "SELECT id_pelajaran FROM kelas_pelajaran WHERE id_kelas=$id_kelas_terpilih");
    while ($r = mysqli_fetch_assoc($q)) $mapel_terpilih[] = $r['id_pelajaran'];
}

// Hitung jumlah mapel sudah diatur per kelas (untuk badge di list kelas)
$jumlah_mapel_per_kelas = [];
$q = mysqli_query($conn, "SELECT id_kelas, COUNT(*) as total FROM kelas_pelajaran GROUP BY id_kelas");
while ($r = mysqli_fetch_assoc($q)) $jumlah_mapel_per_kelas[$r['id_kelas']] = $r['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kurikulum - SMAN 2 Toraja Utara</title>
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
    .kelas-item { display: block; padding: 10px 14px; border-radius: 8px; color: #374151; text-decoration: none; font-size: 13.5px; margin-bottom: 4px; }
    .kelas-item:hover { background: #f3f4f6; color: #1a3c5e; }
    .kelas-item.active { background: #1a3c5e; color: #fff; }
    .kelas-item .badge { float: right; }
    .mapel-check { padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 8px; cursor: pointer; }
    .mapel-check:hover { background: #f9fafb; }
    .mapel-check input { margin-right: 10px; }
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
  <a href="kurikulum.php" class="nav-link active"><i class="bi bi-journal-bookmark"></i> Kurikulum</a>
  <div class="nav-label">Penjadwalan</div>
  <a href="generate.php" class="nav-link"><i class="bi bi-gear-wide-connected"></i> Generate Jadwal</a>
  <a href="jadwal.php" class="nav-link"><i class="bi bi-table"></i> Lihat Jadwal</a>
  <a href="arsip.php" class="nav-link"><i class="bi bi-archive"></i> Arsip Jadwal</a>
  <div class="nav-label">Akun</div>
  <a href="manajemen_akun.php" class="nav-link"><i class="bi bi-people"></i> Manajemen Akun</a>
  <div class="nav-label">Keluar</div>
  <a href="login.php?logout=1" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
</div>

<div class="main-content">
  <div class="topbar">
    <h5><i class="bi bi-journal-bookmark me-2"></i>Kurikulum - Mapel per Kelas</h5>
    <a href="login.php?logout=1" class="admin-link text-muted text-decoration-none" style="font-size:13px;" title="Klik untuk logout" onclick="return confirm('Yakin ingin logout?');"><i class="bi bi-person-circle me-1"></i>Admin</a>
  </div>

  <div class="p-4">
    <?php if (isset($_GET['status']) && $_GET['status']==='sukses'): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <i class="bi bi-check-circle me-2"></i>Mapel untuk kelas ini berhasil disimpan.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (empty($kelas_list)): ?>
    <div class="alert alert-warning">Belum ada data kelas pada semester aktif.</div>
    <?php else: ?>
    <div class="row g-3">
      <!-- List kelas -->
      <div class="col-md-3">
        <div class="bg-white rounded-3 border p-3">
          <h6 class="fw-semibold mb-3" style="color:#1a3c5e;font-size:13px;">PILIH KELAS</h6>
          <div style="max-height:600px; overflow-y:auto;">
            <?php foreach ($kelas_list as $k): ?>
            <a href="kurikulum.php?id_kelas=<?= $k['id_kelas'] ?>"
               class="kelas-item <?= $k['id_kelas'] == $id_kelas_terpilih ? 'active' : '' ?>">
              Kelas <?= htmlspecialchars($k['kelas']) ?>
              <span class="badge <?= isset($jumlah_mapel_per_kelas[$k['id_kelas']]) ? 'bg-success' : 'bg-secondary' ?>">
                <?= $jumlah_mapel_per_kelas[$k['id_kelas']] ?? 0 ?>
              </span>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Form centang mapel -->
      <div class="col-md-9">
        <div class="bg-white rounded-3 border p-4">
          <?php
            $kelas_now = array_values(array_filter($kelas_list, fn($k) => $k['id_kelas'] == $id_kelas_terpilih))[0] ?? null;
          ?>
          <h6 class="fw-semibold mb-1" style="color:#1a3c5e;">
            Mata Pelajaran untuk Kelas <?= $kelas_now ? htmlspecialchars($kelas_now['kelas']) : '-' ?>
          </h6>
          <p class="text-muted mb-3" style="font-size:13px;">
            Centang mapel yang diajarkan di kelas ini. Total slot waktu tersedia per minggu perlu dicek di menu Jam Pelajaran agar tidak melebihi kapasitas.
          </p>

          <form action="simpan_kurikulum.php" method="POST">
            <input type="hidden" name="id_kelas" value="<?= $id_kelas_terpilih ?>">
            <div class="row g-2 mb-3">
              <?php foreach ($semua_mapel as $m): ?>
              <div class="col-md-6">
                <label class="mapel-check d-flex align-items-center">
                  <input type="checkbox" name="mapel[]" value="<?= $m['id_pelajaran'] ?>"
                    <?= in_array($m['id_pelajaran'], $mapel_terpilih) ? 'checked' : '' ?>>
                  <span><?= htmlspecialchars($m['pelajaran']) ?></span>
                </label>
              </div>
              <?php endforeach; ?>
            </div>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-2"></i>Simpan Mapel Kelas Ini
              </button>
              <span class="text-muted align-self-center" style="font-size:12px;">
                Terpilih: <span id="counter"><?= count($mapel_terpilih) ?></span> mapel
              </span>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('input[name="mapel[]"]').forEach(cb => {
  cb.addEventListener('change', () => {
    document.getElementById('counter').textContent =
      document.querySelectorAll('input[name="mapel[]"]:checked').length;
  });
});
</script>
</body>
</html>