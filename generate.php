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
$semester_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM semester WHERE status='1' LIMIT 1"));
$total_guru     = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM guru"));
$total_kelas    = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM kelas WHERE id_semester=".($semester_aktif['id_semester'] ?? 0)));
$total_mapel    = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM pelajaran"));
$total_jam      = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM jam"));
$total_ruang    = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM ruang_kelas"));
$siap = $semester_aktif && $total_guru > 0 && $total_kelas > 0 && $total_mapel > 0 && $total_jam > 0 && $total_ruang > 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Generate Jadwal - SMAN 2 Toraja Utara</title>
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
    .step-circle { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0; }
    .log-box { background: #0f172a; color: #94a3b8; font-family: monospace; font-size: 12px; padding: 16px; border-radius: 8px; height: 200px; overflow-y: auto; }
    .log-success { color: #4ade80; }
    .log-info    { color: #60a5fa; }
    .log-warning { color: #facc15; }
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
  <a href="generate.php" class="nav-link active"><i class="bi bi-gear-wide-connected"></i> Generate Jadwal</a>
  <a href="jadwal.php" class="nav-link"><i class="bi bi-table"></i> Lihat Jadwal</a>
        <a href="arsip.php" class="nav-link"><i class="bi bi-archive"></i> Arsip Jadwal</a>
  <div class="nav-label">Akun</div>
  <a href="manajemen_akun.php" class="nav-link"><i class="bi bi-people"></i> Manajemen Akun</a>
  <div class="nav-label">Keluar</div>
  <a href="login.php?logout=1" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
</div>

<div class="main-content">
  <div class="topbar">
    <h5><i class="bi bi-gear-wide-connected me-2"></i>Generate Jadwal (Algoritma Genetika)</h5>
    <a href="login.php?logout=1" class="admin-link text-muted text-decoration-none" style="font-size:13px;" title="Klik untuk logout" onclick="return confirm('Yakin ingin logout?');"><i class="bi bi-person-circle me-1"></i>Admin</a>
  </div>

  <div class="p-4">
    <div class="row g-3">
      <div class="col-md-12">

        <?php if(!$siap): ?>
        <div class="alert alert-warning mb-3">
          <i class="bi bi-exclamation-triangle me-2"></i>
          <strong>Data belum lengkap!</strong>
          <ul class="mb-0 mt-2" style="font-size:13px;">
            <?php if(!$semester_aktif): ?><li>Semester aktif belum diset</li><?php endif; ?>
            <?php if(!$total_guru): ?><li>Data guru kosong</li><?php endif; ?>
            <?php if(!$total_kelas): ?><li>Data kelas kosong</li><?php endif; ?>
            <?php if(!$total_mapel): ?><li>Data mata pelajaran kosong</li><?php endif; ?>
            <?php if(!$total_jam): ?><li>Data jam pelajaran kosong</li><?php endif; ?>
            <?php if(!$total_ruang): ?><li>Data ruang kelas kosong</li><?php endif; ?>
          </ul>
        </div>
        <?php endif; ?>

        <!-- Parameter -->
        <div class="bg-white rounded-3 border p-4 mb-3">
          <h6 class="fw-semibold mb-4" style="color:#1a3c5e;">
            <i class="bi bi-sliders me-2"></i>Parameter Algoritma Genetika
          </h6>
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label fw-semibold" style="font-size:13px;">Ukuran Populasi</label>
              <input type="number" id="popSize" class="form-control" value="50" min="10" max="200">
              <div class="form-text">Jumlah kromosom per generasi</div>
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold" style="font-size:13px;">Jumlah Generasi</label>
              <input type="number" id="maxGen" class="form-control" value="100" min="10" max="500">
              <div class="form-text">Maksimal iterasi evolusi</div>
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold" style="font-size:13px;">Probabilitas Crossover</label>
              <input type="number" id="crossRate" class="form-control" value="0.8" min="0.1" max="1.0" step="0.1">
              <div class="form-text">Standar: 0.8</div>
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold" style="font-size:13px;">Probabilitas Mutasi</label>
              <input type="number" id="mutRate" class="form-control" value="0.1" min="0.01" max="0.5" step="0.01">
              <div class="form-text">Standar: 0.1</div>
            </div>
          </div>
          <div class="mt-4">
            <button id="btnGenerate" class="btn btn-primary w-100" <?= !$siap ? 'disabled' : '' ?>>
              <i class="bi bi-play-fill me-2"></i>Mulai Generate Jadwal
            </button>
          </div>
        </div>

        <!-- Progress -->
        <div class="bg-white rounded-3 border p-4" id="progressSection" style="display:none;">
          <h6 class="fw-semibold mb-3" style="color:#1a3c5e;">
            <i class="bi bi-activity me-2"></i>Proses Generate
          </h6>
          <div class="d-flex justify-content-between mb-1" style="font-size:13px;">
            <span id="progressLabel">Memulai...</span>
            <span id="progressPct">0%</span>
          </div>
          <div class="progress mb-3" style="height:10px;">
            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width:0%;transition:width 0.3s;"></div>
          </div>
          <div class="log-box" id="logBox">
            <div class="log-info">[ Sistem ] Siap memulai proses...</div>
          </div>
          <div class="row g-2 mt-3" id="fitnessResult" style="display:none;">
            <div class="col-4">
              <div class="p-3 rounded-3 text-center" style="background:#f0fdf4;">
                <div style="font-size:11px;color:#6b7280;">Generasi</div>
                <div style="font-size:20px;font-weight:700;color:#16a34a;" id="resGenerasi">-</div>
              </div>
            </div>
            <div class="col-4">
              <div class="p-3 rounded-3 text-center" style="background:#f0f9ff;">
                <div style="font-size:11px;color:#6b7280;">Fitness Terbaik</div>
                <div style="font-size:20px;font-weight:700;color:#1a3c5e;" id="resFitness">-</div>
              </div>
            </div>
            <div class="col-4">
              <div class="p-3 rounded-3 text-center" style="background:#fff7ed;">
                <div style="font-size:11px;color:#6b7280;">Bentrok</div>
                <div style="font-size:20px;font-weight:700;color:#ea580c;" id="resBentrok">-</div>
              </div>
            </div>
          </div>
          <div id="btnLihatJadwal" style="display:none;" class="mt-3">
            <a href="jadwal.php" class="btn btn-success w-100">
              <i class="bi bi-table me-2"></i>Lihat Hasil Jadwal
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('btnGenerate').addEventListener('click', function() {
  const popSize   = document.getElementById('popSize').value;
  const maxGen    = document.getElementById('maxGen').value;
  const crossRate = document.getElementById('crossRate').value;
  const mutRate   = document.getElementById('mutRate').value;

  document.getElementById('progressSection').style.display = 'block';
  this.disabled = true;
  this.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Sedang Proses...';

  addLog('info', '[ GA ] Memulai Algoritma Genetika...');
  addLog('info', `[ GA ] Populasi: ${popSize} | Generasi: ${maxGen} | Cr: ${crossRate} | Mr: ${mutRate}`);
  updateProgress(10, 'Menginisialisasi populasi awal...');

  const formData = new FormData();
  formData.append('pop_size',   popSize);
  formData.append('max_gen',   maxGen);
  formData.append('cross_rate', crossRate);
  formData.append('mut_rate',  mutRate);

  fetch('algoritma_ga.php', { method: 'POST', body: formData })
  .then(res => res.json())
  .then(data => {
    if(data.success) {
      updateProgress(100, 'Generate selesai!');
      addLog('success', `[ GA ] Selesai di generasi ke-${data.generasi}`);
      addLog('success', `[ GA ] Fitness terbaik: ${data.fitness.toFixed(4)}`);
      addLog('success', `[ GA ] Jumlah bentrok: ${data.bentrok}`);
      addLog('success', '[ GA ] Jadwal berhasil disimpan ke database!');

      document.getElementById('fitnessResult').style.display = 'flex';
      document.getElementById('resGenerasi').textContent = data.generasi;
      document.getElementById('resFitness').textContent  = data.fitness.toFixed(4);
      document.getElementById('resBentrok').textContent  = data.bentrok;
      document.getElementById('btnLihatJadwal').style.display = 'block';
      document.getElementById('btnGenerate').innerHTML = '<i class="bi bi-check-circle me-2"></i>Generate Selesai';
    } else {
      addLog('warning', '[ ERROR ] ' + (data.message || 'Terjadi kesalahan'));
      document.getElementById('btnGenerate').disabled = false;
      document.getElementById('btnGenerate').innerHTML = '<i class="bi bi-play-fill me-2"></i>Mulai Generate Jadwal';
    }
  })
  .catch(err => {
    addLog('warning', '[ ERROR ] Gagal: ' + err);
    document.getElementById('btnGenerate').disabled = false;
    document.getElementById('btnGenerate').innerHTML = '<i class="bi bi-play-fill me-2"></i>Mulai Generate Jadwal';
  });
});

function updateProgress(pct, label) {
  document.getElementById('progressBar').style.width = pct + '%';
  document.getElementById('progressPct').textContent = pct + '%';
  document.getElementById('progressLabel').textContent = label;
}

function addLog(type, msg) {
  const box = document.getElementById('logBox');
  const d = document.createElement('div');
  d.className = 'log-' + type;
  d.textContent = msg;
  box.appendChild(d);
  box.scrollTop = box.scrollHeight;
}
</script>
</body>
</html>