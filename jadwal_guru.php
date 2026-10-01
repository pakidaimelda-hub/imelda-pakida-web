<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    header('Location: login.php');
    exit;
}

$id_guru = $_SESSION['id_guru'];

$jadwal = mysqli_query($conn, "
    SELECT j.hari, jam.mulai, jam.akhir, p.pelajaran, k.kelas
    FROM jadwal j
    JOIN jam ON j.id_jam = jam.id_jam
    JOIN pelajaran p ON j.id_pelajaran = p.id_pelajaran
    JOIN kelas k ON j.id_kelas = k.id_kelas
    WHERE j.id_guru = $id_guru
    ORDER BY FIELD(j.hari,'Senin','Selasa','Rabu','Kamis','Jumat'), jam.mulai
");

if (!$jadwal) {
    die("Query error: " . mysqli_error($conn));
}

$hari_list = ['Senin','Selasa','Rabu','Kamis','Jumat'];
$data = [];
while ($r = mysqli_fetch_assoc($jadwal)) {
    $data[ucfirst($r['hari'])][] = $r;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jadwal Saya - <?= $_SESSION['nama'] ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root { --sidebar-bg: #1a3c5e; --sidebar-width: 240px; --accent: #4fc3f7; }
    body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }

    /* SIDEBAR */
    .sidebar { width: var(--sidebar-width); background: var(--sidebar-bg); min-height: 100vh; position: fixed; top: 0; left: 0; z-index: 100; }
    .sidebar-brand { padding: 20px 16px; border-bottom: 1px solid rgba(255,255,255,0.1); }
    .sidebar-brand h6 { color: #fff; font-size: 14px; font-weight: 600; margin: 0; }
    .sidebar-brand small { color: rgba(255,255,255,0.5); font-size: 11px; }
    .nav-label { padding: 16px 20px 4px; font-size: 10px; color: rgba(255,255,255,0.35); letter-spacing: 1px; text-transform: uppercase; }
    .sidebar .nav-link { color: rgba(255,255,255,0.7); padding: 10px 20px; font-size: 13.5px; display: flex; align-items: center; gap: 10px; border-left: 3px solid transparent; transition: all 0.15s; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,0.08); color: #fff; border-left-color: var(--accent); }

    /* MAIN */
    .main-content { margin-left: var(--sidebar-width); }
    .topbar { background: #fff; padding: 14px 24px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; }
    .topbar h5 { font-size: 16px; font-weight: 600; color: #1a3c5e; margin: 0; }

    /* CARD HARI */
    .hari-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 1.25rem; overflow: hidden; }
    .hari-header { background: #1a3c5e; color: #fff; padding: 10px 20px; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
    .hari-header .badge-count { background: var(--accent); color: #1a3c5e; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 20px; }

    .jadwal-item { display: flex; align-items: center; padding: 12px 20px; border-bottom: 1px solid #f3f4f6; gap: 16px; transition: background 0.1s; }
    .jadwal-item:last-child { border-bottom: none; }
    .jadwal-item:hover { background: #f9fafb; }

    .jam-box { background: #e8f4fd; color: #1a3c5e; border-radius: 8px; padding: 6px 12px; font-size: 12px; font-weight: 600; white-space: nowrap; min-width: 120px; text-align: center; }
    .mapel-name { font-size: 14px; font-weight: 600; color: #111827; flex: 1; }
    .kelas-badge { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; border-radius: 6px; padding: 4px 10px; font-size: 12px; font-weight: 600; }

    .empty-state { text-align: center; padding: 3rem; color: #9ca3af; }
    .empty-state i { font-size: 48px; margin-bottom: 1rem; display: block; }
  </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <div class="sidebar-brand">
    <h6><i class="bi bi-calendar3 me-2"></i>Sistem Jadwal</h6>
    <small>SMAN 2 Toraja Utara</small>
  </div>
  <div class="nav-label">Menu</div>
  <a href="jadwal_guru.php" class="nav-link active"><i class="bi bi-table"></i> Jadwal Saya</a>
  <div class="nav-label">Akun</div>
  <a href="login.php?logout=1" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
</div>

<!-- MAIN -->
<div class="main-content">
  <div class="topbar">
    <h5><i class="bi bi-table me-2"></i>Jadwal Mengajar</h5>
    <span class="text-muted" style="font-size:13px;">
      <i class="bi bi-person-circle me-1"></i><?= $_SESSION['nama'] ?>
    </span>
  </div>

  <div class="p-4">

    <?php if (empty($data)): ?>
    <div class="hari-card">
      <div class="empty-state">
        <i class="bi bi-calendar-x"></i>
        <p class="mb-0">Belum ada jadwal yang di-generate untuk kamu.</p>
      </div>
    </div>

    <?php else: ?>
    <?php foreach ($hari_list as $hari): ?>
      <?php if (!isset($data[$hari])) continue; ?>
      <div class="hari-card">
        <div class="hari-header">
          <i class="bi bi-calendar-day"></i>
          <?= $hari ?>
          <span class="badge-count"><?= count($data[$hari]) ?> sesi</span>
        </div>
        <?php foreach ($data[$hari] as $r): ?>
        <div class="jadwal-item">
          <div class="jam-box"><i class="bi bi-clock me-1"></i><?= $r['mulai'] ?> - <?= $r['akhir'] ?></div>
          <div class="mapel-name"><i class="bi bi-book me-2 text-primary"></i><?= $r['pelajaran'] ?></div>
          <div class="kelas-badge"><i class="bi bi-mortarboard me-1"></i><?= $r['kelas'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
    <?php endif; ?>

  </div>
</div>

</body>
</html>