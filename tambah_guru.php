<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
        integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.css">
</head>

<body>
    <br>
    <div class="row col-12">
        <div class="col-3">
            <div class="container">
                <?php include 'menu.php'; ?>
            </div>
        </div>
        <div class="col-9">
            <div class="container">
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title">Daftar Guru</h1>
                    </div>
                </div>
                <br>
                <form method="post" action="proses_tambah_guru.php">
                    <div class="form-group">
                        <label>Nama Guru</label>
                        <input type="text" class="form-control" placeholder="Nama Guru" name="nama">
                    </div>
                    <div class="form-group">
                        <label>Jenis Kelamin</label>
                        <select name="jk" class="form-control"><?php include 'koneksi.php'; ?>
<?php
$daftar_pelajaran = mysqli_query($conn, "SELECT * FROM pelajaran ORDER BY pelajaran ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Guru - SMAN 2 Toraja Utara</title>
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
    .mapel-box { max-height: 220px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px; padding: 12px; }
    .mapel-box .form-check { margin-bottom: 6px; }
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
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
  <div class="topbar">
    <h5><i class="bi bi-person-plus me-2"></i>Tambah Data Guru</h5>
    <a href="guru.php" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
  </div>

  <div class="p-4">
    <div class="bg-white rounded-3 border p-4" style="max-width:600px;">
      <h6 class="fw-semibold mb-4" style="color:#1a3c5e;">
        <i class="bi bi-person-badge me-2"></i>Tambah Guru Baru
      </h6>

      <form action="proses_tambah_guru.php" method="POST">

        <div class="mb-3">
          <label class="form-label fw-semibold" style="font-size:13px;">Nama Lengkap <span class="text-danger">*</span></label>
          <input type="text" name="nama" class="form-control" placeholder="Nama Guru" required>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold" style="font-size:13px;">Jenis Kelamin <span class="text-danger">*</span></label>
          <select name="jk" class="form-select" required>
            <option value="laki-laki">Laki-laki</option>
            <option value="perempuan">Perempuan</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold" style="font-size:13px;">NIP</label>
          <input type="text" name="nip" class="form-control" placeholder="Nomor Induk Pegawai">
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold" style="font-size:13px;">Mata Pelajaran yang Diajar</label>
          <div class="mapel-box">
            <?php if(mysqli_num_rows($daftar_pelajaran) == 0): ?>
              <p class="text-muted mb-0" style="font-size:13px;">Belum ada data mata pelajaran. Tambahkan dulu di menu Mata Pelajaran.</p>
            <?php else: ?>
              <?php while($p = mysqli_fetch_assoc($daftar_pelajaran)): ?>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="pelajaran[]"
                         value="<?= $p['id_pelajaran'] ?>" id="mapel_<?= $p['id_pelajaran'] ?>">
                  <label class="form-check-label" for="mapel_<?= $p['id_pelajaran'] ?>" style="font-size:13.5px;">
                    <?= htmlspecialchars($p['pelajaran']) ?>
                  </label>
                </div>
              <?php endwhile; ?>
            <?php endif; ?>
          </div>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-save me-1"></i>Simpan
          </button>
          <a href="guru.php" class="btn btn-secondary btn-sm">Batal</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>